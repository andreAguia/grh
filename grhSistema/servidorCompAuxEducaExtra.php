<?php

/*
 * Rotina Extra de Validação
 * 
 */

# Conecta ao Banco de Dados
$pessoal = new Pessoal();
$dependente = new Dependente();

# Pega os dados do dependente
$idDependente = $campoValor[4];
$dados = $dependente->get_dados($idDependente);
$dtNasc = date_to_php($dados["dtNasc"]);

# Datas Limite
$aux = new AuxilioEducacao();

# Pega as datas limite desse dependente
$dataInicialCobranca = $aux->get_data21Anos($idDependente);
$dataFinalCobranca = $aux->get_data25AnosMenos1Dia($idDependente);
$dataFinalCobrancaMaisUmDia = addDias($dataFinalCobranca, 1, false);

# Pega as datas digitadas
$dataInicial = date_to_php($campoValor[0]);
$dataTermino = date_to_php($campoValor[1]);

# Verifica se a data inicial é posterior a data de encerramento do direito
if (dataMenor($dataInicial, $dataFinalCobrancaMaisUmDia) == $dataFinalCobrancaMaisUmDia) {
    $erro = 1;
    $msgErro .= 'A data de início está alem da data limite! (' . $dataFinalCobranca . ')\n';
}

# Verifica se a data termino é posterior a data de encerramento do direito
if (dataMenor($dataTermino, $dataFinalCobrancaMaisUmDia) == $dataFinalCobrancaMaisUmDia) {
    $erro = 1;
    $msgErro .= 'A data de Término está alem da data limite! (' . $dataFinalCobranca . ')\n';
}

# Verifica se a data inicial é anterior a data de 21anos
if ($dataInicial <> $dataInicialCobranca AND dataMenor($dataInicial, $dataInicialCobranca) == $dataInicial) {
    $erro = 1;
    $msgErro .= 'A data de início deverá ser posterior a data de aniversário de 21 anos! (' . $dataInicialCobranca . ')\n';
}