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
    $servidor = new Pessoal();

    # Começa uma nova página
    $page = new Page();			
    $page->iniciaPagina();

    ######
    
    $select ='SELECT tbfuncionario.matricula,
                     tbfuncionario.idFuncional,
                     tbpessoa.nome,
                     tbfuncionario.matricula,
                     "[","]"
                FROM tbfuncionario LEFT JOIN tbpessoa ON (tbfuncionario.idPessoa = tbpessoa.idPessoa)
               WHERE tbfuncionario.Sit = 1
            ORDER BY tbpessoa.nome';

    $result = $servidor->select($select);

    $relatorio = new Relatorio();
    $relatorio->set_titulo('Relatório Geral de Servidores Ativos');
    $relatorio->set_subtitulo('Check');
    $relatorio->set_label(array('Matricula','Id','Nome','Lotação','',''));
    $relatorio->set_width(array(10,15,40,25,5,5));
    $relatorio->set_align(array("center","center","left","left","right"));
    $relatorio->set_funcao(array("dv"));
    
    $relatorio->set_classe(array(null,null,null,"pessoal"));
    $relatorio->set_metodo(array(null,null,null,"get_lotacao"));
    $relatorio->set_zebrado(FALSE);
    $relatorio->set_bordaInterna(TRUE);
    
    $relatorio->set_conteudo($result);
    #$relatorio->set_numGrupo(4);
    #$relatorio->set_botaoVoltar('../sistema/areaServidor.php');
    $relatorio->show();

    $page->terminaPagina();
}
?>
