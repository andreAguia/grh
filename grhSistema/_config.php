<?php

/**
 * Configuração do Sistema de Pessoal
 * 
 * By Alat
 */

/*
 *  Iniciando a sessão
 */

if (session_status() !== PHP_SESSION_ACTIVE) {
    session_start();
    setcookie(session_name(),session_id(),time()+800);
}

/*
 *  Classes
 */
define("PASTA_CLASSES_GERAIS", "../../_framework/_classesGerais/");  # Classes Gerais
define("PASTA_CLASSES_ADMIN", "../../areaServidor/_classes/");       # Classes do sistema de Administração 
define("PASTA_CLASSES", "../_classes/");                              # Classes Específicas

/*
 *  Funções
 */
define("PASTA_FUNCOES_GERAIS", "../../_framework/_funcoesGerais/");  # Funções Gerais
define("PASTA_FUNCOES", "../_funcoes/");                    # Funções Específicas GRH

/*
 *  Figuras
 */
define("PASTA_FIGURAS_GERAIS", "../../_framework/_imgGerais/");      # Figuras Gerais
define("PASTA_FIGURAS", "../_img/");                               # Figuras Específicas

/*
 *  Estilos
 */
define("PASTA_ESTILOS_GERAIS", "../../_framework/_cssGerais/");      # Estilos Gerais (Foundation)
define("PASTA_ESTILOS", "../_css/");                                  # Estilos Específicos

/*
 *  Arquivos
 */
define("PASTA_FOTOS", "../../_arquivos/fotos/");                     # Fotos dos Servidores
define("PASTA_MCF", "../../_arquivos/mcf/");                         # MCF dos Servidores
define("PASTA_CONCURSO", "../../_arquivos/concurso/");               # Publicações de concurso
define("PASTA_FUNCIONAL", "../../_arquivos/pastaFuncional/");                     # Fotos dos Servidores

/*
 *  Tags aceitas em campos com htmlTag = true
 */
define('TAGS', '<p></p><a></a><br/><br><div></div><table></table><tr></tr><td></td><th></th><strong></strong>'
        . '<em></em><u></u><sub></sub><sup></sup><ol></ol><li></li><ul></ul><hr><span></span><h1></h1>'
        . '<h2></h2><h3></h3><h4></h4><h5></h5><pre></pre>');

/*
 *  Cria array dos meses
 */
$mes = array(array("1", "Janeiro"),
    array("2", "Fevereiro"),
    array("3", "Março"),
    array("4", "Abril"),
    array("5", "Maio"),
    array("6", "Junho"),
    array("7", "Julho"),
    array("8", "Agosto"),
    array("9", "Setembro"),
    array("10", "Outubro"),
    array("11", "Novembro"),
    array("12", "Dezembro"));

$nomeMes = array(null,
    "Janeiro",
    "Fevereiro",
    "Março",
    "Abril",
    "Maio",
    "Junho",
    "Julho",
    "Agosto",
    "Setembro",
    "Outubro",
    "Novembro",
    "Dezembro");

/*
 *  Funções
 */
include_once (PASTA_FUNCOES_GERAIS . "funcoes.gerais.php");
include_once (PASTA_FUNCOES . "funcoes.especificas.php");

# Dados do Browser
$browser = get_BrowserName();

# Nome do browser
define("BROWSER_NAME", $browser['browser']);

# Versão do browser
define("BROWSER_VERSION", $browser['version']);

# Pega o ip da máquina
define("IP", getenv("REMOTE_ADDR"));

# Sistema Operacional
define("SO", get_So());

# Carrega as session do servidor logado
$idUsuario = get_session('idUsuario');

# Carregaa session do servidor editado na pesquisa do sistema do GRH
$idServidorPesquisado = get_session('idServidorPesquisado');

# Define o horário
date_default_timezone_set("America/Sao_Paulo");
setlocale(LC_ALL, 'pt_BR');
setlocale(LC_CTYPE, 'pt_BR');

/**
 * Função que é chamada automaticamente pelo sistema
 * para carregar na memória uma classe no exato momento
 * que a classe é instanciada.
 * 
 * @param  $classe = a classe instanciada
 */
function autoload($classe) {
    # Array com as pastas existentes
    $pastasClasses = [PASTA_CLASSES_GERAIS, PASTA_CLASSES, PASTA_CLASSES_ADMIN];
    $categoriasClasses = ["class", "interface", "container", "html", "outros", "rel", "bd", "documento", "w3"];

    # Percorre as pastas
    foreach ($pastasClasses as $pasta) {
        # Percorre as categorias
        foreach ($categoriasClasses as $categoria) {
            if (file_exists($pasta . $categoria . ".{$classe}.php")) {
                include_once $pasta . $categoria . ".{$classe}.php";
            }
        }
    }
}

spl_autoload_register("autoload");

/*
 *  Sobre o Sistema
 */
$intra = new Intra();
define("SISTEMA", $intra->get_variavel("sistemaGrh"));             # Nome do sistema
define("DESCRICAO", $intra->get_variavel("sistemaGrhDescricao"));  # Descrição do sistema
define("AUTOR", $intra->get_variavel("sistemaAutor"));             # Autor do sistema
define("EMAILAUTOR", $intra->get_variavel("sistemaAutorEmail"));   # Autor do sistema

/*
 *  Versão do sistema
 */
$versao = $intra->get_versaoAtual();
define("VERSAO", $versao[0]);                    # Versão do Sistema 								
define("ATUALIZACAO", date_to_php($versao[1]));  # Última Atualização

#echo "As sessões em cache irão expirar depois de " . ($cache_expire / 60) . " horas";
