<?php

/*
 * Rotina Extra de Validação
 * 
 */

/*
 * Evita que campo parentesco seja preenchido quando for atestado do próprio servidor
 */
if($campoValor[2] == 'Próprio'){
    if($campoValor[3]<>0){
        $msgErro.='O Atestado do tipo próprio não deve ter o campo parentesco preenchido!';
        $erro = 1;
    }
}

/*
 * Exige o preenchimento do parentesco quando não for do próprio servidor
 */
if($campoValor[2] <> 'Próprio'){
    if($campoValor[3] == 0){
        $msgErro.='Deve-se informar o parentesco!';
        $erro = 1;
    }
}

/*
 * Verifica se data é anterior a admissão
 */

$dtInicial = $campoValor[0];
$matricula = $campoValor[7];

$pessoal = new Pessoal();
$dtAdmissao = date_to_bd($pessoal->get_dtAdmissao($matricula));

if(($dtInicial < $dtAdmissao) AND (!is_null($dtInicial))){
    $msgErro.='Você não pode ser lotado antes de ser admitido!<br/>A data está errada!';
    $erro = 1;
}

