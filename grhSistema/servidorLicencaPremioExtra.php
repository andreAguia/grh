<?php

/*
 * Rotina Extra de Validação
 * 
 */

# Conecta ao Banco de Dados
$pessoal = new Pessoal();

$dtInicial = $campoValor[0];
$numDias = $campoValor[1];
$idServidor = $campoValor[4];

# Verifica se a data Inicial é anterior a data de admissão
$dtAdmissao = $pessoal->get_dtAdmissao($idServidor);
$dtAdmissao = date_to_bd($dtAdmissao);
if ($dtInicial < $dtAdmissao) {
    $erro = 1;
    $msgErro .= 'O servidor não pode pedir Licença ANTES de ser admitido!\n';
}

# Verifica se a data Inicial é posterior a data de saida
$dtSaida = $pessoal->get_dtSaida($idServidor);

# Se tiver data de saida
if (!is_null($dtSaida)) {
    $dtSaida = date_to_bd($dtSaida);
    if ($dtInicial > $dtSaida) {
        $erro = 1;
        $msgErro .= 'O servidor não pode pedir licença DEPOIS de sair da UENF!\n';
    }
}

# Verifica se já tem outro afastamento nesse período
$dtFinal = addDias(date_to_php($dtInicial), $numDias);

$verifica = new VerificaAfastamentos($idServidor, date_to_php($dtInicial), $dtFinal);
$verifica->setIsento("tblicencapremio", $id);
$outro = $verifica->verifica();
if (!empty($outro[0])) {
    $msgErro .= 'Já existe um(a) ' . $outro[0] . ' nesse período!\n';
    $erro = 1;
}

/*
 *  Verifica se já tem outro afastamento nesse período
 */

$verifica = new VerificaAfastamentos($idServidor);
$verifica->setPeriodo(date_to_php($dtInicial), addDias(date_to_php($dtInicial), $numDias));
$verifica->setIsento("tblicencapremio", $id);

if ($verifica->verifica()) {
    $erro = 1;
    $msgErro .= 'Já existe um(a) ' . $verifica->getAfastamento() . ' (' . $verifica->getDetalhe() . ') nesse período!\n';
}