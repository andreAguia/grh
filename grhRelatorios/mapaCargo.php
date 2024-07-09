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
$idServidorPesquisado = null;

# Configuração
include ("../grhSistema/_config.php");

# Permissão de Acesso
$acesso = Verifica::acesso($idUsuario, [1, 2, 12]);

if ($acesso) {
    # Conecta ao Banco de Dados
    $servidor = new Pessoal();

    # Começa uma nova página
    $page = new Page();
    $page->set_title("Mapa do Cargo");
    $page->iniciaPagina();

    # Limita o tamanho da tela
    $grid = new Grid();
    $grid->abreColuna(12);

    # Verifica se o perfil permite a declaração
    $idPerfil = $servidor->get_idPerfil($idServidorPesquisado);
    if (($idPerfil == 1) OR ($idPerfil == 4) OR (is_null($idPerfil))) {

        # Pega os parâmetros dos relatórios
        $cargo = get('cargo');

        ###### 

        $select = 'SELECT tbtipocargo.cargo, tbarea.area, nome, tbcargo.idTIpoCargo, tbcargo.idArea'
                . ' FROM tbcargo LEFT JOIN tbtipocargo USING (idTIpoCargo)'
                . '              LEFT JOIN tbarea USING (idarea)'
                . ' WHERE idcargo = ' . $cargo;

        $result1 = $servidor->select($select);

        $cargoNome = $result1[0][2];
        $tipoCargo = $result1[0][3];
        $area = $result1[0][4];

        $titulo = "Cargo: {$servidor->get_nomeTipoCargo($tipoCargo)}<br/>Área: {$servidor->get_nomeArea($area)}<br/>Função: {$cargoNome}";

        ######

        $select = "SELECT descricao FROM tbarea WHERE idarea = {$area}";
        $result2 = $servidor->select($select);

        $relatorio = new Relatorio();
        $relatorio->set_titulo("Mapa do Cargo");
        $relatorio->set_subtitulo($titulo);
        $relatorio->set_tituloTabela("Área: {$servidor->get_nomeArea($area)}");
        $relatorio->set_label(['Descrição Sintética da Área']);
        $relatorio->set_width([100]);
        $relatorio->set_align(["left"]);
        $relatorio->set_conteudo($result2);
        $relatorio->set_totalRegistro(false);
        $relatorio->set_dataImpressao(false);
        $relatorio->set_subTotal(false);
        $relatorio->show();

        ######

        $select = "SELECT requisitos FROM tbarea WHERE idarea = {$area}";
        $result3 = $servidor->select($select);

        $relatorio = new Relatorio();
        $relatorio->set_label(['Requisitos para o Provimento da Área']);
        $relatorio->set_width([100]);
        $relatorio->set_align(["left"]);
        $relatorio->set_conteudo($result3);
        $relatorio->set_totalRegistro(false);
        $relatorio->set_dataImpressao(false);
        $relatorio->set_subTotal(false);
        $relatorio->set_cabecalhoRelatorio(false);
        $relatorio->set_menuRelatorio(false);
        $relatorio->set_log(false);
        $relatorio->show();

        ######

        $select = "SELECT atribuicoes FROM tbcargo WHERE idcargo = {$cargo}";
        $result4 = $servidor->select($select);

        $select = "SELECT formacao FROM tbcargo WHERE idcargo = {$cargo}";
        $result5 = $servidor->select($select);

        $relatorio = new Relatorio();
        $relatorio->set_tituloTabela("Função: {$cargoNome}");
        $relatorio->set_label(['Atribuições da Função']);
        $relatorio->set_width([100]);
        $relatorio->set_align(["left"]);
        $relatorio->set_funcao(['formataAtribuicao']);
        $relatorio->set_conteudo($result4);
        $relatorio->set_totalRegistro(false);
        $relatorio->set_subTotal(false);
        $relatorio->set_cabecalhoRelatorio(false);
        $relatorio->set_menuRelatorio(false);
        $relatorio->set_log(false);

        if (empty($result5[0]["formacao"])) {
            $relatorio->set_dataImpressao(true);
        } else {
            $relatorio->set_dataImpressao(false);
        }

        $relatorio->show();

        ######

        if (!empty($result5[0]["formacao"])) {

            $relatorio = new Relatorio();
            $relatorio->set_label(['Formação Específica para a Função']);
            $relatorio->set_width([100]);
            $relatorio->set_align(["left"]);
            $relatorio->set_conteudo($result5);
            $relatorio->set_totalRegistro(false);
            $relatorio->set_dataImpressao(true);
            $relatorio->set_subTotal(false);
            $relatorio->set_cabecalhoRelatorio(false);
            $relatorio->set_menuRelatorio(false);
            $relatorio->set_log(false);
            $relatorio->show();
        }
    } else {
        br(4);
        p("O Mapa do Cargo é somente para Servidores Concursados", "f14", "center");
    }

    $grid->fechaColuna();
    $grid->fechaGrid();
    $page->terminaPagina();
}