<?php
/**
 * Informação da çocalização f~isica da pasta funcional
 *  
 * By Alat
 */

# Inicia as variáveis que receberão as sessions
$idUsuario = NULL;              # Servidor logado
$idServidorPesquisado = NULL;	# Servidor Editado na pesquisa do sistema do GRH

# Configuração
include ("_config.php");

# Permissão de Acesso
$acesso = Verifica::acesso($idUsuario,2);

if($acesso)
{    
    # Conecta ao Banco de Dados
    $pessoal = new Pessoal();
	
    # Verifica a fase do programa
    $fase = get('fase','editar');

    # Começa uma nova página
    $page = new Page();			
    $page->iniciaPagina();

    # Cabeçalho da Página
    AreaServidor::cabecalho();

    # Abre um novo objeto Modelo
    $objeto = new Modelo();

    ################################################################

    # Exibe os dados do Servidor
    $objeto->set_rotinaExtra("get_DadosServidor");
    $objeto->set_rotinaExtraParametro($idServidorPesquisado);

    # Nome do Modelo (aparecerá nos fildset e no caption da tabela)
    $objeto->set_nome('Arquivo e Gaveta onde está fisicamente a pasta do servidor no GRH');

    # select do edita
    $objeto->set_selectEdita('SELECT arquivo,
                                     gaveta
                                FROM tbservidor
                               WHERE idServidor = '.$idServidorPesquisado);


    # Caminhos
    $objeto->set_linkGravar('?fase=gravar');
    #$objeto->set_linkListar('?');
    $objeto->set_linkListar('servidorMenu.php');

    # botão voltar
    $objeto->set_voltarForm('servidorMenu.php');

    # retira o botão incluir
    $objeto->set_botaoIncluir(FALSE);

    # Classe do banco de dados
    $objeto->set_classBd('Pessoal');

    # Nome da tabela
    $objeto->set_tabela('tbservidor');

    # Nome do campo id
    $objeto->set_idCampo('idServidor');

    # Tipo de label do formulário
    $objeto->set_formlabelTipo(1);

    # Campos para o formulario
    $objeto->set_campos(array(
                        array ('linha' => 1,
                               'nome' => 'arquivo',
                               'label' => 'Arquivo:',
                               'tipo' => 'texto',
                               'title' => 'Arquivo',
                               'col' => 1,
                               'size' => 15),
                       array ( 'linha' => 1,
                               'nome' => 'gaveta',
                               'label' => 'Gaveta:',
                               'tipo' => 'combo',
                               'array' => array(NULL,"A","B","C","D"),
                               'size' => 5,
                               'col' => 1)));

    # Log
    $objeto->set_idUsuario($idUsuario);
    $objeto->set_idServidorPesquisado($idServidorPesquisado);

    ################################################################
    switch ($fase)
    {
        case "editar" :            
        case "excluir" :	
        case "gravar" :
            $objeto->$fase($idServidorPesquisado);
            break;	
    }									 	 		

    $page->terminaPagina();
}else{
    loadPage("../../areaServidor/sistema/login.php");
}