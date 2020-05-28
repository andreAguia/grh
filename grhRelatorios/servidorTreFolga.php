<?php

/**
 * Relatório
 *    
 * By Alat
 */
# Inicia as variáveis que receberão as sessions
$idUsuario = NULL;              # Servidor logado
$idServidorPesquisado = NULL; # Servidor Editado na pesquisa do sistema do GRH
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
    $folgasFruidas = $pessoal->get_treFolgasFruidas($idServidorPesquisado);

    # Dados do Servidor
    Grh::listaDadosServidorRelatorio($idServidorPesquisado, 'Relatório de Folgas Fruídas do TRE');

    br();

    #####################################

    $select = "SELECT data,
                      ADDDATE(data,dias-1),
                      dias
                 FROM tbfolga
                WHERE idServidor = $idServidorPesquisado
             ORDER BY data";


    $result = $pessoal->select($select);

    $relatorio = new Relatorio();
    $relatorio->set_cabecalhoRelatorio(FALSE);
    $relatorio->set_menuRelatorio(FALSE);
    $relatorio->set_subTotal(FALSE);
    $relatorio->set_label(array('Data Inicial', 'Data Final', 'Dias'));
    $relatorio->set_align(array('center'));
    $relatorio->set_funcao(array("date_to_php", "date_to_php"));
    $relatorio->set_conteudo($result);
    $relatorio->set_colunaSomatorio(2);
    $relatorio->show();

    $page->terminaPagina();
}