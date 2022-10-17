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
    
    # Grava o log
    $Objetolog = new Intra();
    $idUsuario = get_session('idUsuario');
    $data = date("Y-m-d H:i:s");
    $atividade = "Visualizou o Relatório de Folgas Fruídas do TRE Geral";
    $Objetolog->registraLog($idUsuario, $data, $atividade, null, null, 4, $idServidorPesquisado);

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
    $relatorio->set_cabecalhoRelatorio(false);
    $relatorio->set_menuRelatorio(false);
    $relatorio->set_subTotal(false);
    $relatorio->set_label(['Data Inicial', 'Data Final', 'Dias']);
    $relatorio->set_align(['center']);
    $relatorio->set_funcao(["date_to_php", "date_to_php"]);
    $relatorio->set_conteudo($result);
    $relatorio->set_colunaSomatorio(2);
    $relatorio->set_log(false);
    $relatorio->show();

    $page->terminaPagina();
}