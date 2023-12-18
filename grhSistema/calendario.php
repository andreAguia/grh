<?php

/*
 *  Calendário      
 */

# Configuração
include ("_config.php");

# Começa uma nova página
$page = new Page();
$page->iniciaPagina();

# Inicia o Grid
$grid = new Grid();
$grid->abreColuna(12);
br();

# Pega os parâmetros do calendário
$ano = post('ano', date("Y"));
$mes = post('mes', date("m"));

# Valida os valores
if ($ano < 1900 OR $ano > 2100) {
    $ano = date("Y");
}

if ($mes < 1) {
    $mes = 1;
}

if ($mes > 12) {
    $mes = 12;
}

# Calendário 
$cal = new Calendario($mes, $ano);
$cal->set_tamanho('g');
$cal->set_anoMinimo(date('Y') - 10);
$cal->set_anoMaximo(date('Y') + 2);
$cal->show("?");

$grid->fechaColuna();
$grid->fechaGrid();

$page->terminaPagina();
