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
                     tbfuncionario.dtAdmissao,
                     CONCAT(tbconcurso.anoBase," - ",tbconcurso.regime," - ",tbconcurso.orgExecutor) as aa
                FROM tbfuncionario LEFT JOIN tbpessoa ON (tbfuncionario.idPessoa = tbpessoa.idPessoa)
                                   LEFT JOIN tbcargo ON (tbfuncionario.idCargo = tbcargo.idCargo)
                                        JOIN tbhistlot ON (tbfuncionario.matricula = tbhistlot.matricula)
                                        JOIN tblotacao ON (tbhistlot.lotacao=tblotacao.idLotacao)
                                   LEFT JOIN tbsituacao ON (tbfuncionario.Sit = tbsituacao.idSit)
                                   LEFT JOIN tbconcurso ON (tbfuncionario.idConcurso = tbconcurso.idConcurso)
                WHERE tbfuncionario.sit = 1 AND tbfuncionario.idPerfil = 1
                  AND tbhistlot.data = (select max(data) from tbhistlot where tbhistlot.matricula = tbfuncionario.matricula)
             ORDER BY tbconcurso.anoBase, tbpessoa.nome';

    $result = $servidor->select($select);

    $relatorio = new Relatorio();
    $relatorio->set_titulo('Relatório de Estatutários');
    $relatorio->set_subtitulo('Agrupados por Concurso - Ordenados pelo Nome do Servidor');

    $relatorio->set_label(array('Matricula','Id','Nome','Cargo','Lotação','Admissão',''));
    $relatorio->set_width(array(10,10,30,20,20,10));
    $relatorio->set_align(array("center","center","left","left","left"));
    $relatorio->set_funcao(array("dv",null,null,null,null,"date_to_php"));
    $relatorio->set_classe(array(null,null,null,"Pessoal"));
    $relatorio->set_metodo(array(null,null,null,"get_cargo"));    

    $relatorio->set_conteudo($result);
    $relatorio->set_numGrupo(6);
    #$relatorio->set_botaoVoltar('../sistema/areaServidor.php');
    $relatorio->show();

    $page->terminaPagina();
}
?>
