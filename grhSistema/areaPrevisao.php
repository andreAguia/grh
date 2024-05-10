<?php

/**
 * Área de Aposentadoria
 *
 * By Alat
 */
# Reservado para o servidor logado
$idUsuario = null;

# Configuração
include ("_config.php");

# Permissão de Acesso
$acesso = Verifica::acesso($idUsuario, [1, 2, 12]);

if ($acesso) {

    # Conecta ao Banco de Dados
    $pessoal = new Pessoal();
    $intra = new Intra();
    $aposentadoria = new Aposentadoria();

    # Verifica a fase do programa
    $fase = get('fase', 'aguardeGeralPorLotacao');

    # pega o id (se tiver)
    $id = soNumeros(get('id'));

    # Verifica se veio menu grh e registra o acesso no log
    $grh = get('grh', false);
    if ($grh) {
        # Grava no log a atividade
        $atividade = "Visualizou a área de previsão de aposentadoria";
        $data = date("Y-m-d H:i:s");
        $intra->registraLog($idUsuario, $data, $atividade, null, null, 7);
    }

    # Começa uma nova página
    $page = new Page();
    $page->iniciaPagina();

    # Pega os parâmetros
    $parametroLotacao = post('parametroLotacao', get_session('parametroLotacao', $pessoal->get_idLotacao($intra->get_idServidor($idUsuario))));

    # Joga os parâmetros par as sessions
    set_session('parametroLotacao', $parametroLotacao);

    # Pega o Link (quando tem)
    $link = get("link");

    # Limita a página
    $grid = new Grid();
    $grid->abreColuna(12);

    # Cria um menu
    if ($fase <> "relatorio") {

        # Cabeçalho da Página
        AreaServidor::cabecalho();

        # Cria um menu
        $menu = new MenuBar();

        # Voltar
        $botaoVoltar = new Link("Voltar", "grh.php");
        $botaoVoltar->set_class('button');
        $botaoVoltar->set_title('Voltar a página anterior');
        $botaoVoltar->set_accessKey('V');
        $menu->add_link($botaoVoltar, "left");

        # Relatório   
        $imagem = new Imagem(PASTA_FIGURAS . 'print.png', null, 15, 15);
        $botaoRel = new Button();
        $botaoRel->set_imagem($imagem);
        $botaoRel->set_title("Relatório da Previsão de Aposentadoria");
        $botaoRel->set_url("?fase=relatorio");
        $botaoRel->set_target("_blank");
        $menu->add_link($botaoRel, "right");
        $menu->show();
    }

    $grid->fechaColuna();
    $grid->abreColuna(12);

    #######################################

    switch ($fase) {
        case "":
        case "aguardeGeralPorLotacao":

            br(4);
            aguarde();
            br();

            # Limita a tela
            $grid1 = new Grid("center");
            $grid1->abreColuna(5);
            p("Aguarde...", "center");
            $grid1->fechaColuna();
            $grid1->fechaGrid();

            loadPage('?fase=geralPorLotacao');
            break;

        #######################################

        case "geralPorLotacao" :

            # Formulário de Pesquisa
            $form = new Form('?fase=aguardeGeralPorLotacao');

            # Lotação
            $result = $pessoal->select('(SELECT idlotacao, concat(IFnull(tblotacao.DIR,"")," - ",IFnull(tblotacao.GER,"")," - ",IFnull(tblotacao.nome,"")) lotacao
                                              FROM tblotacao
                                             WHERE ativo) UNION (SELECT distinct DIR, DIR
                                              FROM tblotacao
                                             WHERE ativo)
                                          ORDER BY 2');
            array_unshift($result, array("Todos", 'Todas'));

            $controle = new Input('parametroLotacao', 'combo', 'Lotação:', 1);
            $controle->set_size(30);
            $controle->set_title('Filtra por Lotação');
            $controle->set_array($result);
            $controle->set_valor($parametroLotacao);
            $controle->set_onChange('formPadrao.submit();');
            $controle->set_linha(1);
            $controle->set_col(8);
            $form->add_item($controle);
            $form->show();

            # Exibe a lista
            $select = "SELECT tbservidor.idServidor,
                              tbservidor.idServidor,           
                              tbservidor.idServidor,           
                              tbservidor.idServidor,
                              tbservidor.idServidor,
                              tbservidor.idServidor,
                              tbservidor.idServidor,
                              tbservidor.idServidor,
                              tbservidor.idServidor,
                              tbservidor.idServidor
                         FROM tbservidor JOIN tbpessoa USING (idPessoa)
                                         JOIN tbhistlot USING (idServidor)
                                         JOIN tblotacao ON (tbhistlot.lotacao=tblotacao.idLotacao)
                        WHERE situacao = 1
                          AND tbhistlot.data = (select max(data) from tbhistlot where tbhistlot.idServidor = tbservidor.idServidor)
                          AND idPerfil = 1";

            # Verifica se tem filtro por lotação
            if ($parametroLotacao <> "Todos") {  // senão verifica o da classe
                if (is_numeric($parametroLotacao)) {
                    $select .= " AND (tblotacao.idlotacao = {$parametroLotacao})";
                } else { # senão é uma diretoria genérica
                    $select .= " AND (tblotacao.DIR = '{$parametroLotacao}')";
                }
            }

            $select .= " ORDER BY tbpessoa.nome";

            $result = $pessoal->select($select);
            $count = $pessoal->count($select);

            # Exibe a tabela
            $tabela = new Tabela();
            $tabela->set_conteudo($result);
            $tabela->set_label(['Servidor', 'Regra Permanente<br/>Voluntária', "Regra Permanente<br/>Compulsória", "Regra de Transição<br/>Pontos - Integral", "Regra de Transição<br/>Pontos - Média", "Regra de Transição<br/>Pedágio - Integral", "Regra de Transição<br/>Pedágio - Média", "Regra de Transição<br/>Pedágio - Redutor", "Direito Adquirido<br/>C.F. Art. 40, §1º, III, alínea a", "Direito Adquirido<br/>C.F. Art. 40, §1º, III, alínea b"]);
            $tabela->set_align(['left']);
            $tabela->set_width([19, 9, 9, 9, 9, 9, 9, 9, 9, 9]);
            $tabela->set_titulo("Previsão Geral de Aposentadoria");
            $tabela->set_subtitulo("(clique no retângulo da previsão para maiores detalhes)");
            $tabela->set_classe(["Pessoal", "AposentadoriaLC195Voluntaria", "AposentadoriaLC195Compulsoria", "AposentadoriaTransicaoPontos1", "AposentadoriaTransicaoPontos2", "AposentadoriaTransicaoPedagio1", "AposentadoriaTransicaoPedagio2", "AposentadoriaTransicaoPedagio3", "AposentadoriaDireitoAdquirido1", "AposentadoriaDireitoAdquirido2"]);
            $tabela->set_metodo(["get_nomeECargoELotacaoEId", "exibeAnaliseTabela", "exibeAnaliseTabela", "exibeAnaliseTabela", "exibeAnaliseTabela", "exibeAnaliseTabela", "exibeAnaliseTabela", "exibeAnaliseTabela", "exibeAnaliseTabela", "exibeAnaliseTabela"]);
            #$tabela->set_idCampo('idServidor');
            #$tabela->set_editar('?fase=editarGeralPorLotacao');
            $tabela->show();
            break;

        ####################################### 

        case "carregarPagina" :
            br(8);
            aguarde();

            # Informa o $id Servidor
            set_session('idServidorPesquisado', $id);

            # Informa a origem
            set_session('origem', 'areaPrevisao.php?fase=aguardeGeralPorLotacao');

            # Carrega a página específica
            loadPage("servidorAposentadoria.php?fase={$link}");
            break;

        #######################################    

        case "relatorio" :
            # Exibe a lista
            $select = "SELECT tbservidor.idServidor,
                              tbservidor.idServidor,           
                              tbservidor.idServidor,           
                              tbservidor.idServidor,
                              tbservidor.idServidor,
                              tbservidor.idServidor,
                              tbservidor.idServidor,
                              tbservidor.idServidor,
                              tbservidor.idServidor,
                              tbservidor.idServidor
                         FROM tbservidor JOIN tbpessoa USING (idPessoa)
                                         JOIN tbhistlot USING (idServidor)
                                         JOIN tblotacao ON (tbhistlot.lotacao=tblotacao.idLotacao)
                        WHERE situacao = 1
                          AND tbhistlot.data = (select max(data) from tbhistlot where tbhistlot.idServidor = tbservidor.idServidor)
                          AND idPerfil = 1";

            # Verifica se tem filtro por lotação
            if ($parametroLotacao <> "Todos") {  // senão verifica o da classe
                if (is_numeric($parametroLotacao)) {
                    $select .= " AND (tblotacao.idlotacao = {$parametroLotacao})";
                    $subtitulo = $pessoal->get_nomeLotacao2($parametroLotacao);
                } else { # senão é uma diretoria genérica
                    $select .= " AND (tblotacao.DIR = '{$parametroLotacao}')";
                    $lotacaoClasse = new Lotacao();
                    if ($parametroLotacao <> "Reitoria" AND $parametroLotacao <> "Prefeitura") {
                        $subtitulo = $lotacaoClasse->get_nomeDiretoriaSigla($parametroLotacao) . " - {$parametroLotacao}<br/>";
                    } else {
                        $subtitulo = "{$parametroLotacao}<br/>";
                    }
                }
            }

            $select .= " ORDER BY tbpessoa.nome";

            $result = $pessoal->select($select);
            $count = $pessoal->count($select);

            $relatorio = new Relatorio();
            $relatorio->set_conteudo($result);

            $relatorio->set_label(['Servidor', 'Regra Permanente<br/>Voluntária', "Regra Permanente<br/>Compulsória", "Regra de Transição<br/>Pontos - Integral", "Regra de Transição<br/>Pontos - Média", "Regra de Transição<br/>Pedágio - Integral", "Regra de Transição<br/>Pedágio - Média", "Regra de Transição<br/>Pedágio - Redutor", "Direito Adquirido<br/>C.F. Art. 40, §1º, III, alínea a", "Direito Adquirido<br/>C.F. Art. 40, §1º, III, alínea b"]);
            $relatorio->set_align(['left']);
            $relatorio->set_width([19, 9, 9, 9, 9, 9, 9, 9, 9, 9]);
            $relatorio->set_titulo("Previsão Geral de Aposentadoria");
            $relatorio->set_subtitulo($subtitulo);
            $relatorio->set_classe(["Pessoal", "AposentadoriaLC195Voluntaria", "AposentadoriaLC195Compulsoria", "AposentadoriaTransicaoPontos1", "AposentadoriaTransicaoPontos2", "AposentadoriaTransicaoPedagio1", "AposentadoriaTransicaoPedagio2", "AposentadoriaTransicaoPedagio3", "AposentadoriaDireitoAdquirido1", "AposentadoriaDireitoAdquirido2"]);
            $relatorio->set_metodo(["get_nomeECargoELotacaoEId", "get_textoReduzido", "get_textoReduzido", "get_textoReduzido", "get_textoReduzido", "get_textoReduzido", "get_textoReduzido", "get_textoReduzido", "get_textoReduzido", "get_textoReduzido"]);
            $relatorio->set_bordaInterna(true);
            $relatorio->set_mensagemGeral("Atenção, esta é uma previsão da posentadoria e as informações aqui contidas podem variar com o tempo.");
            $relatorio->show();
            break;

        #######################################                            
    }

    $grid->fechaColuna();
    $grid->fechaGrid();

    $page->terminaPagina();
} else {
    loadPage("../../areaServidor/sistema/login.php");
}