<?php
/**
 * Sistema GRH
 * 
 * Relatório
 *   
 * By Alat
 */

# Reservado para a matrícula do servidor logado
$matricula = null;

# Configuração
include ("../grhSistema/_config.php");

# Permissão de Acesso
$acesso = Verifica::acesso($matricula,13);

if($acesso)
{    
    # Conecta ao Banco de Dados
    $pessoal = new Pessoal();

    # Começa uma nova página
    $page = new Page();			
    $page->iniciaPagina();

    # Corpo do relatorio
    $select ='SELECT tbfuncionario.matricula,
                     tbfuncionario.idfuncional,
                     tbpessoa.nome,
                     tbdependente.nome,
                     tbdependente.dtNasc,
                     YEAR(CURDATE( )) - YEAR(tbdependente.dtNasc) - IF(RIGHT(CURDATE( ),5) < RIGHT(tbdependente.dtNasc,5),1,0),
                     tbdependente.processo,                 
                     tbdependente.dttermino,
                     tbdependente.ciExclusao                 
                FROM tbfuncionario LEFT JOIN tbpessoa ON (tbfuncionario.idPessoa = tbpessoa.idPessoa)
                                        JOIN tbdependente ON (tbdependente.idPessoa = tbpessoa.idPessoa)
                                        JOIN tbparentesco ON (tbparentesco.idParentesco = tbdependente.parentesco)
               WHERE tbdependente.parentesco = 2
                 AND tbfuncionario.Sit=1
                 AND auxCreche = "Sim"
                 AND dtTermino >= CURDATE()
            ORDER BY tbpessoa.nome';

    $result = $pessoal->select($select);

    $relatorio = new Relatorio();
    $relatorio->set_titulo('Relatório Geral do Auxílio Creche de Servidores Ativos');
    $relatorio->set_subtitulo('(Servidores que estão recebendo)');
    $relatorio->set_label(array('Matricula','Id','Servidor','Nome do Filho(a)','Nascimento','Idade','Processo','Término','Documento Exclusão'));
    $relatorio->set_width(array(10,5,20,25,10,5,15,10,10));
    $relatorio->set_align(array("center","center","left","left"));
    $relatorio->set_funcao(array("dv",null,null,null,"date_to_php",null,null,"date_to_php"));
    $relatorio->set_conteudo($result);
    $relatorio->set_dataImpressao(false);
    #$relatorio->set_botaoVoltar('../sistema/areaServidor.php');
    $relatorio->show();

    ######################################

    # Corpo do relatorio
    $select ='SELECT tbfuncionario.matricula,
                     tbfuncionario.idfuncional,
                     tbpessoa.nome,
                     tbdependente.nome,
                     tbdependente.dtNasc,
                     YEAR(CURDATE( )) - YEAR(tbdependente.dtNasc) - IF(RIGHT(CURDATE( ),5) < RIGHT(tbdependente.dtNasc,5),1,0),
                     tbdependente.processo,                 
                     tbdependente.dttermino,
                     tbdependente.ciExclusao                 
                FROM tbfuncionario LEFT JOIN tbpessoa ON (tbfuncionario.idPessoa = tbpessoa.idPessoa)
                                        JOIN tbdependente ON (tbdependente.idPessoa = tbpessoa.idPessoa)
                                        JOIN tbparentesco ON (tbparentesco.idParentesco = tbdependente.parentesco)
               WHERE tbdependente.parentesco = 2
                 AND tbfuncionario.Sit=1
                 AND auxCreche = "Sim"
                 AND dtTermino < CURDATE()
            ORDER BY tbpessoa.nome';

    $result = $pessoal->select($select);

    $relatorio = new Relatorio();
    $relatorio->set_titulo('Relatório Geral do Auxílio Creche de Servidores Ativos');
    $relatorio->set_subtitulo('(Servidores que já receberam mais NÂO estão mais recebendo)');
    $relatorio->set_label(array('Matricula','Servidor','Nome do Filho(a)','Nascimento','Idade','Processo','Término','Documento Exclusão'));
    $relatorio->set_width(array(10,5,20,25,10,5,15,10,10));
    $relatorio->set_align(array("center","center","left","left"));
    $relatorio->set_funcao(array("dv",null,null,null,"date_to_php",null,null,"date_to_php"));
    $relatorio->set_conteudo($result);
    $relatorio->set_cabecalhoRelatorio(false);
    $relatorio->set_menuRelatorio(false);
    $relatorio->show();

    $page->terminaPagina();
}
?>
