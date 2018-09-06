<?php

/*
 * Rotina Extra de Validação (#dando erro !!!!)
 * 
 */

$nomeacao = $campoValor[4];     // Data de Nomeação
$exoneracao = $campoValor[8];   // Data de Exoneração
$idServidor = $campoValor[15];  // Matrícula do servidor

$pessoal = new Pessoal();
$dtAdmissao = date_to_bd($pessoal->get_dtAdmissao($idServidor));

## Verifica se a data de nomeação é anterior a data de admissão
if(($nomeacao < $dtAdmissao) AND (!is_null($nomeacao))){
    $msgErro.='Você não pode ter cargo em comissão antes de ser admitido!\nA data está errada!\n';
    $erro = 1;
}

## Verifica se a data de exoneração é anterior a data de nomeação é anterior a data de admissão
if(($exoneracao < $nomeacao) AND (!is_null($nomeacao)) AND (!is_null($exoneracao))){
    $msgErro.='Você não pode ser exonerado antes de ser nomeado!\nA data está errada!'.$nomeacao.'-'.$exoneracao.'\n';;
    $erro = 1;
}
