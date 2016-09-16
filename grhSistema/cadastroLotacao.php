<?php
/**
 * Cadastro de Lotação
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
    
    # Pega o número de Lotações ativas para a paginação
    $numLotacaoAtiva = $pessoal->get_numLotacaoAtiva();
	
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
    $objeto->set_nome('Lotação');	

    # botão salvar
    $objeto->set_botaoSalvarGrafico(false);

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
                                      ativo,
                                      idLotacao,
                                      idLotacao
                                 FROM tblotacao
                                WHERE UADM LIKE "%'.$parametro.'%"
                                   OR DIR LIKE "%'.$parametro.'%"
                                   OR GER LIKE "%'.$parametro.'%"
                                   OR nome LIKE "%'.$parametro.'%"
                                   OR ramais LIKE "%'.$parametro.'%"
                                   OR idLotacao LIKE "%'.$parametro.'%" 
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
    $objeto->set_label(array("id","Código","Unid.Adm.","Diretoria","Gerência","Nome","Servidores","Lotação Ativa?","Ver"));
    $objeto->set_width(array(5,8,8,8,8,43,5,5,5));
    $objeto->set_align(array("center","center","center","center","center","left"));

    $objeto->set_classe(array(null,null,null,null,null,null,"pessoal"));
    $objeto->set_metodo(array(null,null,null,null,null,null,"get_lotacaoNumServidores"));

    #$objeto->set_function(array(null,null,null,null,null,null,null,"get_lotacaoNumServidores"));
    $objeto->set_formatacaoCondicional(array(
                                        array('coluna' => 7,
                                              'valor' => 'Não',
                                              'operador' => '=',
                                              'id' => 'inativo')));

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

    # Pega os dados da combo de responsáveis
    $responsavel = new Pessoal();
    $result = $responsavel->select('SELECT matricula, 
                                           nome
                                      FROM tbservidor LEFT JOIN tbpessoa USING (idPessoa)
                                     WHERE tbservidor.situacao = 1
                                  ORDER BY nome');

    # Campos para o formulario
    $objeto->set_campos(array(
        array ('linha' => 1,
               'col' => 3,
               'nome' => 'codigo',
               'label' => 'Código:',
               'tipo' => 'texto',
               'autofocus' => true,
               'size' => 15),               
        array ('linha' => 1,
               'col' => 3,
               'nome' => 'UADM',
               'label' => 'Unidade Administrativa:',
               'tipo' => 'combo',
               'array' => array('UENF','FENORTE'),
               'size' => 15),
        array ('linha' => 1,
               'col' => 3,
               'nome' => 'DIR',
               'label' => 'Sigla da Diretoria:',
               'title' => 'Sigla da Diretoria',
               'tipo' => 'texto',
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
               'size' => 100),
        array ('linha' => 2,
               'col' => 2,
               'nome' => 'ativo',
               'label' => 'Ativo:',
               'title' => 'Se a lotação está ativa e permite movimentações',
               'tipo' => 'combo',
               'array' => array('Sim','Não'),
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
    
    # Paginação
    $objeto->set_paginacao(true);
    $objeto->set_paginacaoInicial($paginacao);
    $objeto->set_paginacaoItens($numLotacaoAtiva);

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
            $btnRel = new Link("Relatorios");
            $btnRel->set_class('button');
            $btnRel->set_onClick("abreFechaDivId('RelServidor');");
            $btnRel->set_title('Relatórios desse servidor');
            $btnRel->set_accessKey('R');
            $menu->add_link($btnRel,"right");
             
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
            titulo('Servidores da Lotação: '.$pessoal->get_nomelotacao($id));
            br();
            
            # Lista de Servidores Ativos
            $lista = new listaServidores('Servidores Ativos');
            $lista->set_situacao(1);
            $lista->set_lotacao($id);            
            $lista->show();
            
            $grid->fechaColuna();
            $grid->fechaGrid();
            break;
    }									 	 		

    $page->terminaPagina();
}else{
    loadPage("../../areaServidor/sistema/login.php");
}