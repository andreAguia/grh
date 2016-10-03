<?php
/**
 * Cadastro de Perfil
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
    $objeto->set_nome('Perfil');	

    # botão salvar
    $objeto->set_botaoSalvarGrafico(false);

    # bot?o de voltar da lista
    $objeto->set_voltarLista('grh.php');

    # controle de pesquisa
    $objeto->set_parametroLabel('Pesquisar');
    $objeto->set_parametroValue($parametro);

    # ordenação
    if(is_null($orderCampo))
            $orderCampo = "1";

    if(is_null($orderTipo))
            $orderTipo = 'asc';

    # select da lista
    $objeto->set_selectLista ('SELECT idPerfil,
                                      nome,
                                      tipo,
                                      progressao,
                                      trienio,
                                      comissao,
                                      gratificacao,
                                      ferias,
                                      licenca,
                                      idPerfil,
                                      idPerfil
                                 FROM tbperfil
                                WHERE nome LIKE "%'.$parametro.'%"
                                   OR idPerfil LIKE "%'.$parametro.'%" 
                             ORDER BY '.$orderCampo.' '.$orderTipo);

    # select do edita
    $objeto->set_selectEdita('SELECT nome,
                                      tipo,
                                      progressao,
                                      trienio,
                                      comissao,
                                      gratificacao,
                                      ferias,
                                      licenca,
                                     obs
                                FROM tbperfil
                               WHERE idPerfil = '.$id);

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
    $objeto->set_label(array("id","Perfil","Tipo","Progressão","Triênio","Cargo em Comissão","Gratificação","Férias","Licença","Servidores","Ver"));
    $objeto->set_width(array(3,10,16,8,8,10,8,8,8,8,8));
    $objeto->set_align(array("center"));
    #$objeto->set_function(array (null,null,null,null,null,null,"get_nome"));

    $objeto->set_classe(array(null,null,null,null,null,null,null,null,null,"Pessoal"));
    $objeto->set_metodo(array(null,null,null,null,null,null,null,null,null,"get_servidoresPerfil"));

    # Botão de exibição dos servidores
    $botao = new BotaoGrafico();
    $botao->set_label('');
    $botao->set_url('?fase=aguarde&id=');     
    $botao->set_image(PASTA_FIGURAS_GERAIS.'ver.png',20,20);

    # Coloca o objeto link na tabela			
    $objeto->set_link(array("","","","","","","","","","",$botao));

    # Classe do banco de dados
    $objeto->set_classBd('Pessoal');

    # Nome da tabela
    $objeto->set_tabela('tbperfil');

    # Nome do campo id
    $objeto->set_idCampo('idPerfil');

    # Tipo de label do formulário
    $objeto->set_formlabelTipo(1);

    # Foco do form
    $objeto->set_formFocus('nome');

    # Campos para o formulario
    $objeto->set_campos(array(
        array ('linha' => 1,
               'nome' => 'nome',
               'title' => 'Nome do Perfil',
               'label' => 'Nome:',
               'tipo' => 'texto',
               'autofocus' => true, 
               'size' => 50),
         array ('linha' => 1,
               'nome' => 'tipo',
               'title' => 'Tipo do Perfil',
               'label' => 'Tipo:',
               'tipo' => 'combo',
               'array' => array("Concursados","Não Concursados"),
               'size' => 20),         
        array ('linha' => 3,
               'nome' => 'progressao',
               'title' => 'informa se esse perfil tem direito a progressão',
               'label' => 'Progressão:',
               'tipo' => 'combo',
               'array' => array("Sim","Não"),
               'size' => 20),
        array ('linha' => 3,
               'nome' => 'trienio',
               'title' => 'informa se esse perfil tem direito ao triênio',
               'label' => 'Triênio:',
               'tipo' => 'combo',
               'array' => array("Sim","Não"),
               'size' => 20),
        array ('linha' => 3,
               'nome' => 'comissao',
               'title' => 'informa se esse perfil tem direito a ter cargo em comissão',
               'label' => 'Comissão:',
               'tipo' => 'combo',
               'array' => array("Sim","Não"),
               'size' => 20),
        array ('linha' => 3,
               'nome' => 'gratificacao',
               'title' => 'informa se esse perfil tem direito a receber gratificação especial',
               'label' => 'Gratificação:',
               'tipo' => 'combo',
               'array' => array("Sim","Não"),
               'size' => 20),
         array ('linha' => 3,
               'nome' => 'ferias',
               'title' => 'informa se esse perfil tem direito as férias',
               'label' => 'Férias:',
               'tipo' => 'combo',
               'array' => array("Sim","Não"),
               'size' => 20),
        array ('linha' => 3,
               'nome' => 'licenca',
               'title' => 'informa se esse perfil tem direito a licença',
               'label' => 'Licença:',
               'tipo' => 'combo',
               'array' => array("Sim","Não"),
               'size' => 20),
        array ('linha' => 4,
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

            # Gráfico Estatístico
            $pessoal = new Pessoal();

            # Gráfico de pizza
            $chart = new PieChart(500,500);
            $chart->getPlot()->getPalette()->setPieColor(array(
                new Color(30, 144, 255),
                new Color(255, 130, 71),
                new Color(67, 205, 128)));

            # Pega os dados
            $selectGrafico = 'SELECT tbperfil.nome, count(tbservidor.matricula) 
                                FROM tbservidor LEFT JOIN tbperfil ON (tbservidor.idPerfil = tbperfil.idPerfil)
                               WHERE tbservidor.situacao = 1
                            GROUP BY tbperfil.nome';

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

            br(2);
            $grid = new Grid("center");
            $grid->abreColuna(5);
                $imagem = new Imagem(PASTA_FIGURAS.'demo3.png','Servidores da Fenorte','100%','100%');
                $imagem->show();
            $grid->fechaColuna();
            $grid->fechaGrid();
            break;

        case "editar" :
        case "excluir" :	
        case "gravar" :
            $objeto->$fase($id);
            break;
        
        case "aguarde" :
            br(10);
            mensagemAguarde();
            br();
            loadPage('?fase=listaServidores&id='.$id);
            break;
            
        case "listaServidores" :
            # Botão voltar
            botaoVoltar('?');
            
            # Limita o tamanho da tela
            $grid = new Grid();
            $grid->abreColuna(12);

            # Titulo
            $servidor = new Pessoal();
            titulo('Servidores '.$servidor->get_perfilNome($id).'s');
            br();

            # Links da tab
            echo '<ul class="tabs" data-tabs id="example-tabs">';
            echo '<li class="tabs-title is-active"><a href="#panel1" aria-selected="true">Servidores Ativos</a></li>';
            echo '<li class="tabs-title"><a href="#panel2">Servidores Inativos</a></li>';
            echo '</ul>';
            
            # Conteúdo
            echo '<div class="tabs-content" data-tabs-content="example-tabs">';
            echo '<div class="tabs-panel is-active" id="panel1">';
            
            # Lista de Servidores Ativos
            $lista = new listaServidores('Servidores Ativos');
            $lista->set_situacao(1);
            $lista->set_perfil($id);            
            $lista->show();
            
            echo '</div>';
            echo '<div class="tabs-panel" id="panel2">';
            
            # Lista de Servidores Inativos
            $lista = new listaServidores('Servidores Inativos');
            $lista->set_situacao(1);
            $lista->set_situacaoSinal("<>");
            $lista->set_perfil($id);            
            $lista->show();
            
            echo '</div>';
            echo '</div>';
            
            $grid->fechaColuna();
            $grid->fechaGrid();
            break;

    }
    $page->terminaPagina();
}else{
    loadPage("../../areaServidor/sistema/login.php");
}