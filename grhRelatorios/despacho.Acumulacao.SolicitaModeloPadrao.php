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

# Pega as variáveis
$postAssinatura = post('postAssinatura');

# Permissão de Acesso
$acesso = Verifica::acesso($idUsuario, [1, 2, 12]);

if ($acesso) {

    # Conecta ao Banco de Dados
    $pessoal = new Pessoal();

    # Começa uma nova página
    $page = new Page();
    $page->iniciaPagina();

    # despacho
    $despacho = new Despacho();
    $despacho->set_destino("Prezado(a) servidor(a) {$pessoal->get_nome($idServidorPesquisado)}");
    $despacho->set_texto("Solicitamos que a Declaração Anual de Acumulação de Cargos Públicos seja encaminhada através do modelo padrão, disponível no nosso site, trazendo a assinatura manual no documento, a fim de evitar problemas futuros, quando da aposentadoria.");
    $despacho->set_texto("Buscando agilizar, encaminhamos o link com os modelos e as orientações gerais.");
    $despacho->set_texto("<a href='https://uenf.br/dga/grh/gerencia-de-recursos-humanos/acumulacao-de-cargos/declaracao-anual-de-acumulacao-de-cargos/'>https://uenf.br/dga/grh/gerencia-de-recursos-humanos/acumulacao-de-cargos/declaracao-anual-de-acumulacao-de-cargos/</a>");
    $despacho->set_texto("Ficamos no aguardo de novo envio, com a maior brevidade possível.");
    $despacho->set_texto("Atenciosamente,");
        
    # Verifica se quem assina é gerente e por o cargo em comissão
    if($postAssinatura == $pessoal->get_gerente(66)){
        $despacho->set_origemDescricao($pessoal->get_cargoComissaoDescricao($postAssinatura));
    }else{
        $despacho->set_origemDescricao($pessoal->get_cargoSimples($postAssinatura));
        $despacho->set_origemLotacao($pessoal->get_lotacao($postAssinatura));
    }

    # Servidor que assina
    $despacho->set_origemNome($pessoal->get_nome($postAssinatura));    
    $despacho->set_origemIdFuncional($pessoal->get_idFuncional($postAssinatura));

    $despacho->set_saltoRodape(1);
    $despacho->show();

    # Grava o log da visualização do relatório
    $data = date("Y-m-d H:i:s");
    $atividades = "Visualizou o Despacho de Acumulação: Solicita Modelo Padrão";
    $tipoLog = 4;
    $intra->registraLog($idUsuario, $data, $atividades, "tbacumulacao", null, $tipoLog, $idServidorPesquisado);

    $page->terminaPagina();
}