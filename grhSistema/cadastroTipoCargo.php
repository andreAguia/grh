<?php
/**
 * Cadastro de Tipos de Cargos
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
    $objeto->set_nome('Tipos de Cargos');

    # Botão de voltar da lista
    $objeto->set_voltarLista('cadastroCargo.php');

    # controle de pesquisa
    $objeto->set_parametroLabel('Pesquisar');
    $objeto->set_parametroValue($parametro);

    # ordenaç?o
    if(is_null($orderCampo))
            $orderCampo = "1";

    if(is_null($orderTipo))
            $orderTipo = 'asc';

    # select da lista
    $objeto->set_selectLista ('SELECT idTipoCargo,
                                      cargo,
                                      sigla,
                                      nivel,
                                      vagas,
                                      idTipoCargo,
                                      idTipoCargo,
                                      obs
                                 FROM tbtipocargo
                                WHERE cargo LIKE "%'.$parametro.'%"
                             ORDER BY '.$orderCampo.' '.$orderTipo);

    # select do edita
    $objeto->set_selectEdita('SELECT cargo,
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
    $objeto->set_label(array("Id","Cargo","Sigla","Nível","Vagas","Servidores Ativos","Vagas Disponíveis","Obs"));
    $objeto->set_width(array(5,20,10,15,10,10,10,15));
    $objeto->set_align(array("center","left"));

    $objeto->set_classe(array(null,null,null,null,null,'pessoal','pessoal'));
    $objeto->set_metodo(array(null,null,null,null,null,'get_servidoresTipoCargo','get_tipoCargoVagasDisponiveis'));

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
               'required' => true,
               'autofocus' => true,
               'col' => 5,
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
               'required' => true,
               'array' => array(NULL,"Doutorado","Superior","Médio","Fundamental","Elementar"),
               'col' => 3,
               'size' => 30),
        array ('linha' => 1,
               'col' => 2,
               'nome' => 'vagas',
               'label' => 'Vagas:',
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
    
    # Relatório
    $botaoGra = new Button("Gráfico");
    $botaoGra->set_title("Exibe gráfico da quantidade de servidores");
    $botaoGra->set_onClick("abreFechaDivId('divGrafico');");
    $botaoGra->set_accessKey('G');

    $objeto->set_botaoListar(array($botaoGra));

    ################################################################
    switch ($fase)
    {
        case "" :            
        case "listar" :
            $div = new Div("divGrafico");
            $div->abre();
    
            # Gráfico Estatístico
            $pessoal = new Pessoal();
            
            titulo('Servidores por Perfil');

            # Gráfico de pizza
            $chart = new PieChart(500,500);
            $chart->getPlot()->getPalette()->setPieColor(array(
                new Color(30, 144, 255),
                new Color(255, 130, 71),
                new Color(67, 205, 128)));

            # Pega os dados
            $selectGrafico = 'SELECT tbtipocargo.sigla, count(tbservidor.matricula) 
                                FROM tbservidor JOIN tbcargo USING (idCargo)
                                                JOIN tbtipocargo USING (idTipoCargo)
                               WHERE tbservidor.situacao = 1
                            GROUP BY tbtipocargo.cargo';

            $servidores = $pessoal->select($selectGrafico);

            $dataSet = new XYDataSet();
            foreach ($servidores as $valor)
            {
                $dataSet->addPoint(new Point($valor[0]." (".$valor[1].")", $valor[1]));
            }
            #$dataSet->addPoint(new Point("Estatutário (".$estatutários.")", $estatutários));
            #$dataSet->addPoint(new Point("Cedidos (".$cedido.")", $cedido));
            #$dataSet->addPoint(new Point("Convidados (".$convidado.")", $convidado));
            $chart->setDataSet($dataSet);

            $chart->setTitle("");
            $chart->render(PASTA_FIGURAS."/demo3.png");

            $imagem = new Imagem(PASTA_FIGURAS.'demo3.png','Servidores da Fenorte','100%','100%');
            $imagem->show();
            
            $div->fecha();
            
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