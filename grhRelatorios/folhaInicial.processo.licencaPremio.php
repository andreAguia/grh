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
    $matricula = $pessoal->get_matricula($idServidorPesquisado);
    $lotacao = $pessoal->get_lotacao($idServidorPesquisado);
    $dtAdmin = $pessoal->get_dtAdmissao($idServidorPesquisado);
    $cargoEfetivo = $pessoal->get_cargoCompleto($idServidorPesquisado);
    $idPerfil = $pessoal->get_idPerfil($idServidorPesquisado);
    $idPessoa = $pessoal->get_idPessoa($idServidorPesquisado);
    $cpf = $pessoal->get_cpf($idPessoa);

    # Começa uma nova página
    $page = new Page();
    $page->set_title("Declaração de Não Acumulação");
    $page->iniciaPagina();
    
    # Monta a Declaração
    $dec = new Declaracao("Senhor Gerente de Recursos Humsanos");
    
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