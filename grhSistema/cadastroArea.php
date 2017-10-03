<?php
/**
 * Cadastro de Área
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

    # pega o id (se tiver)
    $id = soNumeros(get('id'));
    
    # Verifica a paginacão
    #$paginacao = get('paginacao',get_session('sessionPaginacao',0));	// Verifica se a paginação vem por get, senão pega a session
    #set_session('sessionPaginacao',$paginacao);                         // Grava a paginação na session
    
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
    $objeto->set_nome('Área');

    # botão de voltar da lista
    $objeto->set_voltarLista('cadastroCargo.php');

    # controle de pesquisa
    $objeto->set_parametroLabel('Pesquisar');
    $objeto->set_parametroValue($parametro);

    # ordenação
    if(is_null($orderCampo))
            $orderCampo = "1";

    if(is_null($orderTipo))
            $orderTipo = 'asc';
        
    # select da lista
    $select = "SELECT idarea,
                        tbtipocargo.cargo,
                        area,
                        idarea
                   FROM tbarea LEFT JOIN tbtipocargo USING (idTipoCargo)
                  WHERE area LIKE '%$parametro%'
               ORDER BY $orderCampo $orderTipo";
    
    $objeto->set_selectLista($select);

    # select do edita
    $objeto->set_selectEdita('SELECT area,
                                     idTipoCargo,
                                     descricao,
                                     obs
                                FROM tbarea
                               WHERE idarea = '.$id);

    # ordem da lista
    $objeto->set_orderCampo($orderCampo);
    $objeto->set_orderTipo($orderTipo);
    $objeto->set_orderChamador('?fase=listar');

    # Caminhos
    $objeto->set_linkEditar('?fase=editar');
    #$objeto->set_linkExcluir('?fase=excluir');
    $objeto->set_linkGravar('?fase=gravar');
    $objeto->set_linkListar('?fase=listar');

    # Parametros da tabela
    $objeto->set_label(array("id","Cargo","Area","Servidores Ativos"));
    $objeto->set_width(array(5,20,60,10));
    $objeto->set_align(array("center","center","left","center"));

    $objeto->set_classe(array(NULL,NULL,NULL,"Pessoal"));
    $objeto->set_metodo(array(NULL,NULL,NULL,"get_servidoresArea"));

    # Botão de exibição dos servidores
    $botao = new BotaoGrafico();
    $botao->set_label('');    
    $botao->set_url('?fase=listaServidores&id=');    
    $botao->set_image(PASTA_FIGURAS_GERAIS.'ver.png',20,20);

    # Coloca o objeto link na tabela			
    $objeto->set_link(array("","","","","","","","",$botao));

    # Classe do banco de dados
    $objeto->set_classBd('Pessoal');

    # Nome da tabela
    $objeto->set_tabela('tbarea');

    # Nome do campo id
    $objeto->set_idCampo('idarea');

    # Tipo de label do formulário
    $objeto->set_formlabelTipo(1);
    
    # Pega os dados da combo de Tipos de Cargos
    $result2 = $pessoal->select('SELECT idTipoCargo, 
                                      cargo
                                  FROM tbtipocargo
                              ORDER BY idTipoCargo desc');
    array_unshift($result2, array(NULL,NULL));

    # Campos para o formulario
    $objeto->set_campos(array(
        array  ('linha' => 1,
               'col' => 6,
               'nome' => 'area',
               'label' => 'Área:',
               'tipo' => 'texto',
               'autofocus' => TRUE,
               'required' => TRUE,
               'size' => 50),
        array('linha' => 1,
               'col' => 6,
               'nome' => 'idTipoCargo',
               'label' => 'Cargo:',
               'tipo' => 'combo',               
               'required' => TRUE,
               'array' => $result2,
               'size' => 30),
        array ('linha' => 2,
               'col' => 6,
               'nome' => 'descricao',
               'label' => 'Descrição:',
               'tipo' => 'textarea',
               'size' => array(40,15)),
        array ('linha' => 2,
               'col' => 6,
               'nome' => 'obs',
               'label' => 'Observação:',
               'tipo' => 'textarea',
               'size' => array(40,15))));

    # Matrícula para o Log
    $objeto->set_idUsuario($idUsuario);
    
    # Paginação
    #$objeto->set_paginacao(TRUE);
    #$objeto->set_paginacaoInicial($paginacao);

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