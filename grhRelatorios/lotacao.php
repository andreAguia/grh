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
$acesso = Verifica::acesso($idUsuario, [2, 10]);

if ($acesso) {
    # Conecta ao Banco de Dados
    $servidor = new Pessoal();

    # Começa uma nova página
    $page = new Page();
    $page->iniciaPagina();

    # Pega os parâmetros dos relatórios
    $lotacao = get('lotacao', post('lotacao'));

    ######

    $select = 'SELECT codigo,
                     DIR,
                     campus,
                     GER,
                     nome
                FROM tblotacao LEFT JOIN tbcampus USING (idCampus)
               WHERE ativo
            ORDER BY DIR, nome';

    $result = $servidor->select($select);

    $relatorio = new Relatorio();
    $relatorio->set_titulo('Relatório Lotações Ativas');
    $relatorio->set_subtitulo('Agrupados por Diretoria - Ordenados pelo Nome');
    $relatorio->set_label(array('Código', 'Diretoria', 'Campus', 'Sigla', 'Nome'));
    #$relatorio->set_width(array(10,10,10,50));
    $relatorio->set_align(array("center", "center", "center", "center", "left"));
    $relatorio->set_conteudo($result);
    $relatorio->set_numGrupo(1);
    $relatorio->show();

    $page->terminaPagina();
}