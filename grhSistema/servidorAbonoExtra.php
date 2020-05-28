<?php

/*
 * Rotina Extra de Validação
 * 
 */


/*
 * Verifica se data é anterior a admissão
 */

$status = $campoValor[2];
$data = $campoValor[3];
$idServidor = $campoValor[4];

# Verifica se o abono deferido tem data de inicio
if ($status == 1) {

    # Verifica se a data esta em branco
    if (is_null($data)) {
        $msgErro .= 'Quando o abono e deferido deve-se informar a data em que o mesmo passa a valer!\n';
        $erro = 1;
    }

    $pessoal = new Pessoal();

    # Verifica se a data Inicial é anterior a data de admissão
    $dtAdmissao = $pessoal->get_dtAdmissao($idServidor);
    $dtAdmissao = date_to_bd($dtAdmissao);
    if ($data < $dtAdmissao) {
        $erro = 1;
        $msgErro .= 'A data Inicial não pode ser antes de ser admitido!\n A data está errada!\n';
    }

    # Verifica se a data Inicial é posterior a data de saida
    $dtSaida = $pessoal->get_dtSaida($idServidor);

    # Se tiver data de saida
    if (!is_null($dtSaida)) {
        $dtSaida = date_to_bd($dtSaida);
        if ($data > $dtSaida) {
            $erro = 1;
            $msgErro .= 'A data Inicial não pode ser DEPOIS da saida da UENF!\n';
        }
    }
}