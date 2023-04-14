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
    $acumulacao = new Acumulacao();

    # Começa uma nova página
    $page = new Page();
    $page->iniciaPagina();

    # despacho
    $despacho = new Despacho();
    $despacho->set_destino("Prezado(a) {$pessoal->get_nome($idServidorPesquisado)},");
    $despacho->set_texto("Considerando que esta Gerência não localizou o processo que analisou a Acumulação de Cargos Públicos declarada, solicitamos anexar a página do DOERJ que publicou a sua Licitude, ou algum documento oficial que contenha o número do processo e/ou a data da publicação para que possamos fazer a busca, com vistas à conclusão do presente processo.");
    $despacho->set_texto("Em não existindo o processo de análise, faz-se necessária a abertura de um novo processo no sistema SEI, seguindo as orientações do link abaixo, para a devida análise na Acumulação de Cargos pelo órgão competente, regularizando a sua situação funcional.");
    $despacho->set_texto("<a href='https://uenf.br/dga/grh/gerencia-de-recursos-humanos/acumulacao-de-cargos/abertura-de-processo/'>https://uenf.br/dga/grh/gerencia-de-recursos-humanos/acumulacao-de-cargos/abertura-de-processo/</a>");
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
    $atividades = "Visualizou o Despacho de Informação sobre processo em análise";
    $tipoLog = 4;
    $intra->registraLog($idUsuario, $data, $atividades, "tbacumulacao", null, $tipoLog, $idServidorPesquisado);

    $page->terminaPagina();
}