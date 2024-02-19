<?php

/*
 * Rotina Extra de Validação
 * 
 */

# Conecta ao Banco de Dados
$pessoal = new Pessoal();

$dtInicial = $campoValor[0];
$numDias = $campoValor[1];
$dtTermino = $campoValor[2];
$processo = $campoValor[3];
$dtPublicacao = $campoValor[4];
$idServidor = $campoValor[8];
$idTpLicenca = 26; // o tipo de licença da suspensão

/*
 * Verifica se digitou a data final ou o numero de dias e preenche automaticamente
 */

if (empty($dtTermino)) {
    $campoValor[2] = date_to_bd(addDias(date_to_php($dtInicial), $numDias));
    $dtTermino = $campoValor[2];
}

/*
 *  Verifica se já tem outro afastamento nesse período
 */

$verifica = new VerificaAfastamentos($idServidor);
$verifica->setPeriodo(date_to_php($dtInicial), addDias(date_to_php($dtInicial), $numDias));
$verifica->setIsento("tblicenca", $id);
$verifica->setTipoLicenca($idTpLicenca);

if ($verifica->verifica()) {
    $erro = 1;
    $msgErro .= 'Já existe um(a) ' . $verifica->getAfastamento() . ' (' . $verifica->getDetalhe() . ') nesse período!\n(' . $id . ')';
}

/*
 *  Verifica se a data Inicial é anterior a data de admissão
 */

$dtAdmissao = date_to_bd($pessoal->get_dtAdmissao($idServidor));

if ($dtInicial < $dtAdmissao) {
    $erro = 1;
    $msgErro .= 'O servidor não pode sofrer suspensão ANTES de ser admitido!\n';
}

/*
 *  Verifica se a data Inicial é posterior a data de saida
 */
$dtSaida = $pessoal->get_dtSaida($idServidor);

# Se tiver data de saida
if (!is_null($dtSaida)) {
    $dtSaida = date_to_bd($dtSaida);
    if ($dtInicial > $dtSaida) {
        $erro = 1;
        $msgErro .= 'O servidor não pode sofrer suspensão DEPOIS de sair da UENF!\n';
    }
}

# Preenche a data de término quando for nula
if (empty($dtTermino)) {
    if (!empty($dtInicial)) {
        $campoValor[6] = date_to_bd(addDias($dtInicial, $numDias));
        $dtTermino = $campoValor[6];
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
        $msgErro .= 'A Data da aposentadoria compulsória deste servidor é ' . $dataCompulsoria . '. A Suspensão deverá iniciar e terminar antes desta data!\n';
    }
}