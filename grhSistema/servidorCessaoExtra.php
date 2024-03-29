<?php

/*
 * Rotina Extra de Validação
 * 
 */

$dtInicial = $campoValor[0];
$idServidor = $campoValor[8];
$orgao = $campoValor[4];

$pessoal = new Pessoal();
$dtAdmissao = date_to_bd($pessoal->get_dtAdmissao($idServidor));

# Verifica se a cessão é posterior a admissão
if (($dtInicial < $dtAdmissao) AND (!is_null($dtInicial))) {
    $msgErro .= 'Você não pode ser cedido antes de ser admitido!\nA data Inicial está errada!';
    $erro = 1;
}

# Se tudo estiver correto (Sem erro) altera automáticamente a lotação do servidor
# para a lotação de todos os cedidos: (Reitoria - Cedidos - 113)
$lotacaoCedidos = 113;
if (!$erro) {
    # Somente altera a lotação se ele já não estiver na 113
    $lotacaoAtual = $pessoal->get_idLotacao($idServidor);
    if ($lotacaoAtual <> $lotacaoCedidos) {
        # Grava a nova lotação
        $campos = array("idServidor", "data", "lotacao", "motivo");
        $valor = array($idServidor, $dtInicial, $lotacaoCedidos, "Cedido a(o) " . $orgao);
        $pessoal->gravar($campos, $valor, null, "tbhistlot", "idHistLot", false);
    }
}

# Verifica se a data Inicial é posterior a data de saida
$dtSaida = $pessoal->get_dtSaida($idServidor);

# Se tiver data de saida
if (!is_null($dtSaida)) {
    $dtSaida = date_to_bd($dtSaida);
    if ($dtInicial > $dtSaida) {
        $erro = 1;
        $msgErro .= 'O servidor não pode ser cedido DEPOIS de sair da UENF!\n';
    }
}
