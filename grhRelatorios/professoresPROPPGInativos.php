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
    
    $select ='SELECT tbdocumentacao.cpf,
                     tbpessoa.nome,
                     tbservidor.idServidor,
                     tbpessoa.dtNasc,
                     tbpais.pais,
                     tbservidor.dtAdmissao,
                     tbservidor.idServidor
                FROM tbservidor LEFT JOIN tbpessoa USING (idPessoa)                                    
                                     JOIN tbdocumentacao USING (idPessoa)
                                     JOIN tbpais ON (tbpessoa.paisOrigem = tbpais.idPais)
               WHERE tbservidor.situacao <> 1
                 AND tbservidor.idPerfil <> 10
                 AND (idCargo = 128 OR idCargo = 129)
            ORDER BY tbpessoa.nome';

    $result = $servidor->select($select);

    $relatorio = new Relatorio();
    $relatorio->set_titulo('Relatório de Professores Inativos (Não Ativos)');
    $relatorio->set_subtitulo('Ordenados por Nome');
    $relatorio->set_label(array('CPF','Nome','Emails','Nascimento','Pais de Origem','Admissão','Situação'));
    #$relatorio->set_width(array(10,30,30,0,10,10,10));
    $relatorio->set_align(array("center","left","left","left"));
    $relatorio->set_funcao(array(NULL,NULL,NULL,"date_to_php",NULL,"date_to_php"));
    
    $relatorio->set_classe(array(NULL,NULL,"pessoal",NULL,NULL,NULL,"pessoal"));
    $relatorio->set_metodo(array(NULL,NULL,"get_emails",NULL,NULL,NULL,"get_Situacao"));
    $relatorio->set_bordaInterna(TRUE);
    
    $relatorio->set_conteudo($result);
    #$relatorio->set_numGrupo(3);
    #$relatorio->set_botaoVoltar('../sistema/areaServidor.php');
    $relatorio->show();

    $page->terminaPagina();
}