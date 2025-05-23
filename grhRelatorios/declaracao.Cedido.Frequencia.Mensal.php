<?php

/**
 * Sistema GRH
 * 
 * Relatório
 *   
 * By Alat
 */
# Inicia as variáveis que receberão as sessions
$idUsuario = null;
$idServidorPesquisado = null;

# Configuração
include ("../grhSistema/_config.php");

# Permissão de Acesso
$acesso = Verifica::acesso($idUsuario, [1, 2, 12]);

if ($acesso) {
    # Conecta ao Banco de Dados
    $pessoal = new Pessoal();

    # Pega os parâmetros dos relatórios
    $relatorioMes = post('mes', date('m'));
    $relatorioAno = post('ano', date('Y'));
    
    ###### Parei aqui . Tem que tratar o mês e o ano para nunca declarar o mês vigente

    # Desde de qual data?
    $dataInicial = "02/01/2020";

    # Pega as faltas do servidor (se tiver)
    $falta = new Faltas();
    $numFaltas = $falta->getFaltasServidor($idServidorPesquisado);
    $ultimaFalta = null;
    $numDiasFaltas = 0;
    $erro = false;

    # Percorre o array
    if (count($numFaltas) > 0) {
        foreach ($numFaltas as $item) {
            $ultimaFalta = addDias(date_to_php($item["dtInicial"]), $item["numDias"]);
            $numDiasFaltas += $item["numDias"];
        }

        if ($ultimaFalta == dataMaior($dataInicial, $ultimaFalta)) {

            # Limita o tamanho da tela
            $grid = new Grid("center");
            $grid->abreColuna(8);
            br(6);

            callout("ATENÇÃO !!! <br/>Este servidor teve {$item["numDias"]} falta(s) a partir de " . date_to_php($item["dtInicial"]) . "!<br/>Lamento, mas esta declaração de que o servidor não tem faltas desde {$dataInicial} não poderá ser emitida");
            $erro = true;

            $grid->fechaColuna();
            $grid->fechaGrid();
        }
    }

    # Servidor
    $nomeServidor = $pessoal->get_nome($idServidorPesquisado);
    $idFuncional = $pessoal->get_idFuncional($idServidorPesquisado);
    $lotacao = $pessoal->get_lotacao($idServidorPesquisado);
    $cargoEfetivo = $pessoal->get_cargoSimples($idServidorPesquisado);
    $sexo = $pessoal->get_sexo($idServidorPesquisado);

    # Altera parte do texto de acordo com o sexo (gênero) do servidor
    if ($sexo == "Masculino") {
        $texto1 = "o servidor";
    } else {
        $texto1 = "a servidora";
    }

    # Começa uma nova página
    $page = new Page();
    $page->iniciaPagina();

    # Monta a Declaração
    $dec = new Declaracao();
    #$dec->set_carimboCnpj(true);
    $dec->set_assinatura(true);

    $dec->set_formCampos(array(
        array('nome' => 'ano',
            'label' => 'Ano:',
            'tipo' => 'texto',
            'size' => 4,
            'title' => 'Ano',
            'onChange' => 'formPadrao.submit();',
            'padrao' => $relatorioAno,
            'col' => 3,
            'linha' => 1),
        array('nome' => 'mes',
            'label' => 'Mês',
            'tipo' => 'combo',
            'array' => $mes,
            'size' => 10,
            'padrao' => $relatorioMes,
            'title' => 'Mês do Ano.',
            'onChange' => 'formPadrao.submit();',
            'col' => 3,
            'linha' => 1)));
    
    $dec->set_formLink('?');

    if (!empty($ultimaFalta)) {
        $dec->set_aviso("Consta {$numDiasFaltas} dias de falta(s) cadastrada(s) no sistema. Sendo a última em {$ultimaFalta}.<br/>Mas, apesar disso, a declaração que o servidor teve frequencia integral desde {$dataInicial} poderá ser emitida.");
    } else {
        $dec->set_aviso("Não existe nenhuma falta cadastrada no sistema para este servidor.");
    }

    $dec->set_data(date("d/m/Y"));

    $dec->set_texto("Declaro para fins de frequência, que {$texto1} <b>" . strtoupper($nomeServidor) . "</b>,"
            . " ID funcional nº {$idFuncional}, lotado(a) no(a) {$lotacao}, {$cargoEfetivo}, teve sua frequência INTEGRAL no mês de " . get_nomeMes($relatorioMes) . " de {$relatorioAno}.");

    $dec->set_saltoAssinatura(2);
    if (!$erro) {
        $dec->show();
    }

    # Grava o log da visualização do relatório
    $data = date("Y-m-d H:i:s");
    $atividades = 'Visualizou a declaração eleitoral de frequência';
    $tipoLog = 4;
    $intra->registraLog($idUsuario, $data, $atividades, null, null, $tipoLog, $idServidorPesquisado);

    $page->terminaPagina();
}