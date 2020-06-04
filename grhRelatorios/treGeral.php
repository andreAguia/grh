<?php

/**
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
    $pessoal = new Pessoal();

    # Pega os parâmetros
    $selectRelatorio = get_session('selectRelatorio');
    $parametroNomeMat = get_session('parametroNomeMat');
    $parametroLotacao = get_session('parametroLotacao');

    # Começa uma nova página
    $page = new Page();
    $page->iniciaPagina();

    $result = $pessoal->select($selectRelatorio);

    $relatorio = new Relatorio();
    $relatorio->set_titulo('Relatório Geral TRE');
    $relatorio->set_subtitulo('Ordenado pelo Nome');

    $titulo2 = null;

    if (!is_null($parametroLotacao) AND ($parametroLotacao <> "*")) {
        $titulo2 .= $pessoal->get_nomeLotacao($parametroLotacao);
    }

    if (!is_null($parametroNomeMat)) {
        $titulo2 .= "Filtro: " . $parametroNomeMat;
    }

    if (!is_null($titulo2)) {
        $relatorio->set_tituloLinha2($titulo2);
    }

    $relatorio->set_label(array("Id", "Matricula", "Nome", "Lotação", "Dias Trabalhados", "Folgas Concedidas", "Folgas Fruidas", "Folgas Pendentes"));
    $relatorio->set_align(array("center", "center", "left", "left"));
    $relatorio->set_classe(array(null, null, null, "pessoal"));
    $relatorio->set_metodo(array(null, null, null, "get_lotacao"));
    $relatorio->set_funcao(array(null, "dv"));

    $relatorio->set_conteudo($result);
    $relatorio->show();

    $page->terminaPagina();
}