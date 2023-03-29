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
$chefe = post("chefia");
$cargo = post("cargo");
$numDocumento = post("numDocumento");

# Pega o id
$id = get('id');

# Permissão de Acesso
$acesso = Verifica::acesso($idUsuario, [1, 2, 12]);

if ($acesso) {

    # Conecta ao Banco de Dados
    $pessoal = new Pessoal();
    $reducao = new ReducaoCargaHoraria();

    # Começa uma nova página
    $page = new Page();
    $page->iniciaPagina();

    # Pega os Dados
    $dados = $reducao->get_dados($id);

    $dtInicio = date_to_php($dados['dtInicio']);
    $dtPublicacao = date_to_php($dados['dtPublicacao']);
    $pgPublicacao = $dados["pgPublicacao"];
    $tipo = $dados["tipo"];
    $periodo = $dados["periodo"];
    $processo = $reducao->get_numProcesso($idServidorPesquisado);

    # Trata a publicação
    if (empty($pgPublicacao)) {
        $publicacao = $dtPublicacao;
    } else {
        $publicacao = "$dtPublicacao, pág. $pgPublicacao";
    }

    # despacho
    $despacho = new Despacho();
    $despacho->set_destino("Ao Sr {$chefe},<br/>$cargo<br/>c/c servidor(a) {$pessoal->get_nome($idServidorPesquisado)}");

    if ($tipo == 1) {
        $despacho->set_texto("Informamos a concessão da <b>Redução de Carga Horária</b> do(a) servidor(a) <b>{$pessoal->get_nome($idServidorPesquisado)}</b>, ID {$pessoal->get_idFuncional($idServidorPesquisado)}, por um período de {$periodo} meses, a contar em {$dtInicio}, publicado no DOERJ de {$dtPublicacao}, em anexo no Documento SEI n° {$numDocumento}, Processo {$processo}.");
    } else {
        $despacho->set_texto("Informamos a concessão da renovação da <b>Redução de Carga Horária</b> do(a) servidor(a) <b>{$pessoal->get_nome($idServidorPesquisado)}</b>, ID {$pessoal->get_idFuncional($idServidorPesquisado)}, por um período de {$periodo} meses, a contar em {$dtInicio}, publicado no DOERJ de {$dtPublicacao}, em anexo no Documento SEI n°  {$numDocumento}, Processo {$processo}.");
    }
    $despacho->set_texto("Solicitamos <b>ciência da chefia imediata e do(a) servidor(a)</b> o presente processo para a devida conclusão do mesmo.");
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
    $atividades = "Visualizou o Despacho de RCH: Início da Concessão";
    $tipoLog = 4;
    $intra->registraLog($idUsuario, $data, $atividades, "tbreducao", null, $tipoLog, $idServidorPesquisado);

    $page->terminaPagina();
}