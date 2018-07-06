<?php

/*
 * Rotina Extra de Validação
 * 
 */

$numProcesso = $campoValor[0];

$processo = new Processo($numProcesso);
$campoValor[0] = $processo->get_numero();