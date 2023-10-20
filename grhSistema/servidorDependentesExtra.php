<?php

/*
 * Rotina Extra de Validação
 * 
 */

# Conecta ao Banco de Dados
$pessoal = new Pessoal();

# Dados do Dependente
$parentesco = $campoValor[3];
$dtNasc = date_to_php($campoValor[1]);
$auxEducacaoDtInicial = date_to_php($campoValor[7]);

# Dados do Servidor
$idPessoa = $campoValor[14];
$idServidor = $pessoal->get_idServidoridPessoa($idPessoa);
$dtAdmissao = $pessoal->get_dtAdmissao($idServidor);

/*
 *  Auxílio Creche
 */

# verifica se dependente é filho
if ($parentesco == 2 OR $parentesco == 8 OR $parentesco == 9) {


    # Calcula a data limite de termino
    $dataHistoricaFinal = "22/12/2021";     // Data da Publicação da Portaria 95/2021
    $dataIdade = addMeses(addAnos($dtNasc, 6), 11);
    $dataLimite = dataMenor($dataIdade, $dataHistoricaFinal);

    # verifica se data é posterior a data limite
    if ($campoValor[11] > date_to_bd($dataLimite)) {
        $erro = 1;
        $msgErro .= 'A data de término está alem da data limite! (' . $dataLimite . ')\n';
    }
}


/*
 * Auxílio Educação
 */
$dep = new Dependente();
if ($dep->verificaDireitoAuxEduca($parentesco)) {
    $intra = new Intra();
    $dataHistoricaInicial = $intra->get_variavel('dataHistoricaInicialAuxEducacao');
    $campoValor[7] = date_to_bd(dataMaiorArray([$dataHistoricaInicial, $dtAdmissao, $dtNasc]));
}

# salva sempre Não para quando o dependente ja tinha mais de 24 na data histórica
# Pega as datas limites
$anos24 = get_dataIdade($dtNasc, 24);

# Data Histórica Inicial
$intra = new Intra();
$dataHistoricaInicial = $intra->get_variavel('dataHistoricaInicialAuxEducacao');

if (dataMenor($dataHistoricaInicial, $anos24) == $anos24) {
    $campoValor[6] = "Não";
    $campoValor[7] = null;
}
