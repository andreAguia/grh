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
    $parametroAfastamento = get_session('parametroAfastamento', 'Todos');

    $exibeAfastamento = get_session('exibeAfastamento', 1);
    $exibeEmail = get_session('exibeEmail', 1);

    # Inicia a Classe
    $sispatri = new Sispatri();
    $sispatri->set_lotacao($parametroLotacao);
    $sispatri->exibeEmail($exibeEmail);
    $sispatri->exibeAfastamento($exibeAfastamento);
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
            $lista = $sispatri->get_servidoresNaoEntregaramAtivos();
            $relatorio->set_titulo('Relatório de Declarações Não Entregues do Sispatri');
        }

        if ($parametroAfastamento == "Férias") {
            $lista = $sispatri->get_servidoresNaoEntregaramAtivosFerias();
            $relatorio->set_titulo('Relatório de Declarações Não Entregues do Sispatri');
            $relatorio->set_tituloLinha3("Em Férias");
        }

        if ($parametroAfastamento == "Licença Prêmio") {
            $lista = $sispatri->get_servidoresNaoEntregaramAtivosLicPremio();
            $relatorio->set_titulo('Relatório de Declarações Não Entregues do Sispatri');
            $relatorio->set_tituloLinha3("Em Licença Prêmio");
        }

        if ($parametroAfastamento == "Licença Médica") {
            $lista = $sispatri->get_servidoresNaoEntregaramAtivosLicMedica();
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
        $relatorio->set_classe([null, null, "pessoal"]);
        $relatorio->set_metodo([null, null, "get_CargoSimples"]);
        $relatorio->set_funcao([null, null, null, null, "get_situacaoRel"]);
        $relatorio->set_conteudo($lista);
        $relatorio->set_numGrupo(3);
    } else {

        $relatorio->set_conteudo($lista);
        $relatorio->set_classe([null, "pessoal"]);
        $relatorio->set_metodo([null, "get_nomeECargo"]);

        $label = ["IdFuncional", "Servidor", "Lotação"];
        $align = ["center", "left", "left"];
        $funcao = [null, null, null];
        $width = [10, 30, 20];

        # Exibe o afastamento ou não
        if ($exibeAfastamento) {
            array_push($label, "Afastamentos");
            array_push($align, "left");
            array_push($funcao, "exibeAfastamentoAtual");
            array_push($width, 20);
        }

        # Exibe o e-mail ou não
        if ($exibeEmail) {
            array_push($label, "E-mail");
            array_push($align, null);
            array_push($funcao, null);
            array_push($width, 10);
        }

        array_push($label, "Situação");
        array_push($funcao, "get_situacaoRel");
        array_push($width, 10);

        $relatorio->set_label($label);
        $relatorio->set_align($align);
        $relatorio->set_funcao($funcao);
        $relatorio->set_width($width);
    }

    $relatorio->show();
    $page->terminaPagina();
}