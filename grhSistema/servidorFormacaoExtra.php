<?php

/*
 * Rotina Extra de Validação
 * 
 */

# Retira as letras das horas
$campoValor[3] = retiraLetras($campoValor[3]);

# Passa os minutos para zero quando nulo
if (is_null($campoValor[4])) {
    $campoValor[4] = 0;
}


