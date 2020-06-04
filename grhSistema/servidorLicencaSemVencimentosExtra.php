<?php

/*
 * Rotina Extra de Validação
 * 
 */

# Conecta ao Banco de Dados
$pessoal = new Pessoal();

$dtInicial = date_to_php($campoValor[6]);
$numDias = $campoValor[7];
$dtTermino = $campoValor[8];
$dtRetorno = $campoValor[9];


# Preenche a data de término quando for nula
if (vazio($dtTermino)) {
    if (!vazio($dtInicial)) {
        $campoValor[8] = date_to_bd(addDias($dtInicial, $numDias));
        $dtTermino = $campoValor[8];
    }
}

if (vazio($numDias)) {
    $campoValor[8] = null;
    $campoValor[6] = null;
}

# Verifica a data de retorno
if (!vazio($dtRetorno)) {
    # Verifica qual é q data maior
    $dtRetorno = date_to_php($dtRetorno);
    $dtTermino = date_to_php($dtTermino);
    $dm = dataMaior($dtRetorno, $dtTermino);

    # Verifica a data de retorno é anterior a data de termino
    if ($dm == $dtRetorno) {
        $msgErro .= 'A data de retorno não pode ser posterior a data prevista de termino!\n';
        $erro = 1;
    }
}