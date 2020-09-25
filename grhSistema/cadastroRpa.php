<?php

/**
 * Cadastro de RPA
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
    $fase = get('fase', 'listar');

    # pega o id (se tiver)
    $id = soNumeros(get('id'));

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

    # Ano Exercício
    $botaoExercicio = new Link("por Ano de Exercício", "areaFeriasExercicio.php");
    $botaoExercicio->set_class('button');
    $botaoExercicio->set_title('Férias por Ano Exercício');
    #$menu1->add_link($botaoExercicio,"right");
    # Ano por Fruíção
    $botaoFruicao = new Link("Ano de Fruição");
    $botaoFruicao->set_class('button');
    $botaoFruicao->set_title('Férias por Ano em que foi realmente fruído');
    #$menu1->add_link($botaoFruicao,"right");

    $menu1->show();
    br(5);

    $grid->fechaColuna();
    $grid->fechaGrid();
    
    $grid = new Grid("center");
    $grid->abreColuna(4);

    $img = new Imagem(PASTA_FIGURAS . 'embreve.png', "Em breve", "100%", "100%");
    $img->set_id("center");
    $img->show();

    p("Rotina ainda não foi implementada", "f20", "center");
    p("Em breve esta rotina estará disponível.", "f16", "center");

    $grid->fechaColuna();
    $grid->fechaGrid();

    $page->terminaPagina();
} else {
    loadPage("../../areaServidor/sistema/login.php");
}
