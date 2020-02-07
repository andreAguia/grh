<?php
/**
 * Relatório
 *    
 * By Alat
 */

# Inicia as variáveis que receberão as sessions
$idUsuario = NULL;              # Servidor logado

# Configuração
include ("../grhSistema/_config.php");

# Permissão de Acesso
$acesso = Verifica::acesso($idUsuario,2);

# Pega os parâmetros
$idVaga = get_session('idVaga');

if($acesso){
    
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
    
    $labOrigem = $pessoal->get_nomeLotacao3($vaga->get_laboratorioOrigem($idVaga));
    
    $status = $vaga->get_status($idVaga);       
    
    ######
    
    # Título & Subtitulo
    $subTitulo = "$idVaga - $centro - $cargo<br/>Origem: $labOrigem<br/>Vaga $status";
    $titulo = "Histórico de Concursos Desta Vaga";

    # Pega os dados
    $select = "SELECT concat(tbconcurso.anoBase,' - Edital: ',DATE_FORMAT(tbconcurso.dtPublicacaoEdital,'%d/%m/%Y')) as concurso,
                      concat(IFNULL(tblotacao.GER,''),' - ',IFNULL(tblotacao.nome,'')) as lotacao,
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
    $relatorio->set_label(array("Concurso","Laboratório","Área","Servidor","Obs"));
    $relatorio->set_align(array("left","left","left","left","left"));
    #$relatorio->set_funcao(array(NULL,NULL,NULL,NULL,"date_to_php"));
    #$relatorio->set_width(array(5,5,5,20,5,20,15,15,5));

    $relatorio->set_classe(array(NULL,NULL,NULL,"Vaga"));
    $relatorio->set_metodo(array(NULL,NULL,NULL,"get_Nome"));

    $relatorio->set_titulo($titulo);
    $relatorio->set_subtitulo($subTitulo);
    
    $relatorio->set_numeroOrdem(TRUE);
    $relatorio->set_numeroOrdemTipo('d');
    $relatorio->set_bordaInterna(TRUE);
    
    $relatorio->show();

    $page->terminaPagina();
}