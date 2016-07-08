<?php
/**
 * Cadastro de Elogios e Advertências do Servidor
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
    # Verifica a fase do programa
    $fase = get('fase','listar');

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
    $objeto->set_nome('Cadastro de Elogios e Advertências do Servidor');

    # botão de voltar da lista
    $objeto->set_voltarLista('servidorMenu.php');

    # select da lista
    $objeto->set_selectLista('SELECT data,
                                     CASE tipo
                                        WHEN "1" THEN "Elogio"
                                        WHEN "2" THEN "Advertência"
                                     end,                                 
                                     descricao,                           
                                     idElogio
                                FROM tbelogio
                          WHERE idServidor='.$idServidorPesquisado.'
                       ORDER BY data desc');

    # select do edita
    $objeto->set_selectEdita('SELECT data,
                                     tipo,
                                     descricao,
                                     idServidor
                                FROM tbelogio
                               WHERE idElogio = '.$id);

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
    $objeto->set_label(array("Data","Tipo","Descrição"));
    $objeto->set_width(array(10,10,70));	
    $objeto->set_align(array("center","center","left"));
    $objeto->set_function(array ("date_to_php"));

    # Classe do banco de dados
    $objeto->set_classBd('pessoal');

    # Nome da tabela
    $objeto->set_tabela('tbelogio');

    # Nome do campo id
    $objeto->set_idCampo('idElogio');

    # Tipo de label do formulário
    $objeto->set_formLabelTipo(1);

    # Campos para o formulario
    $objeto->set_campos(array( array ( 'nome' => 'data',
                                       'label' => 'Data:',
                                       'tipo' => 'data',
                                       'size' => 20,
                                       'maxLength' => 20,                                   
                                       'required' => true,
                                       'autofocus' => true,
                                       'col' => 3,
                                       'title' => 'Data do Elogio/Advertência.',
                                       'linha' => 1),
                               array ( 'nome' => 'tipo',
                                       'label' => 'Tipo:',
                                       'tipo' => 'combo',
                                       'required' => true,
                                       'array' => array(array(1,'Elogio'),array(2,'Advertência')),
                                       'size' => 20,
                                       'title' => 'Qual o tipo de ocorrência',
                                       'col' => 4,
                                       'linha' => 1),        
                               array ( 'nome' => 'descricao',
                                       'label' => 'Descrição:',
                                       'tipo' => 'textarea',
                                       'size' => array(80,5),
                                       'col' => 12,
                                       'required' => true,
                                       'title' => 'Descrição do Elogio ou Advertência.',
                                       'linha' => 2),
                               array ( 'nome' => 'idServidor',
                                       'label' => 'Matrícula:',
                                       'tipo' => 'hidden',
                                       'padrao' => $idServidorPesquisado,
                                       'size' => 5,
                                       'title' => 'Matrícula',
                                       'linha' => 4)));

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
            $objeto->$fase($id);  
            break;

        case "gravar" :
            $objeto->gravar($id,'servidorElogiosAdvertenciasExtra.php'); 	
            break;
    }									 	 		

    $page->terminaPagina();
}
?>
