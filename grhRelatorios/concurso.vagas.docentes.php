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
    # Pega o id
    $id = soNumeros(get('id'));

    # Conecta ao Banco de Dados
    $servidor = new Pessoal();
    $concurso = new Concurso();

    # Começa uma nova página
    $page = new Page();
    $page->iniciaPagina();

    ######

    $select = "SELECT tblotacao.DIR,
                      tblotacao.GER,
                      tbcargo.nome,
                      area,
                      tbvagahistorico.idVaga,
                      idServidor,
                      tbvagahistorico.obs,
                      idVagaHistorico
                 FROM tbvagahistorico JOIN tbconcurso USING (idConcurso)
                                      JOIN tblotacao USING (idLotacao)
                                      JOIN tbvaga USING (idVaga)
                                      JOIN tbcargo USING (idCargo)
                WHERE idConcurso = {$id} 
             ORDER BY tblotacao.DIR, tblotacao.GER";

    $result = $servidor->select($select);

    $relatorio = new Relatorio();
    $relatorio->set_titulo('Relatório de Vagas do Concurso de ');
    $relatorio->set_subtitulo("Concurso: " . $concurso->get_nomeConcurso($id) . "<br/>Agrupados pelo Centro");
    $relatorio->set_align(["center", "center", "center", "left", "center", "left"]);
    $relatorio->set_label(["Centro", "Laboratório", "Cargo", "Área", "Vaga", "Servidor", "Obs"]);

    $relatorio->set_classe([null, null, null, null, null, "Vaga"]);
    $relatorio->set_metodo([null, null, null, null, null, "get_nomeRel"]);

    $relatorio->set_conteudo($result);
    $relatorio->set_numGrupo(0);
    $relatorio->set_bordaInterna(true);
    $relatorio->show();

    $page->terminaPagina();
}