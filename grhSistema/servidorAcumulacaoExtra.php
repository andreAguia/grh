<?php

/*
 * Rotina Extra de Validação
 * 
 */


/*
 * Verifica se data é anterior a admissão
 */

$dtSaida = $campoValor[8];
$motivo = $campoValor[9];

$resultado = $campoValor[10];
$resultado1 = $campoValor[13];
$resultado2 = $campoValor[16];
$resultado3 = $campoValor[19];
$conclusao = $campoValor[20];
$resultadoFinal = null;

# Se tiver data de saida
if ((!is_null($dtSaida)) XOR (!is_null($motivo))) {
    $erro = 1;
    $msgErro .= 'A Data de Saída e o Motivo devem estar preenchidos\n';
}