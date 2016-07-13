<?php
/**
 * Cadastro de Parentesco
 *  
 * By Alat
 */

# Reservado para o servidor logado
$idUsuario = null;

# Configuração
include ("_config.php");

# Permissão de Acesso
$acesso = Verifica::acesso($idUsuario,2);

if($acesso)
{    
    # Conecta ao Banco de Dados
    $intra = new Intra();
    $pessoal = new Pessoal();
	
    # Verifica a fase do programa
    $fase = get('fase','listar');

    # pega o id (se tiver)
    $id = soNumeros(get('id'));    

    # Pega o parametro de pesquisa (se tiver)
    if (is_null(post('parametro')))					# Se o parametro n?o vier por post (for nulo)
        $parametro = retiraAspas(get_session('sessionParametro'));	# passa o parametro da session para a variavel parametro retirando as aspas
    else
    { 
        $parametro = post('parametro');                # Se vier por post, retira as aspas e passa para a variavel parametro
        set_session('sessionParametro',$parametro);    # transfere para a session para poder recuperá-lo depois
    }

    # Ordem da tabela
    $orderCampo = get('orderCampo');
    $orderTipo = get('orderTipo');

    # Começa uma nova página
    $page = new Page();			
    $page->iniciaPagina();

    # Cabeçalho da Página
    AreaServidor::cabecalho();

    # Abre um novo objeto Modelo
    $objeto = new Modelo();

    ################################################################

    # Nome do Modelo (aparecerá nos fildset e no caption da tabela)
    $objeto->set_nome('Parentesco');

    # Botão de voltar da lista
    $objeto->set_voltarLista('grh.php');

    # controle de pesquisa
    $objeto->set_parametroLabel('Pesquisar');
    $objeto->set_parametroValue($parametro);

    # ordenação
    if(is_null($orderCampo))
            $orderCampo = "2";

    if(is_null($orderTipo))
            $orderTipo = 'asc';

    # select da lista
    $objeto->set_selectLista ('SELECT idparentesco,parentesco,obs,
                                      idparentesco
                                 FROM tbparentesco
                                WHERE parentesco LIKE "%'.$parametro.'%"
                             ORDER BY '.$orderCampo.' '.$orderTipo);

    # select do edita
    $objeto->set_selectEdita('SELECT parentesco,
                                     obs
                                FROM tbparentesco
                               WHERE idparentesco = '.$id);

    # ordem da lista
    $objeto->set_orderCampo($orderCampo);
    $objeto->set_orderTipo($orderTipo);
    $objeto->set_orderChamador('?fase=listar');

    # Caminhos
    $objeto->set_linkEditar('?fase=editar');
    $objeto->set_linkExcluir('?fase=excluir');
    $objeto->set_linkGravar('?fase=gravar');
    $objeto->set_linkListar('?fase=listar');

    # Parametros da tabela
    $objeto->set_label(array("id","Parentesco","Obs"));
    $objeto->set_width(array(5,40,45));
    $objeto->set_align(array("center","center","left"));

    # Classe do banco de dados
    $objeto->set_classBd('Pessoal');

    # Nome da tabela
    $objeto->set_tabela('tbparentesco');

    # Nome do campo id
    $objeto->set_idCampo('idparentesco');

    # Tipo de label do formulário
    $objeto->set_formlabelTipo(1);

    # Campos para o formulario
    $objeto->set_campos(array(
        array ('linha' => 1,
               'nome' => 'parentesco',
               'label' => 'Parentesco:',
               'tipo' => 'texto',
               'required' => true,
               'autofocus' => true,
               'size' => 45),
        array ('linha' => 2,
               'nome' => 'obs',
               'label' => 'Observação:',
               'tipo' => 'textarea',
               'size' => array(80,5))));

    # Matrícula para o Log
    $objeto->set_idUsuario($idUsuario);

    ################################################################
    switch ($fase)
    {
        case "" :
        case "listar" :
            $objeto->listar();
            break;

        case "editar" :	
        case "excluir" :	
        case "gravar" :
            $objeto->$fase($id);
            break;
    }									 	 		

    $page->terminaPagina();
}
?>