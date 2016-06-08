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
    $servidor = new Pessoal();

    # Começa uma nova página
    $page = new Page();			
    $page->iniciaPagina();
    
    #####

    # Corpo do relatorio
    $select ='SELECT tbfuncionario.matricula, 
                     tbfuncionario.idFuncional,
                     tbpessoa.nome,
                     tbdependente.nome,
                     YEAR(CURDATE( )) - YEAR(tbdependente.dtNasc) - IF(RIGHT(CURDATE( ),5) < RIGHT(tbdependente.dtNasc,5),1,0)                 
                FROM tbfuncionario LEFT JOIN tbpessoa ON (tbfuncionario.idPessoa = tbpessoa.idPessoa)
                                        JOIN tbdependente ON (tbdependente.idPessoa = tbpessoa.idPessoa)
                                        JOIN tbparentesco ON (tbparentesco.idParentesco = tbdependente.parentesco)
               WHERE tbdependente.parentesco = 2
                 AND tbfuncionario.Sit=1 
            ORDER BY tbpessoa.nome';

    $result = $servidor->select($select);

    $relatorio = new Relatorio();
    $relatorio->set_titulo('Relatório Servidores Ativos com Dependentes (Filhos)');
    $relatorio->set_subtitulo('Ordenado pelo Nome do Servidor');
    $relatorio->set_label(array('Matricula','Id','Nome','Nome do Filho(a)','Idade'));
    $relatorio->set_width(array(10,10,30,30,10));
    $relatorio->set_align(array("center","center","left","left"));
    $relatorio->set_funcao(array("dv"));
    $relatorio->set_conteudo($result);
    #$relatorio->set_numGrupo(4);
    #$relatorio->set_botaoVoltar('../sistema/areaServidor.php');
    $relatorio->show();

    $page->terminaPagina();
}
?>
