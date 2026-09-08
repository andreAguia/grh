<?php

/*
 * Rotina Extra de Validação
 * 
 */

# Conecta ao Banco de Dados
$pessoal = new Pessoal();

$dtInicial = date_to_php($campoValor[6]);
$numDias = $campoValor[7];
$dtTermino = $campoValor[8];
$dtRetorno = $campoValor[9];
$idServidor = $campoValor[18];

# Preenche a data de término quando for nula
if (empty($dtTermino)) {
    if (!empty($dtInicial)) {
        $campoValor[8] = date_to_bd(addDias($dtInicial, $numDias));
        $dtTermino = $campoValor[8];
    }
}

if (empty($numDias)) {
    $campoValor[8] = null;
    $campoValor[6] = null;
}

/*
 *  Verifica se já tem outro afastamento nesse período
 */
if (!empty($dtInicial) AND !empty($numDias)) {
    $verifica = new VerificaAfastamentos($idServidor);
    $verifica->setPeriodo($dtInicial, addDias($dtInicial, $numDias));
    $verifica->setIsento("tblicencasemvencimentos", $id);

    if ($verifica->verifica()) {
        $erro = 1;
        $msgErro .= 'Já existe um(a) ' . $verifica->getAfastamento() . ' (' . $verifica->getDetalhe() . ') nesse período!\n';
    }
}

/*
 *  Verifica a aposentadoria compulsória
 */

# Pega a data compulsória
$aposentadoria = new Aposentadoria();

if (!is_null($aposentadoria->get_dataAposentadoriaCompulsoria($idServidor))) {
    $dataCompulsoria = $aposentadoria->get_dataAposentadoriaCompulsoria($idServidor);

    # Verifica a data de termino
    if ($dtTermino >= date_to_bd($dataCompulsoria)) {
        $erro = 1;
        $msgErro .= 'A Data da aposentadoria compulsória deste servidor é ' . $dataCompulsoria . '. Todos os afastamentos deverão iniciar e terminar antes desta data!\n';
    }
}

/*
 *  Verifica se a ultima licença sem vencimentos foi de 4 anos
 *  Pois se for tem que cumprir o período de 1 ano
 */

# Pega a ultima licença
$lsv = new LicencaSemVencimentos();
$ultimaLicenca = $lsv->get_idUltimaLicenca($idServidor);

echo "id: {$id} = {$ultimaLicenca["idLicencaSemVencimentos"]}";

# Verifica se não está editando a ultima licença
if (!empty($id) AND $id <> $ultimaLicenca["idLicencaSemVencimentos"]) {

# Verifica o tempo acumulado dessa ultima licença
    if ($lsv->get_ultimoTempoConsecutivo($idServidor) >= 1460) {
        # Pega os dados desta licença
        $dados = $lsv->get_dados($ultimaLicenca["idLicencaSemVencimentos"]);

        # Verifica se a data de inicio atende a anualidade exigida pela lei
        # A data de termino
        $dtTermino = date_to_php($dados["dtTermino"]);

        # Um ano após
        $dataLimite = addAnos($dtTermino, 1);

        if (dataMaior($dataLimite, $dtInicial) == $dataLimite) {
            $erro = 1;
            $msgErro .= 'O servidor tem que aguardar 1 ano a contar do término da última licença sem vencimentos para solicitar outra licença sem vencimentos. Somente após ' . $dataLimite . '.\n';
        }
    }
}
