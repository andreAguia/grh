<?php

/*
 * Rotina Extra de Validação
 * 
 */

# Conecta ao Banco de Dados
$pessoal = new Pessoal();

$dtInicial = $campoValor[0];
$dtFinal = $campoValor[1];
$idServidor = $campoValor[5];

# Verifica se a data Inicial é anterior a data de admissão
$dtAdmissao = date_to_bd($pessoal->get_dtAdmissao($idServidor));
if ($dtInicial < $dtAdmissao) {
    $erro = 1;
    $msgErro .= 'O servidor não pode ter frequência ANTES de ser admitido!\n';
}

# Verifica se a data Inicial é posterior a data de saida
$dtSaida = $pessoal->get_dtSaida($idServidor);

# Se tiver data de saida
if (!is_null($dtSaida)) {
    $dtSaida = date_to_bd($dtSaida);
    if ($dtInicial > $dtSaida) {
        $erro = 1;
        $msgErro .= 'O servidor não pode ter frequência DEPOIS de sair da UENF!\n';
    }
}

$verifica = new VerificaAfastamentos($idServidor, date_to_php($dtInicial), date_to_php($dtFinal));
$outro = $verifica->verifica();
if (!empty($outro)) {
    $msgErro .= 'Já existe um(a) '.$outro.' nesse período!\n';
    $erro = 1;
}
