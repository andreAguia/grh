<?php

/*
 * Rotina Extra de Validação
 * 
 */

$dtInicial = $campoValor[0];
$matricula = $campoValor[3];

$pessoal = new Pessoal();
$dtAdmissao = date_to_bd($pessoal->get_dtAdmissao($matricula));

if(($dtInicial < $dtAdmissao) AND (!is_null($dtInicial))){
    $msgErro.='Você não ter ocorrência antes de ser admitido!!\nA data está errada !!';
    $erro = 1;
}

if(($dtInicial > date("Y/m/d")) AND (!is_null($dtInicial))){
    $msgErro.='Você não pode ter ocorrência com data futura!!';
    $erro = 1;
} 