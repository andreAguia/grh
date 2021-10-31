<?php

/*
 * Rotina Extra de Validação
 * 
 */

# Pega os valores digitados
$exercicio = $campoValor[0];
$numDias = $campoValor[2];
$idServidor = $campoValor[4];
$dtInicial = $campoValor[1];

# Conecta ao banco de dados
$pessoal = new Pessoal();

# Pega o perfil do servidor
$idPerfil = $pessoal->get_idPerfil($idServidor);

# Define a data de hoje
$hoje = date("Y-m-d");

# Muda o status para solicitada ou fruída de acordo com a data Inicial e a data de hoje
if ($dtInicial <= $hoje) {
    $campoValor[5] = "fruída";
} else {
    $campoValor[5] = "solicitada";
}

# Verifica se nao e ferias antecipadas. Com o inicio anterior ao ano exercicio
$anoInicio = year(date_to_php($dtInicial));

if($anoInicio < $exercicio){
    $erro = 1;
    $msgErro .= 'Não se pode marcar ferias de '.$exercicio.' começando em '.$anoInicio.'!\n';
}

# Verifica se a data Inicial é anterior a data de admissão
$dtAdmissao = $pessoal->get_dtAdmissao($idServidor);

# Somente para quem né cedido, pois pode ter férias em seu órgão de origem
if ($idPerfil <> 2) { 
    $dtAdmissao = date_to_bd($dtAdmissao);
    if ($dtInicial < $dtAdmissao) {
        $erro = 1;
        $msgErro .= 'O servidor não pode pedir férias ANTES de ser admitido!\n';
    }
}

# Verifica se a data Inicial é posterior a data de saida
$dtSaida = $pessoal->get_dtSaida($idServidor);

# Se tiver data de saida
if (!is_null($dtSaida)) {
    $dtSaida = date_to_bd($dtSaida);
    if ($dtInicial > $dtSaida) {
        $erro = 1;
        $msgErro .= 'O servidor não pode pedir férias DEPOIS de sair da UENF!\n';
    }
}

# Verifica quantos dias o servidor já pediu nesse exercicio
$diasFerias = $pessoal->get_feriasSomaDias($exercicio, $idServidor, $id);

switch ($diasFerias) {

    case 30 :       // Já pediu o limite
        $erro = 1;
        $msgErro .= 'O servidor não tem mais dias disponíveis para férias nesse ano exercicio!\n';
        break;

    case 20:
        if ($numDias > 10) {
            $erro = 1;
            $msgErro .= 'O servidor não pode tirar mais de 30 dias de férias em um mesmo exercício!\n';
        }
        break;

    case 15:
        if ($numDias <> 15) {
            $erro = 1;
            $msgErro .= 'O servidor só poderá tirar 15 dias nesse exercício!\n';
        }
        break;

    case 10:
        if (($numDias <> 10) AND ($numDias <> 20)) {
            $erro = 1;
            $msgErro .= 'O servidor já tem 10 dias de férias, só poderá pedir mais 20 ou 10 dias nesse exercício\n';
        }
        break;
}

/*
 *  Verifica se já tem outro afastamento nesse período
 */

$verifica = new VerificaAfastamentos($idServidor);
$verifica->setPeriodo(date_to_php($dtInicial), addDias(date_to_php($dtInicial), $numDias));
$verifica->setIsento("tbferias", $id);

if ($verifica->verifica()) {
    $erro = 1;
    $msgErro .= 'Já existe um(a) ' . $verifica->getAfastamento() . ' (' . $verifica->getDetalhe() . ') nesse período!\n';
}
