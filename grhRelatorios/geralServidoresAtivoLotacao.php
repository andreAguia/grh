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
                     concat(tblotacao.UADM," - ",tblotacao.DIR," - ",tblotacao.GER) lotacao,
                     tbperfil.nome,
                     tbfuncionario.dtAdmissao,
                     tbfuncionario.matricula
                FROM tbfuncionario LEFT JOIN tbpessoa ON (tbfuncionario.idPessoa = tbpessoa.idPessoa)
                                        JOIN tbhistlot ON (tbfuncionario.matricula = tbhistlot.matricula)
                                        JOIN tblotacao ON (tbhistlot.lotacao=tblotacao.idLotacao)
                                   LEFT JOIN tbsituacao ON (tbfuncionario.sit = tbsituacao.idSit)
                                   LEFT JOIN tbperfil ON (tbfuncionario.idPerfil = tbperfil.idPerfil)
               WHERE tbfuncionario.Sit = 1
                 AND tbhistlot.data = (select max(data) from tbhistlot where tbhistlot.matricula = tbfuncionario.matricula)
            ORDER BY lotacao, tbpessoa.nome';

    $result = $servidor->select($select);

    $relatorio = new Relatorio();
    $relatorio->set_titulo('Relatório Geral de Servidores Ativos');
    $relatorio->set_subtitulo('Agrupados por Lotação - Ordenados pelo Nome');
    $relatorio->set_label(array('Matricula','Id','Nome','Cargo','Lotação','Perfil','Admissão','Situação'));
    $relatorio->set_width(array(8,10,30,30,0,10,10,8));
    $relatorio->set_align(array("center","center","left","left"));
    $relatorio->set_funcao(array("dv",null,null,null,null,null,"date_to_php"));
    
    $relatorio->set_classe(array(null,null,null,"pessoal",null,null,null,"pessoal"));
    $relatorio->set_metodo(array(null,null,null,"get_Cargo",null,null,null,"get_Situacao"));
    
    $relatorio->set_conteudo($result);
    $relatorio->set_numGrupo(4);
    #$relatorio->set_botaoVoltar('../sistema/areaServidor.php');
    $relatorio->show();

    $page->terminaPagina();
}