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

    # Define o array de modalidades de aposentadoria
    $arrayModalidades = [
        ["Regras Permanentes", "voluntaria"],
        ["Regras Permanentes", "compulsoria"],
        ["Regras de Transição", "pontos1"],
        ["Regras de Transição", "pontos2"],
        ["Regras de Transição", "pedagio1"],
        ["Regras de Transição", "pedagio2"],
        ["Regras de Transição", "pedagio3"],
        ["Direito Adquirido", "adquirido1"],
        ["Direito Adquirido", "adquirido2"],
        ["Direito Adquirido", "adquirido3"],
    ];

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

        # Servidores Aposentados
        $botaoCompulsoria = new Link("Servidores Aposentados");
        $botaoCompulsoria->set_class('button');
        $botaoCompulsoria->set_title("Servidores Aposentados");
        $botaoCompulsoria->set_url("areaAposentadoria.php");
        $menu->add_link($botaoCompulsoria, "right");

        # Aposentadoria Compulsória 
        $botaoCompulsoria = new Link("Aposentadoria Compulsória");
        $botaoCompulsoria->set_class('button');
        $botaoCompulsoria->set_title("Previsão da Aposentadoria Compulsória por Ano");
        $botaoCompulsoria->set_url("areaPrevisaoCompulsoria.php");
        $menu->add_link($botaoCompulsoria, "right");

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
            $result1 = $pessoal->select('(SELECT idlotacao, concat(IFnull(tblotacao.DIR,"")," - ",IFnull(tblotacao.GER,"")," - ",IFnull(tblotacao.nome,"")) lotacao
                                              FROM tblotacao
                                             WHERE ativo) UNION (SELECT distinct DIR, DIR
                                              FROM tblotacao
                                             WHERE ativo)
                                          ORDER BY 2');
            array_unshift($result1, array("Todos", 'Todas'));

            $controle = new Input('parametroLotacao', 'combo', 'Lotação:', 1);
            $controle->set_size(30);
            $controle->set_title('Filtra por Lotação');
            $controle->set_array($result1);
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

            $result2 = $pessoal->select($select);
            $count = $pessoal->count($select);

            # Exibe a tabela
            $tabela = new Tabela();
            $tabela->set_conteudo($result2);
            $tabela->set_label(["Servidor", "Regras Permanentes", "Regras de Transição", "Direito Adquirido"]);
            $tabela->set_align(['left']);
            $tabela->set_width([20, 20, 40, 20]);
            $tabela->set_titulo("Previsão Geral de Aposentadoria");
            $tabela->set_subtitulo("(clique no retângulo da previsão para maiores detalhes)");
            $tabela->set_classe(["Pessoal", "Aposentadoria", "Aposentadoria", "Aposentadoria"]);
            $tabela->set_metodo(["get_nomeECargoELotacaoEId", "exibe_previsãoPermanente", "exibe_previsãoTransicao", "exibe_previsãoAdquirido"]);
            $tabela->set_bordaInterna(true);
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
            $select = "SELECT tbservidor.idServidor
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

            $result2 = $pessoal->select($select);
            $count = $pessoal->count($select);

            # flag da primeira linha
            $primeiraLinha = true;

            if ($count == 0) {
                $arrayRelatorio = $result2;
            } else {

                # Trata o array encontrado
                foreach ($result2 as $cadaServidor) {
                    # Preenche o array do conteúdo do relatório
                    $arrayLinha = null;
                    $arrayLinha[] = $cadaServidor[0];

                    if ($primeiraLinha) {
                        $arrayLabel[] = "Servidor";
                    }

                    # Preenche as modalidades
                    foreach ($arrayModalidades as $item) {
                        $previsaoAposentadoria = new PrevisaoAposentadoria($item[1], $cadaServidor[0]);
                        $arrayLinha[] = $previsaoAposentadoria->get_textoReduzido($cadaServidor[0]);

                        if ($primeiraLinha) {
                            $arrayLabel[] = "{$previsaoAposentadoria->get_tipo()}<br/>{$previsaoAposentadoria->get_descricao()}";
                        }
                    }
                    $primeiraLinha = false;
                    $arrayRelatorio[] = $arrayLinha;
                }
            }

            $relatorio = new Relatorio();
            $relatorio->set_titulo("Previsão Geral de Aposentadoria");
            $relatorio->set_subtitulo($subtitulo);

            $relatorio->set_conteudo($arrayRelatorio);
            $relatorio->set_label($arrayLabel);
            $relatorio->set_align(['left']);

            $relatorio->set_classe(["Pessoal"]);
            $relatorio->set_metodo(["get_nomeECargoELotacaoEId"]);
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