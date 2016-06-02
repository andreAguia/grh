<?php
/**
 * Cadastro de Cargos
 *  
 * By Alat
 */

# Reservado para a matrícula do servidor logado
$matricula = null;

# Configuração
include ("_config.php");

# Permissão de Acesso
$acesso = Verifica::acesso($matricula,13);

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
    set_session('sessionPaginacao',$paginacao);	
    
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
            $orderCampo = "3 asc, 2";

    if(is_null($orderTipo))
            $orderTipo = 'asc';

    # select da lista
    $objeto->set_selectLista ('SELECT idCargo,
                                      nome,
                                      tpCargo,
                                      tbplano.numDecreto,                                  
                                      idCargo,
                                      idCargo,
                                      idCargo
                                 FROM tbcargo JOIN tbplano USING (idPlano)
                                WHERE nome LIKE "%'.$parametro.'%"
                             ORDER BY '.$orderCampo.' '.$orderTipo);

    # select do edita
    $objeto->set_selectEdita('SELECT nome,
                                     tpCargo,
                                     idPlano,
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
    $objeto->set_label(array("id","Cargo","Tipo","Plano de Cargos","Servidores","Ver"));
    $objeto->set_width(array(5,30,20,20,10,10));
    $objeto->set_align(array("center"));

    $objeto->set_classe(array(null,null,null,null,"Pessoal"));
    $objeto->set_metodo(array(null,null,null,null,"get_servidoresCargo"));

    # Botão de exibição dos servidores
    $botao = new BotaoGrafico();
    $botao->set_label('');    
    $botao->set_url('?fase=listaServidores&id=');    
    $botao->set_image(PASTA_FIGURAS_GERAIS.'ver.png',20,20);

    # Coloca o objeto link na tabela			
    $objeto->set_link(array("","","","","",$botao));

    # Classe do banco de dados
    $objeto->set_classBd('Pessoal');

    # Nome da tabela
    $objeto->set_tabela('tbcargo');

    # Nome do campo id
    $objeto->set_idCampo('idCargo');

    # Tipo de label do formulário
    $objeto->set_formlabelTipo(1);

    # Pega os dados da combo de Plano e Cargos
    $tabela = new Pessoal();
    $result = $tabela->select('SELECT idPlano, 
                                      numDecreto
                                  FROM tbplano
                              ORDER BY numDecreto');

    # Campos para o formulario
    $objeto->set_campos(array(
        array ('linha' => 1,
               'nome' => 'nome',
               'label' => 'Cargo:',
               'tipo' => 'texto',
               'autofocus' => true,
               'required' => true,
               'size' => 50),
         array ('linha' => 1,
               'nome' => 'tpCargo',
               'label' => 'Nível do Cargo:',
               'tipo' => 'combo',
               'required' => true,
               'array' => array("Superior","Médio","Fundamental","Elementar"),
               'size' => 30),
        array ('linha' => 1,
               'nome' => 'idPlano',
               'label' => 'Plano de Cargos:',
               'tipo' => 'combo',
               'required' => true,
               'array' => $result,
               'size' => 30),
        array ('linha' => 4,
               'nome' => 'obs',
               'label' => 'Observação:',
               'tipo' => 'textarea',
               'size' => array(80,6))));

    # Matrícula para o Log
    $objeto->set_matricula($matricula);
    
    # Paginação
    $objeto->set_paginacao(true);
    $objeto->set_paginacaoInicial($paginacao);
    $objeto->set_paginacaoItens(6);

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
            $servidor = new Pessoal();
            titulo('Servidores com o Cargo: '.$servidor->get_nomeCargo($id));
            br();
        
            # Lista de Servidores Ativos
            $lista = new listaServidores('Servidores Ativos');       
            $lista->set_situacao(1);
            $lista->set_cargo($id);
            $lista->show();

            # Lista de Servidores Inativos
            $lista = new listaServidores('Servidores Inativos');
            $lista->set_situacao(2);
            $lista->set_cargo($id);
            $lista->show();
            
            $grid->fechaColuna();
            $grid->fechaGrid();
            break;
    }
    $page->terminaPagina();
}