<?php

/*
 * Rotina Extra de Validação
 * 
 */

# Email da Uenf
if (!is_null($campoValor[10])) {
    $campoValor[10] = strtolower($campoValor[10]);

    # Verifica se email Uenf é válido
    if (!filter_var($campoValor[10], FILTER_VALIDATE_EMAIL)) {
        $msgErro .= 'Email Uenf inválido!\n';
        $erro = 1;
    } else {
        # Verifica se e realmente @uenf
        $pos = stripos($campoValor[10], "@uenf");

        # se tem @uenf
        if ($pos === false) {
            $msgErro .= 'O e-mail institucional nao é @uenf!\n';
            $erro = 1;
        }
    }
}

# Email Pessoal
if (!is_null($campoValor[11])) {
    $campoValor[11] = strtolower($campoValor[11]);

    # Verifica se email Pessoal é válido
    if (!filter_var($campoValor[11], FILTER_VALIDATE_EMAIL)) {
        $msgErro .= 'Email Pessoal inválido!\n';
        $erro = 1;
    }
}





