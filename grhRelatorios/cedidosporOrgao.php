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
                     tbcedido.orgaoOrigem,
                     tbservidor.dtAdmissao,
                     tbservidor.dtDemissao
                FROM tbservidor LEFT JOIN tbpessoa USING (idPessoa)
                               RIGHT JOIN tbcedido USING (idServidor)
               WHERE tbservidor.idPerfil = 2
                 AND situacao = 1 
                 AND ((tbservidor.dtDemissao is NULL) OR (tbservidor.dtDemissao > CURDATE()))
             ORDER BY tbcedido.orgaoOrigem, nome';

    $result = $servidor->select($select);

    $relatorio = new Relatorio();
    $relatorio->set_titulo('Relatório de Cedidos de Outros Órgãos');
    $relatorio->set_subtitulo('Agrupados pelo Órgão');

    $relatorio->set_label(array('IdFuncional','Nome','Cargo','Órgão','Início','Término'));
    $relatorio->set_width(array(10,30,20,20,10,10));
    $relatorio->set_align(array("center","left","left","left"));
    $relatorio->set_funcao(array(NULL,NULL,NULL,NULL,"date_to_php","date_to_php"));
    $relatorio->set_classe(array(NULL,NULL,"Pessoal"));
    $relatorio->set_metodo(array(NULL,NULL,"get_Cargo"));  

    $relatorio->set_conteudo($result);
    $relatorio->set_numGrupo(3);
    #$relatorio->set_botaoVoltar('../sistema/areaServidor.php');
    $relatorio->show();

    $page->terminaPagina();
}