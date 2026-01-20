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

# Pega o id
$id = get('id');

# Permissão de Acesso
$acesso = Verifica::acesso($idUsuario, [1, 2, 12]);

if ($acesso) {

    # Conecta ao Banco de Dados
    $pessoal = new Pessoal();
    $reducao = new Readaptacao();

    # Começa uma nova página
    $page = new Page();
    $page->set_title("Despacho Aviso de Término");
    $page->iniciaPagina();

    # Pega os Dados
    $dados = $reducao->get_dados($id);

    $dtInicio = date_to_php($dados['dtInicio']);
    $dtTermino = date_to_php($dados["dtTermino"]);
    $dtPublicacao = date_to_php($dados['dtPublicacao']);
    $pgPublicacao = $dados["pgPublicacao"];
    $tipo = $dados["tipo"];
    $periodo = $dados["periodo"];
    $processo = $dados["processo"];

    $hoje = date("d/m/Y");
    $dias = dataDif($hoje, $dtTermino);

    # Trata a publicação
    if (empty($pgPublicacao)) {
        $publicacao = $dtPublicacao;
    } else {
        $publicacao = "$dtPublicacao, pág. $pgPublicacao";
    }

    # despacho
    $despacho = new Despacho();
    $despacho->set_destino("Ao Sr {$chefe},<br/>$cargo<br/>c/c servidor(a) {$pessoal->get_nome($idServidorPesquisado)}");

    if (jaPassou($dtTermino)) {
        $despacho->set_texto("Comunicamos o <b>ENCERRAMENTO da concessão da Readaptação</b> do(a) servidor(a) "
                . "<b>{$pessoal->get_nome($idServidorPesquisado)}</b>, ID {$pessoal->get_idFuncional($idServidorPesquisado)}, "
                . "em {$dtTermino}, conforme publicado no DOERJ de {$dtPublicacao}.");
    } else {
        $despacho->set_texto("Comunicamos que faltam <b>{$dias} dias</b> para o <b>ENCERRAMENTO da concessão da Readaptação</b> do(a) servidor(a) "
                . "<b>{$pessoal->get_nome($idServidorPesquisado)}</b>, ID {$pessoal->get_idFuncional($idServidorPesquisado)}, "
                . "em {$dtTermino}, conforme publicado no DOERJ de {$dtPublicacao}.");
    }

    $despacho->set_texto("Caso haja interesse em renovar o referido benefício, solicitamos"
            . " sua manifestação o quanto antes, através de processo eletrônico no"
            . " sistema SEI, para que os procedimentos administrativos sejam"
            . " providenciados com a devida antecedência.");
    
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
    $atividades = "Visualizou o Despacho de Readaptação Aviso de Término";
    $tipoLog = 4;
    $intra->registraLog($idUsuario, $data, $atividades, "tbreducao", null, $tipoLog, $idServidorPesquisado);

    $page->terminaPagina();
}