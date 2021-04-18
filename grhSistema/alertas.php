<?php

/**
 * Alertas
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
    $pessoal = new Pessoal();
    $intra = new Intra();

    # Classe de alertas
    $alertas = new Alertas();
    $checkup = new Checkup(false);

    # Verifica a fase e o alerta
    $fase = get('fase');
    $categoria = get("categoria", get_session('categoria'));
    $alerta = get('alerta', get_session('alerta'));

    # Joga os parâmetros par as sessions
    set_session('categoria', $categoria);
    set_session('alerta', $alerta);

    # pega o id (se tiver)
    $id = soNumeros(get('id'));

    # Verifica se veio menu grh e registra o acesso no log
    $grh = get('grh', false);
    if ($grh) {
        # Grava no log a atividade
        $atividade = "Visualizou os alertas do sistema";
        $data = date("Y-m-d H:i:s");
        $intra->registraLog($idUsuario, $data, $atividade, null, null, 7);
    }

    # Começa uma nova página
    $page = new Page();
    $page->iniciaPagina();

    # Cabeçalho da Página
    AreaServidor::cabecalho();

    $grid = new Grid();
    $grid->abreColuna(12);

    ################################################################

    switch ($fase) {

        case "" :
            # Cria um menu
            $menu = new MenuBar();

            # Voltar
            $botaoVoltar = new Link("Voltar", "grh.php");
            $botaoVoltar->set_class('button');
            $botaoVoltar->set_title('Voltar a página anterior');
            $botaoVoltar->set_accessKey('V');
            $menu->add_link($botaoVoltar, "left");
            $menu->show();

            # Título
            titulo("Alertas");
            br();

            $grid->fechaColuna();
            $grid->abreColuna(12, 3);

            $alertas->menu($categoria);

            $grid->fechaColuna();
            $grid->abreColuna(12, 9);

            # Mostra o início
            br(5);
            p("Informe a Categoria", "f16", "center");
            break;

        case "menu" :

            # Cria um menu
            $menu = new MenuBar();

            # Voltar
            $botaoVoltar = new Link("Voltar", "grh.php");
            $botaoVoltar->set_class('button');
            $botaoVoltar->set_title('Voltar a página anterior');
            $botaoVoltar->set_accessKey('V');
            $menu->add_link($botaoVoltar, "left");
            $menu->show();

            # Título
            titulo("Alertas");
            br();

            $grid->fechaColuna();
            $grid->abreColuna(12, 3);

            $alertas->menu($categoria);

            $grid->fechaColuna();
            $grid->abreColuna(12, 9);

            tituloTable($alertas->getNomeCategoria($categoria));
            br(3);

            aguarde("Aguarde ...");
            loadPage("?fase=menu2&categoria={$categoria}");
            break;
        
        case "menu2" :

            # Cria um menu
            $menu = new MenuBar();

            # Voltar
            $botaoVoltar = new Link("Voltar", "grh.php");
            $botaoVoltar->set_class('button');
            $botaoVoltar->set_title('Voltar a página anterior');
            $botaoVoltar->set_accessKey('V');
            $menu->add_link($botaoVoltar, "left");
            $menu->show();

            # Título
            titulo("Alertas");
            br();

            $grid->fechaColuna();
            $grid->abreColuna(12, 3);

            $alertas->menu($categoria);

            $grid->fechaColuna();
            $grid->abreColuna(12, 9);

            tituloTable($alertas->getNomeCategoria($categoria));
            br();

            $checkup->listaCategoria($categoria);
            break;

        case "tabela" :

            # Cria um menu
            $menu = new MenuBar();

            # Voltar
            $botaoVoltar = new Link("Voltar", "?fase=menu");
            $botaoVoltar->set_class('button');
            $botaoVoltar->set_title('Voltar a página anterior');
            $botaoVoltar->set_accessKey('V');
            $menu->add_link($botaoVoltar, "left");
            $menu->show();

            # Título
            titulo("Alertas");
            br(3);

            aguarde("Aguarde ...");
            loadPage("?fase=tabela2&categoria={$categoria}");
            break;
        
         case "tabela2" :

            # Cria um menu
            $menu = new MenuBar();

            # Voltar
            $botaoVoltar = new Link("Voltar", "?fase=menu");
            $botaoVoltar->set_class('button');
            $botaoVoltar->set_title('Voltar a página anterior');
            $botaoVoltar->set_accessKey('V');
            $menu->add_link($botaoVoltar, "left");
            $menu->show();

            # Título
            titulo("Alertas");
            br();

            # Mostra a tabela
            $checkup->set_lista(true);
            $checkup->$alerta();

            # Grava no log a atividade
            $data = date("Y-m-d H:i:s");
            $atividade = 'Visualizou o método: ' . $alerta . ' da classe Checkup.';
            $intra->registraLog($idUsuario, $data, $atividade, null, null, 7);
            break;
    }

    ################################################################ 

    $grid->fechaColuna();
    $grid->fechaGrid();
    $page->terminaPagina();
} else {
    loadPage("../../areaServidor/sistema/login.php");
}
    