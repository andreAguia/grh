<?php

/*
 * Rotina Extra de Validação
 * 
 */

$dtInicio = date_to_php($campoValor[1]);
$dtFim = date_to_php($campoValor[2]);

$licenca = new LicencaPremio();

# Preenche a data final quando deixada em branco
if (empty($dtFim)) {
    # Define a data como sendo 5 anos menos 1 dia
    $dtFim = addAnos($dtInicio, 5);
    $dtFim = addDias($dtFim, -1, false);
    $campoValor[2] = date_to_bd($dtFim);
}

# Informa o período em anos
$dias = getNumDias($dtInicio, $dtFim);

$anos = intval($dias / 365);

# Informa se teve menos que 5 aos de período aquisitivo
if ($anos < 5) {
    $erro = 1;
    $msgErro .= 'O Período Aquisitivo não pode ser menor que 5 anos! (' . $dias . ' dias).\n';
}

# Verifica se a data inicial é posterior a data final do período anterior
if (!empty($id)) {
    
    # Pega o valor da data do fim do período do periodo anterior
    $dataAnterior = $licenca->get_dataFinalPeriodoAnterior($id);   
    
    
    if (strtotime($campoValor[1]) <= strtotime(date_to_bd($dataAnterior))) {
        $erro = 1;
        $msgErro .= 'A Data Inicial do Período aquisitivo deve ser posterior a ' . $dataAnterior . '. Que foi a data de término do período anterior!.\n';
    }
}