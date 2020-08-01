<?php

/*
 * Rotina Extra de Validação
 * 
 */

$exercicio = $campoValor[0];    // Ano exercício
$dias = $campoValor[2];         // Dias solicitado
$idServidor = $campoValor[4];   // idServidor
$dtInicial = $campoValor[1];  // dataInicial 
# Define a data de hoje
$hoje = date("Y-m-d");

# Muda o status para solicitada ou fruída de acordo com a data Inicial e a data de hoje
if ($dtInicial <= $hoje) {
    $campoValor[5] = "fruída";
} else {
    $campoValor[5] = "solicitada";
}

# Conecta ao banco de dados
$pessoal = new Pessoal();

# Verifica se a data Inicial é anterior a data de admissão
$dtAdmissao = $pessoal->get_dtAdmissao($idServidor);
$dtAdmissao = date_to_bd($dtAdmissao);
if ($dtInicial < $dtAdmissao) {
    $erro = 1;
    $msgErro .= 'O servidor não pode pedir férias ANTES de ser admitido!\n';
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
        $msgErro .= 'O servidor não tem mais dias disponíveis para férias nesse período!\n';
        break;

    case 20:
        if ($dias > 10) {
            $erro = 1;
            $msgErro .= 'O servidor não pode tirar mais de 30 dias de férias em um mesmo exercício!\n';
        }
        break;

    case 15:
        if ($dias <> 15) {
            $erro = 1;
            $msgErro .= 'O servidor só poderá tirar 15 dias nesse exercício!\n';
        }
        break;

    case 10:
        if (($dias <> 10) AND ($dias <> 20)) {
            $erro = 1;
            $msgErro .= 'O servidor já tem 10 dias de férias, só poderá pedir mais 20 ou 10 dias nesse exercício\n';
        }
        break;
}

# Verifica se já tem outro afastamento nesse período
$dtFinal = addDias(date_to_php($dtInicial), $dias);

$verifica = new VerificaAfastamentos($idServidor, date_to_php($dtInicial), $dtFinal);
$verifica->setIsento("tbferias", $id);
$outro = $verifica->verifica();
if (!empty($outro)) {
    $msgErro .= 'Já existe um(a) '.$outro.' nesse período!\n';
    $erro = 1;
}
