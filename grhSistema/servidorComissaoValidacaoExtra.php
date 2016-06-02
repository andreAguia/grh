<?php

/*
 * Rotina Extra de Validação (#dando erro !!!!)
 * 
 */

$nomeacao = date_to_php($campoValor[2],'/'); // Data de Nomeação
$matricula = $campoValor[13];                // Matrícula do servidor

$pessoal = new Pessoal();
$dtAdmissao = $pessoal->get_dtAdmissao($matricula); // Data da Admissão

## Verifica se a data de nomeação é anterior a data de admissão
if(($nomeacao < $dtAdmissao) AND (!is_null($nomeacao))){
    $msgErro.='Você não pode ter cargo em comissão antes de ser admitido!!\nA data está errada !!';
    $erro = 1;
}
