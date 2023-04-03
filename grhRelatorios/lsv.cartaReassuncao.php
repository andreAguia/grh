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

# Pega o id
$id = get('id');

# Permissão de Acesso
$acesso = Verifica::acesso($idUsuario, [1, 2, 12]);

if ($acesso) {

    # Começa uma nova página
    $page = new Page();
    $page->iniciaPagina();

    # Conecta ao Banco de Dados
    $pessoal = new Pessoal();
    $lsv = new LicencaSemVencimentos();

    # Pega os dados digitados
    $postAssinatura = post('postAssinatura');
    $chefe = post("chefia");
    $cargo = post("cargo");
    $dtRetornoDigitado = post("dtRetorno");
    $dtPublicacaoDigitado = date_to_php(post("dtPublicacao"));
    $pgPublicacaoDigitado = post("pgPublicacao");

    # Pega os dados do banco
    $dados = $lsv->get_dados($id);
    $dtRetorno = $dados["dtRetorno"];
    $dtTermino = $dados["dtTermino"];
    $dtPublicacao = $dados["dtPublicacao"];
    $pgPublicacao = $dados["pgPublicacao"];

    # Valida os dados digitados
    $msgErro = null;
    $erro = 0;

    if (empty($dtPublicacaoDigitado)) {
        $msgErro .= 'Não tem data da Publicação cadastrada!\n';
        $erro = 1;
    }

    if ($erro == 0) {
        # Verifica se houve alterações
        $alteracoes = null;
        $atividades = null;

        # Verifica as alterações para o log
        if ($dtRetorno <> $dtRetornoDigitado) {
            # Faz acertos
            if(empty($dtRetornoDigitado)){
                $dtRetornoDigitado = $dtTermino;                
            }           
            $alteracoes .= '[dtRetorno] ' . date_to_php($dtRetorno) . '->' . date_to_php($dtRetornoDigitado) . '; ';
        }

        if ($dtPublicacao <> $dtPublicacaoDigitado) {
            $alteracoes .= '[dtPublicacao] ' . date_to_php($dtPublicacao) . '->' . date_to_php($dtPublicacaoDigitado) . '; ';
        }

        if ($pgPublicacao <> $pgPublicacaoDigitado) {
            $alteracoes .= '[pgPublicacao] ' . $pgPublicacao . '->' . $pgPublicacaoDigitado . '; ';
        }

        # Salva as alterações
        $pessoal->set_tabela("tblicencasemvencimentos");
        $pessoal->set_idCampo("idLicencaSemVencimentos");
        $campoNome = array('dtRetorno', 'dtPublicacao', 'pgPublicacao');
        $campoValor = array($dtRetornoDigitado, $dtPublicacaoDigitado, $pgPublicacaoDigitado);
        $pessoal->gravar($campoNome, $campoValor, $id);
        $data = date("Y-m-d H:i:s");

        # Grava o log das alterações caso tenha
        if (!is_null($alteracoes)) {
            $atividades .= 'Alterou: ' . $alteracoes;
            $tipoLog = 2;
            $intra->registraLog($idUsuario, $data, $atividades, "tblicencasemvencimentos", $id, $tipoLog, $idServidorPesquisado);
        }

        # Verifica se o retorno foi antecipado
        if (strtotime($dtRetornoDigitado) < strtotime($dtTermino)) {
            $dtRetornoTexto = date_to_php($dtRetornoDigitado) . " ,antecipando o ";
        } else {
            $dtRetornoTexto = date_to_php($dtTermino);
        }


        # Trata a publicação
        if (empty($pgPublicacao)) {
            $publicacao = $dtPublicacao;
        } else {
            $publicacao = "$dtPublicacao, pág. $pgPublicacao";
        }

        # despacho
        $despacho = new Despacho();
        $despacho->set_destino("A(o) Sr(a) {$chefe},<br/>$cargo");

        $despacho->set_texto("Apresentamos a V.Sª. o(a) Sr(a) <b>{$pessoal->get_nome($idServidorPesquisado)}</b>, "
                . "ID {$pessoal->get_idFuncional($idServidorPesquisado)}, "
                . "cargo {$pessoal->get_cargoSimples($idServidorPesquisado)}, "
                . "para reassumir o exercício de suas atividades na {$pessoal->get_lotacao($idServidorPesquisado)}, "
                . "a contar de {$dtRetornoTexto}, término do prazo da Licença Sem Vencimentos publicada no DOERJ de {$publicacao}.");

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
    } else {
        alert($msgErro);
        #back(1);
    }

    $page->terminaPagina();
}