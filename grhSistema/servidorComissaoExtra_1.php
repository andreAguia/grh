<?php

/*
 * Rotina Extra de Validação (#dando erro !!!!)
 * 
 */

$nomeacao = $campoValor[3];     // Data de Nomeação
$exoneracao = $campoValor[7];   // Data de Exoneração
$idServidor = $campoValor[12];  // id do servidor

$pessoal = new Pessoal();
$dtAdmissao = date_to_bd($pessoal->get_dtAdmissao($idServidor));

## Verifica se a data de nomeação é anterior a data de admissão
if (($nomeacao < $dtAdmissao) AND (!is_null($nomeacao))) {
    $msgErro .= 'Você não pode ter cargo em comissão antes de ser admitido!\nA data está errada!\n';
    $erro = 1;
}

## Verifica se a data de exoneração é anterior a data de nomeação é anterior a data de admissão
if (($exoneracao < $nomeacao) AND (!is_null($nomeacao)) AND (!is_null($exoneracao))) {
    $msgErro .= 'Você não pode ser exonerado antes de ser nomeado!\nA data está errada!' . $nomeacao . '-' . $exoneracao . '\n';
    $erro = 1;
}

# Verifica se a data Inicial é posterior a data de saida
$dtSaida = $pessoal->get_dtSaida($idServidor);

# Se tiver data de saida
if (!is_null($dtSaida)) {
    $dtSaida = date_to_bd($dtSaida);
    if ($nomeacao > $dtSaida) {
        $erro = 1;
        $msgErro .= 'O servidor não pode ser nomeado DEPOIS de sair da UENF!\n';
    }
}
