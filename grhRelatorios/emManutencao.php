<?php

/**
 * Sistema GRH
 * 
 * Capa da Pasta do Servidor
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

    # Menu do Relatório
    $menuRelatorio = new menuRelatorio();
    $menuRelatorio->show();

    # Cabeçalho
    $cabecalho = new Relatorio();
    $cabecalho->exibeCabecalho();

    br(4);

    p("Relatório em Manutenção!", "center", "f20");
    echo "<div style='page-break-before:always;'>&nbsp</div>";
    $page->terminaPagina();
}