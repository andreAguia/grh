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
    
    $select ='SELECT tbpessoa.nome,
                     tbservidor.dtAdmissao,
                     tbpessoa.sexo,
                     tbpessoa.dtNasc,
                     date_format(tbpessoa.dtNasc,"%d/%m/%Y")
                FROM tbservidor LEFT JOIN tbpessoa ON (tbservidor.idPessoa = tbpessoa.idPessoa)
                                LEFT JOIN tbdocumentacao ON (tbdocumentacao.idPessoa = tbpessoa.idPessoa)
                WHERE situacao = 1 
                 AND (idCargo = 128 OR idCargo = 129)
             ORDER BY tbpessoa.dtNasc';

    $result = $servidor->select($select);

    $relatorio = new Relatorio();
    $relatorio->set_titulo('Relatório de Professores Ativos');
    $relatorio->set_subtitulo('(Com Sexo e Idade Ordenado por Idade Decrescente)');
    $relatorio->set_label(array('Nome','Admissão','Sexo','Nascimento','Idade'));
    $relatorio->set_width(array(35,15,10,20,10));
    $relatorio->set_align(array("left","center","center","center"));
    $relatorio->set_funcao(array(NULL,"date_to_php",NULL,"date_to_php","idade"));
    #$relatorio->set_classe(array(NULL,NULL,NULL,NULL,NULL,NULL,"Pessoal"));
    #$relatorio->set_metodo(array(NULL,NULL,NULL,NULL,NULL,NULL,"get_Situacao"));    
    $relatorio->set_conteudo($result);
    #$relatorio->set_numGrupo(2);
    #$relatorio->set_botaoVoltar('../sistema/areaServidor.php');
    $relatorio->show();

    $page->terminaPagina();
}