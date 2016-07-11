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
$acesso = Verifica::acesso($idUsuario,2);

if($acesso)
{    
    # Conecta ao Banco de Dados
    $pessoal = new Pessoal();

    # Começa uma nova página
    $page = new Page();			
    $page->iniciaPagina();

    ######

    $select ='SELECT CASE substring(simbolo,1,1)
                        WHEN "F" THEN "FENORTE"
                        WHEN "P" THEN "TECNORTE"
                     END,
                     descricao,
                     simbolo,
                     valsal,
                     vagas
                FROM tbtipocomissao
           ORDER BY simbolo';

    $result = $pessoal->select($select);

    $relatorio = new Relatorio();
    $relatorio->set_titulo('Relatório de Cargos em Comissão');
    $relatorio->set_subtitulo('Agrupados por Instituição');

    $relatorio->set_label(array('','Cargo','Símbolo','Valor','Vagas'));
    $relatorio->set_width(array(0,50,20,20,10));
    $relatorio->set_align(array("center"));
    $relatorio->set_funcao(array(null,null,null,'formataMoeda'));
    
    $relatorio->set_colunaSomatorio(4);
    $relatorio->set_textoSomatorio('Total de Vagas:');

    $relatorio->set_conteudo($result);
    $relatorio->set_numGrupo(0);
    
    $relatorio->set_colunaSomatorio(4);
    #$relatorio->set_funcaoSomatorio('formataMoeda');
    $relatorio->set_textoSomatorio('Total de Vagas:');
    #$relatorio->set_exibeSomatorioGeral(false);
    
    $relatorio->show();

    $page->terminaPagina();
}