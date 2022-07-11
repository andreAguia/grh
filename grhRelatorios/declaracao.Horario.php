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
    $dtAdmin = $pessoal->get_dtAdmissao($idServidorPesquisado);
    $lotacao = $pessoal->get_lotacao($idServidorPesquisado);
    $situacao = $pessoal->get_idSituacao($idServidorPesquisado);

    # Começa uma nova página
    $page = new Page();
    $page->iniciaPagina();

    # Monta a Declaração
    $dec = new Declaracao();
    #$dec->set_carimboCnpj(true);
    $dec->set_assinatura(true);
    $dec->set_data(date("d/m/Y"));

    $dec->set_texto("Declaro para os devidor fins que o(a) servidor(a) <b>" . strtoupper($nomeServidor) . "</b>,"
            . " ID funcional nº {$idFuncional}, admitido(a) em {$dtAdmin}, através de Concurso Público, lotado(a) no(a)"
            . " {$lotacao} vem cumprindo normalmente sua carga"
            . " horária de 40 horas semanais, de 8 h às 18 h, de segunda á sexta, com 2 h de almoço.");

    if ($situacao == 1) {
        $dec->show();
    } else {
        # Limita o tamanho da tela
        $grid = new Grid("center");
        $grid->abreColuna(8);
        br(6);

        callout("ATENÇÃO !!! <br/>Este servidor não está ativo. A declaração não poderá ser emitida.");

        $grid->fechaColuna();
        $grid->fechaGrid();
    }

    # Grava o log da visualização do relatório
    $data = date("Y-m-d H:i:s");
    $atividades = 'Visualizou a declaração de horário';
    $tipoLog = 4;
    $intra->registraLog($idUsuario, $data, $atividades, null, null, $tipoLog, $idServidorPesquisado);

    $page->terminaPagina();
}