<?php

/**
 * Área de Férias
 *
 * By Alat
 */
# Reservado para o servidor logado
$idUsuario = null;

# Configuração
include "_config.php";

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
        $atividade = "Visualizou a área de férias";
        $data = date("Y-m-d H:i:s");
        $intra->registraLog($idUsuario, $data, $atividade, null, null, 7);
    }

    # pega o id (se tiver)
    $id = soNumeros(get('id'));
    set_session('areaFerias', false);

    # Pega os parâmetros
    $parametroAno = post('parametroAno', get_session('parametroAno', date("Y")));
    $parametroLotacao = post('parametroLotacao', get_session('parametroLotacao'));
    $parametroSituacao = post('parametroSituacao', get_session('parametroSituacao', 1));
    
    # Joga os parâmetros par as sessions
    set_session('parametroAno', $parametroAno);
    set_session('parametroLotacao', $parametroLotacao);
    set_session('parametroSituacao', $parametroSituacao);

    # Começa uma nova página
    $page = new Page();
    $page->iniciaPagina();

    # Cabeçalho da Página
    AreaServidor::cabecalho();

    $grid = new Grid();
    $grid->abreColuna(12);

    # Cria um menu
    $menu1 = new MenuBar();

    # Voltar
    $botaoVoltar = new Link("Voltar", "grh.php");
    $botaoVoltar->set_class('button');
    $botaoVoltar->set_title('Voltar a página anterior');
    $botaoVoltar->set_accessKey('V');
    $menu1->add_link($botaoVoltar, "left");
    $menu1->show();

    # Título
    titulo("Área de Férias - Pendentes");

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
    $controle->set_col(12);
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

            $grid2 = new Grid();

            # Área Lateral
            $grid2->abreColuna(3);

            ########################################
            # Menu
            tituloTable("Menu");

            $menu = new Menu("menuProcedimentos");
            $menu->add_item('titulo', 'Tipo');
            $menu->add_item('link', 'por Ano de Exercício', 'areaFeriasExercicio.php');
            $menu->add_item('link', 'por Ano de Fruíção', 'areaFeriasFruicao.php');
            $menu->add_item('link', '<b>pendentes</b>', 'areaFeriasPendentes.php');

            $menu->add_item('titulo', 'Relatórios');
            $menu->add_item('linkWindow', 'Agrupado pelo Total de Dias', '../grhRelatorios/ferias.exercicio.porTotalDias.php');
            $menu->add_item('linkWindow', 'Agrupado pelo Total de Dias (menor que 30)', '../grhRelatorios/ferias.exercicio.porTotalDias.menor30.php');
            $menu->add_item('linkWindow', 'Solicitações Agrupadas por Mês', '../grhRelatorios/ferias.exercicio.solicitacoes.php');
            $menu->show();

            #######################################
            # Resumo Geral
            # Informa a classe com os parâmetros
            $lista1 = new ListaFerias($parametroAno);
            $lista1->set_lotacao($parametroLotacao);
            $lista1->set_situacao($parametroSituacao);

            # resumo geral
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
