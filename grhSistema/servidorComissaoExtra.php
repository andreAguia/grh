<?php

/*
 * Rotina Extra de Validação (#dando erro !!!!)
 * 
 */

$nomeacao = $campoValor[2];     // Data de Nomeação
$matricula = $campoValor[13];   // Matrícula do servidor

$pessoal = new Pessoal();
$dtAdmissao = date_to_bd($pessoal->get_dtAdmissao($matricula));

## Verifica se a data de nomeação é anterior a data de admissão
if(($nomeacao < $dtAdmissao) AND (!is_null($nomeacao))){
    $msgErro.='Você não pode ter cargo em comissão antes de ser admitido!!\nA data está errada !!';
    $erro = 1;
}
