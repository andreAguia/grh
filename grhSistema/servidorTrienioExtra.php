<?php

/*
 * Rotina Extra de Validação
 * 
 */

# Conecta ao Banco de Dados
$pessoal = new Pessoal();

$dtInicial = $campoValor[1];
$idServidor = $campoValor[8];

# Pega a data de admissão do servidor
$dtAdmissao = date_to_bd($pessoal->get_dtAdmissao($idServidor));

# Verifica se a data Inicial é anterior a data de admissão
if($dtInicial < $dtAdmissao){
    $erro = 1;
    $msgErro .= 'O servidor não pode ter trienio ANTES de ser admitido!\n';
}

# Verifica se a data Inicial é posterior a data de saida
$dtSaida = $pessoal->get_dtSaida($idServidor);

# Se tiver data de saida
if(!is_null($dtSaida)){
    $dtSaida = date_to_bd($dtSaida);
    if($dtInicial > $dtSaida){
        $erro = 1;
        $msgErro .= 'O servidor não pode ter trienio DEPOIS de sair da UENF!\n';
    }
}
