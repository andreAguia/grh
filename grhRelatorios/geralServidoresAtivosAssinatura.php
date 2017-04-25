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

    ######
    
    $select ='SELECT tbservidor.idFuncional,
                     tbpessoa.nome,
                     tbservidor.idServidor,
                     "_________________________"
                FROM tbservidor LEFT JOIN tbpessoa ON (tbservidor.idPessoa = tbpessoa.idPessoa)
               WHERE tbservidor.situacao = 1
            ORDER BY tbpessoa.nome';

    $result = $servidor->select($select);

    $relatorio = new Relatorio();
    $relatorio->set_titulo('Relatório Geral de Servidores Ativos');
    $relatorio->set_subtitulo('Assinatura');
    $relatorio->set_label(array('IdFuncional','Nome','Lotação','Assinatura'));
    $relatorio->set_width(array(10,40,30,20));
    $relatorio->set_align(array("center","left","left"));
    $relatorio->set_classe(array(NULL,NULL,"pessoal"));
    $relatorio->set_metodo(array(NULL,NULL,"get_lotacao"));
    
    $relatorio->set_conteudo($result);
    
    #$relatorio->set_botaoVoltar('../sistema/areaServidor.php');
    $relatorio->show();

    $page->terminaPagina();
}