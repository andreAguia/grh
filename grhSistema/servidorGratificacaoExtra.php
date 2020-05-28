<?php

/*
 * Rotina Extra de Validação
 * 
 */

$dtInicial = $campoValor[0];
$valor = $campoValor[2];
$idServidor = $campoValor[5];

$pessoal = new Pessoal();

# Valida se o valor é igual a zero
if ($valor == 0) {
    $msgErro .= 'O valor deve ser informado!\n';
    $erro = 1;
}

# Verifica se a data Inicial é anterior a data de admissão
$dtAdmissao = $pessoal->get_dtAdmissao($idServidor);
$dtAdmissao = date_to_bd($dtAdmissao);
if ($dtInicial < $dtAdmissao) {
    $erro = 1;
    $msgErro .= 'O servidor não pode ter trienio ANTES de ser admitido!\n';
}

# Verifica se a data Inicial é posterior a data de saida
$dtSaida = $pessoal->get_dtSaida($idServidor);

# Se tiver data de saida
if (!is_null($dtSaida)) {
    $dtSaida = date_to_bd($dtSaida);
    if ($dtInicial > $dtSaida) {
        $erro = 1;
        $msgErro .= 'O servidor não pode ter trienio DEPOIS de sair da UENF!\n';
    }
}