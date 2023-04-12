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
$id = get('id');
$postAssinatura = post('postAssinatura');

# Permissão de Acesso
$acesso = Verifica::acesso($idUsuario, [1, 2, 12]);

if ($acesso) {

    # Conecta ao Banco de Dados
    $pessoal = new Pessoal();
    $acumulacao = new Acumulacao();

    # Começa uma nova página
    $page = new Page();
    $page->iniciaPagina();
    
    # Pega os Dados
    $dados = $acumulacao->get_dados($id);
    $processo = $dados ['processo'];

    # despacho
    $despacho = new Despacho();
    $despacho->set_destino("Prezado(a) servidor(a) {$pessoal->get_nome($idServidorPesquisado)}");
    $despacho->set_texto("Para prosseguirmos com a tramitação devida do processo {$processo}, faz-se necessária a inclusão de todos os documentos relacionados no link abaixo, com vistas à correta análise na Acumulação de Cargos Públicos.");
    $despacho->set_texto("https://uenf.br/dga/grh/gerencia-de-recursos-humanos/acumulacao-de-cargos/abertura-de-processo/");
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
    $atividades = "Visualizou o Despacho de Solicitação de Documentos para Acumulação";
    $tipoLog = 4;
    $intra->registraLog($idUsuario, $data, $atividades, "tbacumulacao", null, $tipoLog, $idServidorPesquisado);

    $page->terminaPagina();
}