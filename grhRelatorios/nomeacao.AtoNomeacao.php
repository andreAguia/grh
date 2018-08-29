<?php
/**
 * Sistema GRH
 * 
 * Ato de Nomeaçao
 *   
 * By Alat
 */

# Configuração
include ("../grhSistema/_config.php");

# Pega o idComissao 
$idComissao = get('idComissao');

# Conecta ao Banco de Dados    
$pessoal = new Pessoal();

# Preenche as variaveis
$nome = NULL;
$idFuncional = NULL;
$dtInicial = NULL;
$cargo = NULL;
$simbolo = NULL;
$curso = NULL;
$centro = NULL;
$reitor = $pessoal->get_nomeReitor();

# Permissão de Acesso
$acesso = Verifica::acesso($idUsuario);

if($acesso){  
    
    # Começa uma nova página
    $page = new Page();
    $page->iniciaPagina();
    
    # Limita a página
    $grid = new Grid();
    $grid->abreColuna(12);
    
    # Inicia o Relatorio
    $ato = new Relatorio();
    $ato->set_titulo("ATO DO REITOR");
    $ato->set_totalRegistro(FALSE);
    $ato->set_dataImpressao(FALSE);
    $ato->show();
    
    $grid->fechaColuna();
    $grid->abreColuna(4);
    $grid->fechaColuna();
    $grid->abreColuna(8);
    
    # Preambulo
    p("O REITOR DA UNIVERSIDADE ESTADUAL DO NORTE FLUMINENSE DARCY RIBEIRO,  no uso das atribuiçoes legais;","preambulo");
    
    $grid->fechaColuna();
    $grid->abreColuna(12);
    br(3);
    
    # Principal
    p("NOMEIA $nome, ID Funcional n $idFuncional, para exercer, com validade a contar de $dtInicial, o cargo em comissao de $cargo, simbolo $simbolo, do $curso do $centro, da Universidade Estadual do Nortte Fluminense - Darcy Ribeiro - UENF, da Secretaria de Estado de Ciencia, Tecnologia e Inovaçao - SECTI, do Quadro Permanente de Pessoal Civil do Poder Executivo do Estado do Rio de Janeiro, em vaga anteriormente ocupada oelo mesmo.","principal");
    br(3);
    
    # Data
    p("Campos dos Goytacazes, ","principal");
    br(3);
    
    # Reitor
    p($reitor,"reitor");
    br();
    p("REITOR","reitor");
    
    
    $grid->fechaColuna();
    $grid->fechaGrid();
    
    $page->terminaPagina();
}