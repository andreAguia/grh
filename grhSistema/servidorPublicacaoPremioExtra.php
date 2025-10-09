<?php

/*
 * Rotina Extra de Validação
 * 
 */

$dtInicio = date_to_php($campoValor[1]);
$dtFim = date_to_php($campoValor[2]);

# Informa o período em anos
$dias = getNumDias($dtInicio, $dtFim, false);
$anos = intval($dias / 365);

# Informa se teve menos que 5 aos de período aquisitivo
if ($anos < 5) {
    $erro = 1;
    $msgErro .= "O Período Aquisitivo não pode ser menor que 5 anos! ({$dias} dias)n";
}