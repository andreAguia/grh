<?php

/*
 * Rotina Extra de Validação
 * 
 */

# Passa para 0 quando o valor for nulo
if (is_null($campoValor[1])) {
    $campoValor[1] = 0;
}

# Trata a aliquota quanto ao ponto e vírgula
$campoValor[3] = strtr($campoValor[3], '.', ',');

# Verifica se o valor inicial é menor que o valor final
if ($campoValor[1] > $campoValor[2]) {
    $erro = 1;
    $msgErro .= 'O valor inicial não pode ser maior que o valor final!\n';
}