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
    
    # Pega o número de faltas
    $faltas = new Faltas();
    $numfaltas = $faltas->getNumFaltasServidor($idServidorPesquisado);
    
    # Exibe alereta de faltas
    if ($numfaltas > 0) {
        alert("ATENÇÃO !! Este Servidor TEM {$numfaltas} FALTA(S) cadastrada(s) no sistema!");
    }

    # Servidor
    $nomeServidor = $pessoal->get_nome($idServidorPesquisado);
    $idFuncional = $pessoal->get_idFuncional($idServidorPesquisado);
    $cargoEfetivo = $pessoal->get_cargoCompleto($idServidorPesquisado, false);
    $sexo = $pessoal->get_sexo($idServidorPesquisado);
    $idPerfil = $pessoal->get_idPerfil($idServidorPesquisado);

    if ($idPerfil == 2) {
        $cargoEfetivo = "exercendo a função equivalente ao {$cargoEfetivo}";
    }

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
    $dec->set_carimboCnpj(true);
    $dec->set_assinatura(true);

    $dec->set_data(date("d/m/Y"));

    $dec->set_texto("Declaro para os devidos fins, que {$texto1} <b>" . strtoupper($nomeServidor) . "</b>,"
            . " ID funcional nº {$idFuncional}, {$cargoEfetivo}, não está respondendo a inquérito administrativo por"
            . " comunicação de faltas nesta Universidade Estadual do Norte Fluminense Darcy Ribeiro.");

    $dec->set_saltoRodape(10);

    # Exibe aviso de que tem faltas
    if ($numfaltas > 0) {
        $dec->set_aviso("ATENÇÃO !! Este Servidor TEM {$numfaltas} FALTA(S) cadastrada(s) no sistema!");
    }

    $dec->show();

    # Grava o log da visualização do relatório
    $data = date("Y-m-d H:i:s");
    $atividades = 'Visualizou a declaração que NÃO responde a inquérito administrativo';
    $tipoLog = 4;
    $intra->registraLog($idUsuario, $data, $atividades, null, null, $tipoLog, $idServidorPesquisado);

    $page->terminaPagina();
} else {
    echo "Ocorreu um erro !!";
}