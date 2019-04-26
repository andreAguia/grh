<?php

/*
 * Rotina Extra de Validação
 * 
 */

$dtInicial = $campoValor[0];
$idServidor = $campoValor[3];

$pessoal = new Pessoal();

# Verifica se a data Inicial é anterior a data de admissão
$dtAdmissao = $pessoal->get_dtAdmissao($idServidor);
$dtAdmissao = date_to_bd($dtAdmissao);
if($dtInicial < $dtAdmissao){
    $erro = 1;
   $msgErro.='A data Inicial não pode ser antes de ser admitido!\n A data está errada!';
}

# Verifica se a data Inicial é posterior a data de saida
$dtSaida = $pessoal->get_dtSaida($idServidor);

# Se tiver data de saida
if(!is_null($dtSaida)){
    $dtSaida = date_to_bd($dtSaida);
    if($dtInicial > $dtSaida){
        $erro = 1;
        $msgErro.='A data Inicial não pode ser DEPOIS da saida da UENF!\n';
    }
}