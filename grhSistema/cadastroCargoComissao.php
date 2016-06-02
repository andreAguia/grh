<?php
/**
 * Cadastro de Cargos em COmissão
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
    $objeto->set_nome('Cargos em Comissão');

    # botão salvar
    $objeto->set_botaoSalvarGrafico(false);

    # bot?o de voltar da lista
    $objeto->set_voltarLista('grh.php');

    # controle de pesquisa
    $objeto->set_parametroLabel('Pesquisar');
    $objeto->set_parametroValue($parametro);

    # ordenaç?o
    if(is_null($orderCampo))
            $orderCampo = "3";

    if(is_null($orderTipo))
            $orderTipo = 'asc';

    # select da lista
    $objeto->set_selectLista ('SELECT idTipoComissao,
                                      descricao,
                                      simbolo,
                                      valsal,
                                      vagas,                                  
                                      idTipoComissao,
                                      idTipoComissao,
                                      idTipoComissao,
                                      IF(exibeSite,"Sim","Não"),
                                      idTipoComissao
                                 FROM tbtipocomissao
                                WHERE descricao LIKE "%'.$parametro.'%"
                                   OR simbolo LIKE "%'.$parametro.'%" 
                             ORDER BY '.$orderCampo.' '.$orderTipo);

    # select do edita
    $objeto->set_selectEdita('SELECT descricao,
                                     simbolo,
                                     valsal,
                                     vagas,
                                     exibeSite,
                                     obs
                                FROM tbtipocomissao
                               WHERE idTipoComissao = '.$id);

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
    $objeto->set_label(array("id","Cargo","Simbolo","Valor","Vagas","Vagas Ocupadas","Ver Servidores","Vagas Não Ocupadas","Exibe no Site?"));
    $objeto->set_width(array(5,20,10,10,10,10,10,10,10));
    $objeto->set_align(array("center"));

    $objeto->set_classe(array(null,null,null,null,null,'pessoal',null,'pessoal'));
    $objeto->set_metodo(array(null,null,null,null,null,'get_servidoresCargoComissao',null,'get_cargoComissaoVagasDisponiveis'));

    # Botão de exibição dos servidores
    $botao = new BotaoGrafico();
    $botao->set_label('');
    #$botao->set_title('Servidores com permissão a essa regra');
    $botao->set_url('?fase=listaServidores&id=');       
    $botao->set_image(PASTA_FIGURAS_GERAIS.'ver.png',20,20);

    # Coloca o objeto link na tabela			
    $objeto->set_link(array("","","","","","",$botao));

    # Classe do banco de dados
    $objeto->set_classBd('Pessoal');

    # Nome da tabela
    $objeto->set_tabela('tbtipocomissao');

    # Nome do campo id
    $objeto->set_idCampo('idTipoComissao');

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
               'nome' => 'descricao',
               'label' => 'Cargo em Comissão:',
               'tipo' => 'texto',
               'autofocus' => true,
               'size' => 30),
         array ('linha' => 2,
               'nome' => 'simbolo',
               'label' => 'Símbolo:',
               'tipo' => 'texto',
               'size' => 10),
        array ('linha' => 2,
               'nome' => 'valsal',
               'label' => 'Valor do Salário:',
               'tipo' => 'texto',
               'size' => 10),
        array ('linha' => 2,
               'nome' => 'vagas',
               'label' => 'Vagas desse cargo na fundação:',
               'tipo' => 'texto',
               'size' => 10),
        array ('linha' => 2,
               'nome' => 'exibeSite',
               'label' => 'Servidores com esse cargo é exibido no site:',
               'tipo' => 'checkbox',
               'size' => 5),    
        array ('linha' => 4,
               'nome' => 'obs',
               'label' => 'Observação:',
               'tipo' => 'textarea',
               'size' => array(80,6))));

    # Matrícula para o Log
    $objeto->set_matricula($matricula);

    ################################################################
    switch ($fase)
    {
        case "" :
        case "listar" :
            $objeto->listar();
            break;

        case "editar" :
            $objeto->editar($id);        
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
            titulo('Servidores da Lotação: '.$servidor->get_nomelotacao($id));

            $select ='SELECT distinct tbfuncionario.matricula, 
                             tbpessoa.nome,
                             tbcomissao.descricao,
                             tbcargo.nome,
                             tbfuncionario.matricula,
                             tbperfil.nome
                        FROM tbfuncionario LEFT JOIN tbpessoa ON (tbfuncionario.idPessoa = tbpessoa.idPessoa)                                               
                                           LEFT JOIN tbperfil ON (tbfuncionario.idPerfil = tbperfil.idPerfil)
                                           LEFT JOIN tbcargo ON (tbfuncionario.idCargo = tbcargo.idCargo)
                                           LEFT JOIN tbcomissao ON(tbfuncionario.matricula = tbcomissao.matricula)
                                                JOIN tbtipocomissao ON(tbcomissao.idTipoComissao=tbtipocomissao.idTipoComissao)
                       WHERE tbfuncionario.sit = 1
                         AND tbcomissao.dtExo is NULL
                         AND tbcomissao.idTipoComissao = '.$id.'
                    ORDER BY comissao, tbpessoa.nome';

            # Conecta com o banco de dados
            $servidor = new Pessoal();
            $result = $servidor->select($select);
            $contador = $servidor->count($select); 

            # Parametros da tabela
            $label = array('Matricula','Nome','Descrição','Cargo','Lotação','Perfil');
            $width = array(10,20,20,15,20,15);	
            $align = array("center","left");
            $funcao = array("dv");
            $classe = array("","","","","Pessoal");
            $metodo = array("","","","","get_lotacao");

            # Monta a tabela
            $tabela = new Tabela();
            $tabela->set_conteudo($result);
            $tabela->set_cabecalho($label,$width,$align);
            $tabela->set_funcao($funcao);
            $tabela->set_classe($classe);
            $tabela->set_metodo($metodo);
            #$tabela->set_titulo($titulo);

            $tabela->show();
            
            $grid->fechaColuna();
            $grid->fechaGrid();
            break;
    }
    $page->terminaPagina();
}