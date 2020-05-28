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
    $pessoal = new Pessoal();

    # Começa uma nova página
    $page = new Page();
    $page->iniciaPagina();

    ######

    $select = 'SELECT tbtipocargo.cargo,
                     tbarea.area,
                     nome
                FROM tbcargo LEFT JOIN tbtipocargo USING (idTipoCargo)
                             LEFT JOIN tbarea USING (idarea)
            ORDER BY 1,2,3';

    $result = $pessoal->select($select);

    $relatorio = new Relatorio();
    $relatorio->set_titulo('Relatório de Cargos');
    $relatorio->set_subtitulo('Agrupados por Nível - Ordenados pelo Nome do Cargo');

    $relatorio->set_label(array("Cargo", "Área", "Função"));
    $relatorio->set_width(array(0, 40, 60));
    $relatorio->set_align(array(NULL, "left", "left"));

    $relatorio->set_conteudo($result);
    $relatorio->set_numGrupo(0);
    $relatorio->show();

    $page->terminaPagina();
}