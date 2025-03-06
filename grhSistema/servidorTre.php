<?php

/**
 * Cadastro Tre
 *  
 * By Alat
 */
# Inicia as variáveis que receberão as sessions
$idUsuario = null;
$idServidorPesquisado = null;

# Configuração
include ("_config.php");

# Permissão de Acesso
$acesso = Verifica::acesso($idUsuario, [1, 2, 12]);

if ($acesso) {
    # Conecta ao Banco de Dados   
    $pessoal = new Pessoal();
    $intra = new Intra();

    # Verifica se veio menu grh e registra o acesso no log
    $grh = get('grh', false);
    if ($grh) {
        # Grava no log a atividade
        $atividade = "Cadastro do servidor - Controle do TRE";
        $data = date("Y-m-d H:i:s");
        $intra->registraLog($idUsuario, $data, $atividade, null, null, 7, $idServidorPesquisado);
    }

    # Verifica a fase do programa
    $fase = get('fase', 'dias');

    # Pega o idPessoa
    $idPessoa = $pessoal->get_idPessoa($idServidorPesquisado);

    # pega o id (se tiver)
    $id = soNumeros(get('id'));

    # Verifica se veio da área de TRE
    $origem = get_session("origem");

    # Começa uma nova página
    $page = new Page();
    $page->iniciaPagina();

    # Cabeçalho da Página
    AreaServidor::cabecalho();

    $grid = new Grid();
    $grid->abreColuna(12);

    # Pegas os valores
    $diasTrabalhados = $pessoal->get_treDiasTrabalhados($idServidorPesquisado);
    $folgasConcedidas = $pessoal->get_treFolgasConcedidas($idServidorPesquisado);
    $folgasFruidas = $pessoal->get_treFolgasFruidas($idServidorPesquisado);
    $folgasPendentes = $folgasConcedidas - $folgasFruidas;

    # botão de voltar da lista
    if ($origem == 'areaTre') {
        $voltar = 'areaTre.php';
    } else {
        $voltar = 'servidorMenu.php';
    }

    if ($fase == "historicoDias") {
        $voltar = '?fase=dias';
    }

    if ($fase == "historicoFolga") {
        $voltar = '?fase=folgas';
    }

    # Cria um menu
    $menu1 = new MenuBar();

    # Voltar
    $botaoVoltar = new Link("Voltar", $voltar);
    $botaoVoltar->set_class('button');
    $botaoVoltar->set_title('Voltar a página anterior');
    $botaoVoltar->set_accessKey('V');
    $menu1->add_link($botaoVoltar, "left");

    # Botões
    if ($fase == "dias") {

        # Histórico
        $botao1 = new Link("Histórioco de Dias Trabalhados", '?fase=historicoDias');
        $botao1->set_class('button');
        $menu1->add_link($botao1, "right");

        # Editar
        $botao1 = new Link("Editar", "servidorTreAfastamento.php?grh=1");
        $botao1->set_class('button');
        $botao1->set_title("Cadastro de Dias Trabalhados e Folgas Concedidas");
        $menu1->add_link($botao1, "right");
    }

    if ($fase == "folgas") {
        # Histórico
        $botao1 = new Link("Histórioco de Folgas fruídas", '?fase=historicoFolga');
        $botao1->set_class('button');
        $menu1->add_link($botao1, "right");

        # Editar
        $botao2 = new Link("Editar", "servidorTreFolga.php?grh=1");
        $botao2->set_class('button');
        $botao2->set_title("Cadastro de Folgas Fruídas");
        $menu1->add_link($botao2, "right");
    }

    $menu1->show();

    # Exibe os dados do Servidor
    get_DadosServidor($idServidorPesquisado);

    # Verifica se Folgas fruídas não são maiores que as concedidas
    if ($folgasFruidas > $folgasConcedidas) {
        callout('Servidor com mais folgas fruídas do Tre do que concedidas', 'warning');
    }

    $grid->fechaColuna();

    $tre = new Tre($idServidorPesquisado);

    ################################################################

    switch ($fase) {
        case "" :
        case "dias" :
            # Menu      
            $grid->abreColuna(12, 4);

            # Exibe resumo
            $tre->exibeResumo();

            # Menu Principal
            $menu = new Menu("menuProcedimentos");
            $menu->add_item('titulo', 'Menu');
            $menu->add_item('link', '<b>Dias Trabalhados e Folgas Concedidas</b>', '?fase=dias');
            $menu->add_item('link', 'Folgas Fruídas', '?fase=folgas');

            $menu->add_item('titulo', 'Relatórios');
            $menu->add_item('linkWindow', 'Relatório Geral', '../grhRelatorios/servidorTre.php');
            $menu->add_item('linkWindow', 'Dias Trabalhados e Folgas Concedidas Geral', '../grhRelatorios/servidorTreAfastamento.php');
            $menu->add_item('linkWindow', 'Dias Trabalhados e Folgas Concedidas Por Ano', '../grhRelatorios/servidorTreAfastamentoPorAno.php');
            $menu->add_item('linkWindow', 'Folgas Fruídas Geral', '../grhRelatorios/servidorTreFolga.php');
            $menu->add_item('linkWindow', 'Folgas Fruídas Por Ano', '../grhRelatorios/servidorTreFolgaPorAno.php');
            $menu->show();

            $grid->fechaColuna();
            $grid->abreColuna(12, 8);

            $tre->exibeDias();

            $grid->fechaColuna();
            break;

        ################################################################

        case "folgas" :
            # Menu      
            $grid->abreColuna(12, 4);

            # Exibe resumo
            $tre->exibeResumo();

            # Menu Principal
            $menu = new Menu("menuProcedimentos");
            $menu->add_item('titulo', 'Menu');
            $menu->add_item('link', 'Dias Trabalhados e Folgas Concedidas', '?fase=dias');
            $menu->add_item('link', '<b>Folgas Fruídas</b>', '?fase=folgas');

            $menu->add_item('titulo', 'Relatórios');
            $menu->add_item('linkWindow', 'Relatório Geral', '../grhRelatorios/servidorTre.php');
            $menu->add_item('linkWindow', 'Dias Trabalhados e Folgas Concedidas Geral', '../grhRelatorios/servidorTreAfastamento.php');
            $menu->add_item('linkWindow', 'Dias Trabalhados e Folgas Concedidas Por Ano', '../grhRelatorios/servidorTreAfastamentoPorAno.php');
            $menu->add_item('linkWindow', 'Folgas Fruídas Geral', '../grhRelatorios/servidorTreFolga.php');
            $menu->add_item('linkWindow', 'Folgas Fruídas Por Ano', '../grhRelatorios/servidorTreFolgaPorAno.php');
            $menu->show();

            $grid->fechaColuna();
            $grid->abreColuna(12, 8);

            $tre->exibeFolgasFruídas();

            $grid->fechaColuna();
            break;

        ################################################################

        case "historicoDias" :
            $grid->abreColuna(12);

            $historico = new Historico("tbtrabalhotre", $idServidorPesquisado);
            $historico->set_titulo("Histórico de Dias Trabalhados");
            $historico->show();

            break;

        ################################################################

        case "historicoFolga" :

            $grid->abreColuna(12);

            $historico = new Historico("tbfolga", $idServidorPesquisado);
            $historico->set_titulo("Histórico de Folgas Fruídas");
            $historico->show();

            break;

        ################################################################
    }

    $grid->fechaGrid();
    $page->terminaPagina();
} else {
    loadPage("../../areaServidor/sistema/login.php");
}        