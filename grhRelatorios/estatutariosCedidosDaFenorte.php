<?php
/**
 * Sistema GRH
 * 
 * Relatório
 *   
 * By Alat
 */

# Inicia as variáveis que receberão as sessions
$matricula = null;		  # Reservado para a matrícula do servidor logado

# Configuração
include ("../grhSistema/_config.php");

# Permissão de Acesso
$acesso = Verifica::acesso($matricula,13);

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
                     tbhistcessao.orgao,
                     tbhistcessao.dtInicio,
                     tbhistcessao.dtFim,
                     year(dtInicio),
                     tbfuncionario.matricula
                FROM tbfuncionario LEFT JOIN tbpessoa ON (tbfuncionario.idPessoa = tbpessoa.idPessoa)
                                   RIGHT JOIN tbhistcessao ON(tbfuncionario.matricula = tbhistcessao.matricula)
               WHERE tbfuncionario.idPerfil = 1
             ORDER BY year(dtInicio), month(dtInicio), nome';

    $result = $servidor->select($select);

    $relatorio = new Relatorio();
    $relatorio->set_titulo('Relatório de Estatutários da Fenorte Cedidos a outros Órgãos');
    $relatorio->set_subtitulo('Agrupados pelo Ano de Cessão');

    $relatorio->set_label(array('Matricula','Id','Nome','Órgão','Início','Término','Ano','Situação'));
    $relatorio->set_width(array(10,10,30,30,10,10,0,10));
    $relatorio->set_align(array("center","center","left","left","left"));
    $relatorio->set_funcao(array("dv",null,null,null,"date_to_php","date_to_php"));
    $relatorio->set_classe(array(null,null,null,null,null,null,null,"Pessoal"));
    $relatorio->set_metodo(array(null,null,null,null,null,null,null,"get_Situacao"));  

    $relatorio->set_conteudo($result);
    $relatorio->set_numGrupo(6);
    #$relatorio->set_botaoVoltar('../sistema/areaServidor.php');
    $relatorio->show();

    $page->terminaPagina();
}
?>
