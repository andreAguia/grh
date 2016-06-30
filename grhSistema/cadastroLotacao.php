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
    $objeto->set_nome('Lotação');	

    # botão salvar
    $objeto->set_botaoSalvarGrafico(false);

    # botão de voltar da lista
    $objeto->set_voltarLista('grh.php');

    # controle de pesquisa
    #$objeto->set_parametroLabel('Pesquisar');
    #$objeto->set_parametroValue($parametro);

    # ordenação
    if(is_null($orderCampo))
         $orderCampo = "8 desc, 1 asc, 2 asc, 3";

    if(is_null($orderTipo))
        $orderTipo = 'asc';

    # select da lista
    $objeto->set_selectLista ('SELECT idLotacao,
                                      UADM,
                                      DIR,
                                      GER,
                                      nome,                                  
                                      CONCAT(ramais," ",email),
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
                             ORDER BY '.$orderCampo.' '.$orderTipo);

    # select do edita
    $objeto->set_selectEdita('SELECT UADM,
                                     DIR,
                                     GER,
                                     nome,                                 
                                     ramais,
                                     email,
                                     ativo,
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
    $objeto->set_label(array("id","UADM","Diretoria","Gerência","Nome","Ramal","Servidores","Lotação Ativa?","Ver"));
    $objeto->set_width(array(5,10,10,15,20,20,5,5,5));
    $objeto->set_align(array("center"));

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
               'nome' => 'UADM',
               'label' => 'Unidade Administrativa:',
               'tipo' => 'texto',
               'autofocus' => true,
               'size' => 15),
        array ('linha' => 1,
               'nome' => 'DIR',
               'label' => 'Diretoria:',
               'title' => 'Sigla da Diretoria',
               'tipo' => 'texto',
               'size' => 15),
        array ('linha' => 1,
               'nome' => 'GER',
               'label' => 'Gerência:',
               'title' => 'Sigla da Gerência',
               'tipo' => 'texto',
               'size' => 15),
        array ('linha' => 1,
               'nome' => 'nome',
               'label' => 'Nome completo da lotação:',
               'title' => 'Nome completo da lotação sem siglas',
               'tipo' => 'texto',
               'size' => 50),    
        array ('linha' => 2,
               'nome' => 'ramais',
               'label' => 'Ramais:',
               'title' => 'Número dos telefones/ramais/faxes da lotação',
               'tipo' => 'texto',
               'size' => 100),
        array ('linha' => 3,
               'nome' => 'email',
               'label' => 'Email:',
               'title' => 'Email do Setor',
               'tipo' => 'texto',
               'size' => 50),
        array ('linha' => 3,
               'nome' => 'ativo',
               'label' => 'Ativo:',
               'title' => 'Se a lotação está ativa e permite movimentações',
               'tipo' => 'combo',
               'array' => array('Sim','Não'),
               'padrao' => 'Sim',
               'size' => 10),   
        array ('linha' => 5,
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
}
?>