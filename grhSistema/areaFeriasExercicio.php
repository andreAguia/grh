<?php

/**
 * Área de Férias
 *
 * Por Ano de exercício
 *  
 * By Alat
 */
# Reservado para o servidor logado
$idUsuario = null;

# Configuração
include "_config.php";

# Permissão de Acesso
$acesso = Verifica::acesso($idUsuario, [1, 2, 12]);

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
        $atividade = "Visualizou a área de férias";
        $data = date("Y-m-d H:i:s");
        $intra->registraLog($idUsuario, $data, $atividade, null, null, 7);
    }

    # pega o id (se tiver)
    $id = soNumeros(get('id'));
    set_session('areaFerias', false);

    # Pega os parâmetros
    $parametroAno = post('parametroAno', get_session('parametroAno', date("Y")));
    $parametroLotacao = post('parametroLotacao', get_session('parametroLotacao', $pessoal->get_idLotacao($intra->get_idServidor($idUsuario))));
    $parametroSituacao = post('parametroSituacao', get_session('parametroSituacao', 1));
    $parametroPerfil = post('parametroPerfil', get_session('parametroPerfil'));
    $parametroDias = post('parametroDias', get_session('parametroDias', 'Todos'));

    # Joga os parâmetros par as sessions
    set_session('parametroAno', $parametroAno);
    set_session('parametroLotacao', $parametroLotacao);
    set_session('parametroSituacao', $parametroSituacao);
    set_session('parametroPerfil', $parametroPerfil);
    set_session('parametroDias', $parametroDias);

    # Começa uma nova página
    $page = new Page();
    $page->iniciaPagina();

    # Cabeçalho da Página
    AreaServidor::cabecalho();

    $grid = new Grid();
    $grid->abreColuna(12);

    # Informa a classe com os parâmetros
    $lista1 = new ListaFerias($parametroAno);
    $lista1->set_lotacao($parametroLotacao);
    $lista1->set_situacao($parametroSituacao);
    $lista1->set_perfil($parametroPerfil);

    # trata o parâmetro dias
    if ($parametroDias <> "Todos") {
        $lista1->set_dias($parametroDias);
    }

    # Cria um menu
    $menu1 = new MenuBar();

    # Voltar
    $botaoVoltar = new Link("Voltar", "grh.php");
    $botaoVoltar->set_class('button');
    $botaoVoltar->set_title('Voltar a página anterior');
    $botaoVoltar->set_accessKey('V');
    $menu1->add_link($botaoVoltar, "left");

    # Cadastro de processo de férias
    $botaoProcesso = new Link("Processos de Férias", "cadastroFeriasProcesso.php");
    $botaoProcesso->set_class('button');
    $botaoProcesso->set_title('Acessa o controle dos processos de férias');
    $menu1->add_link($botaoProcesso, "right");

    # Importa fèrias do SigRH
    $botaoProcesso = new Link("Importa do SigRH", "importaFerias.php");
    $botaoProcesso->set_class('button warning');
    $botaoProcesso->set_title('Acessa a rotina que importa os dados de férias do SigRh de um ano exercício');
    $menu1->add_link($botaoProcesso, "right");

    $menu1->show();

    # Título
    titulo("Área de Férias - Por Ano de Exercício");

################################################################
    # Formulário de Pesquisa
    $form = new Form('?');

    # Cria um array com os anos possíveis
    $anoInicial = 1999;
    $anoAtual = date('Y');
    $anoExercicio = arrayPreenche($anoInicial, $anoAtual + 2, "d");

    $controle = new Input('parametroAno', 'combo', 'Ano Exercício:', 1);
    $controle->set_size(8);
    $controle->set_title('Filtra por Ano exercício');
    $controle->set_array($anoExercicio);
    $controle->set_valor(date("Y"));
    $controle->set_valor($parametroAno);
    $controle->set_onChange('formPadrao.submit();');
    $controle->set_linha(1);
    $controle->set_col(2);
    $controle->set_autofocus(true);
    $form->add_item($controle);

    # Dias
    $result = $lista1->getArrayPorDia();
    if (!is_null($result)) {
        array_unshift($result, ['Todos', 'Todos']);
    }

    if (Verifica::acesso($idUsuario, 1)) {
        echo $parametroDias;
        var_dump($result);
    }

    $controle = new Input('parametroDias', 'combo', 'Dias:', 1);
    $controle->set_size(30);
    $controle->set_title('Filtra por Dias de férias');
    $controle->set_array($result);
    $controle->set_valor($parametroDias);
    $controle->set_onChange('formPadrao.submit();');
    $controle->set_linha(1);
    $controle->set_col(2);
    $form->add_item($controle);

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
    $controle->set_col(4);
    $form->add_item($controle);

    # Perfil
    $result = $pessoal->select('SELECT idperfil,
                                       nome,
                                       tipo
                                  FROM tbperfil
                                 WHERE tipo <> "Outros"  
                              ORDER BY tipo, nome');

    array_unshift($result, array('*', '-- Todos --'));

    $controle = new Input('parametroPerfil', 'combo', 'Perfil:', 1);
    $controle->set_size(30);
    $controle->set_title('Filtra por Perfil');
    $controle->set_array($result);
    $controle->set_optgroup(true);
    $controle->set_valor($parametroPerfil);
    $controle->set_onChange('formPadrao.submit();');
    $controle->set_linha(1);
    $controle->set_col(2);
    $form->add_item($controle);

    # Situação
    $result = $pessoal->select('SELECT idsituacao, situacao
                                              FROM tbsituacao                                
                                          ORDER BY 1');
    array_unshift($result, array('*', '-- Todos --'));

    $controle = new Input('parametroSituacao', 'combo', 'Situação:', 1);
    $controle->set_size(30);
    $controle->set_title('Filtra por Situação');
    $controle->set_array($result);
    $controle->set_valor($parametroSituacao);
    $controle->set_onChange('formPadrao.submit();');
    $controle->set_linha(1);
    $controle->set_col(2);
    $form->add_item($controle);

    $form->show();

################################################################

    switch ($fase) {
        case "":
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

        case "exibeLista":

            # Área Lateral
            $grid2 = new Grid();
            $grid2->abreColuna(3);

            ########################################
            # Exibe o Processo de férias            
            $classeFerias = new Ferias();
            $classeFerias->exibeProcesso($parametroLotacao);

            ########################################
            # Menu
            tituloTable("Menu");

            $menu = new Menu("menuProcedimentos");
            $menu->add_item('titulo', 'Tipo');
            $menu->add_item('link', '<b>por Ano de Exercício</b>', '#');
            $menu->add_item('link', 'por Ano de Fruíção', 'areaFeriasFruicao.php');

            $menu->add_item('titulo', 'Relatórios');
            $menu->add_item('linkWindow', 'Agrupado pelo Total de Dias', '../grhRelatorios/ferias.exercicio.porTotalDias.php');
            $menu->add_item('linkWindow', 'Agrupado pelo Total de Dias (menor que 30)', '../grhRelatorios/ferias.exercicio.porTotalDias.menor30.php');
            $menu->add_item('linkWindow', 'Solicitações Agrupadas por Mês', '../grhRelatorios/ferias.exercicio.solicitacoes.php');
            $menu->add_item('linkWindow', 'Férias Pendentes', '../grhRelatorios/ferias.pendentes.php');

            $menu->show();

            #######################################
            # Resumo Geral
            $lista1->showResumoGeral();

            # por dias
            $lista1->showResumoPorDia();

            #######################################
            # Área Principal
            $grid2->fechaColuna();
            $grid2->abreColuna(9);

            $lista1->showPorDia();

            $grid2->fechaColuna();
            $grid2->fechaGrid();
            break;

################################################################
        # Chama o menu do Servidor que se quer editar
        case "editaServidorFerias":
            set_session('idServidorPesquisado', $id);
            set_session('areaFerias', "exercicio");
            loadPage('servidorFerias.php');
            break;

################################################################
    }
    $grid->fechaColuna();
    $grid->fechaGrid();

    $page->terminaPagina();
} else {
    loadPage("../../areaServidor/sistema/login.php");
}
