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
$acesso = Verifica::acesso($idUsuario, 2);

if ($acesso) {
    # Conecta ao Banco de Dados
    $servidor = new Pessoal();

    # Começa uma nova página
    $page = new Page();
    $page->iniciaPagina();

    # Pega os parâmetros dos relatórios
    $lotacao = get('lotacao', post('lotacao'));

    ######

    $select = 'SELECT idVaga,
                      centro,
                      idVaga,
                      idCargo,
                      idVaga
                 FROM tbvaga
             ORDER BY idVaga';

    $result = $servidor->select($select);

    $relatorio = new Relatorio();
    $relatorio->set_titulo('Relatório de Resumo de Vagas');
    $relatorio->set_subtitulo('Ordenados pela Vaga');
    
    $relatorio->set_label(array("Vaga", "Centro", "Laboratório de Origem","Cargo", "Status"));
    $relatorio->set_funcao(array(null, null, null));
    $relatorio->set_align(array("center","center","left"));
    $relatorio->set_classe(array(null, null, "Vaga","Pessoal","Vaga"));
    $relatorio->set_metodo(array(null, null, "exibeLaboratorioOrigem","get_nomeCargo","get_status"));

    $relatorio->set_conteudo($result);
    #$relatorio->set_numGrupo(5);
    #$relatorio->set_numGrupoEnfeite(false);
    $relatorio->show();

    $page->terminaPagina();
}