<?php

/**
 * Área de Cargo Efetivo
 *  
 * By Alat
 */
# Reservado para o servidor logado
$idUsuario = null;

# Configuração
include ("_config.php");

# Permissão de Acesso
$acesso = Verifica::acesso($idUsuario, 2);

if ($acesso) {
    # Conecta ao Banco de Dados
    $intra = new Intra();
    $pessoal = new Pessoal();

    # Verifica a fase do programa
    $fase = get('fase');

    # Verifica se veio menu grh e registra o acesso no log
    $grh = get('grh', false);
    if ($grh) {
        # Grava no log a atividade
        $atividade = "Visualizou a área de cargo efetivo";
        $data = date("Y-m-d H:i:s");
        $intra->registraLog($idUsuario, $data, $atividade, null, null, 7);
    }

    # pega o id (se tiver)
    $id = soNumeros(get('id'));

    # Pega os parâmetros    
    $parametroCargo = post('parametroCargo', get_session('parametroCargo', 'Todos'));
    $parametroArea = post('parametroArea', get_session('parametroArea', 'Todos'));

    # Joga os parâmetros par as sessions   
    set_session('parametroCargo', $parametroCargo);
    set_session('parametroArea', $parametroArea);

    # Começa uma nova página
    $page = new Page();
    $page->iniciaPagina();

    # Cabeçalho da Página
    if ($fase <> "relatorio") {
        AreaServidor::cabecalho();
    }

################################################################

    switch ($fase) {
        case "" :
            br(4);
            aguarde();
            br();

            # Limita a tela
            $grid1 = new Grid("center");
            $grid1->abreColuna(5);
            p("Aguarde...", "center");
            $grid1->fechaColuna();
            $grid1->fechaGrid();

            loadPage('?fase=exibeLista');
            break;

################################################################

        case "exibeLista" :
            $grid = new Grid();
            $grid->abreColuna(12);
            br();

            # Cria um menu
            $menu1 = new MenuBar();

            # Voltar
            $botaoVoltar = new Link("Voltar", "grh.php");
            $botaoVoltar->set_class('button');
            $botaoVoltar->set_title('Voltar a página anterior');
            $botaoVoltar->set_accessKey('V');
            $menu1->add_link($botaoVoltar, "left");

            # Cadastro de Cargos
            $botaoCargo = new Button("Cargos");
            $botaoCargo->set_title("Acessa o Cadastro de Cargos");
            $botaoCargo->set_url('cadastroCargo.php');
            $menu1->add_link($botaoCargo, "right");

            # Cadastro de Áreas
            $botaoArea = new Button("Áreas");
            $botaoArea->set_title("Acessa o Cadastro de Áreas");
            $botaoArea->set_url('cadastroArea.php');
            $menu1->add_link($botaoArea, "right");

            # Cadastro de Funções
            $botaoFuncao = new Button("Funções");
            $botaoFuncao->set_title("Acessa o Cadastro de Funções");
            $botaoFuncao->set_url('cadastroFuncao.php');
            $menu1->add_link($botaoFuncao, "right");

            $menu1->show();

            ##############
            # Pega os dados da combo cargo
            $result = $pessoal->select('SELECT idTipoCargo,
                                               cargo
                                          FROM tbtipocargo 
                                      ORDER BY idTipoCargo');
            array_unshift($result, array("Todos", "Todos"));

            # Formulário de Pesquisa
            $form = new Form('?');

            # Nivel do Cargo    
            $controle = new Input('parametroCargo', 'combo', 'Cargo Efetivo:', 1);
            $controle->set_size(20);
            $controle->set_title('Nível do Cargo');
            $controle->set_valor($parametroCargo);
            $controle->set_onChange('formPadrao.submit();');
            $controle->set_linha(1);
            $controle->set_col(4);
            $controle->set_array($result);
            $controle->set_autofocus(true);
            $form->add_item($controle);

            # Area
            $selectArea = "SELECT idArea, area FROM tbarea";
            if ($parametroCargo <> "Todos") {
                $selectArea .= " WHERE idTipoCargo = {$parametroCargo}";
            }
            $selectArea .= " ORDER BY area";

            $result = $pessoal->select($selectArea);
            array_unshift($result, array('Todos', 'Todos'));

            $controle = new Input('parametroArea', 'combo', 'Área:', 1);
            $controle->set_size(30);
            $controle->set_title('Filtra por área');
            $controle->set_array($result);
            $controle->set_valor($parametroArea);
            $controle->set_onChange('formPadrao.submit();');
            $controle->set_linha(1);
            $controle->set_col(5);
            $form->add_item($controle);
            $form->show();

            ##############
            # Pega os dados
            $select = "SELECT tbtipocargo.cargo,
                              tbarea.area,
                              nome,
                              idCargo,
                              idCargo,
                              idCargo,
                              idCargo,
                              idCargo
                         FROM tbcargo LEFT JOIN tbplano USING (idPlano)
                                      LEFT JOIN tbtipocargo USING (idTipoCargo)
                                      LEFT JOIN tbarea USING (idarea)
                        WHERE true";

            if ($parametroCargo <> "Todos") {
                $select .= " AND tbtipocargo.idTipoCargo = {$parametroCargo}";
            }

            if ($parametroArea <> "Todos") {
                $select .= " AND tbarea.idArea = {$parametroArea}";
            }

            $select .= " ORDER BY tbtipocargo.cargo asc, tbarea.area asc, nome asc";
            #echo $select;

            $result = $pessoal->select($select);

            $tabela = new Tabela();
            $tabela->set_titulo('Cadastro de Cargo Efetivo');
            $tabela->set_label(["Cargo", "Área", "Função", "Servidores<br/>Ativos", "Ver", "Servidores<br/>Inativos", "Ver", "Mapa"]);
            $tabela->set_align(["left", "left", "left"]);
            $tabela->set_conteudo($result);
            $tabela->set_classe([null, null, null, "Pessoal", null, "pessoal", null, "Grh"]);
            $tabela->set_metodo([null, null, null, "get_numServidoresAtivosCargo", null, "get_numServidoresInativosCargo", null, "exibeMapaFuncao"]);
            $tabela->set_rowspan(0);
            $tabela->set_grupoCorColuna(0);
            $tabela->set_colunaSomatorio([3, 5]);

            # Ver servidores ativos
            $servAtivos = new Link(null, "?fase=aguardeAtivos&id=");
            $servAtivos->set_imagem(PASTA_FIGURAS_GERAIS . 'olho.png', 20, 20);
            $servAtivos->set_title("Exibe os servidores ativos");

            # Ver servidores inativos
            $servInativos = new Link(null, '?fase=aguardeInativos&id=');
            $servInativos->set_imagem(PASTA_FIGURAS_GERAIS . 'olho.png', 20, 20);
            $servInativos->set_title("Exibe os servidores inativos");

            # Coloca o objeto link na tabela			
            $tabela->set_link(array(null, null, null, null, $servAtivos, null, $servInativos));
            $tabela->set_idCampo('idCargo');

            $tabela->show();

            $grid->fechaColuna();
            $grid->fechaGrid();
            break;

        ################################################################

        case "aguardeAtivos" :
            br(10);
            aguarde("Montando a Listagem");
            br();
            loadPage('?fase=exibeServidoresAtivos&id=' . $id);
            break;

        ################################################################

        case "aguardeInativos" :
            br(10);
            aguarde("Montando a Listagem");
            br();
            loadPage('?fase=exibeServidoresInativos&id=' . $id);
            break;

        ################################################################

        case "exibeServidoresAtivos" :
            # Limita o tamanho da tela
            $grid = new Grid();
            $grid->abreColuna(12);

            # Informa a origem
            set_session('origem', 'areaCargoEfetivo.php?fase=exibeServidoresAtivos&id=' . $id);

            # Cria um menu
            $menu = new MenuBar();

            # Botão voltar
            $btnVoltar = new Button("Voltar", "?");
            $btnVoltar->set_title('Volta para a página anterior');
            $btnVoltar->set_accessKey('V');
            $menu->add_link($btnVoltar, "left");
            
            # Relatório
            $imagem2 = new Imagem(PASTA_FIGURAS . 'print.png', null, 15, 15);
            $botaoRel = new Button();
            $botaoRel->set_title("Relatório dos Servidores");
            $botaoRel->set_target("_blank");
            $botaoRel->set_url("?fase=relatorioAtivos&id=$id");
            $botaoRel->set_imagem($imagem2);
            $menu->add_link($botaoRel, "right");

            $menu->show();

            # Lista de Servidores Ativos
            $lista = new ListaServidores('Servidores Ativos - Cargo: ' . $pessoal->get_nomeCargo($id));
            $lista->set_situacao(1);
            $lista->set_cargo($id);
            $lista->showTabela();

            $grid->fechaColuna();
            $grid->fechaGrid();
            break;

        ################################################################

        case "exibeServidoresInativos" :
            # Limita o tamanho da tela
            $grid = new Grid();
            $grid->abreColuna(12);

            # Informa a origem
            set_session('origem', 'areaCargoEfetivo.php?fase=exibeServidoresInativos&id=' . $id);

            # Cria um menu
            $menu = new MenuBar();

            # Botão voltar
            $btnVoltar = new Button("Voltar", "?");
            $btnVoltar->set_title('Volta para a página anterior');
            $btnVoltar->set_accessKey('V');
            $menu->add_link($btnVoltar, "left");
            
            # Relatório
            $imagem2 = new Imagem(PASTA_FIGURAS . 'print.png', null, 15, 15);
            $botaoRel = new Button();
            $botaoRel->set_title("Relatório dos Servidores");
            $botaoRel->set_target("_blank");
            $botaoRel->set_url("?fase=relatorioInativos&id=$id");
            $botaoRel->set_imagem($imagem2);
            $menu->add_link($botaoRel, "right");

            $menu->show();
            
            # Lista de Servidores Inativos
            $lista = new ListaServidores('Servidores Inativos - Cargo: ' . $pessoal->get_nomeCargo($id));
            $lista->set_situacao(1);
            $lista->set_situacaoSinal("<>");
            $lista->set_cargo($id);
            $lista->showTabela();
            $grid->fechaColuna();
            $grid->fechaGrid();
            break;

        ################################################################

        case "relatorioAtivos" :
            # Lista de Servidores Ativos
            $lista = new ListaServidores('Servidores Ativos');
            $lista->set_situacao(1);
            $lista->set_cargo($id);
            $lista->showRelatorio();
            break;

        ################################################################

        case "relatorioInativos" :
            # Lista de Servidores Inativos
            $lista = new ListaServidores('Servidores Inativos');
            $lista->set_situacao(1);
            $lista->set_situacaoSinal("<>");
            $lista->set_cargo($id);
            $lista->showRelatorio();
            break;

        ################################################################
    }
    $page->terminaPagina();
} else {
    loadPage("../../areaServidor/sistema/login.php");
}


