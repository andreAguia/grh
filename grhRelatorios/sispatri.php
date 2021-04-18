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
$acesso = Verifica::acesso($idUsuario, 2);

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
    $relatorio->set_label(array('IdFuncional', 'Nome', 'Cargo', 'Lotação', 'Situação'));
    $relatorio->set_align(array("center", "left", "left", "left"));
    $relatorio->set_classe(array(null, null, "pessoal", null, "pessoal"));
    $relatorio->set_metodo(array(null, null, "get_Cargo", null, "get_situacao"));
    $relatorio->set_conteudo($lista);
    $relatorio->set_numGrupo(3);
    $relatorio->show();

    $page->terminaPagina();
}