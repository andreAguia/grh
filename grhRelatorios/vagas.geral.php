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

    $select = 'SELECT concat(tbconcurso.anoBase," - Edital: ",DATE_FORMAT(tbconcurso.dtPublicacaoEdital,"%d/%m/%Y")) as concurso,
                      concat(IFnull(tblotacao.GER,"")," - ",IFnull(tblotacao.nome,"")) as lotacao,
                      area,
                      idServidor,
                      tbvagahistorico.obs,
                      idVaga
                 FROM tbvagahistorico JOIN tbconcurso USING (idConcurso)
                                      JOIN tblotacao USING (idLotacao)
                                      JOIN tbvaga USING (idVaga)
             ORDER BY idVaga, tbconcurso.dtPublicacaoEdital desc';

    $result = $servidor->select($select);

    $relatorio = new Relatorio();
    $relatorio->set_titulo('Relatório Geral de Vagas');
    $relatorio->set_subtitulo('Ordenados pela Vaga');
    
    $relatorio->set_label(array("Concurso", "Laboratório", "Área", "Servidor", "Obs","Vaga"));
    $relatorio->set_funcao(array(null, null, null));
    $relatorio->set_align(array("left", "left", "left", "left", "left"));
    $relatorio->set_classe(array(null, null, null, "Vaga",null,"Vaga"));
    $relatorio->set_metodo(array(null, null, null, "get_Nome",null,"exibeDadosVagaRelatorio"));

    $relatorio->set_conteudo($result);
    $relatorio->set_numGrupo(5);
    $relatorio->set_numGrupoEnfeite(false);
    $relatorio->show();

    $page->terminaPagina();
}