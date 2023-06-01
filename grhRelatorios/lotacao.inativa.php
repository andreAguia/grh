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

    $select = 'SELECT UADM,
                      DIR,
                      GER,
                      nome
                 FROM tblotacao LEFT JOIN tbcampus USING (idCampus)
                WHERE NOT ativo
             ORDER BY UADM, DIR, GER, nome';

    $result = $servidor->select($select);

    $relatorio = new Relatorio();
    $relatorio->set_titulo('Relatório Lotações Inativas');
    $relatorio->set_subtitulo('Agrupados por Diretoria - Ordenados pelo Nome');
    $relatorio->set_label(array('UADM', 'Diretoria', 'Gerência', 'Nome'));
    #$relatorio->set_width(array(10,10,10,50));
    $relatorio->set_align(array("left", "left", "left", "left"));
    $relatorio->set_conteudo($result);
    $relatorio->show();

    $page->terminaPagina();
}