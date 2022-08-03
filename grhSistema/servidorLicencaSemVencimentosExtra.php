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
$dtRetorno = $campoValor[11];
$idServidor = $campoValor[19];

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



