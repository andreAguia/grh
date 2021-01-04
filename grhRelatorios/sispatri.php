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
    $parametroSituacao = get_session('parametroSituacao', 'Fizeram');

    $sispatri = new Sispatri();
    $sispatri->set_lotacao($parametroLotacao);

    ######
    # Pega os registros
    if ($parametroSituacao == "Fizeram") {
        # Exibe os servidores ativos que entregaram o sispatri
        $lista = $sispatri->get_servidoresEntregaramAtivos();
        $titulo = 'Relatório dos Servidores Ativos que FIZERAM o Sispatri';
    } else {
        # Exibe os servidores ativos que Não entregaram o sispatri
        $lista = $sispatri->get_servidoresNaoEntregaramAtivos();
        $titulo = 'Relatório dos Servidores Ativos que NÃO FIZERAM o Sispatri';
    }

    $relatorio = new Relatorio();
    $relatorio->set_titulo($titulo);
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