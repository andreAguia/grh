<?php

/**
 * Relatório
 *    
 * By Alat
 */
# Inicia as variáveis que receberão as sessions
$idUsuario = null;              # Servidor logado
# Configuração
include ("../grhSistema/_config.php");

# Permissão de Acesso
$acesso = Verifica::acesso($idUsuario, [1, 2, 12]);

# Pega os parâmetros
$idVaga = get('idVaga',get_session('idVaga'));

if ($acesso) {

    # Conecta ao Banco de Dados
    $pessoal = new Pessoal();
    $vaga = new Vaga();

    # Começa uma nova página
    $page = new Page();
    $page->iniciaPagina();

    ######   

    $conteudo = $vaga->get_dados($idVaga);

    $centro = $conteudo["centro"];
    $idCargo = $conteudo["idCargo"];
    $cargo = $pessoal->get_nomeCargo($idCargo);

    $labOrigem = $pessoal->get_nomeLotacao2($vaga->get_laboratorioOrigem($idVaga));

    $status = $vaga->get_status($idVaga);

    ######
    # Pega os dados
    $select = "SELECT concat(tbconcurso.anoBase,' - Edital: ',DATE_FORMAT(tbconcurso.dtPublicacaoEdital,'%d/%m/%Y')) as concurso,
                      concat(IFnull(tblotacao.GER,''),' - ',IFnull(tblotacao.nome,'')) as lotacao,
                      area,
                      idServidor,
                      tbvagahistorico.obs,
                      idVagaHistorico
                 FROM tbvagahistorico JOIN tbconcurso USING (idConcurso)
                                      JOIN tblotacao USING (idLotacao)
                WHERE idVaga = $idVaga ORDER BY tbconcurso.dtPublicacaoEdital desc";

    $resumo = $pessoal->select($select);

    # Monta o Relatório
    $relatorio = new Relatorio();
    $relatorio->set_conteudo($resumo);
    $relatorio->set_label(["Concurso", "Laboratório", "Área", "Servidor", "Obs"]);
    $relatorio->set_align(["left", "left", "left", "left", "left"]);
    
    $relatorio->set_classe([null, null, null, "Vaga"]);
    $relatorio->set_metodo([null, null, null, "get_Nome"]);

    $relatorio->set_titulo("Histórico de Concursos<br/>Vaga {$idVaga}");
    $relatorio->set_subtitulo("{$centro} - {$cargo}<br/>Lab de Origem:<br/>{$labOrigem}<br/>Vaga {$status}");

    $relatorio->set_numeroOrdem(true);
    $relatorio->set_numeroOrdemTipo('d');
    $relatorio->set_bordaInterna(true);

    $relatorio->show();

    $page->terminaPagina();
}