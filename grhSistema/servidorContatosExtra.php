<?php

/*
 * Rotina Extra de Validação
 * 
 */

# Email da Uenf
if (!is_null($campoValor[6])) {
    $campoValor[6] = strtolower($campoValor[6]);

    # Verifica se email Uenf é válido
    if (!filter_var($campoValor[6], FILTER_VALIDATE_EMAIL)) {
        $msgErro .= 'Email Uenf inválido!\n';
        $erro = 1;
    } else {
        # Verifica se e realmente @uenf
        $pos = stripos($campoValor[6], "@uenf");

        # se tem @uenf
        if ($pos === false) {
            $msgErro .= 'O e-mail institucional nao é @uenf!\n';
            $erro = 1;
        }
    }
}

# Email Pessoal
if (!is_null($campoValor[7])) {
    $campoValor[7] = strtolower($campoValor[7]);

    # Verifica se email Pessoal é válido
    if (!filter_var($campoValor[7], FILTER_VALIDATE_EMAIL)) {
        $msgErro .= 'Email Pessoal inválido!\n';
        $erro = 1;
    }
}





