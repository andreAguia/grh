<?php

/**
 * Sistema GRH
 * 
 * Relatório
 *   
 * By Alat
 */
# Servidor logado 
$idUsuario = null;

# Configuração
include ("../grhSistema/_config.php");

# Permissão de Acesso
$acesso = Verifica::acesso($idUsuario, [1, 2, 12]);

if ($acesso) {
    # Conecta ao Banco de Dados
    $servidor = new Pessoal();

    # Começa uma nova página
    $page = new Page();
    $page->iniciaPagina();

    $parametroLotacao = get_session('parametroLotacao', 'Todos');
    $parametroSituacao = get_session('parametroSituacao', 'Entregaram');
    $parametroAfastamento = post('parametroAfastamento', get_session('parametroAfastamento', 'Todos'));

    $sispatri = new Sispatri();
    $sispatri->set_lotacao($parametroLotacao);
    $sispatri->set_ordenacao("lotacao");

    ######

    $relatorio = new Relatorio();

    # Pega os registros
    if ($parametroSituacao == "Entregaram") {
        # Exibe os servidores ativos que entregaram o sispatri
        $lista = $sispatri->get_servidoresEntregaramAtivos();
        $relatorio->set_titulo('Relatório de Declarações Entregues do Sispatri');
    } else {

        # Exibe os servidores ativos que Não entregaram o sispatri
        if ($parametroAfastamento == "Todos") {
            $lista = $sispatri->get_servidoresNaoEntregaramAtivos(false);
            $relatorio->set_titulo('Relatório de Declarações Não Entregues do Sispatri');
        }

        if ($parametroAfastamento == "Férias") {
            $lista = $sispatri->get_servidoresNaoEntregaramAtivosFerias(false);
            $relatorio->set_titulo('Relatório de Declarações Não Entregues do Sispatri');
            $relatorio->set_tituloLinha3("Em Férias");
        }

        if ($parametroAfastamento == "Licença Prêmio") {
            $lista = $sispatri->get_servidoresNaoEntregaramAtivosLicPremio(false);
            $relatorio->set_titulo('Relatório de Declarações Não Entregues do Sispatri');
            $relatorio->set_tituloLinha3("Em Licença Prêmio");
        }

        if ($parametroAfastamento == "Licença Médica") {
            $lista = $sispatri->get_servidoresNaoEntregaramAtivosLicMedica(false);
            $relatorio->set_titulo('Relatório de Declarações Não Entregues do Sispatri');
            $relatorio->set_tituloLinha3("Em Licença Médica");
        }
    }

    if (!is_numeric($parametroLotacao)) {
        $relatorio->set_tituloLinha2($parametroLotacao);
    }

    $relatorio->set_subtitulo('Ordenados pelo Nome');

    if ($parametroSituacao == "Entregaram") {

        $relatorio->set_label(['IdFuncional', 'Nome', 'Cargo', 'Lotação', 'Situação']);
        $relatorio->set_align(["center", "left", "left", "left"]);
        $relatorio->set_classe([null, null, "pessoal", null, "pessoal"]);
        $relatorio->set_metodo([null, null, "get_CargoSimples", null, "get_situacaoRel"]);
        $relatorio->set_conteudo($lista);
        $relatorio->set_numGrupo(3);
    } else {

        $relatorio->set_label(["IdFuncional", "Servidor", "Lotação", "Afastamentos", "Situação"]);
        $relatorio->set_conteudo($lista);
        $relatorio->set_align(["center", "left", "left", "left"]);
        $relatorio->set_classe([null, "pessoal"]);
        $relatorio->set_metodo([null, "get_nomeECargo"]);
        $relatorio->set_funcao([null, null, null, "exibeAfastamentoAtual", "get_situacaoRel"]);
        $relatorio->set_numGrupo(2);
        $relatorio->set_bordaInterna(true);
    }

    $relatorio->show();

    $page->terminaPagina();
}