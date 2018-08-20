<?php

/*
 * Rotina Extra de Validação
 * 
 */

$anoChegada = $campoValor[7];

$pessoal = new Pessoal();
$dtAdmissao = date_to_bd($pessoal->get_dtAdmissao($idServidor));

# Valida a data Inicial posterior a data de admissão
if(($dtInicial < $dtAdmissao) AND (!is_null($dtInicial))){
    $msgErro.='A data Inicial não pode ser antes de ser admitido!\nA data está errada!\n';
    $erro = 1;
}

# Valida se o valor é igual a zero
if($valor == 0){
    $msgErro.='O valor deve ser informado!\n';
    $erro = 1;
}