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
$acesso = Verifica::acesso($idUsuario, 2);

if ($acesso) {
    # Conecta ao Banco de Dados
    $pessoal = new Pessoal();
    $readaptacao = new Readaptacao();

    # Pega o id
    $id = get('id');

    # Pega o nome e cargo do chefe
    $array = unserialize(get('array'));
    $chefe = $array[0];
    $cargo = $array[1];

    # Começa uma nova página
    $page = new Page();
    $page->iniciaPagina();

    # Pega os Dados
    $dados = $readaptacao->get_dados($id);

    # Da Readaptação
    $numCitermino = $dados['numCiTermino'];
    $dtCiTermino = date_to_php($dados['dtCiTermino']);
    $dtInicio = date_to_php($dados['dtInicio']);
    $dtTermino = date_to_php($dados['dtTermino']);
    $dtPublicacao = date_to_php($dados['dtPublicacao']);
    $pgPublicacao = $dados['pgPublicacao'];
    $periodo = $dados['periodo'];
    $processo = $dados['processo'];

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
    $assunto = "<b>TÉRMINO</b> do período de Readaptação de " . $nomeServidor;

    # Monta a CI
    $ci = new Ci($numCitermino, $dtCiTermino, $assunto);
    $ci->set_destinoNome($chefe);
    $ci->set_destinoSetor($cargo);

    $ci->set_texto("Vimos comunicar o <b>TÉRMINO</b> do período"
            . " de <b>Readaptação</b> do(a) servidor(a) <b>" . strtoupper($nomeServidor) . "</b>,"
            . " ID $idFuncional, em $dtTermino, conforme Ato do Reitor publicado no DOERJ de $publicacao,"
            . " concedendo o benefício pelo prazo de $periodo meses.");

    $ci->set_texto("Esclarecemos que o referido servidor deverá voltar a cumprir"
            . " as atividades normalmente, enquanto aguarda o parecer da perícia médica oficial do Estado do RJ para concessão de"
            . " prorrogação, se for o caso.");

    $ci->set_texto("Sem mais para o momento, reiteramos votos de estima e consideração.");
    $ci->set_saltoRodape(1);
    $ci->show();

    # Grava o log da visualização do relatório
    $data = date("Y-m-d H:i:s");
    $atividades = 'Visualizou a Ci de término de readaptação.';
    $tipoLog = 4;
    $intra->registraLog($idUsuario, $data, $atividades, "tbreducao", $id, $tipoLog, $idServidorPesquisado);

    $page->terminaPagina();
}