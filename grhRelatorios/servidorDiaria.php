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
$acesso = Verifica::acesso($idUsuario, 2);

if ($acesso) {
    # Conecta ao Banco de Dados
    $pessoal = new Pessoal();

    # Começa uma nova página
    $page = new Page();
    $page->iniciaPagina();

    ######
    # Dados do Servidor
    Grh::listaDadosServidorRelatorio($idServidorPesquisado, 'Relatório de Histórico de Diárias');

    # Pega o idPessoa
    $idPessoa = $pessoal->get_idPessoa($idServidorPesquisado);

    br();
    $select = "SELECT idDiaria,
                    CONCAT('CI Diária nº ',numeroCi,'/',YEAR(dataCi)),
                    origem,
                     destino,
                     dataSaida,
                     dataChegada,
                     valor,
                     iddiaria
                    FROM tbdiaria
                   WHERE idServidor = $idServidorPesquisado
                ORDER BY YEAR(dataCi) desc,numeroCi desc";

    $result = $pessoal->select($select);

    $relatorio = new Relatorio();
    $relatorio->set_cabecalhoRelatorio(false);
    $relatorio->set_menuRelatorio(false);
    $relatorio->set_subTotal(true);
    $relatorio->set_totalRegistro(false);
    $relatorio->set_label(array("id", "CI", "Origem", "Destino", "Saída", "Chegada", "Valor"));
    #$relatorio->set_width(array(10,80));
    $relatorio->set_align(array("center"));
    $relatorio->set_funcao(array(null, null, null, null, "date_to_php", "date_to_php", "formataMoeda"));

    $relatorio->set_conteudo($result);
    #$relatorio->set_numGrupo(2);
    $relatorio->set_botaoVoltar(false);
    $relatorio->set_logServidor($idServidorPesquisado);
    $relatorio->set_logDetalhe("Visualizou o Relatório de Histórico de Diárias");
    $relatorio->show();

    $page->terminaPagina();
}