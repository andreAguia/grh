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
    Grh::listaDadosServidorRelatorio($idServidorPesquisado, 'Relatório de Dias Trabalhados e Folgas Concedidas');

    br();

    $select = "SELECT data,
                      ADDDATE(data,dias-1),  
                      dias,
                      folgas,
                      descricao,
                      documento,
                      year(data)
                 FROM tbtrabalhotre 
                WHERE idServidor = $idServidorPesquisado
             ORDER BY data desc";

    $result = $pessoal->select($select);

    $relatorio = new Relatorio();
    $relatorio->set_cabecalhoRelatorio(false);
    $relatorio->set_menuRelatorio(false);
    $relatorio->set_subTotal(true);
    $relatorio->set_label(array('Data Inicial', 'Data Final', 'Dias', 'Folgas<br/>Concedidas', 'Descriçao', 'Documentaçao','Ano'));
    $relatorio->set_align(array('center', 'center', 'center', 'center', 'left', 'left'));
    $relatorio->set_funcao(array("date_to_php", "date_to_php"));
    $relatorio->set_colunaSomatorio(3);
    $relatorio->set_conteudo($result);
    $relatorio->set_numGrupo(6);
    $relatorio->show();

    $page->terminaPagina();
}