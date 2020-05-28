<?php

/**
 * Sistema GRH
 * 
 * Relatório
 *   
 * By Alat
 */
# Inicia as variáveis que receberão as sessions
$idUsuario = NULL;              # Servidor logado
$idServidorPesquisado = NULL; # Servidor Editado na pesquisa do sistema do GRH
# Configuração
include ("../grhSistema/_config.php");

# Permissão de Acesso
$acesso = Verifica::acesso($idUsuario, 2);

if ($acesso) {

    # Conecta ao Banco de Dados
    $pessoal = new Pessoal();
    $reducao = new ReducaoCargaHoraria();

    # Pega o id
    $id = get('id');

    # Pega a folha
    $folha = get('folha');

    # Começa uma nova página
    $page = new Page();
    $page->iniciaPagina();

    # Pega os dados
    $dados = $reducao->get_dados($id);

    # da Redução
    $tipo = $dados["tipo"];
    $dtTermino = date_to_php($dados["dtTermino"]);
    $dtPublicacao = date_to_php($dados["dtPublicacao"]);

    # do Servidor
    $nomeServidor = $pessoal->get_nome($idServidorPesquisado);

    $destino = "Ao Protocolo/DGA,";
    $texto[] = "Para abertura do processo de 'Redução da carga horária', em nome de <b>" . strtoupper($nomeServidor) . "</b>.";

    # despacho
    $despacho = new Despacho();
    $despacho->set_destino($destino);
    $despacho->set_data(date("d/m/Y"));
    $despacho->set_folha($folha);
    $despacho->set_texto($texto);

    # Pega o idServidor do gerente GRH
    $idGerente = $pessoal->get_gerente(66);
    $gerente = $pessoal->get_nome($idGerente);
    $cargo = $pessoal->get_cargoComissaoDescricao($idGerente);
    $idFuncional = $pessoal->get_idFuncional($idGerente);

    $despacho->set_origemNome($gerente);
    $despacho->set_origemDescricao($cargo);
    $despacho->set_origemIdFuncional($idFuncional);

    $despacho->set_saltoRodape(1);
    $despacho->show();

    # Grava o log da visualização do relatório
    $data = date("Y-m-d H:i:s");
    $atividades = 'Visualizou O Despacho Inicial de redução da carga horária: ';
    $tipoLog = 4;
    $intra->registraLog($idUsuario, $data, $atividades, "tbreducao", $id, $tipoLog, $idServidorPesquisado);

    $page->terminaPagina();
}