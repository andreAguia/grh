<?php

/*
 * Rotina Extra de Validação
 * 
 */

# Conecta ao Banco de Dados
$pessoal = new Pessoal();

$idTpLicenca = $campoValor[0];
$alta = $campoValor[1];
$dtInicioPeriodo = $campoValor[2];
$dtFimPeriodo = $campoValor[3];
$dtInicial = $campoValor[4];
$numDias = $campoValor[5];
$dtTermino = $campoValor[6];
$processo = $campoValor[7];
$dtPublicacao = $campoValor[8];
$dtPericia = $campoValor[9];
$num_Bim = $campoValor[10];
$obs = $campoValor[11];
$idServidor = $campoValor[12];

/*
 * Verifica se digitou a data final oiu o numero de dias e preenche automaticamente
 */

if (empty($dtTermino)) {
    $campoValor[6] = date_to_bd(addDias(date_to_php($dtInicial), $numDias));
    $dtTermino = $campoValor[6];
}

/*
 *  Verifica se o tipo de licença foi informado
 */
if ($idTpLicenca == "Inicial") {
    $msgErro .= 'Tem que informar o tipo da licença!\n';
    $erro = 1;
} else {
    /*
     *  Verifica se já tem outro afastamento nesse período
     */

    $verifica = new VerificaAfastamentos($idServidor);
    $verifica->setPeriodo(date_to_php($dtInicial), addDias(date_to_php($dtInicial), $numDias));
    $verifica->setIsento("tblicenca", $id);
    $verifica->setTipoLicenca($idTpLicenca);

    if ($verifica->verifica()) {
        $erro = 1;
        $msgErro .= 'Já existe um(a) ' . $verifica->getAfastamento() . ' (' . $verifica->getDetalhe() . ') nesse período!\n(' . $id . ')';
    }

    /*
     *  Verifica restrição de genero para esse afastamento
     */
    $sexo = $pessoal->get_sexo($idServidor);
    $restricao = $pessoal->get_licencaSexo($idTpLicenca);

    # Feminino
    if ($restricao == "Feminino") {
        if ($sexo <> "Feminino") {
            $msgErro .= 'Esse tipo de licença só é permitido para servidores do sexo feminino!\n';
            $erro = 1;
        }
    }

    # Masculino
    if ($restricao == "Masculino") {
        if ($sexo <> "Masculino") {
            $msgErro .= 'Esse tipo de licença só é permitido para servidores do sexo masculino!\n';
            $erro = 1;
        }
    }

    /*
     * Verifica se nas licenças artigo 110 e 111, ou seja 1 ou 30, tem a alta digitada
     */
    if (($idTpLicenca == 1) OR ($idTpLicenca == 30)) {
        if (empty($alta)) {
            $msgErro .= 'E necessario informar se teve ou não alta!\n';
            $erro = 1;
        }
    }


    /*
     *  Apaga a alta se nao for licenca medica
     */
    if (($idTpLicenca <> 1) AND ($idTpLicenca <> 30) AND ($idTpLicenca <> 2)) {
        $campoValor[1] = null;
        $campoValor[2] = null;
    }

    /*
     *  Apaga o periodo aquisitivo quando não precisa
     */
    if ($pessoal->get_licencaPeriodo($idTpLicenca) == "Não") {
        $campoValor[2] = null;
        $campoValor[3] = null;
    }

    /*
     *  Apaga o processo quando não precisa
     */
    if ($pessoal->get_licencaProcesso($idTpLicenca) == "Não") {
        $campoValor[7] = null;
    }

    /*
     *  Apaga a publicação quando não precisa
     */
    if ($pessoal->get_licencaPublicacao($idTpLicenca) == "Não") {
        $campoValor[8] = null;
    }

    /*
     *  Apaga a perícia quando não precisa
     */
    if ($pessoal->get_licencaPericia($idTpLicenca) == "Não") {
        $campoValor[9] = null;
        $campoValor[10] = null;
    }

    /*
     *  Verifica se a data Inicial é anterior a data de admissão
     */

    $dtAdmissao = date_to_bd($pessoal->get_dtAdmissao($idServidor));

    if ($dtInicial < $dtAdmissao) {
        $erro = 1;
        $msgErro .= 'O servidor não pode pedir Licença ANTES de ser admitido!\n';
    }

    /*
     *  Verifica se a data Inicial é posterior a data de saida
     */
    $dtSaida = $pessoal->get_dtSaida($idServidor);

# Se tiver data de saida
    if (!is_null($dtSaida)) {
        $dtSaida = date_to_bd($dtSaida);
        if ($dtInicial > $dtSaida) {
            $erro = 1;
            $msgErro .= 'O servidor não pode pedir licença DEPOIS de sair da UENF!\n';
        }
    }

    # Preenche a data de término quando for nula
    if (vazio($dtTermino)) {
        if (!vazio($dtInicial)) {
            $campoValor[6] = date_to_bd(addDias($dtInicial, $numDias));
            $dtTermino = $campoValor[6];
        }
    }

    /*
     *  Verifica a aposentadoria compulsória
     */

    # Pega a data compulsória
    $compulsoria = new AposentadoriaCompulsoria();

    if (!is_null($compulsoria->getDataAposentadoriaCompulsoria($idServidor))) {
        $dataCompulsoria = $compulsoria->getDataAposentadoriaCompulsoria($idServidor);

        # Verifica a data de termino
        if ($dtTermino >= date_to_bd($dataCompulsoria)) {
            $erro = 1;
            $msgErro .= 'A Data da aposentadoria compulsória deste servidor é ' . $dataCompulsoria . '. Todos os afastamentos deverão iniciar e terminar antes desta data!\n';
        }
    }
}