<?php

/**
 * Área de Controle da Entrega da Certidão de Tempo do INSS
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

    # Defne a data limite
    $dataLimite = "31/12/2001";

    # Conecta ao Banco de Dados
    $pessoal = new Pessoal();
    $intra = new Intra();

    # Verifica a fase do programa
    $fase = get('fase', 'aguarde');

    # pega o id (se tiver)
    $id = soNumeros(get('id'));

    # Verifica se veio menu grh e registra o acesso no log
    $grh = get('grh', false);
    if ($grh) {
        # Grava no log a atividade
        $atividade = "Visualizou a área de controle da entrega da certidão do INSS";
        $data = date("Y-m-d H:i:s");
        $intra->registraLog($idUsuario, $data, $atividade, null, null, 7);
    }

    # Começa uma nova página
    $page = new Page();
    $page->iniciaPagina();

    # Pega os parâmetros
    $parametroLotacao = post('parametroLotacao', get_session('parametroLotacao', $pessoal->get_idLotacao($intra->get_idServidor($idUsuario))));    
    $parametroEntregou = post('parametroEntregou', get_session('parametroEntregou', "Todos"));

    # Joga os parâmetros para as sessions
    set_session('parametroLotacao', $parametroLotacao);
    set_session('parametroEntregou', $parametroEntregou);

    # Limita a página
    $grid = new Grid();
    $grid->abreColuna(12);

    # Menu e Cabeçalho
    if ($fase <> "relatorio") {

        AreaServidor::cabecalho();

        # Cria o Menu
        $menu = new MenuBar();

        # Voltar
        $botaoVoltar = new Link("Voltar", "areaPrevisao.php");
        $botaoVoltar->set_class('button');
        $botaoVoltar->set_title('Voltar a página anterior');
        $botaoVoltar->set_accessKey('V');
        $menu->add_link($botaoVoltar, "left");
        $menu->show();

        ################################################################
        # Formulário de Pesquisa
        $form = new Form('?');

        # Lotação    
        $result = $pessoal->select('(SELECT idlotacao, concat(IFnull(tblotacao.DIR,"")," - ",IFnull(tblotacao.GER,"")," - ",IFnull(tblotacao.nome,"")) lotacao
                                              FROM tblotacao
                                             WHERE ativo) UNION (SELECT distinct DIR, DIR
                                              FROM tblotacao
                                             WHERE ativo)
                                          ORDER BY 2');
        array_unshift($result, array("*", 'Todas'));

        $controle = new Input('parametroLotacao', 'combo', 'Lotação:', 1);
        $controle->set_size(30);
        $controle->set_title('Filtra por Lotação');
        $controle->set_array($result);
        $controle->set_valor($parametroLotacao);
        $controle->set_onChange('formPadrao.submit();');
        $controle->set_linha(1);
        $controle->set_col(8);
        $form->add_item($controle);

        # Entregou ? 
        $controle = new Input('parametroEntregou', 'combo', 'Entregou ?', 1);
        $controle->set_size(30);
        $controle->set_title('Filtra por Entrega');
        $controle->set_array(["Todos", "Sim", "Não", "Não Informado"]);
        $controle->set_valor($parametroEntregou);
        $controle->set_onChange('formPadrao.submit();');
        $controle->set_linha(1);
        $controle->set_col(3);
        $form->add_item($controle);
        $form->show();
    }

    #######################################

    switch ($fase) {
        case "":
        case "aguarde":

            br(4);
            aguarde();
            br();

            # Limita a tela
            $grid1 = new Grid("center");
            $grid1->abreColuna(5);
            p("Aguarde...", "center");
            $grid1->fechaColuna();
            $grid1->fechaGrid();

            loadPage('?fase=listar');
            break;

        #######################################

        case "listar" :

            $grid->fechaColuna();
            $grid->abreColuna(3);

            #######################################
            # Resumo 

            $subtitulo = null;

            # Pega os dados
            $select = "SELECT CASE entregouCtc
                                   WHEN 's' THEN '<span class=\"label success\">Sim</span>'
                                   WHEN 'n' THEN '<span class=\"label alert\">Não</span>'
                              ELSE '<span class=\"label warning\">Não Informado</span>'
                              END,
                              count(*) as tot                          
                         FROM tbservidor LEFT JOIN tbpessoa ON (tbservidor.idPessoa = tbpessoa.idPessoa)
                                             JOIN tbhistlot ON (tbservidor.idServidor = tbhistlot.idServidor)
                                             JOIN tblotacao ON (tbhistlot.lotacao=tblotacao.idLotacao)
                                             LEFT JOIN tbcargo ON (tbservidor.idCargo = tbcargo.idCargo)
                                             JOIN tbtipocargo ON (tbcargo.idTipoCargo = tbtipocargo.idTipoCargo)
                       WHERE tbhistlot.data =(select max(data) from tbhistlot where tbhistlot.idServidor = tbservidor.idServidor)
                             AND situacao = 1
                             AND dtAdmissao < '" . date_to_bd($dataLimite) . "'";

            # Lotação
            if (($parametroLotacao <> "*") AND ($parametroLotacao <> "")) {
                # Verifica se o que veio é numérico
                if (is_numeric($parametroLotacao)) {
                    $select .= ' AND (tblotacao.idlotacao = "' . $parametroLotacao . '")';
                    $subtitulo = $pessoal->get_nomeLotacao($parametroLotacao);
                } else { # senão é uma diretoria genérica
                    $select .= ' AND (tblotacao.DIR = "' . $parametroLotacao . '")';
                    $subtitulo = $parametroLotacao;
                }
            }

            $select .= " GROUP BY entregouCtc ORDER BY entregouCtc";

            $resumo = $pessoal->select($select);

            # Monta a tabela
            $tabela = new Tabela();
            $tabela->set_conteudo($resumo);
            $tabela->set_label(["Entregou?", "Quantidade"]);
            $tabela->set_totalRegistro(false);
            $tabela->set_align(["center"]);
            $tabela->set_titulo("Quantidades");
            $tabela->set_subtitulo($subtitulo);
            $tabela->set_colunaSomatorio(1);
            $tabela->show();

            #######################################

            $grid->fechaColuna();
            $grid->abreColuna(9);

            # Conecta com o banco de dados
            $servidor = new Pessoal();

            $select = "SELECT tbservidor.idServidor,
                              tbpessoa.nome,
                              tbservidor.idServidor,
                              tbservidor.idServidor,
                              tbservidor.dtAdmissao,
                              tbservidor.idServidor,
                              tbservidor.idServidor
                        FROM tbservidor LEFT JOIN tbpessoa ON (tbservidor.idPessoa = tbpessoa.idPessoa)
                                             JOIN tbhistlot ON (tbservidor.idServidor = tbhistlot.idServidor)
                                             JOIN tblotacao ON (tbhistlot.lotacao=tblotacao.idLotacao)
                                             LEFT JOIN tbcargo ON (tbservidor.idCargo = tbcargo.idCargo)
                                             JOIN tbtipocargo ON (tbcargo.idTipoCargo = tbtipocargo.idTipoCargo)
                       WHERE tbhistlot.data =(select max(data) from tbhistlot where tbhistlot.idServidor = tbservidor.idServidor)
                             AND situacao = 1
                             AND dtAdmissao < '" . date_to_bd($dataLimite) . "'";

            # Lotação
            if (($parametroLotacao <> "*") AND ($parametroLotacao <> "")) {
                # Verifica se o que veio é numérico
                if (is_numeric($parametroLotacao)) {
                    $select .= " AND (tblotacao.idlotacao = '{$parametroLotacao}')";
                } else { # senão é uma diretoria genérica
                    $select .= " AND (tblotacao.DIR = '{$parametroLotacao}')";
                }
            }

            # Entregou  
            if ($parametroEntregou <> "Todos") {
                if ($parametroEntregou == "Sim") {
                    $select .= " AND tbservidor.entregouCtc = 's'";
                } elseif ($parametroEntregou == "Não") {
                    $select .= " AND tbservidor.entregouCtc = 'n'";
                } else {
                    $select .= " AND (tbservidor.entregouCtc is null)";
                }
            }


            $select .= " ORDER BY tbservidor.entregouCtc desc, tbpessoa.nome";

            $result = $servidor->select($select);

            $tabela = new Tabela();
            $tabela->set_titulo("Servidores Estatutários Admitidos antes de {$dataLimite}");
            $tabela->set_label(['Id Funcional / Matricula', 'Nome', 'Cargo', 'Lotação', 'Admissão', 'Entregou CTC?']);
            $tabela->set_align(["center", "left", "left", "left"]);
            $tabela->set_funcao([null, null, null, null, "date_to_php"]);
            $tabela->set_classe(["pessoal", null, "pessoal", "pessoal", null, "Aposentadoria"]);
            $tabela->set_metodo(["get_idFuncionalEMatricula", null, "get_cargo", "get_lotacao", null, "exibeEntregouCtc"]);
            #$tabela->set_rowspan(0);
            #$tabela->set_grupoCorColuna(0);
            $tabela->set_editar('?fase=editaServidor&id=');
            $tabela->set_nomeColunaEditar("Acessar");
            $tabela->set_editarBotao("olho.png");
            $tabela->set_idCampo('idServidor');

            $tabela->set_conteudo($result);
            $tabela->show();
            break;

        #######################################            

        case "editaServidor" :

            br(8);
            aguarde();

            # Informa o $id Servidor
            set_session('idServidorPesquisado', $id);

            # Informa a origem
            set_session('origem', 'areaCtcInss.php?fase=aguarde');
            set_session('voltaCtc', 'areaCtcInss.php?fase=aguarde');

            # Carrega a página específica
            loadPage('servidorCtc.php');
            break;

        #######################################
    }

    $grid->fechaColuna();
    $grid->fechaGrid();

    $page->terminaPagina();
} else {
    loadPage("../../areaServidor/sistema/login.php");
}