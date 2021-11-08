<?php

/*
 * Rotina Extra de Validação
 * 
 */

$nota1 = $campoValor[4];
$nota2 = $campoValor[5];
$nota3 = $campoValor[6];

# Critica a Nota 1
if (!is_null($nota1)) {
    if ($nota1 > 120) {
        $msgErro .= 'A nota máxima é 120\n';
        $erro = 1;
    }

    if ($nota1 < 0) {
        $msgErro .= 'A nota mínima é 0\n';
        $erro = 1;
    }

    # Formata a nota
    $campoValor[4] = str_replace(",", ".", $campoValor[4]);
}

# Critica a Nota 2
if (!is_null($nota2)) {
    if ($nota2 > 120) {
        $msgErro .= 'A nota máxima é 120\n';
        $erro = 1;
    }

    if ($nota2 < 0) {
        $msgErro .= 'A nota mínima é 0\n';
        $erro = 1;
    }

    # Formata a nota
    $campoValor[5] = str_replace(",", ".", $campoValor[5]);
}

# Critica a Nota 3
if (!is_null($nota3)) {
    if ($nota3 > 120) {
        $msgErro .= 'A nota máxima é 120\n';
        $erro = 1;
    }

    if ($nota3 < 0) {
        $msgErro .= 'A nota mínima é 0\n';
        $erro = 1;
    }

    # Formata a nota
    $campoValor[6] = str_replace(",", ".", $campoValor[6]);
}