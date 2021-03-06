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
    $folgasFruidas = $pessoal->get_treFolgasFruidas($idServidorPesquisado);

    # Dados do Servidor
    Grh::listaDadosServidorRelatorio($idServidorPesquisado, 'Relatório de Folgas Fruídas do TRE');

    br();

    #####################################

    $select = "SELECT data,                                    
                      dias,
                      ADDDATE(data,dias-1),
                      year(data)
                 FROM tbfolga
                WHERE idServidor = $idServidorPesquisado
             ORDER BY data";


    $result = $pessoal->select($select);

    $relatorio = new Relatorio();
    $relatorio->set_cabecalhoRelatorio(false);
    $relatorio->set_menuRelatorio(false);
    $relatorio->set_subTotal(false);
    #$relatorio->set_titulo('Relatório Mensal de Folgas Fruídas do TRE');
    #$relatorio->set_tituloLinha2($relatorioAno);
    #$relatorio->set_subtitulo('Ordenado pelo Nome do Servidor');

    $relatorio->set_label(array('Data Inicial', 'Dias', 'Data Final'));
    #$relatorio->set_width(array(10,30,20,10,10,10));
    $relatorio->set_align(array('center'));
    $relatorio->set_funcao(array("date_to_php", null, "date_to_php"));
    #$relatorio->set_classe(array(null,null,"pessoal"));
    #$relatorio->set_metodo(array(null,null,"get_lotacao"));  

    $relatorio->set_conteudo($result);
    $relatorio->set_numGrupo(3);
    $relatorio->show();

    p($folgasFruidas . " dias", "f11", "center");

    $page->terminaPagina();
}