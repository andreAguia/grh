<?php
/**
 * Cadastro de Plano de Cargos e Salários
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
    $objeto->set_nome('Plano de Cargos & Salários');

    # botão salvar
    $objeto->set_botaoSalvarGrafico(false);

    # bot?o de voltar da lista
    $objeto->set_voltarLista('grh.php');

    # controle de pesquisa
    $objeto->set_parametroLabel('Pesquisar');
    $objeto->set_parametroValue($parametro);

    # ordenaç?o
    if(is_null($orderCampo))
            $orderCampo = "planoAtual desc,";

    if(is_null($orderTipo))
            $orderTipo = 'idPlano desc';

    # select da lista
    $objeto->set_selectLista ('SELECT idPlano,numDecreto,
                                      dtDecreto,
                                      dtPublicacao,
                                      pgPublicacao,
                                      CASE planoAtual                                        
                                            WHEN 1 THEN "Atual"
                                            ELSE "Antigo"
                                       end,                                  
                                      idPlano
                                 FROM tbplano
                                WHERE numDecreto LIKE "%'.$parametro.'%"
                             ORDER BY '.$orderCampo.' '.$orderTipo);

    # select do edita
    $objeto->set_selectEdita('SELECT numDecreto,
                                     dtDecreto,
                                     dtPublicacao,
                                     pgPublicacao,
                                     planoAtual,
                                     obs
                                FROM tbplano
                               WHERE idPlano = '.$id);

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
    $objeto->set_label(array("id","Decreto / Lei","Data do Decreto / Lei","Publicação no DOERJ","Página no DOERJ","Plano Atual"));
    $objeto->set_width(array(10,25,20,20,10,10));
    $objeto->set_align(array("center"));
    $objeto->set_function(array (null,null,"date_to_php","date_to_php"));

    $objeto->set_formatacaoCondicional(array(
                                             array('coluna' => 5,
                                                   'valor' => "Antigo",
                                                   'operador' => '=',
                                                   'id' => 'inativo')));

    # Classe do banco de dados
    $objeto->set_classBd('Pessoal');

    # Nome da tabela
    $objeto->set_tabela('tbplano');

    # Nome do campo id
    $objeto->set_idCampo('idPlano');

    # Tipo de label do formulário
    $objeto->set_formlabelTipo(1);

    # Campos para o formulario
    $objeto->set_campos(array(
        array ('linha' => 1,
               'nome' => 'numDecreto',
               'label' => 'Decreto ou Lei:',
               'title' => 'Número do Decreto',
               'tipo' => 'texto',
               'required' => true,
               'autofocus' => true,
               'size' => 30),
         array ('linha' => 1,
               'nome' => 'dtDecreto',
               'label' => 'Data do Decreto:',
               'title' => 'Data do decreto',
               'tipo' => 'data',
               'required' => true,
               'size' => 15),
        array ('linha' => 1,
               'nome' => 'dtPublicacao',
               'label' => 'Data da Publicação:',
               'title' => 'Data da Publicação no DOERJ',
               'tipo' => 'data',
               'required' => true,
               'size' => 15),
         array ('linha' => 1,
               'nome' => 'pgPublicacao',
               'label' => 'Página:',
               'tipo' => 'texto',
               'size' => 10),
        array ('linha' => 1,
               'nome' => 'planoAtual',
               'label' => 'Plano atual:',
               'title' => 'Se é o Plano de Cargos atualmente ativo',
               'tipo' => 'combo',
               'array' => array(array('1','Sim'),array(null,'Não')),
               'padrao' => 'Sim',
               'size' => 10),   
        array ('linha' => 3,
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
        case "excluir" :	
        case "gravar" :
            $objeto->$fase($id);
            break;
    }									 	 		

    $page->terminaPagina();
}
?>