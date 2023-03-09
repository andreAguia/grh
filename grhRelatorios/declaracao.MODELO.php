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

    # Servidor
    $nomeServidor = $pessoal->get_nome($idServidorPesquisado);
    $idFuncional = $pessoal->get_idFuncional($idServidorPesquisado);
    $lotacao = $pessoal->get_lotacao($idServidorPesquisado);
    $dtAdmin = $pessoal->get_dtAdmissao($idServidorPesquisado);
    $cargoEfetivo = $pessoal->get_cargoCompleto($idServidorPesquisado);
    $idPerfil = $pessoal->get_idPerfil($idServidorPesquisado);

    if ($idPerfil == 1) {
        $dtAdmin = "{$dtAdmin}, através de Concurso Público";
        $cargoEfetivo = "para o cargo de {$cargoEfetivo},";
    }

    # Começa uma nova página
    $page = new Page();
    $page->iniciaPagina();

    $identificacao = "Declaro, para os devidos fins, que o(a) servidor(a)"
            . " <b>" . strtoupper($nomeServidor) . "</b>,"
            . " ID funcional nº {$idFuncional}, "
            . " admitido(a) em {$dtAdmin},"
            . " {$cargoEfetivo} "
            . "lotado(a) no(a) {$lotacao}, ";

    # Monta a Declaração
    $dec = new Declaracao();
    #$dec->set_carimboCnpj(true);
    $dec->set_assinatura(true);
    $dec->set_data(date("d/m/Y"));
    $dec->set_texto("{$identificacao} bla bla bla");
    $dec->set_saltoAssinatura(2);
    $dec->show();

    # Grava o log da visualização do relatório
    $data = date("Y-m-d H:i:s");
    $atividades = 'Visualizou a declaração eleitoral de rendimentos';
    $tipoLog = 4;
    $intra->registraLog($idUsuario, $data, $atividades, null, null, $tipoLog, $idServidorPesquisado);

    $page->terminaPagina();
}