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

if($acesso)
{    
    # Conecta ao Banco de Dados
    $intra = new Intra();
    $pessoal = new Pessoal();
    
    # Verifica a fase do programa
    $fase = get('fase','listar');
    
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
    if(is_null($orderCampo))
        $orderCampo = "8 desc, 3 asc, 4 asc, 5";

    if(is_null($orderTipo))
        $orderTipo = 'asc';

    # select da lista
    $objeto->set_selectLista ('SELECT idLotacao,
                                      codigo,
                                      UADM,
                                      DIR,
                                      GER,
                                      nome,
                                      idLotacao,                                  
                                      if(ativo = 0,"Não","Sim"),
                                      idLotacao,
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
    $objeto->set_label(array("id","Código","Unid.Adm.","Diretoria","Gerência","Nome","Servidores","Ativa","Ver"));
    $objeto->set_width(array(5,8,8,8,8,43,5,5,5));
    $objeto->set_align(array("center","center","center","center","center","left"));

    $objeto->set_classe(array(NULL,NULL,NULL,NULL,NULL,NULL,"pessoal"));
    $objeto->set_metodo(array(NULL,NULL,NULL,NULL,NULL,NULL,"get_lotacaoNumServidores"));

    #$objeto->set_function(array(NULL,NULL,NULL,NULL,NULL,NULL,NULL,"get_lotacaoNumServidores"));

    # Botão de exibição dos servidores
    $botao = new BotaoGrafico();
    $botao->set_label('');
    #$botao->set_title('Servidores com permissão a essa regra');
    $botao->set_url('?fase=listaServidores&id=');       
    $botao->set_image(PASTA_FIGURAS_GERAIS.'ver.png',20,20);

    # Coloca o objeto link na tabela			
    $objeto->set_link(array("","","","","","","","",$botao));

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
               'size' => 10),
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
    
    # Matrícula para o Log
    $objeto->set_idUsuario($idUsuario);
       
    # Grafico
    $imagem1 = new Imagem(PASTA_FIGURAS.'pie.png',NULL,15,15);
    $botaoGra = new Button();
    $botaoGra->set_title("Exibe gráfico da quantidade de servidores");
    $botaoGra->set_url("?fase=grafico");
    $botaoGra->set_imagem($imagem1);
    #$botaoGra->set_accessKey('G');
    
    # Relatório
    $imagem2 = new Imagem(PASTA_FIGURAS.'print.png',NULL,15,15);
    $botaoRel = new Button();
    $botaoRel->set_title("Exibe Relatório das Lotações Ativas");
    $botaoRel->set_onClick("window.open('../grhRelatorios/lotacao.php','_blank','menubar=no,scrollbars=yes,location=no,directories=no,status=no,width=750,height=600');");
    $botaoRel->set_imagem($imagem2);
    #$botaoRel->set_accessKey('R');
    
    # Organograma
    $imagem3 = new Imagem(PASTA_FIGURAS.'organograma2.png',NULL,15,15);
    $botaoOrg = new Button();
    $botaoOrg->set_title("Exibe o Organograma da UENF");
    $botaoOrg->set_imagem($imagem3);
    $botaoOrg->set_onClick("window.open('../_img/organograma.png','_blank','menubar=no,scrollbars=yes,location=no,directories=no,status=no,width=1000,height=700');");
    #$botaoOrg->set_accessKey('O');
    
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

        case "listaServidores" :
            # Limita o tamanho da tela
            $grid = new Grid();
            $grid->abreColuna(12);
            
            # Cria um menu
            $menu = new MenuBar();

            # Botão voltar
            $btnVoltar = new Button("Voltar","?");
            $btnVoltar->set_title('Volta para a página anterior');
            $btnVoltar->set_accessKey('V');
            $menu->add_link($btnVoltar,"left");

            # Relatórios
            $imagem2 = new Imagem(PASTA_FIGURAS.'print.png',NULL,15,15);
            $botaoRel = new Button();
            $botaoRel->set_title("Relatório dos Servidores");
            $botaoRel->set_onClick("abreFechaDivId('RelServidor');");
            $botaoRel->set_imagem($imagem2);
            $menu->add_link($botaoRel,"right");
             
            $menu->show();
            
            $grid->fechaColuna();
            $grid->fechaGrid();
            
            # Menu Relatório    
            $div = new Div("RelServidor");
            $div->abre();

            $grid = new Grid("right");
            $grid->abreColuna(3);

            echo '<nav aria-label="You are here:" role="navigation">';
            echo '<ul class="breadcrumbs">';

            # Servidores
            echo '<li>';
            $link = new Link("Servidores","../grhRelatorios/lotacaoServidoresAtivos.php?lotacao=".$id);
            $link->set_title("Exibe a Lista de Servidores");
            $link->set_janela(TRUE);    
            $link->show();
            echo '</li>';

            # Aniversariantes
            echo '<li>';
            $link = new Link("Aniversariantes","../grhRelatorios/lotacaoAniversariantes.php?lotacao=".$id);
            $link->set_title("Exibe a Lista de aniversariantes deste setor");
            #$link->set_class("disabled");
            $link->set_janela(TRUE);    
            $link->show();
            echo '</li>';

            echo '</ul>';
            echo '</nav>';

            $grid->fechaColuna();
            $grid->fechaGrid();
            $div->fecha();
            
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
            $grid3->abreColuna(4);
            br();

            # Tabela
            $tabela = new Tabela();
            $tabela->set_conteudo($servidores);
            $tabela->set_label(array("Lotação","Servidores"));
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