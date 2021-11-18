<?php

/*
 * Rotina Extra de Validação
 * 
 */

# Retira o 'SEI-' caso tenha sido digitado
$campoValor[0] = str_ireplace("sei-", "", $campoValor[0]);

