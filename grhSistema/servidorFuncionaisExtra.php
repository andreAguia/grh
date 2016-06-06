<?php

/*
 * Rotina Extra de Validação
 * 
 */

/*
 * Verifica se a exoneração é posterior a admissão
 */

$dtAdmissao = $campoValor[5];
$dtExoneracao = $campoValor[10];

if(($dtExoneracao < $dtAdmissao) AND (!is_null($dtExoneracao))){
    $msgErro.='O servidor não pode ser exonerado antes de ser admitido!!\nA data está errada !!';
    $erro = 1;
}