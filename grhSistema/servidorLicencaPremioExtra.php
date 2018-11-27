<?php

/*
 * Rotina Extra de Validação
 * 
 */

# Conecta ao Banco de Dados
$pessoal = new Pessoal();

$dtInicial = $campoValor[0];
$idServidor = $campoValor[4];

# Verifica se a data Inicial é anterior a data de admissão
$dtAdmissao = $pessoal->get_dtAdmissao($idServidor);
$dtAdmissao = date_to_bd($dtAdmissao);
if($dtInicial < $dtAdmissao){
    $erro = 1;
    $msgErro .= 'O servidor não pode pedir Licença ANTES de ser admitido!\n';
}

# Verifica se a data Inicial é posterior a data de saida
$dtSaida = $pessoal->get_dtSaida($idServidor);

# Se tiver data de saida
if(!is_null($dtSaida)){
    $dtSaida = date_to_bd($dtSaida);
    if($dtInicial > $dtSaida){
        $erro = 1;
        $msgErro .= 'O servidor não pode pedir licença DEPOIS gggde sair da UENF!'.$idServidor.'\n';
    }
}
