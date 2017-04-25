<?php
/**
 * Dados do servidor cedido
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
    $objeto->set_nome('Dados do Servidor Cedido');

    # select do edita
    $objeto->set_selectEdita('SELECT orgaoOrigem,
                                     matExterna,
                                     matsare,
                                     onus,
                                     salario,
                                     processo,
                                     dtPublicacao,
                                     pgPublicacao,
                                     obs,
                                     idServidor
                                FROM tbcedido
                               WHERE idServidor = '.$idServidorPesquisado);


    # Caminhos
    $objeto->set_linkGravar('?fase=gravar');
    #$objeto->set_linkListar('?');
    $objeto->set_linkListar('servidorMenu.php');

    # botão salvar
    $objeto->set_botaoSalvarGrafico(FALSE);

    # botão voltar
    $objeto->set_voltarForm('servidorMenu.php');

    # retira o botão incluir
    $objeto->set_botaoIncluir(FALSE);

    # Classe do banco de dados
    $objeto->set_classBd('Pessoal');

    # Nome da tabela
    $objeto->set_tabela('tbcedido');

    # Nome do campo id
    $objeto->set_idCampo('idServidor');

    # Tipo de label do formulário
    $objeto->set_formlabelTipo(1);

    # Campos para o formulario
    $objeto->set_campos(array(
                        array ('linha' => 1,
                               'nome' => 'orgaoOrigem',
                               'label' => 'Órgão de Origem:',
                               'tipo' => 'texto',
                               'required' => TRUE,
                               'autofocus' => TRUE,
                               'title' => 'Órgão de Origem do servidor cedido',
                               'col' => 6,
                               'size' => 50),
                        array ('linha' => 1,
                               'nome' => 'matExterna',
                               'label' => 'Matrícula do Órgão de Origem:',
                               'tipo' => 'texto',
                               'title' => 'Matrícula do Órgão de Origem',
                               'col' => 3,
                               'size' => 20),
                        array ('linha' => 1,
                               'nome' => 'matsare',
                               'label' => 'Matrícula da Sare:',
                               'tipo' => 'texto',
                               'title' => 'Matrícula da Sare',
                               'col' => 3,
                               'size' => 25),
                        array ('linha' => 2,
                               'nome' => 'onus',
                               'label' => 'Cedido com ônus para a FENORTE?:',
                               'tipo' => 'combo',
                               'array' => array("Sim","Não"),
                               'size' => 20,
                               'col' => 4,
                               'title' => 'Cedido com ônus para a UENF?'),
                        array ('linha' => 2,
                               'nome' => 'salario',
                               'label' => 'Valor recebido pelo órgão de origem:',
                               'tipo' => 'moeda',
                               'col' => 4,
                               'title' => 'Valor recebido pelo órgão de origem',                           
                               'size' => 10),
                       array ( 'nome' => 'processo',
                               'label' => 'Processo:',
                               'tipo' => 'texto',
                               'size' => 30,
                               'col' => 4,
                               'title' => 'Número do Processo',
                               'linha' => 2), 
                       array ( 'nome' => 'dtPublicacao',
                               'label' => 'Data da Pub. no DOERJ:',
                               'tipo' => 'data',
                               'size' => 20,
                               'col' => 3,
                               'title' => 'Data da Publicação no DOERJ.',
                               'linha' => 3),
                       array ( 'nome' => 'pgPublicacao',
                               'label' => 'Pág:',
                               'tipo' => 'texto',
                               'col' => 3,
                               'size' => 5,                         
                               'title' => 'A Página do DOERJ',
                               'linha' => 3),
                        array ('linha' => 4,
                               'nome' => 'obs',
                               'col' => 12,
                               'label' => 'Observação:',
                               'tipo' => 'textarea',
                               'size' => array(80,5)),
                       array ( 'nome' => 'idServidor',
                               'label' => 'idServidor:',
                               'tipo' => 'hidden',
                               'padrao' => $idServidorPesquisado,
                               'size' => 5,
                               'title' => 'Matrícula',
                               'linha' => 8)));


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