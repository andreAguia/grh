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
    $despacho->set_texto("Informamos o INDEFERIMENTO na solicitação por existir instaurado Inquérito Administrativo em fase de tramitação.");
    $despacho->set_texto("Solicitamos <b>ciência do(a) servidor(a)</b> para a devida conclusão do presente processo.");
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
    $atividades = "Visualizou o Despacho: Ciência do Indeferimento por Inquérito";
    $tipoLog = 4;
    $intra->registraLog($idUsuario, $data, $atividades, "tbreducao", null, $tipoLog, $idServidorPesquisado);

    $page->terminaPagina();
}