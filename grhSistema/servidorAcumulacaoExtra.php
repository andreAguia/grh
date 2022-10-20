<?php

/*
 * Rotina Extra de Validação * 
 */


/*
 * Verifica se data é anterior a admissão
 */

$dtSaida = $campoValor[9];
$motivo = $campoValor[10];

# Se tiver data de saida
if ((!is_null($dtSaida)) XOR (!is_null($motivo))) {
    $erro = 1;
    $msgErro .= 'A Data de Saída e o Motivo devem estar preenchidos\n';
}