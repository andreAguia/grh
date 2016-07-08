<?php

/*
 * Rotina Extra de Validação
 * 
 */

$dtInicial = $campoValor[0];
$idServidor = $campoValor[6];

$pessoal = new Pessoal();
$dtAdmissao = date_to_bd($pessoal->get_dtAdmissao($idServidor));

if(($dtInicial < $dtAdmissao) AND (!is_null($dtInicial))){
    $msgErro.='A data Inicial não pode ser antes de ser admitido!\nA data está errada!\n';
    $erro = 1;
}