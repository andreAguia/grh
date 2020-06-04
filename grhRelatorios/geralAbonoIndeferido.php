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
$acesso = Verifica::acesso($idUsuario, 2);

if ($acesso) {
    # Conecta ao Banco de Dados
    $pessoal = new Pessoal();

    # Começa uma nova página
    $page = new Page();
    $page->iniciaPagina();

    ######   

    br();
    $select = "SELECT idFuncional,
                      tbpessoa.nome,
                      idServidor,
                      idServidor,
                      tbabono.data,
                      tbabono.processo,
                      tbabono.dtPublicacao,
                      if(status = 1,'Deferido','Indeferido')
                 FROM tbservidor LEFT JOIN tbpessoa USING (idPessoa)
                                 JOIN tbabono USING (idServidor)
                WHERE tbabono.status = 2 AND situacao = 1
             ORDER BY 2";

    $result = $pessoal->select($select);

    $relatorio = new Relatorio();
    $relatorio->set_titulo('Relatório Geral de Servidores Ativos com Abono Permanencia Indeferido');
    $relatorio->set_subtitulo('Ordenados pelo Nome');
    $relatorio->set_label(array("IdFuncional", "Nome", "Cargo", "Lotação", "Data", "Processo", "Publicaçao", "Status"));
    #$relatorio->set_width(array(10,10,10,5,8,10,15));
    $relatorio->set_align(array("left", "left", "left", "left"));
    $relatorio->set_funcao(array(null, null, null, null, "date_to_php", null, "date_to_php"));
    $relatorio->set_classe(array(null, null, "pessoal", "pessoal"));
    $relatorio->set_metodo(array(null, null, "get_cargoRel", "get_lotacaoRel"));

    $relatorio->set_conteudo($result);
    #$relatorio->set_numGrupo(2);
    $relatorio->set_botaoVoltar(false);
    $relatorio->set_logServidor($idServidorPesquisado);
    $relatorio->set_logDetalhe("Visualizou o Relatório de Servidores com Abono Permanencia");
    $relatorio->show();

    $page->terminaPagina();
}