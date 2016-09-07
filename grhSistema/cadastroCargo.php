<?php
/**
 * Cadastro de Cargos
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
    
    # Verifica a paginacão
    $paginacao = get('paginacao',get_session('sessionPaginacao',0));	// Verifica se a paginação vem por get, senão pega a session
    set_session('sessionPaginacao',$paginacao);                         // Grava a paginação na session
    
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
    $objeto->set_nome('Cargos');	

    # botão salvar
    $objeto->set_botaoSalvarGrafico(false);

    # bot?o de voltar da lista
    $objeto->set_voltarLista('grh.php');

    # controle de pesquisa
    $objeto->set_parametroLabel('Pesquisar');
    $objeto->set_parametroValue($parametro);

    # ordenação
    if(is_null($orderCampo))
            $orderCampo = " 2 asc, 3 asc, 4 asc, 5 asc, 6";

    if(is_null($orderTipo))
            $orderTipo = 'asc';

    # select da lista
    $objeto->set_selectLista ('SELECT idCargo,
                                      grupo,
                                      tbtipocargo.cargo,
                                      classe,
                                      area,
                                      nome,
                                      tbplano.numDecreto,                                  
                                      idCargo,
                                      idCargo,
                                      idCargo
                                 FROM tbcargo LEFT JOIN tbplano USING (idPlano)
                                              LEFT JOIN tbtipocargo USING (idTipoCargo)
                                WHERE nome LIKE "%'.$parametro.'%"
                                   OR idCargo LIKE "%'.$parametro.'%" 
                                   OR grupo LIKE "%'.$parametro.'%" 
                                   OR tbtipocargo.cargo LIKE "%'.$parametro.'%"
                                   OR classe LIKE "%'.$parametro.'%"
                                   OR area LIKE "%'.$parametro.'%"
                             ORDER BY '.$orderCampo.' '.$orderTipo);

    # select do edita
    $objeto->set_selectEdita('SELECT grupo,
                                     idtipocargo,
                                     classe,
                                     area,
                                     nome,
                                     idPlano,
                                     atribuicoes,
                                     obs
                                FROM tbcargo
                               WHERE idCargo = '.$id);

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
    $objeto->set_label(array("id","Grupo","Cargo","Classe","Área","Função","Plano de Cargos","Servidores","Ver"));
    $objeto->set_width(array(5,10,15,15,15,20,10,5,5));
    $objeto->set_align(array("center","center","center","center","center","left"));

    $objeto->set_classe(array(null,null,null,null,null,null,null,"Pessoal"));
    $objeto->set_metodo(array(null,null,null,null,null,null,null,"get_servidoresCargo"));

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
    $objeto->set_tabela('tbcargo');

    # Nome do campo id
    $objeto->set_idCampo('idCargo');

    # Tipo de label do formulário
    $objeto->set_formlabelTipo(1);

    # Pega os dados da combo de Plano e Cargos
    $result1 = $pessoal->select('SELECT idPlano, 
                                      numDecreto
                                  FROM tbplano
                              ORDER BY numDecreto');
    
    # Pega os dados da combo de Tipos de Cargos
    $result2 = $pessoal->select('SELECT idTipoCargo, 
                                      cargo
                                  FROM tbtipocargo
                              ORDER BY idTipoCargo desc');
    array_push($result2, array(null,null));

    # Campos para o formulario
    $objeto->set_campos(array(
        array ('linha' => 1,
               'col' => 3,
               'nome' => 'grupo',
               'label' => 'Grupo:',
               'autofocus' => true,
               'tipo' => 'combo',
               'required' => true,
               'array' => array(NULL,"Nível Elementar","Nível Fundamental","Nível Médio","Nível Superior","Doutorado",),              
               'required' => true,
               'size' => 30),
         array('linha' => 1,
               'col' => 5,
               'nome' => 'idtipocargo',
               'label' => 'Cargo:',
               'tipo' => 'combo',               
               'required' => true,
               'array' => $result2,
               'size' => 30),
        array ('linha' => 1,
               'col' => 4,
               'nome' => 'classe',
               'label' => 'Classe:',
               'tipo' => 'texto',               
               'required' => true,
               'size' => 50),
        array ('linha' => 2,
               'col' => 4,
               'nome' => 'area',
               'label' => 'Área:',
               'tipo' => 'texto',               
               'required' => true,
               'size' => 50),
        array ('linha' => 2,
               'col' => 5,
               'nome' => 'nome',
               'label' => 'Função:',
               'tipo' => 'texto',               
               'required' => true,
               'size' => 50),
        array ('linha' => 2,
               'col' => 3,
               'nome' => 'idPlano',
               'label' => 'Plano de Cargos:',
               'tipo' => 'combo',
               'required' => true,
               'array' => $result1,
               'size' => 30),
        array ('linha' => 4,
               'col' => 8,
               'nome' => 'atribuicoes',
               'label' => 'Atribuições do Cargo:',
               'tipo' => 'textarea',
               'size' => array(40,15)),
        array ('linha' => 4,
               'col' => 4,
               'nome' => 'obs',
               'label' => 'Observação:',
               'tipo' => 'textarea',
               'size' => array(40,15))));

    # Matrícula para o Log
    $objeto->set_idUsuario($idUsuario);
    
    # Paginação
    $objeto->set_paginacao(true);
    $objeto->set_paginacaoInicial($paginacao);

    ################################################################
    switch ($fase)
    {
        case "" :
        case "listar" :
            $objeto->listar();

            # Div da listagem de servidores
            $divServidores = new div('divServidores');
            $divServidores->abre();
            $divServidores->fecha();
            break;

        case "editar" :
            $objeto->$fase($id);        
            break;

        case "excluir" :	
        case "gravar" :		
            $objeto->$fase($id);
            break;

        case "listaServidores" :
            # Botão voltar
            botaoVoltar('?');
            
            # Limita o tamanho da tela
            $grid = new Grid();
            $grid->abreColuna(12);
        
            # Titulo
            titulo('Servidores com o Cargo: '.$pessoal->get_nomeCargo($id));
            br();
        
            # Lista de Servidores Ativos
            $lista = new listaServidores('Servidores Ativos');       
            $lista->set_situacao(1);
            $lista->set_cargo($id);
            $lista->show();

            # Lista de Servidores Inativos
            $lista = new listaServidores('Servidores Inativos');
            $lista->set_situacao(1);
            $lista->set_situacaoSinal("<>");
            $lista->set_cargo($id);
            $lista->show();
            
            $grid->fechaColuna();
            $grid->fechaGrid();
            break;
    }
    $page->terminaPagina();
}else{
    loadPage("../../areaServidor/sistema/login.php");
}