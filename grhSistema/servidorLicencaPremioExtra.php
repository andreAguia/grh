<?php

/*
 * Rotina Extra de Validação
 * 
 */

# Conecta ao Banco de Dados
$pessoal = new Pessoal();

$dtInicial = $campoValor[0];
$numDias = $campoValor[1];
$dtTermino = $campoValor[2];
$idPublicacaoPremio = $campoValor[4];
$idServidor = $campoValor[6];

# Preenche a data de término quando for nula
if (empty($dtTermino)) {
    $campoValor[2] = date_to_bd(addDias(date_to_php($dtInicial), $numDias));
    $dtTermino = $campoValor[2];
}

# Verifica se a data Inicial é anterior a data de admissão
if (strtotime($dtInicial) < strtotime(date_to_bd($pessoal->get_dtAdmissao($idServidor)))) {
    $erro = 1;
    $msgErro .= 'O servidor não pode pedir Licença ANTES de ser admitido!\n';
}

# Verifica se a data Inicial é posterior a data de saida
$dtSaida = $pessoal->get_dtSaida($idServidor);

# Se tiver data de saida
if (!is_null($dtSaida)) {
    $dtSaida = date_to_bd($dtSaida);
    if ($dtInicial > $dtSaida) {
        $erro = 1;
        $msgErro .= 'O servidor não pode pedir licença DEPOIS de sair da UENF!\n';
    }
}

# Verifica se já tem outro afastamento nesse período
$dtFinal = addDias(date_to_php($dtInicial), $numDias);

$verifica = new VerificaAfastamentos($idServidor, date_to_php($dtInicial), $dtFinal);
$verifica->setIsento("tblicencapremio", $id);
$outro = $verifica->verifica();
if (!empty($outro[0])) {
    $msgErro .= 'Já existe um(a) ' . $outro[0] . ' nesse período!\n';
    $erro = 1;
}

/*
 *  Verifica se já tem outro afastamento nesse período
 */

$verifica = new VerificaAfastamentos($idServidor);
$verifica->setPeriodo(date_to_php($dtInicial), addDias(date_to_php($dtInicial), $numDias));
$verifica->setIsento("tblicencapremio", $id);

if ($verifica->verifica()) {
    $erro = 1;
    $msgErro .= 'Já existe um(a) ' . $verifica->getAfastamento() . ' (' . $verifica->getDetalhe() . ') nesse período!\n';
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
 * Verifica a regra da anuidade
 */

#strtotime(date_to_bd($data1));

# Verifica se é inclusão
if (empty($id)) {
    $licenca = new LicencaPremio();
    $proximaData = date_to_bd($licenca->get_proximaData($idServidor));

    if ($dtInicial < $proximaData) {
        $erro = 1;
        $msgErro .= 'A Data inicial da Licença deverá respeitar a Regra da Anuidade!\n';
    }
}

/*
 * Verifica se a data inicial da fruição da licença é igual ou posterior a data final do período aquisitivo
 */

# Pega os dados da publicação dessa licença
$publicacaoPremio = new PublicacaoPremio();
$dados = $publicacaoPremio->get_dados($idPublicacaoPremio);

# Pega a data inicial do período escolhido
if (strtotime($dtInicial) <= strtotime($dados["dtFimPeriodo"])) {
    $erro = 1;
    $msgErro .= 'A data de fruição não pode ser anterior ao término do período aquisitivo!\n';
}