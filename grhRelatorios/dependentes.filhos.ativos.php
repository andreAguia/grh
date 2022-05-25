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
$acesso = Verifica::acesso($idUsuario, [1, 2, 12]);

if ($acesso) {
    # Conecta ao Banco de Dados
    $servidor = new Pessoal();

    # Começa uma nova página
    $page = new Page();
    $page->iniciaPagina();

    #####
    # Corpo do relatorio
    $select = 'SELECT tbdependente.nome,
                     tbdependente.dtNasc,
                     YEAR(CURDATE( )) - YEAR(tbdependente.dtNasc) - IF(RIGHT(CURDATE( ),5) < RIGHT(tbdependente.dtNasc,5),1,0),
                     tbpessoa.nome
                FROM tbservidor LEFT JOIN tbpessoa ON (tbservidor.idPessoa = tbpessoa.idPessoa)
                                     JOIN tbdependente ON (tbdependente.idPessoa = tbpessoa.idPessoa)
                                     JOIN tbparentesco ON (tbparentesco.idParentesco = tbdependente.parentesco)
               WHERE tbdependente.parentesco = 2
                 AND tbservidor.situacao = 1 
            ORDER BY tbdependente.dtNasc';

    $result = $servidor->select($select);

    $relatorio = new Relatorio();
    $relatorio->set_titulo('Relatório Dependentes (Filhos) de Servidores Ativos');
    $relatorio->set_subtitulo('Ordenados por Idade');
    $relatorio->set_label(array('Dependente', 'Nascimento', 'Idade', 'Servidor'));
    #$relatorio->set_width(array(10,40,40,10));
    $relatorio->set_align(array("left", "center", "center", "left"));
    $relatorio->set_funcao(array(null, "date_to_php"));
    $relatorio->set_conteudo($result);
    #$relatorio->set_numGrupo(4);
    #$relatorio->set_botaoVoltar('../sistema/areaServidor.php');
    $relatorio->show();

    $page->terminaPagina();
}
