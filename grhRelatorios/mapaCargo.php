<?php
/**
 * Sistema GRH
 * 
 * Relatório
 *   
 * By Alat
 */

# Servidor logado 
$idUsuario = NULL;

# Configuração
include ("../grhSistema/_config.php");

# Permissão de Acesso
$acesso = Verifica::acesso($idUsuario,2);

if($acesso)
{    
    # Conecta ao Banco de Dados
    $servidor = new Pessoal();

    # Começa uma nova página
    $page = new Page();			
    $page->iniciaPagina();
    
    # Limita o tamanho da tela
    $grid = new Grid();
    $grid->abreColuna(12);
    
    # Pega os parâmetros dos relatórios
    $cargo = get('cargo');
    
    ###### 
    
    $select ='SELECT tbtipocargo.cargo, tbarea.area, nome, tbcargo.idTIpoCargo, tbcargo.idArea'
            . ' FROM tbcargo LEFT JOIN tbtipocargo USING (idTIpoCargo)'
            . '              LEFT JOIN tbarea USING (idarea)'
            . ' WHERE idcargo = '.$cargo;

    $result = $servidor->select($select);

    $relatorio = new Relatorio();
    $relatorio->set_titulo('Mapa do Cargo');
    $relatorio->set_label(array('Cargo','Área','Função'));
    $relatorio->set_width(array(30,30,30));
    $relatorio->set_align(array("center"));
    #$relatorio->set_funcao(array(NULL,NULL,NULL,NULL,NULL,"date_to_php"));
    #$relatorio->set_classe(array(NULL,NULL,NULL,NULL,NULL,NULL,"Pessoal"));
    #$relatorio->set_metodo(array(NULL,NULL,NULL,NULL,NULL,NULL,"get_Situacao"));    
    $relatorio->set_conteudo($result);
    $relatorio->set_totalRegistro(FALSE);
    $relatorio->set_dataImpressao(FALSE);
    $relatorio->set_subTotal(FALSE);
    $relatorio->show();
    
    ######
    
    $tipoCargo = $result[0][3];
    $area = $result[0][4];
    
    br();
    p('Área: '.$servidor->get_nomeArea($area));
    
    ######
    
    $select ='SELECT descricao'
            . ' FROM tbarea'
            . ' WHERE idarea = '.$area;

    $result = $servidor->select($select);

    $relatorio = new Relatorio();
    #$relatorio->set_titulo($servidor->get_nomeArea($area));
    $relatorio->set_label(array('Descrição'));
    $relatorio->set_width(array(100));
    $relatorio->set_align(array("left"));
    #$relatorio->set_funcao(array(NULL,NULL,NULL,NULL,NULL,"date_to_php"));
    #$relatorio->set_classe(array(NULL,NULL,NULL,NULL,NULL,NULL,"Pessoal"));
    #$relatorio->set_metodo(array(NULL,NULL,NULL,NULL,NULL,NULL,"get_Situacao"));    
    $relatorio->set_conteudo($result);
    $relatorio->set_totalRegistro(FALSE);
    $relatorio->set_dataImpressao(FALSE);
    $relatorio->set_subTotal(FALSE);
    $relatorio->set_cabecalhoRelatorio(FALSE);
    $relatorio->set_menuRelatorio(FALSE);
    $relatorio->show();
    
    ######
    
    $select ='SELECT obs'
            . ' FROM tbarea'
            . ' WHERE idarea = '.$area;

    $result = $servidor->select($select);

    $relatorio = new Relatorio();
    #$relatorio->set_titulo($servidor->get_nomeArea($area));
    $relatorio->set_label(array('Obs'));
    $relatorio->set_width(array(100));
    $relatorio->set_align(array("left"));
    #$relatorio->set_funcao(array('htmlentities'));
    #$relatorio->set_classe(array(NULL,NULL,NULL,NULL,NULL,NULL,"Pessoal"));
    #$relatorio->set_metodo(array(NULL,NULL,NULL,NULL,NULL,NULL,"get_Situacao"));    
    $relatorio->set_conteudo($result);
    $relatorio->set_totalRegistro(FALSE);
    $relatorio->set_dataImpressao(FALSE);
    $relatorio->set_subTotal(FALSE);
    $relatorio->set_cabecalhoRelatorio(FALSE);
    $relatorio->set_menuRelatorio(FALSE);
    $relatorio->show();
    
    ######
    
    br();
    p('Função: '.$servidor->get_nomeCargo($cargo));
    
    $select ='SELECT atribuicoes'
            . ' FROM tbcargo'
            . ' WHERE idcargo = '.$cargo;

    $result = $servidor->select($select);

    $relatorio = new Relatorio();
    #$relatorio->set_titulo($servidor->get_nomeArea($area));
    $relatorio->set_label(array('Atribuições'));
    $relatorio->set_width(array(100));
    $relatorio->set_align(array("left"));
    $relatorio->set_funcao(array('formataAtribuicao'));
    #$relatorio->set_classe(array(NULL,NULL,NULL,NULL,NULL,NULL,"Pessoal"));
    #$relatorio->set_metodo(array(NULL,NULL,NULL,NULL,NULL,NULL,"get_Situacao"));    
    $relatorio->set_conteudo($result);
    $relatorio->set_totalRegistro(FALSE);
    $relatorio->set_dataImpressao(FALSE);
    $relatorio->set_subTotal(FALSE);
    $relatorio->set_cabecalhoRelatorio(FALSE);
    $relatorio->set_menuRelatorio(FALSE);
    $relatorio->show();
    
    
    $grid->fechaColuna();
    $grid->fechaGrid();
    $page->terminaPagina();
}