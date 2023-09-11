<?php

/*
 * Rotina Extra de Validação
 * 
 */

# Passa para 0 quando o valor for nulo
if (is_null($campoValor[1])) {
    $campoValor[1] = 0;
}

# Passa para 0 quando a aliquota for nula
if (is_null($campoValor[3])) {
    $campoValor[3] = 0;
}else{
    $campoValor[3] = strtr($campoValor[3], '.', ',');
}

# Passa para 0 quando a decução for nula
if (is_null($campoValor[4])) {
    $campoValor[4] = 0;
}

# Verifica se o valor inicial é menor que o valor final
if ($campoValor[1] > $campoValor[2]) {
    $erro = 1;
    $msgErro .= 'O valor inicial não pode ser maior que o valor final!\n';
}