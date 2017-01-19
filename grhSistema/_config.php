<?php
/**
 * Configuração do Sistema de Pessoal
 * 
 * By Alat
 */

# Sobre o Sistema
define("VERSAO","1.0.02 UENF");                                # Versão do Sistema 								
define("ATUALIZACAO","19/01/2017");                            # Última Atualização
define("SISTEMA","Sistema de Gestão de Pessoas");              # Nome do sistema
define("DESCRICAO","Sistema da Gerência de Recursos Humanos"); # Descrição do sistema
define("PALAVRAS_CHAVE","Uenf,GRH");                           # Palavras chave para sites de busca
define("AUTOR","André Luis Águia Tavares");                    # Autor do sistema
define("EMAILAUTOR","alat@uenf.br");                           # Email do Autor do sistema
define("SETOR","GRH - Gerência de Recursos Humanos");          # Setor

# Classes
define("PASTA_CLASSES_GERAIS","../../_framework/_classesGerais/");  # Classes Gerais
define("PASTA_CLASSES_ADMIN","../../areaServidor/_classes/");       # Classes do sistema de Administração 
define("PASTA_CLASSES","../_classes/");                             # Classes Específicas

# Funções
define("PASTA_FUNCOES_GERAIS","../../_framework/_funcoesGerais/"); # Funções Gerais
define("PASTA_FUNCOES","../_funcoes/");                 # Funções Específicas

# Figuras
define("PASTA_FIGURAS_GERAIS","../../_framework/_imgGerais/");     # Figuras Gerais
define("PASTA_FIGURAS","../_img/");                     # Figuras Específicas

# Estilos
define("PASTA_ESTILOS_GERAIS","../../_framework/_cssGerais/");     # Estilos Gerais (Foundation)
define("PASTA_ESTILOS","../_css/");                     # Estilos Específicos

# Fontes para PDF
define('FPDF_FONTPATH','../../_framework/_pdfFont/');

# Tags aceitas em campos com htmlTag = true
define('TAGS','<p></p><a></a><br/><br><div></div><table></table><tr></tr><td></td><th></th><strong></strong><em></em><u></u><sub></sub><sup></sup><ol></ol><li></li><ul></ul><hr><span></span><h3></h3>');       

# Cria array dos meses
$mes = array(array("1","Janeiro"),
             array("2","Fevereiro"),
             array("3","Março"),
             array("4","Abril"),
             array("5","Maio"),
             array("6","Junho"),
             array("7","Julho"),
             array("8","Agosto"),
             array("9","Setembro"),
             array("10","Outubro"),
             array("11","Novembro"),
             array("12","Dezembro"));

# Inicia a Session
session_start();
session_cache_limiter('private'); 

# Funçõess gerais	
include_once (PASTA_FUNCOES_GERAIS."funcoes.gerais.php");
include_once (PASTA_FUNCOES."funcoes.especificas.php");

# Framework gráfico 
#include ('../../_framework/_outros/libchart/classes/libchart.php');

# Dados do Browser
$browser = get_BrowserName();
define("BROWSER_NAME",$browser['browser']);	# Nome do browser
define("BROWSER_VERSION",$browser['version']);	# Versão do browser

# Pega o ip da máquina
define("IP",getenv("REMOTE_ADDR"));     # Ip da máquina

# Sistema Operacional
define("SO",get_So());

# Programa Chamador
$arquivo = explode("/",$_SERVER['PHP_SELF']);
$arquivo = end($arquivo);
define("CHAMADOR",$arquivo);

setlocale (LC_ALL, 'pt_BR');
setlocale (LC_CTYPE, 'pt_BR');

# Servidor logado
if((CHAMADOR == 'areaServidor.php') OR (CHAMADOR == 'grh.php'))
{    
    set_session('sessionParametro');	# Zera a session do parâmetro de pesquisa da classe modelo
    set_session('sessionPaginacao');	# Zera a session de pagina��o da classe modelo1
    set_session('sessionLicenca');      # Zera a session do tipo de licen�a
    set_session('sessionDiariaMes');    # Zera a session do Pesquisa M�s Cadastro de di�rias daa �rea do Servidor
    set_session('sessionDiariaAno');    # Zera a session do Pesquisa Ano Cadastro de di�rias daa �rea do Servidor
    set_session('matriculaGrh');        # Zera a session da pesquisa do sistema grh
    
    # Zera os parâmetros da pesquisa da classe listaServidores
    set_session('parametroNomeMat');
    set_session('parametroCargo');
    set_session('parametroCargoComissao');
    set_session('parametroLotacao');
    set_session('parametroPerfil');
    set_session('parametroSituacao');
    set_session('parametroPaginacao');    
    set_session('sessionSelect');                      // Select para gerar relatório
    set_session('sessionTítulo');                      // Título do relatório
    set_session('sessionSubTítulo');                   // SubTítulo do relatório
}

# carrega as session
$idUsuario = get_session('idUsuario');                       # Servidor Logado
$idServidorPesquisado = get_session('idServidorPesquisado'); # Servidor Editado na pesquisa do sistema do GRH	

# Define se usa o input type data do html5 ou se usa o javascript
# Se usar o html 5 o controle não trabalha com formato brasileiro
# mas browsers exibem no format brasileiro ao 'perceber' o idioma do usuário
if(BROWSER_NAME == 'CHROME'){
    define('HTML5',true);
}else{
    define('HTML5',false);
}

/**
 * Função que é chamada automaticamente pelo sistema
 * para carregar na memória uma classe no exato momento
 * que a classe é instanciada.
 * 
 * @param  $classe = a classe instanciada
 */

function __autoload($classe)
{
    # Verifica se existe essa classe nas classes gerais
    if (file_exists(PASTA_CLASSES_GERAIS."/class.{$classe}.php")){
        include_once PASTA_CLASSES_GERAIS."/class.{$classe}.php";
    }
        
    if (file_exists(PASTA_CLASSES_GERAIS."/interface.{$classe}.php")){
        include_once PASTA_CLASSES_GERAIS."/interface.{$classe}.php";
    }
        
    if (file_exists(PASTA_CLASSES_GERAIS."/container.{$classe}.php")){
        include_once PASTA_CLASSES_GERAIS."/container.{$classe}.php"; 
    }
        
    if (file_exists(PASTA_CLASSES_GERAIS."/html.{$classe}.php")){
        include_once PASTA_CLASSES_GERAIS."/html.{$classe}.php";
    }
        
    if (file_exists(PASTA_CLASSES_GERAIS."/outros.{$classe}.php")){
        include_once PASTA_CLASSES_GERAIS."/outros.{$classe}.php"; 
    }

    if (file_exists(PASTA_CLASSES_GERAIS."/rel.{$classe}.php")){
        include_once PASTA_CLASSES_GERAIS."/rel.{$classe}.php"; 
    }
    
    if (file_exists(PASTA_CLASSES_GERAIS."/bd.{$classe}.php"))
        include_once PASTA_CLASSES_GERAIS."/bd.{$classe}.php";   

    # Verifica se existe a classe nas classes específicas
    if (file_exists(PASTA_CLASSES."/class.{$classe}.php")){
        include_once PASTA_CLASSES."/class.{$classe}.php";
    }
        
    if (file_exists(PASTA_CLASSES."/interface.{$classe}.php")){
        include_once PASTA_CLASSES."/interface.{$classe}.php";
    }
        
    # Verifica se existe a classe nas classes do sistema de Administração
    if (file_exists(PASTA_CLASSES_ADMIN."/class.{$classe}.php")){
        include_once PASTA_CLASSES_ADMIN."/class.{$classe}.php";
    }
        
    if (file_exists(PASTA_CLASSES_ADMIN."/interface.{$classe}.php")){
        include_once PASTA_CLASSES_ADMIN."/interface.{$classe}.php";
    }
}