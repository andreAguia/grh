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
                     "_________________________"
                FROM tbfuncionario LEFT JOIN tbpessoa ON (tbfuncionario.idPessoa = tbpessoa.idPessoa)
               WHERE tbfuncionario.Sit = 1 AND idPerfil = 1
            ORDER BY tbpessoa.nome';

    $result = $servidor->select($select);

    $relatorio = new Relatorio();
    $relatorio->set_titulo('Relatório Estatutários Ativos');
    $relatorio->set_subtitulo('Assinatura');

    $relatorio->set_label(array('Matricula','Id','Nome','Lotação','Assinatura'));
    $relatorio->set_width(array(10,15,40,25,10));
    $relatorio->set_align(array("center","center","left","left"));
    $relatorio->set_funcao(array("dv"));
    
    $relatorio->set_classe(array(null,null,null,"Pessoal"));
    $relatorio->set_metodo(array(null,null,null,"get_lotacao"));

    $relatorio->set_conteudo($result);
    $relatorio->set_zebrado(false);
    #$relatorio->set_botaoVoltar('../sistema/areaServidor.php');
    $relatorio->show();

    $page->terminaPagina();
}
?>
