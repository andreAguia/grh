<?php

/**
 * Cadastro de Plano de Cargos e Salários
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
    $plano = new PlanoCargos();

    # Pega o idPlano atual
    $idPlano = $plano->get_planoAtual();

    # pega o id (se tiver)
    $id = soNumeros(get('id', $idPlano));

    # Começa uma nova página
    $page = new Page();
    $page->iniciaPagina();

    # Limita o tamanho da tela
    $grid = new Grid();
    $grid->abreColuna(12);

    echo '<div class="title-bar">
            <button class="menu-icon" type="button" onclick="abreFechaDivId(\'menuSuspenso\');"></button>
            <div class="title-bar-title">Tabela Salarial</div>
          </div>';

    br();

    $div = new Div("menuSuspenso");
    $div->abre();

    $plano->menuPlanos($id);

    $div->fecha();

    # Exibe a tabela
    if (is_null($id)) {
        br(5);
        p("Escolha um plano de Cargos", "f14", "center");
    } else {
        $plano->exibeTabela($id);
    }

    $grid->fechaColuna();
    $grid->fechaGrid();

    $grid->fechaColuna();
    $grid->fechaGrid();

    $page->terminaPagina();
} else {
    loadPage("../../areaServidor/sistema/login.php");
}