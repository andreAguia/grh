<?php

/*
 * Rotina Extra de Validação
 * 
 */

$nota1 = $campoValor[4];
$nota2 = $campoValor[5];
$nota3 = $campoValor[6];

if ($nota1 > 120 OR $nota2 > 120 OR $nota3 > 120)  {
    $msgErro .= 'A nota máxima é 120\n';
    $erro = 1;
}

if ($nota1 < 0 OR $nota2 < 0 OR $nota3 < 0)  {
    $msgErro .= 'A nota mínima é 0\n';
    $erro = 1;
}

$campoValor[4] = str_replace(",", ".", $campoValor[4]);
$campoValor[5] = str_replace(",", ".", $campoValor[5]);
$campoValor[6] = str_replace(",", ".", $campoValor[6]);