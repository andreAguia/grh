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
$cpf = $campoValor[2];
$auxEduc = $campoValor[6];
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

# Inicia a classe
$aux = new AuxilioEducacao();
$idadeLimite = $aux->get_idadeFinalLei();

if ($auxEduc == "Sim") {

    if ($aux->verificaDireitoAuxEduca($parentesco)) {
        $intra = new Intra();
        $dataHistoricaInicial = $intra->get_variavel('dataHistoricaInicialAuxEducacao');
        $campoValor[7] = date_to_bd(dataMaiorArray([$dataHistoricaInicial, $dtAdmissao, $dtNasc]));
    } else {
        $erro = 1;
        $msgErro .= 'esse dependente Não Tem Direito ao Auxílio Educação\n';
    }
}

# Verifica se o CPF foi preenchido quando se cadastra SIM para o auxilio Educação
# Retirado a pedido de Débora
//if ($auxEduc == "Sim" AND empty($cpf)) {
//    $erro = 1;
//    $msgErro .= 'O CPF deverá ser informado para o dependente com Auxílio Educação\n';
//}
# Coloca o auxEducação como Não quando tinha 24 anos ou mais na data da publicação da lei
$intra = new Intra();
$dataHistoricaInicial = $intra->get_variavel('dataHistoricaInicialAuxEducacao');

$anos24 = get_dataIdade($dtNasc, $idadeLimite);
if (dataMenor($dataHistoricaInicial, $anos24) == $anos24) {
    $campoValor[6] = "Não";
    $campoValor[7] = null;
}
