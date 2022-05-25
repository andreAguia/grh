<?php

/**
 * Relatório
 *    
 * By Alat
 */
# Inicia as variáveis que receberão as sessions
$idUsuario = null;              # Servidor logado
$idServidorPesquisado = null; # Servidor Editado na pesquisa do sistema do GRH
# Configuração
include ("../grhSistema/_config.php");

# Permissão de Acesso
$acesso = Verifica::acesso($idUsuario, [1, 2, 12]);

if ($acesso) {
    # Conecta ao Banco de Dados
    $pessoal = new Pessoal();

    # Começa uma nova página
    $page = new Page();
    $page->iniciaPagina();

    ######
    # Dados do Servidor
    Grh::listaDadosServidorRelatorio($idServidorPesquisado, 'Histórico de Lotação');

    br();
    $select = "SELECT data,
                    lotacao,
                    motivo,
                    idHistLot
               FROM tbhistlot
         WHERE idServidor = $idServidorPesquisado
      ORDER BY data desc";

    $result = $pessoal->select($select);

    $relatorio = new Relatorio();
    $relatorio->set_cabecalhoRelatorio(false);
    $relatorio->set_menuRelatorio(false);
    $relatorio->set_subTotal(true);
    $relatorio->set_totalRegistro(false);
    $relatorio->set_label(array("Data", "Lotação", "Motivo"));
    #$relatorio->set_width(array(10,10,10,5,8,10,15));
    $relatorio->set_align(array("center", "left", "left"));
    $relatorio->set_funcao(array("date_to_php"));
    $relatorio->set_classe(array(null, "pessoal"));
    $relatorio->set_metodo(array(null, "get_nomelotacao"));

    $relatorio->set_conteudo($result);
    #$relatorio->set_numGrupo(2);
    $relatorio->set_botaoVoltar(false);
    $relatorio->set_logServidor($idServidorPesquisado);
    $relatorio->set_logDetalhe("Visualizou o Relatório de Histórico de Lotação");
    $relatorio->show();

    $page->terminaPagina();
}