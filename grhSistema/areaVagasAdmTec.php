<?php

/**
 * Cadastro de Banco
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

    # pega o id (se tiver)
    $id = soNumeros(get('id'));

    # Verifica se veio menu grh e registra o acesso no log
    $grh = get('grh', false);
    if ($grh) {
        # Grava no log a atividade
        $atividade = "Visualizou o cadastro de vagas de Administrativo e Técnico";
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

    switch ($fase) {
        case "":
            /*
             * Menu
             */
            $menu = new MenuBar();

            # Voltar
            $botao = new Link("Voltar", "cadastroConcurso.php");
            $botao->set_class('button');
            $botao->set_title('Voltar a página anterior');
            $botao->set_accessKey('V');
            $menu->add_link($botao, "left");

            $menu->show();
            
            tituloTable("Área de Vagas de Concurso para Cargos Administrativos e Técnicos");
            
            emConstrucao("Esta área do sistema está em construção.<br/>Aguarde, pois em breve estará disponível.");
            break;
    }

    $grid->fechaColuna();
    $grid->fechaGrid();

    $page->terminaPagina();
} else {
    loadPage("../../areaServidor/sistema/login.php");
}