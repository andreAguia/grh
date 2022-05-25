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

    # Pega os parâmetros dos relatórios
    $lotacao = get('lotacao', post('lotacao'));

    ######

    $select = 'SELECT idVaga,
                      centro,
                      (SELECT idLotacao FROM tbvagahistorico l JOIN tbconcurso USING(idConcurso) WHERE l.idVaga = p.idVaga ORDER BY tbconcurso.dtPublicacaoEdital LIMIT 1),
                      idCargo,
                      idVaga
                 FROM tbvaga p
             ORDER BY centro, 3';

    $result = $servidor->select($select);

    $relatorio = new Relatorio();
    $relatorio->set_titulo('Relatório de Resumo de Vagas');
    $relatorio->set_subtitulo('Ordenados pela Vaga');
    
    $relatorio->set_label(array("Vaga", "Centro", "Laboratório de Origem","Cargo", "Status"));
    $relatorio->set_funcao(array(null, null, null));
    $relatorio->set_align(array("center","center","left"));
    $relatorio->set_classe(array(null, null, "Pessoal","Pessoal","Vaga"));
    $relatorio->set_metodo(array(null, null, "get_nomeLotacao3","get_nomeCargo","get_status"));

    $relatorio->set_conteudo($result);
    $relatorio->set_numGrupo(1);
    #$relatorio->set_numGrupoEnfeite(false);
    $relatorio->show();

    $page->terminaPagina();
}