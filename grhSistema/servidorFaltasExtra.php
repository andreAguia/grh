<?php

/*
 * Rotina Extra de Validação
 * 
 */

$dtInicial = $campoValor[0];
$idServidor = $campoValor[4];

$pessoal = new Pessoal();
$dtAdmissao = date_to_bd($pessoal->get_dtAdmissao($idServidor));

if(($dtInicial < $dtAdmissao) AND (!is_null($dtInicial)))
{
    $msgErro.='Você não ter faltas antes de ser admitido!\nA data está errada!\n';
    $erro = 1;
}

if(($dtInicial > date("Y/m/d")) AND (!is_null($dtInicial)))
{
    $msgErro.='Você não pode ter faltas futuras!\n';
    $erro = 1;
} 