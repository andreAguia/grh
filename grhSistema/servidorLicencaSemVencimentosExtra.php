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
$idServidor = $campoValor[17];

# Preenche a data de término quando for nula
if (empty($dtTermino)) {
    if (!empty($dtInicial)) {
        $campoValor[8] = date_to_bd(addDias($dtInicial, $numDias));
        $dtTermino = $campoValor[8];
    }
}

if (empty($numDias)) {
    $campoValor[8] = null;
    $campoValor[6] = null;
}

# Verifica a data de retorno
if (!empty($dtRetorno)) {
    # Verifica qual é q data maior
    $dtRetorno = date_to_php($dtRetorno);
    $dtTermino = date_to_php($dtTermino);
    $dm = dataMaior($dtRetorno, $dtTermino);

    # Verifica a data de retorno é anterior a data de termino
    if ($dtRetorno <> $dtTermino AND $dm == $dtRetorno) {
        $msgErro .= 'A data de retorno não pode ser posterior a data prevista de termino!\n';
        $erro = 1;
    }
}

/*
 *  Verifica se já tem outro afastamento nesse período
 */
if (!empty($dtInicial) AND!empty($numDias)) {
    $verifica = new VerificaAfastamentos($idServidor);
    $verifica->setPeriodo($dtInicial, addDias($dtInicial, $numDias));
    $verifica->setIsento("tblicencasemvencimentos", $id);

    if ($verifica->verifica()) {
        $erro = 1;
        $msgErro .= 'Já existe um(a) ' . $verifica->getAfastamento() . ' (' . $verifica->getDetalhe() . ') nesse período!\n';
    }
}



