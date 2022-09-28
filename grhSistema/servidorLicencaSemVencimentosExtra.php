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
$idServidor = $campoValor[18];

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

/*
 *  Verifica a aposentadoria compulsória
 */

# Pega a data compulsória
$compulsoria = new AposentadoriaCompulsoria();

if (!is_null($compulsoria->getDataAposentadoriaCompulsoria($idServidor))) {
    $dataCompulsoria = $compulsoria->getDataAposentadoriaCompulsoria($idServidor);

    # Verifica a data de termino
    if ($dtTermino >= date_to_bd($dataCompulsoria)) {
        $erro = 1;
        $msgErro .= 'A Data da aposentadoria compulsória deste servidor é ' . $dataCompulsoria . '. Todos os afastamentos deverão iniciar e terminar antes desta data!\n';
    }
}