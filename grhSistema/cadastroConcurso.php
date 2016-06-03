<?php
/**
 * Cadastro de Concursos
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
    $objeto->set_nome('Concursos');	

    # botão salvar
    $objeto->set_botaoSalvarGrafico(false);

    # bot?o de voltar da lista
    $objeto->set_voltarLista('grh.php');

    # controle de pesquisa
    $objeto->set_parametroLabel('Pesquisar');
    $objeto->set_parametroValue($parametro);

    # ordenaç?o
    if(is_null($orderCampo))
            $orderCampo = "2";

    if(is_null($orderTipo))
            $orderTipo = 'asc';

    # select da lista
    $objeto->set_selectLista ('SELECT idConcurso,
                                      anobase,
                                      regime,
                                      orgExecutor,
                                      tbplano.numDecreto,
                                      idConcurso,
                                      idConcurso,
                                      idConcurso
                                 FROM tbconcurso JOIN tbplano USING (idPlano)
                                WHERE anobase LIKE "%'.$parametro.'%"
                                   OR regime LIKE "%'.$parametro.'%"
                                   OR orgExecutor LIKE "%'.$parametro.'%"    
                             ORDER BY '.$orderCampo.' '.$orderTipo);

    # select do edita
    $objeto->set_selectEdita('SELECT anobase,
                                     regime,
                                     orgExecutor,
                                     idPlano,
                                     obs
                                FROM tbconcurso
                               WHERE idConcurso = '.$id);

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
    $objeto->set_label(array("id","Ano Base","Regime","Executor","Plano de Cargos","Servidores","Ver"));
    $objeto->set_width(array(5,10,20,20,20,10,10));
    $objeto->set_align(array("center"));

    $objeto->set_classe(array(null,null,null,null,null,"Pessoal"));
    $objeto->set_metodo(array(null,null,null,null,null,"get_servidoresConcurso"));
    
    # Botão de exibição dos servidores
    $botao = new BotaoGrafico();
    $botao->set_label('');    
    $botao->set_url('?fase=listaServidores&id=');    
    $botao->set_image(PASTA_FIGURAS_GERAIS.'ver.png',20,20);

    # Coloca o objeto link na tabela			
    $objeto->set_link(array("","","","","","",$botao));

    # Classe do banco de dados
    $objeto->set_classBd('Pessoal');

    # Nome da tabela
    $objeto->set_tabela('tbconcurso');

    # Nome do campo id
    $objeto->set_idCampo('idConcurso');

    # Tipo de label do formulário
    $objeto->set_formlabelTipo(1);

    # Foco do form
    $objeto->set_formFocus('anobase');

    # Pega os dados da combo de Plano e Cargos
    $tabela = new Pessoal();
    $result = $tabela->select('SELECT idPlano, 
                                      numDecreto
                                  FROM tbplano
                              ORDER BY numDecreto');

    # Campos para o formulario
    $objeto->set_campos(array(
        array ('linha' => 1,
               'nome' => 'anobase',
               'label' => 'Ano Base:',
               'tipo' => 'texto',
               'autofocus' => true,
               'size' => 10),
         array ('linha' => 1,
               'nome' => 'regime',
               'label' => 'Regime:',
               'tipo' => 'texto',
               'size' => 20),
         array ('linha' => 2,
               'nome' => 'orgExecutor',
               'label' => 'Executor:',
               'tipo' => 'texto',
               'size' => 30),
        array ('linha' => 2,
               'nome' => 'idPlano',
               'label' => 'Plano de Cargos:',
               'tipo' => 'combo',
               'array' => $result,
               'size' => 30),
        array ('linha' => 4,
               'nome' => 'obs',
               'label' => 'Observação:',
               'tipo' => 'textarea',
               'size' => array(80,5))));

    # Matrícula para o Log
    $objeto->set_matricula($matricula);

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
            $selectGrafico = 'SELECT anoBase, count(tbfuncionario.matricula) 
                                FROM tbfuncionario LEFT JOIN tbconcurso ON (tbfuncionario.idConcurso = tbconcurso.idConcurso)
                               WHERE tbfuncionario.Sit = 1
                                 AND tbfuncionario.idPerfil = 1
                            GROUP BY anoBase';

            $servidores = $pessoal->select($selectGrafico);

            $dataSet = new XYDataSet();
            foreach ($servidores as $valor){
                $dataSet->addPoint(new Point($valor[0]." (".$valor[1].")", $valor[1]));
            }
            $chart->setDataSet($dataSet);

            $chart->setTitle("");
            $chart->render(PASTA_FIGURAS."/demo3.png");

            $grid = new Grid();
            $grid->abreColuna(3);
            $grid->fechaColuna();
            $grid->abreColuna(6);
            
                echo '<div class="callout secondary">';
                $imagem = new Imagem(PASTA_FIGURAS.'demo3.png','Servidores da Fenorte','100%','100%');
                $imagem->show();                
                echo '</div>';
                
            $grid->fechaColuna();
            $grid->abreColuna(3);
            $grid->fechaColuna();
            $grid->fechaGrid();    
            break;

        case "editar" :
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
            echo '<br />';
            titulo('Servidores '.$servidor->get_perfilNome($id).'s');
            echo '<br />';

            # Lista de Servidores Ativos
            $lista = new listaServidores('Servidores Ativos esse Concurso');
            $lista->set_situacao(1);
            $lista->set_concurso($id);            
            $lista->show();

            # Lista de Servidores Inativos
            $lista = new listaServidores('Servidores Inativos com esse Concurso');
            $lista->set_situacao(2);
            $lista->set_concurso($id);            
            $lista->show();
            
            $grid->fechaColuna();
            $grid->fechaGrid();
            break;
    }
    $page->terminaPagina();
}