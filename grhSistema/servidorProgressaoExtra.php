<?php

/*
 * Rotina Extra de Validação
 * 
 */

# Conecta ao Banco de Dados
$pessoal = new Pessoal();
$classe = new Classe();
$progressao = new Progressao();
$plano = new PlanoCargos();

$dtInicial = date_to_php($campoValor[0]);
$idClasse = $campoValor[2];
$idServidor = $campoValor[7];

# Verifica se a data Inicial é anterior a data de admissão
if (strtotime(date_to_bd($dtInicial)) < strtotime(date_to_bd($pessoal->get_dtAdmissao($idServidor)))) {
    $erro = 1;
    $msgErro .= 'O servidor não pode progredir ANTES de ser admitido!\n';
}

# Verifica se a data Inicial é posterior a data de saida
$dtSaida = $pessoal->get_dtSaida($idServidor);

# Se tiver data de saida
if (!is_null($dtSaida)) {
    if (date_to_bd(strtotime($dtInicial)) > date_to_bd(strtotime($dtSaida))) {
        $erro = 1;
        $msgErro .= 'O servidor não pode progredir DEPOIS de sair da UENF!\n';
    }
}

# Verifica se o desta progressão plano está correto
$idPlanoDigitado = $classe->get_idPlano($idClasse);
$idPlanoCorreto = $progressao->get_planoVigenteNaEpocaServidor($dtInicial, $idServidor);

if ($idPlanoDigitado <> $idPlanoCorreto) {

    $erro = 1;
    $nomePlano = $plano->get_numDecreto($idPlanoCorreto);

    # Verifica pela data
    if (strtotime(date_to_bd($dtInicial)) < strtotime(date_to_bd($plano->get_dtVigencia($idPlanoDigitado)))) {
        $msgErro .= 'A data inicial é anterior a data de vigência deste pĺano. O plano vigente nesta data é o ' . $nomePlano . '\n';
    } else {
        $msgErro .= 'O Plano de cargos está errado! De acordo com a data inicial, o plano vigente nesta data é: ' . $nomePlano . '\n';
    }
}