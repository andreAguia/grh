<?php
/**
 * Cadastro de Tipos de Cargos
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

    # Pega o parametro de pesquisa (se tiver)
    if (is_null(post('parametro'))){					# Se o parametro n?o vier por post (for nulo)
        $parametro = retiraAspas(get_session('sessionParametro'));	# passa o parametro da session para a variavel parametro retirando as aspas
    }else{ 
        $parametro = post('parametro');                # Se vier por post, retira as aspas e passa para a variavel parametro
        set_session('sessionParametro',$parametro);    # transfere para a session para poder recuperá-lo depois
    }

    # Ordem da tabela
    $orderCampo = get('orderCampo');
    $orderTipo = get('orderTipo');

    # Começa uma nova página
    $page = new Page();
    $page->set_jscript('<script type="text/javascript" src="https://www.gstatic.com/charts/loader.js"></script>');
    $page->iniciaPagina();

    # Cabeçalho da Página
    AreaServidor::cabecalho();

    # Abre um novo objeto Modelo
    $objeto = new Modelo();

    ################################################################

    # Nome do Modelo (aparecerá nos fildset e no caption da tabela)
    $objeto->set_nome('Cargos');

    # Botão de voltar da lista
    $objeto->set_voltarLista('grh.php');

    # controle de pesquisa
    $objeto->set_parametroLabel('Pesquisar');
    $objeto->set_parametroValue($parametro);

    # ordenaç?o
    if(is_null($orderCampo)){
        $orderCampo = "1";
    }

    if(is_null($orderTipo)){
        $orderTipo = 'asc';
    }

    # select da lista
    $objeto->set_selectLista ('SELECT idTipoCargo,
                                      tipo,
                                      cargo,
                                      sigla,
                                      nivel,
                                      vagas,
                                      idTipoCargo,
                                      idTipoCargo
                                 FROM tbtipocargo
                                WHERE cargo LIKE "%'.$parametro.'%"
                             ORDER BY '.$orderCampo.' '.$orderTipo);

    # select do edita
    $objeto->set_selectEdita('SELECT cargo,
                                     tipo,
                                     sigla,
                                     nivel,
                                     vagas,
                                     obs
                                FROM tbtipocargo
                               WHERE idTipoCargo = '.$id);

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
    $objeto->set_label(["Id","Tipo","Cargo","Sigla","Nível","Vagas<br/>Publicadas","Servidores<br/>Ativos","Vagas<br/>Disponíveis"]);
    $objeto->set_align(["center","center","left"]);
    
    $objeto->set_classe([NULL,NULL,NULL,NULL,NULL,NULL,'pessoal','pessoal']);
    $objeto->set_metodo([NULL,NULL,NULL,NULL,NULL,NULL,'get_servidoresTipoCargo','get_tipoCargoVagasDisponiveis']);

    # Classe do banco de dados
    $objeto->set_classBd('Pessoal');

    # Nome da tabela
    $objeto->set_tabela('tbtipocargo');

    # Nome do campo id
    $objeto->set_idCampo('idTipoCargo');

    # Tipo de label do formulário
    $objeto->set_formlabelTipo(1);

    # Campos para o formulario
    $objeto->set_campos(array(
        array ('linha' => 1,
               'nome' => 'cargo',
               'label' => 'Cargo:',
               'tipo' => 'texto',
               'required' => TRUE,
               'autofocus' => TRUE,
               'col' => 3,
               'size' => 50),
        array ('linha' => 1,
               'nome' => 'tipo',
               'label' => 'Tipo:',
               'tipo' => 'combo',
               'required' => TRUE,
               'array' => array(NULL,"Adm/Tec","Professor"),
               'col' => 2,
               'size' => 50),
        array ('linha' => 1,
               'nome' => 'sigla',
               'label' => 'Sigla:',
               'tipo' => 'texto',
               'col' => 2,
               'size' => 50),
        array ('linha' => 1,
               'nome' => 'nivel',
               'label' => 'Nível do Cargo:',
               'tipo' => 'combo',
               'required' => TRUE,
               'array' => array(NULL,"Doutorado","Superior","Médio","Fundamental","Elementar"),
               'col' => 3,
               'size' => 30),
        array ('linha' => 1,
               'col' => 2,
               'nome' => 'vagas',
               'label' => 'Vagas Publicadas:',
               'tipo' => 'numero',
               'size' => 10),
        array ('linha' => 2,
               'nome' => 'obs',
               'label' => 'Observação:',
               'tipo' => 'textarea',
               'col' => 12,
               'size' => array(80,5))));

    # Matrícula para o Log
    $objeto->set_idUsuario($idUsuario);
    
    # Gráfico
    $imagem = new Imagem(PASTA_FIGURAS.'pie.png',NULL,15,15);            
    $botaoGra = new Button();
    $botaoGra->set_title("Exibe gráfico da quantidade de servidores");
    #$botaoGra->set_onClick("abreFechaDivId('divGrafico');");
    $botaoGra->set_url("?fase=grafico");
    $botaoGra->set_imagem($imagem);
    #$botaoGra->set_accessKey('G');
    
    # Cadastro de Cargos
    $botaoCargo = new Button("Funções");
    $botaoCargo->set_title("Acessa o Cadastro de Cargos");
    $botaoCargo->set_url('cadastroFuncao.php');  
    #$botaoCargo->set_accessKey('L');

    $objeto->set_botaoListarExtra([$botaoGra,$botaoCargo]);

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
        
        case "grafico" :
            # Gráfico Estatístico
            $pessoal = new Pessoal();
            
            # Pega os dados
            $selectGrafico = 'SELECT tbtipocargo.cargo, count(tbservidor.matricula) 
                                FROM tbservidor JOIN tbcargo USING (idCargo)
                                                JOIN tbtipocargo USING (idTipoCargo)
                               WHERE tbservidor.situacao = 1
                            GROUP BY tbtipocargo.cargo';

            $servidores = $pessoal->select($selectGrafico);
            
            $grid2 = new Grid();
            $grid2->abreColuna(12);
            
            botaoVoltar("?");            
            titulo('Servidores por Cargo');

            $grid3 = new Grid();
            $grid3->abreColuna(4);
            br();

            # Tabela
            $tabela = new Tabela();
            $tabela->set_conteudo($servidores);
            $tabela->set_label(array("Cargo","Servidores"));
            $tabela->set_width(array(80,20));
            $tabela->set_align(array("left","center"));    
            $tabela->show();

            $grid3->fechaColuna();
            $grid3->abreColuna(8);

            $chart = new Chart("Pie",$servidores);
            $chart->show();

            $grid3->fechaColuna();
            $grid3->fechaGrid();
            
            $grid2->fechaColuna();
            $grid2->fechaGrid();
            break;
    }									 	 		

    $page->terminaPagina();
}else{
    loadPage("../../areaServidor/sistema/login.php");
}