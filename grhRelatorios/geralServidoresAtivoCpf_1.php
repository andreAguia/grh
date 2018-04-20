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
    
    $select ='SELECT tbservidor.matricula,
                     tbpessoa.nome,
                     tbservidor.idServidor,
                     concat(IFNULL(tblotacao.UADM,"")," - ",IFNULL(tblotacao.DIR,"")," - ",IFNULL(tblotacao.GER,"")," - ",IFNULL(tblotacao.nome,"")) lotacao,
                     tbperfil.nome,
                     tbdocumentacao.cpf,
                     tbservidor.dtAdmissao,
                     tbpessoa.dtNasc
                FROM tbservidor LEFT JOIN tbpessoa ON (tbservidor.idPessoa = tbpessoa.idPessoa)
                                        JOIN tbhistlot ON (tbservidor.idServidor = tbhistlot.idServidor)
                                        JOIN tblotacao ON (tbhistlot.lotacao=tblotacao.idLotacao)
                                   LEFT JOIN tbdocumentacao ON (tbdocumentacao.idPessoa = tbpessoa.idPessoa)
                                   LEFT JOIN tbperfil ON (tbservidor.idPerfil = tbperfil.idPerfil)
               WHERE tbservidor.situacao <> 1
                 AND tbhistlot.data = (select max(data) from tbhistlot where tbhistlot.idServidor = tbservidor.idServidor)
            ORDER BY tbpessoa.nome';

    $result = $servidor->select($select);

    $relatorio = new Relatorio();
    $relatorio->set_titulo('Relatório Geral de Servidores Ativos');
    $relatorio->set_subtitulo('Agrupados por Lotação - Com CPF - Ordenados pelo Nome');
    $relatorio->set_label(array('Matricula','Nome','Cargo','Lotação','Perfil','CPF','Admissão','Nascimento'));
    #$relatorio->set_width(array(10,30,30,0,10,20));
    $relatorio->set_align(array("center","left","left"));
    
    $relatorio->set_classe(array(NULL,NULL,"pessoal"));
    $relatorio->set_metodo(array(NULL,NULL,"get_Cargo"));
    $relatorio->set_funcao(array(NULL,NULL,NULL,NULL,NULL,NULL,'date_to_php','date_to_php'));
    
    $relatorio->set_conteudo($result);
    #$relatorio->set_numGrupo(3);
    #$relatorio->set_botaoVoltar('../sistema/areaServidor.php');
    $relatorio->show();

    $page->terminaPagina();
}