<?php

/*
 * Rotina Extra de Validação
 * 
 */

$dtInicial = $campoValor[0];
$matricula = $campoValor[4];

$pessoal = new Pessoal();
$dtAdmissao = date_to_bd($pessoal->get_dtAdmissao($matricula));

if(($dtInicial < $dtAdmissao) AND (!is_null($dtInicial)))
{
    $msgErro.='Você não ter faltas antes de ser admitido!<br/>A data está errada!';
    $erro = 1;
}

if(($dtInicial > date("Y/m/d")) AND (!is_null($dtInicial)))
{
    $msgErro.='Você não pode ter faltas futuras!';
    $erro = 1;
} 