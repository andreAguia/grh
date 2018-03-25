<?php

/*
 * Rotina Extra de Validação
 * 
 */

$dtInicial = $campoValor[0];
$idServidor = $campoValor[7];
$orgao = $campoValor[2];

$pessoal = new Pessoal();
$dtAdmissao = date_to_bd($pessoal->get_dtAdmissao($idServidor));

# Verifica se a cessão é posterior a adimissão
if(($dtInicial < $dtAdmissao) AND (!is_null($dtInicial))){
    $msgErro.='Você não pode ser cedido antes de ser admitido!\nA data Inicial está errada!';
    $erro = 1;
}

# Se tudo estiver correto (Sem erro) altera automáticamente a lotação do servidor
# para a lotação de todos os cedidos: (Reitoria - Cedidos - 113)
$lotacaoCedidos = 113;
if(!$erro){
    # Somente altera a lotação se ele já não estiver na 113
    $lotacaoAtual = $pessoal->get_idlotacao($idServidor);
    if($lotacaoAtual <> $lotacaoCedidos){
        # Grava a nova lotação
        $campos = array("idServidor","data","lotacao","motivo");
        $valor = array($idServidor,$dtInicial,$lotacaoCedidos,"Cedido a(o) ".$orgao);                    
        $pessoal->gravar($campos,$valor,NULL,"tbhistlot","idHistLot",FALSE);
    }
}
