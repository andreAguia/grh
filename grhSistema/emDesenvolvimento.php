<?php

/**
 * Em Desenvolvimento
 *  
 * By Alat
 */
# Reservado para o servidor logado
$idUsuario = null;

# Configuração
include ("_config.php");

# Começa uma nova página
$page = new Page();
$page->iniciaPagina();

# Cabeçalho da Página
AreaServidor::cabecalho();

emConstrucao("Lamentamos profundamente, mas<br/>esta rotina ainda não está pronta.</br></br>Que Tal Tomar um Café?", 3, PASTA_FIGURAS_GERAIS . 'cafe.png');