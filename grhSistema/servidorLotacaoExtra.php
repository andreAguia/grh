<?php

/*
 * Rotina Extra de Validação
 * 
 */

$dtInicial = $campoValor[0];
$idServidor = $campoValor[3];

$pessoal = new Pessoal();
$dtAdmissao = date_to_bd($pessoal->get_dtAdmissao($idServidor));

if(($dtInicial < $dtAdmissao) AND (!is_null($dtInicial))){
    $msgErro.='Você não pode ser lotado antes de ser admitido!\nA data está errada!\n';
    $erro = 1;
}
