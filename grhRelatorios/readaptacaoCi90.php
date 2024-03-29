<?php

/**
 * Sistema GRH
 * 
 * Relatório
 *   
 * By Alat
 */
# Inicia as variáveis que receberão as sessions
$idUsuario = null;              # Servidor logado
$idServidorPesquisado = null; # Servidor Editado na pesquisa do sistema do GRH
# Configuração
include ("../grhSistema/_config.php");

# Permissão de Acesso
$acesso = Verifica::acesso($idUsuario, [1, 2, 12]);

if ($acesso) {

    # Conecta ao Banco de Dados
    $pessoal = new Pessoal();
    $readaptacao = new Readaptacao();

    # Pega os dados
    $id = get('id');
    $servidorGrh = get('servidorGrh');

    # Começa uma nova página
    $page = new Page();
    $page->iniciaPagina();

    # pega os dados
    $dados = $readaptacao->get_dados($id);

    # Da Redução
    $numCi90 = $dados["numCi90"];
    $dtCi90 = date_to_php($dados["dtCi90"]);
    $dtPublicacao = date_to_php($dados["dtPublicacao"]);
    $pgPublicacao = $dados["pgPublicacao"];
    $dtTermino = date_to_php($dados["dtTermino"]);

    # Verifica o número da Ci
    if (vazio($numCi90)) {
        $numCi90 = "????";
    }

    # Verifica a data da CI
    if (vazio($dtCi90)) {
        $dtCi90 = "????";
    }

    # Verifica a data da Publicação
    if (vazio($dtPublicacao)) {
        $dtPublicacao = "????";
    }

    # Verifica se estamos a 90 dias da data Termino
    if (!vazio($dtTermino)) {
        $hoje = date("d/m/Y");
        $dias = dataDif($hoje, $dtTermino);
    }

    # Trata a publicação
    if (vazio($pgPublicacao)) {
        $publicacao = $dtPublicacao;
    } else {
        $publicacao = "$dtPublicacao, pág. $pgPublicacao";
    }

    # Servidor
    $nomeServidor = $pessoal->get_nome($idServidorPesquisado);
    $idFuncional = $pessoal->get_idFuncional($idServidorPesquisado);
    $lotacao = $pessoal->get_nomeLotacao2($pessoal->get_idLotacao($idServidorPesquisado));

    # Assunto
    $assunto = "Aviso de prazo para fim do benefício.";

    # Monta a CI
    $ci = new Ci($numCi90, $dtCi90, $assunto);

    # Verifica se alterou o servidor da GRH
    if ($servidorGrh <> $pessoal->get_gerente(66)) {
        $ci->set_nomeAssinatura(
                $pessoal->get_nome($servidorGrh),
                $pessoal->get_cargoSimples($servidorGrh),
                $pessoal->get_idFuncional($servidorGrh));
    }

    $ci->set_destinoNome($lotacao);
    $ci->set_destinoSetor("A/C " . $nomeServidor);
    $ci->set_texto("Vimos alertar que faltam $dias dias para encerrar a concessão "
            . "de sua Readaptação, conforme publicação no DOERJ de $publicacao.<br/>");
    $ci->set_texto("Caso haja interesse em renovar o referido benefício, solicitamos"
            . " sua manifestação o quanto antes, através de processo eletrônico no"
            . " sistema SEI, para que os procedimentos administrativos sejam"
            . " providenciados com a devida antecedência.");
    $ci->set_saltoRodape(5);
    $ci->show();

    # Grava o log da visualização do relatório
    $data = date("Y-m-d H:i:s");
    $atividades = "Visualizou a Ci que informa que a readaptação irá terminar em {$dias} dias";
    $tipoLog = 4;
    $intra->registraLog($idUsuario, $data, $atividades, "tbreducao", $id, $tipoLog, $idServidorPesquisado);

    $page->terminaPagina();
}