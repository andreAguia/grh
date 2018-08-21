<?php

/*
 * Rotina Extra de Validação
 * 
 */


/*
 * Verifica se data é anterior a admissão
 */

$status = $campoValor[2];
$data = $campoValor[3];

# Verifica se o abono deferido tem data de inicio
if(($status == 1) AND (is_null($data))){
    $msgErro.='Quando o abono e deferido deve-se informar a data em que o mesmo passa a valer!';
    $erro = 1;
}

