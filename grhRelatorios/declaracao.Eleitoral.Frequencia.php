<?php

/**
 * Sistema GRH
 * 
 * Relatório
 *   
 * By Alat
 */
# Inicia as variáveis que receberão as sessions
$idUsuario = null;              # Servidor logado
$idServidorPesquisado = null; # Servidor Editado na pesquisa do sistema do GRH
# Configuração
include ("../grhSistema/_config.php");

# Permissão de Acesso
$acesso = Verifica::acesso($idUsuario, [1, 2, 12]);

if ($acesso) {
    # Conecta ao Banco de Dados
    $pessoal = new Pessoal();
    
    # Começa uma nova página
    $page = new Page();
    $page->iniciaPagina();

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
    #$cargoEfetivo = $pessoal->get_cargoCompleto($idServidorPesquisado, false);
    $cargoEfetivo = $pessoal->get_cargoSimples($idServidorPesquisado);
    $sexo = $pessoal->get_sexo($idServidorPesquisado);
    $idPerfil = $pessoal->get_idPerfil($idServidorPesquisado);
    
    # Começa o texto
    $texto = "Declaro para fins de afastamento eleitoral, que ";

    # Altera parte do texto de acordo com o sexo (gênero) do servidor
    if ($pessoal->get_perfilTipo($idPerfil) == "Concursados") {
        if ($sexo == "Masculino") {
            $texto .= "o servidor";
        } else {
            $texto .= "a servidora";
        }
    }
    
    # O nome do servidor
    $texto .= " <b>" . strtoupper($nomeServidor) . "</b>,";

    # O id(se tiver)
    if (!empty($idFuncional)) {
        $texto .= " ID funcional nº {$idFuncional},";
    }
    
    # Altera o texto de acordo com o perfil do servidor
    $textoExtra = null;
    if ($idPerfil == 2) {
        if ($sexo == "Masculino") {
            $texto .= " cedido do(a) {$pessoal->get_orgaoCedidoFora($idServidorPesquisado)} a esta Universidade, ";
        } else {
            $texto .= " cedida do(a) {$pessoal->get_orgaoCedidoFora($idServidorPesquisado)} a esta Universidade, ";
        }
    }
    
    # Continua o texto
    $texto .= " {$cargoEfetivo}, teve sua frequência INTEGRAL no período de {$dataInicial} até a presente data.";

    # Monta a Declaração
    $dec = new Declaracao("DECLARAÇÃO ELEITORAL DE FREQUÊNCIA");
    $dec->set_assinatura(true);

    # Verifica e avisa se tem ou não faltas para este servidor
    if (!empty($ultimaFalta)) {
        $dec->set_aviso("Consta {$numDiasFaltas} dias de falta(s) cadastrada(s) no sistema. Sendo a última em {$ultimaFalta}.<br/>Mas, apesar disso, a declaração que o servidor teve frequencia integral desde {$dataInicial} poderá ser emitida.");
    } else {
        $dec->set_aviso("Não existe nenhuma falta cadastrada no sistema para este servidor.");
    }

    $dec->set_data(date("d/m/Y"));
    $dec->set_texto($texto);

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