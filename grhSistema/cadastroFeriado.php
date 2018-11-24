<?php
/**
 * Cadastro de Feriados
 *  
 * By Alat
 */

# Reservado para o servidor logado
$idUsuario = NULL;

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
    
    # Verifica se veio menu grh e registra o acesso no log
    $origem = get('origem',FALSE);
    if($origem){
        # Grava no log a atividade
        $atividade = "Visualizou o cadastro de feriados";
        $data = date("Y-m-d H:i:s");
        $intra->registraLog($idUsuario,$data,$atividade,NULL,NULL,7);
    }

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
    $objeto->set_nome('Feriados');

    # Botão de voltar da lista
    $objeto->set_voltarLista('grh.php');

    # controle de pesquisa
    $objeto->set_parametroLabel('Pesquisar');
    $objeto->set_parametroValue($parametro);

    # ordenaç?o
    if (is_null($orderCampo)) {
        $orderCampo = "1";
    }

    if (is_null($orderTipo)) {
        $orderTipo = 'desc';
    }

    # select da lista
    $objeto->set_selectLista ('SELECT data,
                                      descricao,
                                      tipo,
                                      idferiado
                                 FROM tbferiado
                                WHERE descricao LIKE "%'.$parametro.'%"
                             ORDER BY '.$orderCampo.' '.$orderTipo);

    # select do edita
    $objeto->set_selectEdita('SELECT data,
                                     descricao,
                                     tipo
                                FROM tbferiado
                               WHERE idferiado = '.$id);

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
    $objeto->set_label(array("Data","Descrição","Tipo"));
    $objeto->set_width(array(10,60,20));
    $objeto->set_align(array("center","left","center"));
    $objeto->set_funcao(array("date_to_php"));

    # Classe do banco de dados
    $objeto->set_classBd('Pessoal');

    # Nome da tabela
    $objeto->set_tabela('tbferiado');

    # Nome do campo id
    $objeto->set_idCampo('idferiado');

    # Tipo de label do formulário
    $objeto->set_formlabelTipo(1);

    # Campos para o formulario
   $objeto->set_campos(array(
       array ( 'nome' => 'data',
               'label' => 'Data:',
               'tipo' => 'date',
               'size' => 20,
               'required' => TRUE,
               'autofocus' => TRUE,
               'title' => 'Data do feriado.',
               'col' => 3,
               'linha' => 1),
        array ('linha' => 1,
               'nome' => 'descricao',
               'label' => 'Descrição:',
               'tipo' => 'texto',
               'required' => TRUE,
               'col' => 6,
               'size' => 50),
        array ('linha' => 1,
               'nome' => 'tipo',
               'label' => 'Tipo:',
               'tipo' => 'combo',
               'array' => array("anual","data única"),
               'col' => 3,
               'size' => 30)));

    # idUsuário para o Log
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
}else{
    loadPage("../../areaServidor/sistema/login.php");
}