<?php

/*
 * Rotina Extra de Validação
 * 
 */


/*
 * Verifica se data é anterior a admissão
 */

$dtInicial = $campoValor[0];
$idServidor = $campoValor[7];

$pessoal = new Pessoal();
$dtAdmissao = date_to_bd($pessoal->get_dtAdmissao($idServidor));

if(($dtInicial < $dtAdmissao) AND (!is_null($dtInicial))){
    $msgErro.='Você não pode ter atestado antes de ser admitido!\nA data está errada!';
    $erro = 1;
}

