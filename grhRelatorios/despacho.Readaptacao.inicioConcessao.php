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

# Pega os Dados
$dados = $readaptacao->get_dados($id);

# Da Readaptação
$dtInicio = date_to_php($dados['dtInicio']);
$dtPublicacao = date_to_php($dados['dtPublicacao']);
$pgPublicacao = $dados['pgPublicacao'];
$periodo = $dados['periodo'];
$processo = $dados['processo'];
$tipo = $dados['tipo'];

# Pega os dados Digitados
$botaoEscolhido = get_post_action("salvar", "imprimir");
$numCiInicioDigitados = vazioPraNulo(post("numCiInicio"));
$dtCiInicioDigitado = vazioPraNulo(post("dtCiInicio"));
$tipo = vazioPraNulo(post("tipo"));

$servidorGrh = post("servidorGrh");
$chefeDigitado = post("chefia");
$cargoDigitado = post("cargo");
$textoCi = post("textoCi");

# Verifica se houve alterações
$alteracoes = null;
$atividades = null;

# Verifica as alterações para o log
if ($numCiInicio <> $numCiInicioDigitados) {
    $alteracoes .= '[numCiInicio] ' . $numCiInicio . '->' . $numCiInicioDigitados . '; ';
}
if ($dtCiInicio <> $dtCiInicioDigitado) {
    $alteracoes .= '[dtCiInicio] ' . date_to_php($dtCiInicio) . '->' . date_to_php($dtCiInicioDigitado) . '; ';
}

# Erro
$msgErro = null;
$erro = 0;

# Verifica a data da Publicação
if (empty($dtPublicacao)) {
    $msgErro .= 'Não tem data da Publicação cadastrada!\n';
    $erro = 1;
}

# Verifica a data de Início
if (empty($dtInicio)) {
    $msgErro .= 'Não tem data de início do benefício cadastrada!\n';
    $erro = 1;
}

# Verifica o período
if (empty($periodo)) {
    $msgErro .= 'O período não foi cadastrado!\n';
    $erro = 1;
}

# Verifica o período
if (empty($tipo)) {
    $msgErro .= 'Deve-se informar se é inicial ou renovação!\n';
    $erro = 1;
}

# Salva as alterações
$pessoal->set_tabela("tbreadaptacao");
$pessoal->set_idCampo("idReadaptacao");
$campoNome = array('tipo', 'textoCi');
$campoValor = array($tipo, $textoCi);
$pessoal->gravar($campoNome, $campoValor, $id);
$data = date("Y-m-d H:i:s");

# Grava o log das alterações caso tenha
if (!is_null($alteracoes)) {
    $atividades .= 'Alterou: ' . $alteracoes;
    $tipoLog = 2;
    $intra->registraLog($idUsuario, $data, $atividades, "tbreadaptacao", $id, $tipoLog, $idServidorPesquisado);
}

# Exibe o relatório ou salva de acordo com o botão pressionado
if ($botaoEscolhido == "imprimir") {
    if ($erro == 0) {
        # Exibe o relatório
        if ($tipo == 1) {

            # Da Readaptação
            $numCiInicio = $dados['numCiInicio'];
            $dtCiInicio = date_to_php($dados['dtCiInicio']);
            $dtInicio = date_to_php($dados['dtInicio']);
            $dtPublicacao = date_to_php($dados['dtPublicacao']);
            $pgPublicacao = $dados['pgPublicacao'];
            $periodo = $dados['periodo'];
            $processo = $dados['processo'];
            $parecer = $dados['parecer'];
            $textoCi = $dados['textoCi'];

            # Trata a publicação
            if (vazio($pgPublicacao)) {
                $publicacao = $dtPublicacao;
            } else {
                $publicacao = "$dtPublicacao, pág. $pgPublicacao";
            }

            # Servidor
            $nomeServidor = $pessoal->get_nome($idServidorPesquisado);
            $idFuncional = $pessoal->get_idFuncional($idServidorPesquisado);

            # Assunto
            $assunto = "Readaptação de " . $nomeServidor;

            # Monta a CI
            $ci = new Ci($numCiInicio, $dtCiInicio, $assunto);

            # Verifica se alterou o servidor da GRH
            if ($servidorGrh <> $pessoal->get_gerente(66)) {

                $ci->set_nomeAssinatura(
                        $pessoal->get_nome($servidorGrh),
                        $pessoal->get_cargoSimples($servidorGrh),
                        $pessoal->get_idFuncional($servidorGrh));
            }

            $ci->set_destinoNome($chefe);
            $ci->set_destinoSetor($cargo);
            $ci->set_texto('Vimos informar a concessão de <b>Readaptação</b> do(a) servidor(a) <b>' . strtoupper($nomeServidor) . '</b>,'
                    . ' ID ' . $idFuncional . ', pelo prazo de ' . $periodo . ' meses, "<i>' . $textoCi . '</i>", conforme publicação no DOERJ em ' . $publicacao
                    . ' em anexo, para fins de cumprimento.');
            $ci->set_saltoRodape(3);
            $ci->show();

            # Grava o log da visualização do relatório
            $data = date("Y-m-d H:i:s");
            $atividades = 'Visualizou a Ci de início de readaptacao.';
            $tipoLog = 4;
            $intra->registraLog($idUsuario, $data, $atividades, "tbreadaptacao", $id, $tipoLog, $idServidorPesquisado);
        } else {
            loadPage("../grhRelatorios/readaptacaoCiProrrogacao.php?id=$id&array=$array", "_blank");
        }
        loadPage("?");
    } else {
        alert($msgErro);
        back(1);
    }
} else {
    loadPage("?");
}





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