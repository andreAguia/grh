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
$acesso = Verifica::acesso($idUsuario, 2);

if ($acesso) {
    # Conecta ao Banco de Dados
    $servidor = new Pessoal();

    # Começa uma nova página
    $page = new Page();
    $page->iniciaPagina();

    ######

    $select = 'SELECT tbservidor.idFuncional,
                     tbpessoa.nome,
                     tbhistcessao.orgao,
                     tbhistcessao.dtInicio,
                     tbhistcessao.dtFim,
                     year(dtInicio),
                     tbservidor.idServidor
                FROM tbservidor LEFT JOIN tbpessoa ON (tbservidor.idPessoa = tbpessoa.idPessoa)
                                   RIGHT JOIN tbhistcessao ON(tbservidor.idServidor = tbhistcessao.idServidor)
               WHERE tbservidor.idPerfil = 1
             ORDER BY year(dtInicio), month(dtInicio), nome';

    $result = $servidor->select($select);

    $relatorio = new Relatorio();
    $relatorio->set_titulo('Histórico de Estatutários Cedidos');
    $relatorio->set_subtitulo('Agrupados pelo Ano de Cessão');

    $relatorio->set_label(array('IdFuncional', 'Nome', 'Órgão', 'Início', 'Término', 'Ano', 'Situação'));
    $relatorio->set_width(array(10, 30, 30, 10, 10, 0, 10));
    $relatorio->set_align(array("center", "left", "left", "left"));
    $relatorio->set_funcao(array(NULL, NULL, NULL, "date_to_php", "date_to_php"));
    $relatorio->set_classe(array(NULL, NULL, NULL, NULL, NULL, NULL, "Pessoal"));
    $relatorio->set_metodo(array(NULL, NULL, NULL, NULL, NULL, NULL, "get_Situacao"));

    $relatorio->set_conteudo($result);
    $relatorio->set_numGrupo(5);
    #$relatorio->set_botaoVoltar('../sistema/areaServidor.php');
    $relatorio->show();

    $page->terminaPagina();
}
