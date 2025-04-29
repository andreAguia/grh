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
    $dtAdmin = $pessoal->get_dtAdmissao($idServidorPesquisado);

    # Começa uma nova página
    $page = new Page();
    $page->set_title("Declaração de Não Acumulação");
    $page->iniciaPagina();
    
    # Monta a Declaração
    $dec = new Declaracao("Senhor Gerente de Recursos Humanos");
    
    $dec->set_texto("Data do Exercício: {$dtAdmin}");
    
    $dec->set_texto("(&nbsp;&nbsp;X&nbsp;&nbsp;)&nbsp;&nbsp;&nbsp; Não Consta Processo");
    $dec->set_texto("(&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;)&nbsp;&nbsp;&nbsp; Consta Processo nº");
    $dec->set_texto("(&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;)&nbsp;&nbsp;&nbsp; Concessão Automática");
    
    #$dec->set_carimboCnpj(true);
    $dec->set_linhaAssinatura(false);
    $dec->set_exibeAssinatura(false);
    $dec->set_data(date("d/m/Y"));

    $dec->show();

    # Grava o log da visualização do relatório
    $data = date("Y-m-d H:i:s");
    $atividades = 'Visualizou a página inicial do processo de licença prêmio';
    $tipoLog = 4;
    $intra->registraLog($idUsuario, $data, $atividades, null, null, $tipoLog, $idServidorPesquisado);

    $page->terminaPagina();
}