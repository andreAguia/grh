<?php
/**
 * Cadastro de Perfil
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
    $subFase = get('subFase',1);
    
    # Verifica se veio menu grh e registra o acesso no log
    $origem = get('origem',FALSE);
    if($origem){
        # Grava no log a atividade
        $atividade = "Visualizou o cadastro de perfil";
        $data = date("Y-m-d H:i:s");
        $intra->registraLog($idUsuario,$data,$atividade,NULL,NULL,7);
    }

    # pega o id (se tiver)
    $id = soNumeros(get('id'));

    # Pega o parametro de pesquisa (se tiver)
    if (is_null(post('parametro')))					# Se o parametro n?o vier por post (for nulo)
        $parametro = retiraAspas(get_session('sessionParametro'));	# passa o parametro da session para a variavel parametro retirando as aspas
    else{ 
        $parametro = post('parametro');                # Se vier por post, retira as aspas e passa para a variavel parametro
        set_session('sessionParametro',$parametro);    # transfere para a session para poder recuperá-lo depois
    }

    # Ordem da tabela
    $orderCampo = get('orderCampo');
    $orderTipo = get('orderTipo');

    # Começa uma nova página
    $page = new Page();
    if($fase == "grafico"){
        $page->set_jscript('<script type="text/javascript" src="https://www.gstatic.com/charts/loader.js"></script>');
    }
    $page->iniciaPagina();

    # Cabeçalho da Página
    AreaServidor::cabecalho();

    # Abre um novo objeto Modelo
    $objeto = new Modelo();

    ################################################################

    # Nome do Modelo (aparecerá nos fildset e no caption da tabela)
    $objeto->set_nome('Perfil');

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
    if(Verifica::acesso($idUsuario,1)){
        $objeto->set_linkExcluir('?fase=excluir');
    }
    $objeto->set_linkGravar('?fase=gravar');
    $objeto->set_linkListar('?fase=listar');

    # Parametros da tabela
    $objeto->set_label(array("id","Perfil","Tipo","Progressão","Triênio","Cargo em Comissão","Gratificação","Férias","Licença","Servidores","Ver"));
    #$objeto->set_width(array(3,10,16,8,8,10,8,8,8,8,8));
    $objeto->set_align(array("center"));
    #$objeto->set_function(array (NULL,NULL,NULL,NULL,NULL,NULL,"get_nome"));

    $objeto->set_classe(array(NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,"Pessoal"));
    $objeto->set_metodo(array(NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,"get_servidoresAtivosPerfil"));

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
               'required' => TRUE,
               'autofocus' => TRUE, 
               'size' => 50),
         array ('linha' => 1,
               'nome' => 'tipo',
               'title' => 'Tipo do Perfil',
               'label' => 'Tipo:',
               'tipo' => 'combo',
               'required' => TRUE,
               'array' => array("Concursados","Não Concursados"),
               'size' => 20),         
        array ('linha' => 3,
               'nome' => 'progressao',
               'title' => 'informa se esse perfil tem direito a progressão',
               'label' => 'Progressão:',
               'required' => TRUE,
               'tipo' => 'combo',
               'array' => array("Sim","Não"),
               'size' => 20),
        array ('linha' => 3,
               'nome' => 'trienio',
               'title' => 'informa se esse perfil tem direito ao triênio',
               'label' => 'Triênio:',
               'required' => TRUE,
               'tipo' => 'combo',
               'array' => array("Sim","Não"),
               'size' => 20),
        array ('linha' => 3,
               'nome' => 'comissao',
               'title' => 'informa se esse perfil tem direito a ter cargo em comissão',
               'required' => TRUE,
               'label' => 'Comissão:',
               'tipo' => 'combo',
               'array' => array("Sim","Não"),
               'size' => 20),
        array ('linha' => 3,
               'nome' => 'gratificacao',
               'title' => 'informa se esse perfil tem direito a receber gratificação especial',
               'label' => 'Gratificação:',
               'required' => TRUE,
               'tipo' => 'combo',
               'array' => array("Sim","Não"),
               'size' => 20),
         array ('linha' => 3,
               'nome' => 'ferias',
               'required' => TRUE,
               'title' => 'informa se esse perfil tem direito as férias',
               'label' => 'Férias:',
               'tipo' => 'combo',
               'array' => array("Sim","Não"),
               'size' => 20),
        array ('linha' => 3,
               'nome' => 'licenca',
               'title' => 'informa se esse perfil tem direito a licença',
               'required' => TRUE,
               'label' => 'Licença:',
               'tipo' => 'combo',
               'array' => array("Sim","Não"),
               'size' => 20),
        array ('linha' => 4,
               'nome' => 'obs',
               'label' => 'Observação:',
               'tipo' => 'textarea',
               'size' => array(80,5))));
    
    # Gráfico
    $imagem = new Imagem(PASTA_FIGURAS.'pie.png',NULL,15,15);            
    $botaoGra = new Button();
    $botaoGra->set_title("Exibe gráfico da quantidade de servidores");
    #$botaoGra->set_onClick("abreFechaDivId('divGrafico');");
    $botaoGra->set_url("?fase=grafico");
    $botaoGra->set_imagem($imagem);
    #$botaoGra->set_accessKey('G');
    
    # Relatório
    $imagem2 = new Imagem(PASTA_FIGURAS.'print.png',NULL,15,15);
    $botaoRel = new Button();
    $botaoRel->set_title("Exibe Relatório os Perfis");
    $botaoRel->set_onClick("window.open('../grhRelatorios/perfil.php','_blank','menubar=no,scrollbars=yes,location=no,directories=no,status=no,width=750,height=600');");
    $botaoRel->set_imagem($imagem2);
    #$botaoRel->set_accessKey('R');

    $objeto->set_botaoListarExtra(array($botaoGra,$botaoRel));

    ################################################################
    switch ($fase)
    {
        case "" :            
        case "listar" :
            $objeto->listar();
            break;
        
        case "excluir" :
            # Verifica se esse perfil tem servidor
            $sevAtivos = $pessoal->get_servidoresAtivosPerfil($id);
            $sevIntivos = $pessoal->get_servidoresInativosPerfil($id);
            $serTotal = $sevAtivos + $sevIntivos;
            
            if($serTotal > 0){
                alert('Este Perfil tem '.$serTotal.' servidores cadastrados:\n'.$sevAtivos.' ativo(s) e '.$sevIntivos.' inativo(s).\nEle não poderá ser excluído.');
                back(1);
            }else{
                $objeto->excluir($id);
            }            
            break;

        case "editar" :
        case "gravar" :
            $objeto->$fase($id);
            break;
        
        case "aguarde" :
            br(10);
            aguarde();
            br();
            loadPage('?fase=listaServidores&id='.$id);
            break;
            
        case "listaServidores" :
            # Limita o tamanho da tela
            $grid = new Grid();
            $grid->abreColuna(12);
            
            # Cria um menu
            $menu = new MenuBar();

            # Voltar
            $linkVoltar = new Link("Voltar","?");
            $linkVoltar->set_class('button');
            $linkVoltar->set_title('Volta para a página anterior');
            $linkVoltar->set_accessKey('V');
            $menu->add_link($linkVoltar,"left");

            # Tipo de servidores
            if($subFase == 1){ 
                $linkTipo = new Link("Servidores Inativos","?fase=listaServidores&subFase=2&id=$id");
                $linkTipo->set_title('Exibe os servidores inativos');
            }else{
                $linkTipo = new Link("Servidores Ativos","?fase=listaServidores&subFase=1&id=$id");
                $linkTipo->set_title('Exibe os servidores ativos');
            }
            $linkTipo->set_class('button');
            $linkTipo->set_title('Exibe os servidores inativos');
            $menu->add_link($linkTipo,"right");
            
            # Relatório
            $imagem2 = new Imagem(PASTA_FIGURAS.'print.png',NULL,15,15);
            $botaoRel = new Button();
            $botaoRel->set_title("Relatório dos Servidores");
            $botaoRel->set_onClick("window.open('?fase=relatorio&subFase=$subFase&id=$id','_blank','menubar=no,scrollbars=yes,location=no,directories=no,status=no,width=750,height=600');");
            $botaoRel->set_imagem($imagem2);
            $menu->add_link($botaoRel,"right");

            $menu->show();
            
            $servidor = new Pessoal();
            
            if($subFase == 1){           
                # Lista de Servidores Ativos
                $lista = new ListaServidores('Servidores Ativos - Perfil: '.$pessoal->get_perfilNome($id));
                $lista->set_situacao(1);
                $lista->set_perfil($id);   
                $lista->set_relatorio(TRUE);   
                $lista->showTabela();
            }else{            
                # Lista de Servidores Inativos
                $lista = new ListaServidores('Servidores Inativos - Perfil: '.$pessoal->get_perfilNome($id));
                $lista->set_situacao(1);
                $lista->set_situacaoSinal("<>");
                $lista->set_perfil($id);            
                $lista->showTabela();
            }
            
            $grid->fechaColuna();
            $grid->fechaGrid();
            break;
            
        case "relatorio" :
            if($subFase == 1){        
                # Lista de Servidores Ativos
                $lista = new ListaServidores('Servidores Ativos');
                $lista->set_situacao(1);
                $lista->set_perfil($id);    
                $lista->showRelatorio();
            }else{            
                # Lista de Servidores Inativos
                $lista = new ListaServidores('Servidores Inativos');
                $lista->set_situacao(1);
                $lista->set_situacaoSinal("<>");
                $lista->set_perfil($id);            
                $lista->showRelatorio();
            }
            break;
        
        case "grafico" :
            # Botão voltar
            botaoVoltar('?');
            
            # Exibe o Título
            $grid = new Grid();
            $grid->abreColuna(12);
            
            # Pega os dados
            $selectGrafico = 'SELECT tbperfil.nome, count(tbservidor.idServidor) 
                                FROM tbservidor LEFT JOIN tbperfil ON (tbservidor.idPerfil = tbperfil.idPerfil)
                               WHERE tbservidor.situacao = 1
                            GROUP BY tbperfil.nome';

            $servidores = $pessoal->select($selectGrafico);
            
            titulo('Servidores por Perfil');
            
            $grid3 = new Grid();
            $grid3->abreColuna(4);
            br();

            # Tabela
            $tabela = new Tabela();
            $tabela->set_conteudo($servidores);
            $tabela->set_label(array("Perfil","Servidores"));
            $tabela->set_width(array(80,20));
            $tabela->set_align(array("left","center"));    
            $tabela->show();

            $grid3->fechaColuna();
            $grid3->abreColuna(8);

            $chart = new Chart("Pie",$servidores);
            $chart->show();

            $grid3->fechaColuna();
            $grid3->fechaGrid();
            
            $grid->fechaColuna();
            $grid->fechaGrid();
            break;
    }
    $page->terminaPagina();
}else{
    loadPage("../../areaServidor/sistema/login.php");
}