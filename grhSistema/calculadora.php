<?php

/**
 * Estatística
 *  
 * By Alat
 */
# Reservado para o servidor logado
$idUsuario = null;

# Configuração
include ("_config.php");

# Começa uma nova página
$page = new Page();
$page->iniciaPagina();

$data1 = post("data1", get("data1"));
$data2 = post("data2");
$numDias = null;
$erro = 0;

# Valida as datas
if ((!vazio($data1)) AND (!vazio($data2))) {

    $data1 = date_to_php($data1);
    $data2 = date_to_php($data2);

    # Valida a data 1
    if (!validaData($data1)) {
        $numDias = null;
        $erro = 1;
    }

    # Valida a data 2
    if (!validaData($data2)) {
        $numDias = null;
        $erro = 1;
    }

    # Calcula
    if ($erro == 0) {
        $numDias = dataDif($data1, $data2);
    } else {
        back(1);
    }
}

tituloTable("Calculadora de Datas");
br();

p(trataNulo($numDias) . " dias", "f20", "center");
br();

# Formuário exemplo de login
$form = new Form('?');

# data 1
$controle = new Input('data1', 'data', 'Data 1:', 1);
$controle->set_size(20);
$controle->set_linha(1);
$controle->set_col(3);
$controle->set_valor(date_to_bd($data1));
#$controle->set_onChange('formPadrao.submit();');
$controle->set_autofocus(true);
$form->add_item($controle);

# data 2
$controle = new Input('data2', 'data', 'Data 2:', 1);
$controle->set_size(20);
$controle->set_linha(2);
$controle->set_col(3);
$controle->set_valor(date_to_bd($data2));
#$controle->set_onChange('formPadrao.submit();');
$form->add_item($controle);

# submit
$controle = new Input('submit', 'submit');
$controle->set_valor('Calcular');
$controle->set_linha(3);
$form->add_item($controle);

$form->show();

$page->terminaPagina();
