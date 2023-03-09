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
    $reducao = new ReducaoCargaHoraria();

    # Pega o id
    $id = get('id');

    # pega os dados
    $dados = $reducao->get_dados($id);

    # Começa uma nova página
    $page = new Page();
    $page->iniciaPagina();

    # despacho
    $despacho = new Despacho();
    $despacho->set_destino("À SES/SUPCPMSO,");
    if ($dados['tipo'] == 1) {
        $despacho->set_texto("Encaminhamos o presente processo para análise no pedido de Redução de Carga Horária do(a) servidor(a) em tela.");
    } else {
        $despacho->set_texto("Encaminhamos o presente processo para análise no pedido de prorrogação da Redução de Carga Horária do(a) servidor(a) em tela.");
    }
    $despacho->set_texto("Atenciosamente,");

    # Verifica se quem assina é gerente e por o cargo em comissão
    if ($postAssinatura == $pessoal->get_gerente(66)) {
        $despacho->set_origemDescricao($pessoal->get_cargoComissaoDescricao($postAssinatura));
    } else {
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
    $atividades = "Visualizou o Despacho de RCH para perícia";
    $tipoLog = 4;
    $intra->registraLog($idUsuario, $data, $atividades, "tbreducao", null, $tipoLog, $idServidorPesquisado);

    $page->terminaPagina();
}