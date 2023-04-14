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

        $result = $servidor->select($select);

        $cargoNome = $result[0][2];
        $tipoCargo = $result[0][3];
        $area = $result[0][4];

        $titulo = "Cargo: {$servidor->get_nomeTipoCargo($tipoCargo)}<br/>Área: {$servidor->get_nomeArea($area)}<br/>Função: {$cargoNome}";

        ######

        $select = 'SELECT descricao'
                . ' FROM tbarea'
                . ' WHERE idarea = ' . $area;

        $result = $servidor->select($select);

        $relatorio = new Relatorio();
        $relatorio->set_titulo("Mapa do Cargo");
        $relatorio->set_subtitulo($titulo);
        $relatorio->set_tituloTabela("Área: {$servidor->get_nomeArea($area)}");
        $relatorio->set_label(array('Descrição Sintética da Área'));
        $relatorio->set_width(array(100));
        $relatorio->set_align(array("left"));
        #$relatorio->set_funcao(array(null,null,null,null,null,"date_to_php"));
        #$relatorio->set_classe(array(null,null,null,null,null,null,"Pessoal"));
        #$relatorio->set_metodo(array(null,null,null,null,null,null,"get_Situacao"));    
        $relatorio->set_conteudo($result);
        $relatorio->set_totalRegistro(false);
        $relatorio->set_dataImpressao(false);
        $relatorio->set_subTotal(false);
        #$relatorio->set_cabecalhoRelatorio(false);
        #$relatorio->set_menuRelatorio(false);
        $relatorio->show();

        ######

        $select = 'SELECT requisitos'
                . ' FROM tbarea'
                . ' WHERE idarea = ' . $area;

        $result = $servidor->select($select);

        $relatorio = new Relatorio();
        $relatorio->set_label(array('Requisitos para o Provimento da Área'));
        $relatorio->set_width(array(100));
        $relatorio->set_align(array("left"));
        #$relatorio->set_funcao(array(null,null,null,null,null,"date_to_php"));
        #$relatorio->set_classe(array(null,null,null,null,null,null,"Pessoal"));
        #$relatorio->set_metodo(array(null,null,null,null,null,null,"get_Situacao"));    
        $relatorio->set_conteudo($result);
        $relatorio->set_totalRegistro(false);
        $relatorio->set_dataImpressao(false);
        $relatorio->set_subTotal(false);
        $relatorio->set_cabecalhoRelatorio(false);
        $relatorio->set_menuRelatorio(false);
        $relatorio->show();

        ######

        $select = 'SELECT atribuicoes'
                . ' FROM tbcargo'
                . ' WHERE idcargo = ' . $cargo;

        $result = $servidor->select($select);

        $relatorio = new Relatorio();
        $relatorio->set_tituloTabela("Função: {$cargoNome}");
        #$relatorio->set_titulo($servidor->get_nomeArea($area));
        $relatorio->set_label(array('Atribuições da Função'));
        $relatorio->set_width(array(100));
        $relatorio->set_align(array("left"));
        $relatorio->set_funcao(array('formataAtribuicao'));
        #$relatorio->set_classe(array(null,null,null,null,null,null,"Pessoal"));
        #$relatorio->set_metodo(array(null,null,null,null,null,null,"get_Situacao"));    
        $relatorio->set_conteudo($result);
        $relatorio->set_totalRegistro(false);
        $relatorio->set_dataImpressao(true);
        $relatorio->set_subTotal(false);
        $relatorio->set_cabecalhoRelatorio(false);
        $relatorio->set_menuRelatorio(false);
        $relatorio->show();
    } else {
        br(4);
        p("O Mapa do Cargo é somente para Servidores Concursados", "f14", "center");
    }

    $grid->fechaColuna();
    $grid->fechaGrid();
    $page->terminaPagina();
}