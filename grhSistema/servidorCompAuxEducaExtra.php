<?php

/*
 * Rotina Extra de Validação
 * 
 */

# Conecta ao Banco de Dados
$pessoal = new Pessoal();
$dependente = new Dependente();

# Pega os dados do dependente
$idDependente = $campoValor[3];
$dados = $dependente->get_dados($idDependente);
$dtNasc = date_to_php($dados["dtNasc"]);

# Datas Limite
$aux = new AuxilioEducacao();

$anos21 = get_dataIdade($dtNasc, $aux->get_idadeInicial());
$anos24 = get_dataIdade($dtNasc, $aux->get_idadeFinal());
$anos24Mais1 = addDias($anos24, 1, false);
$dataInicial = date_to_php($campoValor[0]);
$dataTermino = date_to_php($campoValor[1]);

# Verifica se a data inicial é posterior a data de encerramento do direito
if (dataMenor($dataInicial, $anos24Mais1) == $anos24Mais1) {
    $erro = 1;
    $msgErro .= 'A data de início está alem da data limite! (' . $anos24 . ')\n';
}

# Verifica se a data termino é posterior a data de encerramento do direito
if (dataMenor($dataTermino, $anos24Mais1) == $anos24Mais1) {
    $erro = 1;
    $msgErro .= 'A data de Término está alem da data limite! (' . $anos24 . ')\n';
}

# Verifica se a data inicial é anterior a data de 21anos
if (dataMenor($dataInicial, $anos21) == $dataInicial) {
    $erro = 1;
    $msgErro .= 'A data de início deverá ser posterior a data de aniversário de 21 anos! (' . $anos21 . ')\n';
}