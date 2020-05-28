<?php

/*
 * Rotina Extra de Validação
 * 
 */

$dtInicial = $campoValor[0];
$idServidor = $campoValor[3];

$pessoal = new Pessoal();
$dtAdmissao = date_to_bd($pessoal->get_dtAdmissao($idServidor));

if (($dtInicial < $dtAdmissao) AND (!is_null($dtInicial))) {
    $msgErro .= 'Você não ter ocorrência antes de ser admitido!\nA data está errada!\n';
    $erro = 1;
}

if (($dtInicial > date("Y/m/d")) AND (!is_null($dtInicial))) {
    $msgErro .= 'Você não pode ter ocorrência com data futura!\n';
    $erro = 1;
} 