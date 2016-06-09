<?php

/*
 * Rotina Extra de Validação
 * 
 */

$dtInicial = $campoValor[0];
$matricula = $campoValor[6];

$pessoal = new Pessoal();
$dtAdmissao = date_to_bd($pessoal->get_dtAdmissao($matricula));

if(($dtInicial < $dtAdmissao) AND (!is_null($dtInicial))){
    $msgErro.='A data Inicial não pode ser antes de ser admitido!<br/>A data está errada!';
    $erro = 1;
}