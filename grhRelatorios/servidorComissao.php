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
    Grh::listaDadosServidorRelatorio($idServidorPesquisado, 'Histórico de Cargo em Comissão');

    br();
    $select = "SELECT idComissao,
                      idComissao,
                      idComissao,
                      idComissao
                 FROM tbcomissao
                WHERE idServidor = {$idServidorPesquisado}
             ORDER BY dtNom desc";

    $result = $pessoal->select($select);

    $relatorio = new Relatorio();
    $relatorio->set_cabecalhoRelatorio(false);
    $relatorio->set_menuRelatorio(false);
    $relatorio->set_subTotal(true);
    $relatorio->set_totalRegistro(false);
    $relatorio->set_label(array("Cargo", "Nomeação", "Exoneração","Ocupante Anterior"));
    $relatorio->set_align(array("left", "left", "left", "left"));
    $relatorio->set_width(array(40,20,20,20));
    $relatorio->set_classe(array("Cargocomissao", "Cargocomissao", "Cargocomissao","Cargocomissao"));
    $relatorio->set_metodo(array("exibeCargoCompleto", "exibeDadosNomeacao", "exibeDadosExoneracao","exibeOcupanteAnterior"));
    $relatorio->set_bordaInterna(true);
    $relatorio->set_conteudo($result);
    $relatorio->set_logServidor($idServidorPesquisado);
    $relatorio->set_logDetalhe("Visualizou o Relatório de Histórico de Cargo em Comissão");
    $relatorio->show();

    $page->terminaPagina();
}