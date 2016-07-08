<?php
/**
 * Cadastro de Telefones e Emails
 *  
 * By Alat
 */

# Inicia as variáveis que receberão as sessions
$idUsuario = null;              # Servidor logado
$idServidorPesquisado = null;	# Servidor Editado na pesquisa do sistema do GRH

# Configuração
include ("_config.php");

# Permissão de Acesso
$acesso = Verifica::acesso($idUsuario,2);

if($acesso)
{    	
    # Conecta ao Banco de Dados   
    $pessoal = new Pessoal();
    
    # Verifica a fase do programa
    $fase = get('fase','listar');
    
    # Pega o idPessoa
    $idPessoa = $pessoal->get_idPessoa($idServidorPesquisado);

    # pega o id (se tiver)
    $id = soNumeros(get('id'));
    
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
    $objeto->set_nome('Histórico de Lotações');

    # botão de voltar da lista
    $objeto->set_voltarLista('servidorMenu.php');

    # select da lista
    $objeto->set_selectLista('SELECT tipo,
                                     numero,
                                     idContatos
                                FROM tbcontatos
                          WHERE idPessoa='.$idPessoa.'
                       ORDER BY tipo');

    # select do edita
    $objeto->set_selectEdita('SELECT tipo,
                                     numero,
                                     idPessoa
                                FROM tbcontatos
                               WHERE idContatos = '.$id);

    # ordem da lista
    #$objeto->set_orderCampo($orderCampo);
    #$objeto->set_orderTipo($orderTipo);
    #$objeto->set_orderChamador('?fase=listar');

    # botão salvar
    $objeto->set_botaoSalvarGrafico(false);

    # Caminhos
    $objeto->set_linkEditar('?fase=editar');
    $objeto->set_linkExcluir('?fase=excluir');
    $objeto->set_linkGravar('?fase=gravar');
    $objeto->set_linkListar('?fase=listar');

    # Parametros da tabela
    $objeto->set_label(array("Tipo","Número"));
    $objeto->set_width(array(40,50));	
    $objeto->set_align(array("center"));
    #$objeto->set_function(array ("date_to_php","get_nomelotacao"));

    # Classe do banco de dados
    $objeto->set_classBd('pessoal');

    # Nome da tabela
    $objeto->set_tabela('tbcontatos');

    # Nome do campo id
    $objeto->set_idCampo('idContatos');

    # Tipo de label do formulário
    $objeto->set_formLabelTipo(1);

    # Campos para o formulario
    $objeto->set_campos(array( array ('linha' => 1,
                                      'nome' => 'tipo',
                                      'label' => 'Tipo:',
                                      'autofocus' => true,
                                      'tipo' => 'combo',
                                      'array' => array("","Celular","E-mail","Residencial","Trabalho","Outros"),
                                      'title' => 'Tipo de Contato',
                                      'col' => 4,
                                      'size' => 15),
                               array ( 'nome' => 'numero',
                                       'label' => 'Valor:',
                                       'tipo' => 'texto',
                                       'size' => 80,                         
                                       'title' => 'O número ou o endereço de email',
                                       'col' => 8,
                                       'linha' => 1),
                               array ( 'nome' => 'idPessoa',
                                       'label' => 'idPessoa:',
                                       'tipo' => 'hidden',
                                       'padrao' => $idPessoa,
                                       'size' => 5,
                                       'title' => 'idPessoa',
                                       'linha' => 3)));

    # Log
    $objeto->set_idUsuario($idUsuario);
    $objeto->set_idServidorPesquisado($idServidorPesquisado);

    ################################################################

    switch ($fase)
    {
        case "" :
        case "listar" :
        case "editar" :			
        case "excluir" :	
        case "gravar" :		
            $objeto->$fase($id);             
            break;
    }
    $page->terminaPagina();
}