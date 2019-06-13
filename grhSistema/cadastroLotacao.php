<?php
/**
 * Cadastro de Lotação
 *  
 * By Alat
 */

# Reservado para o servidor logado
$idUsuario = NULL;

# Configuração
include("_config.php");

# Permissão de Acesso
$acesso = Verifica::acesso($idUsuario,2);

if($acesso){    
    # Conecta ao Banco de Dados
    $intra = new Intra();
    $pessoal = new Pessoal();
    
    # Verifica a fase do programa
    $fase = get('fase','listar');
    $subFase = get('subFase',1);
    
    # Verifica se veio menu grh e registra o acesso no log
    $grh = get('grh',FALSE);
    if($grh){
        # Grava no log a atividade
        $atividade = "Visualizou o cadastro de lotação";
        $data = date("Y-m-d H:i:s");
        $intra->registraLog($idUsuario,$data,$atividade,NULL,NULL,7);
    }
    
    # Verifica tipo (1->ativo ou 0->inativo)
    $tipo = get('tipo',1);
    
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
    $page->iniciaPagina();

    # Cabeçalho da Página
    if($fase <> "relatorio"){
        AreaServidor::cabecalho();
    }

    # Abre um novo objeto Modelo
    $objeto = new Modelo();

    ################################################################

    # Nome do Modelo (aparecerá nos fildset e no caption da tabela)
    if($tipo){
        $complemento = " Ativas";
    }else{
        $complemento = " Inativas";
    }
    $objeto->set_nome('Lotações '.$complemento);

    # botão de voltar da lista
    $objeto->set_voltarLista('grh.php');

    # controle de pesquisa
    $objeto->set_parametroLabel('Pesquisar');
    $objeto->set_parametroValue($parametro);

    # ordenação
    if(is_null($orderCampo)){
        $orderCampo = "9 desc, 3 asc, 4 asc, 5";
    }

    if(is_null($orderTipo)){
        $orderTipo = 'asc';
    }

    # select da lista
    $objeto->set_selectLista ('SELECT idLotacao,
                                      codigo,
                                      UADM,
                                      DIR,
                                      GER,
                                      nome,
                                      idLotacao,
                                      idLotacao,
                                      if(ativo = 0,"Não","Sim"),
                                      idLotacao
                                 FROM tblotacao
                                WHERE ativo = '.$tipo.'  
                                AND (UADM LIKE "%'.$parametro.'%"
                                   OR DIR LIKE "%'.$parametro.'%"
                                   OR GER LIKE "%'.$parametro.'%"
                                   OR nome LIKE "%'.$parametro.'%"
                                   OR ramais LIKE "%'.$parametro.'%"
                                   OR idLotacao LIKE "%'.$parametro.'%") 
                             ORDER BY '.$orderCampo.' '.$orderTipo);

    # select do edita
    $objeto->set_selectEdita('SELECT codigo,
                                     UADM,
                                     DIR,
                                     GER,
                                     nome,
                                     ativo,                                 
                                     ramais,
                                     email,
                                     obs
                                FROM tblotacao
                               WHERE idLotacao = '.$id);

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
    $objeto->set_label(array("id","Código","Unid.Adm.","Diretoria","Gerência","Nome","Servidores<br/>Ativos","Servidores<br/>Inativos","Ativa"));
    #$objeto->set_width(array(5,8,8,8,8,43,5,5,5));
    $objeto->set_align(array("center","center","center","center","center","left"));

    $objeto->set_classe(array(NULL,NULL,NULL,NULL,NULL,NULL,"Grh","Grh"));
    $objeto->set_metodo(array(NULL,NULL,NULL,NULL,NULL,NULL,"get_numServidoresAtivosLotacao","get_numServidoresInativosLotacao"));

    # Classe do banco de dados
    $objeto->set_classBd('Pessoal');

    # Nome da tabela
    $objeto->set_tabela('tblotacao');

    # Nome do campo id
    $objeto->set_idCampo('idLotacao');

    # Tipo de label do formulário
    $objeto->set_formlabelTipo(1);

    # Campos para o formulario
    $objeto->set_campos(array(
        array ('linha' => 1,
               'col' => 3,
               'nome' => 'codigo',
               'label' => 'Código:',
               'tipo' => 'texto',
               'autofocus' => TRUE,
               'size' => 15),               
        array ('linha' => 1,
               'col' => 3,
               'nome' => 'UADM',
               'label' => 'Unidade Administrativa:',
               'tipo' => 'combo',
               'required' => TRUE,
               'array' => array('UENF','FENORTE'),
               'size' => 15),
        array ('linha' => 1,
               'col' => 3,
               'nome' => 'DIR',
               'label' => 'Sigla da Diretoria:',
               'title' => 'Sigla da Diretoria',
               'tipo' => 'texto',
               'required' => TRUE,
               'size' => 15),
        array ('linha' => 1,
               'col' => 3,
               'nome' => 'GER',
               'label' => 'Sigla da Gerência:',
               'title' => 'Sigla da Gerência',
               'tipo' => 'texto',
               'size' => 15),
        array ('linha' => 2,
               'col' => 10,
               'nome' => 'nome',
               'label' => 'Nome completo da lotação:',
               'title' => 'Nome completo da lotação sem siglas',
               'tipo' => 'texto',
               'required' => TRUE,
               'size' => 100),
        array ('linha' => 2,
               'col' => 2,
               'nome' => 'ativo',
               'required' => TRUE,
               'label' => 'Ativo:',
               'title' => 'Se a lotação está ativa e permite movimentações',
               'tipo' => 'combo',
               'array' => array(array(1,'Sim'),array(0,'Não')),
               'padrao' => 'Sim',
               'size' => 5),
        array ('linha' => 3,
               'col' => 6,
               'nome' => 'ramais',
               'label' => 'Ramais:',
               'title' => 'Número dos telefones/ramais/faxes da lotação',
               'tipo' => 'texto',
               'size' => 100),
        array ('linha' => 3,
               'col' => 6,
               'nome' => 'email',
               'label' => 'Email:',
               'title' => 'Email do Setor',
               'tipo' => 'texto',
               'size' => 50),           
        array ('linha' => 5,
               'nome' => 'obs',
               'label' => 'Observação:',
               'tipo' => 'textarea',
               'size' => array(80,5))));
    
    # idUsuário para o Log
    $objeto->set_idUsuario($idUsuario);
       
    # Grafico
    $imagem1 = new Imagem(PASTA_FIGURAS.'pie.png',NULL,15,15);
    $botaoGra = new Button();
    $botaoGra->set_title("Exibe gráfico da quantidade de servidores");
    $botaoGra->set_url("?fase=grafico");
    $botaoGra->set_imagem($imagem1);
    #$botaoGra->set_accessKey('G');
    
    # Relatório
    $imagem = new Imagem(PASTA_FIGURAS.'print.png',NULL,15,15);
    $botaoRel = new Button();
    $botaoRel->set_imagem($imagem);
    $botaoRel->set_title("Imprimir");
    $botaoRel->set_target("_blank");
    $botaoRel->set_url('../grhRelatorios/lotacao.php');
           
    # Organograma
    $imagem3 = new Imagem(PASTA_FIGURAS.'organograma2.png',NULL,15,15);
    $botaoOrg = new Button();
    $botaoOrg->set_title("Exibe o Organograma da UENF");
    $botaoOrg->set_imagem($imagem3);
    $botaoOrg->set_target("_blank");
    $botaoOrg->set_url('../_img/organograma.png');
    
    # Organograma2
    $imagem3 = new Imagem(PASTA_FIGURAS.'organograma2.png',NULL,15,15);
    $botaoOrga = new Button();
    $botaoOrga->set_title("Exibe o Organograma2 da UENF");
    $botaoOrga->set_imagem($imagem3);
    $botaoOrga->set_url("?fase=organograma");
    #$botaoOrg->set_accessKey('O');
    
    # Cargos Ativos
    $botaoAtivo = new Button("Lotações Ativas","?tipo=1");
    $botaoAtivo->set_title("Exibe os Cargos Ativos");
    
    # Cargos Ativos
    $botaoInativo = new Button("Lotações Inativas","?tipo=0");
    $botaoInativo->set_title("Exibe os Cargos Inativos");
    
    # Cria o array de botões
    $arrayBotoes = array($botaoGra,$botaoRel,$botaoOrg);
    if($tipo){
        array_unshift($arrayBotoes,$botaoInativo);
    }else{
        $arrayBotoes = array($botaoAtivo);
    }

    $objeto->set_botaoListarExtra($arrayBotoes); 

    ################################################################
    
    switch ($fase){
        case "" :            
        case "listar" :            
            $objeto->listar();
            break;
        
    ################################################################

        case "editar" :
        case "excluir" :	
        case "gravar" :
            $objeto->$fase($id);
            break;
        
    ################################################################

        case "listaServidoresAtivos" :
            # Limita o tamanho da tela
            $grid = new Grid();
            $grid->abreColuna(12);
            
             # Informa a origem
            set_session('origem','cadastroLotacao.php?fase=listaServidoresAtivos&id='.$id);
            
            # Cria um menu
            $menu = new MenuBar();

            # Voltar
            $linkVoltar = new Link("Voltar","?");
            $linkVoltar->set_class('button');
            $linkVoltar->set_title('Volta para a página anterior');
            $linkVoltar->set_accessKey('V');
            $menu->add_link($linkVoltar,"left");
             
            # Relatório
            $imagem2 = new Imagem(PASTA_FIGURAS.'print.png',NULL,15,15);
            $botaoRel = new Button();
            $botaoRel->set_title("Relatório dos Servidores");
            $botaoRel->set_target("_blank");
            $botaoRel->set_url("?fase=relatorio&subFase=1&id=$id");            
            $botaoRel->set_imagem($imagem2);
            $menu->add_link($botaoRel,"right");

            $menu->show();
            
            # Limita o tamanho da tela
            $grid = new Grid();
            $grid->abreColuna(12);
            
            # Titulo
            titulo('Servidores da Lotação: '.$pessoal->get_nomeLotacao($id));
            br();
            
            # Lista de Servidores Ativos
            $lista = new ListaServidores('Servidores Ativos');
            $lista->set_situacao(1);
            $lista->set_lotacao($id);            
            $lista->showTabela();
            
            $grid->fechaColuna();
            $grid->fechaGrid();
            break;
        
    ################################################################

        case "listaServidoresInativos" :
            # Limita o tamanho da tela
            $grid = new Grid();
            $grid->abreColuna(12);
            
            # Informa a origem
            set_session('origem','cadastroLotacao.php?fase=listaServidoresInativos&id='.$id);
            
            # Cria um menu
            $menu = new MenuBar();

            # Voltar
            $linkVoltar = new Link("Voltar","?");
            $linkVoltar->set_class('button');
            $linkVoltar->set_title('Volta para a página anterior');
            $linkVoltar->set_accessKey('V');
            $menu->add_link($linkVoltar,"left");
             
            # Relatório
            $imagem2 = new Imagem(PASTA_FIGURAS.'print.png',NULL,15,15);
            $botaoRel = new Button();
            $botaoRel->set_title("Relatório dos Servidores");
            $botaoRel->set_target("_blank");
            $botaoRel->set_url("?fase=relatorio&subFase=2&id=$id");            
            $botaoRel->set_imagem($imagem2);
            $menu->add_link($botaoRel,"right");

            $menu->show();
            
            # Titulo
            titulo('Servidores da Lotação: '.$pessoal->get_nomeLotacao($id));
            br();
            
            # Lista de Servidores Ativos
            $lista = new ListaServidores('Servidores Inativos');
            $lista->set_situacao(1);
            $lista->set_situacaoSinal("<>");
            $lista->set_lotacao($id);            
            $lista->showTabela();
            
            $grid->fechaColuna();
            $grid->fechaGrid();
            break;
        
    ################################################################
			
        case "relatorio" :
            if($subFase == 1){        
                # Lista de Servidores Ativos
                $lista = new ListaServidores('Servidores Ativos');
                $lista->set_situacao(1);				
	        $lista->set_lotacao($id);   
                $lista->showRelatorio();
            }else{            
                # Lista de Servidores Inativos
                $lista = new ListaServidores('Servidores Inativos');
               	$lista->set_situacao(1);				
	        $lista->set_situacaoSinal("<>");
	        $lista->set_lotacao($id);         
                $lista->showRelatorio();
            }
            break;
    
    ################################################################
        
        case "grafico" :
            # Botão voltar
            botaoVoltar('?');
            
            # Exibe o Título
            $grid = new Grid();
            $grid->abreColuna(12);
            
            # Pega os dados
            $selectGrafico = 'SELECT tblotacao.dir, count(tbservidor.matricula) 
                                FROM tbservidor LEFT  JOIN tbhistlot ON (tbservidor.idServidor = tbhistlot.idServidor)
                                                      JOIN tblotacao ON (tbhistlot.lotacao=tblotacao.idLotacao)
                               WHERE tbhistlot.data = (select max(data) from tbhistlot where tbhistlot.idServidor = tbservidor.idServidor)
                                 AND situacao = 1
                                 AND ativo
                            GROUP BY tblotacao.dir';

            $servidores = $pessoal->select($selectGrafico);

            
            titulo('Servidores por Lotação');
            
            $grid3 = new Grid();
            $grid3->abreColuna(12,4);
            br();

            # Tabela
            $tabela = new Tabela();
            $tabela->set_conteudo($servidores);
            $tabela->set_label(array("Lotação","Servidores"));
            $tabela->set_width(array(80,20));
            $tabela->set_align(array("left","center"));    
            $tabela->show();

            $grid3->fechaColuna();
            $grid3->abreColuna(12,8);

            $chart = new Chart("Pie",$servidores);
            $chart->set_tamanho(700,500);
            $chart->show();

            $grid3->fechaColuna();
            $grid3->fechaGrid();
            
            $grid->fechaColuna();
            $grid->fechaGrid();
            break;
        
    ################################################################
        
        case "organograma" :
            
            $org = new OrganogramaUenf("CCTA");
            $org->show();
            break;
    }									 	 		

    if($fase <> "organograma"){
        $page->terminaPagina();
    }
}else{
    loadPage("../../areaServidor/sistema/login.php");
}