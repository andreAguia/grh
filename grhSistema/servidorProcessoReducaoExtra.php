<?php

/*
 * Rotina Extra de Validação
 * 
 */


# Pasa para nulo quando o campo for apenas um espaço;
if (vazio($campoValor[1])) {
    $campoValor[1] = NULL;
}