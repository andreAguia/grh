<?php
/**
 * Cadastro de Classes e Padrões (Tabela Salarial)
 *  
 * By Alat
 */

# Reservado para o servidor logado
$idusuario = null;

# Configuração
include ("_config.php");

# Permissão de Acesso
$acesso = Verifica::acesso($idusuario,2);

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
    $objeto->set_nome('Tabela Salarial');

    # botão salvar
    $objeto->set_botaoSalvarGrafico(false);

    # bot?o de voltar da lista
    $objeto->set_voltarLista('grh.php');

    # controle de pesquisa
    $objeto->set_parametroLabel('Pesquisar');
    $objeto->set_parametroValue($parametro);

    # ordenaç?o
    if(is_null($orderCampo))
            $orderCampo = "2 desc, 3 desc, 4";

    if(is_null($orderTipo))
            $orderTipo = 'asc';

    # select da lista
    $objeto->set_selectLista ('SELECT idClasse,
                                      tbplano.numDecreto,
                                      nivel,
                                      faixa,
                                      valor,                                 
                                      idClasse
                                 FROM tbclasse JOIN tbplano USING (idPlano)
                                WHERE nivel LIKE "%'.$parametro.'%"
                             ORDER BY '.$orderCampo.' '.$orderTipo);

    # select do edita
    $objeto->set_selectEdita('SELECT nivel,
                                      faixa,
                                      valor,
                                      idPlano
                                FROM tbclasse
                               WHERE idClasse = '.$id);

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
    $objeto->set_label(array("id","Plano","Nível","Faixa","Valor"));
    $objeto->set_width(array(5,22,22,22,22));
    $objeto->set_align(array("center"));
    $objeto->set_function(array(null,null,null,null,"formataMoeda"));

    $planoAtual = $pessoal->get_numDecretoPlanoAtual();

    $objeto->set_formatacaoCondicional(array(array('coluna' => 1,
                                                   'valor' => $planoAtual,
                                                   'operador' => '<>',
                                                   'id' => 'inativo')));
    # Classe do banco de dados
    $objeto->set_classBd('Pessoal');

    # Nome da tabela
    $objeto->set_tabela('tbclasse');

    # Nome do campo id
    $objeto->set_idCampo('idClasse');

    # Tipo de label do formulário
    $objeto->set_formlabelTipo(1);

    # Pega os dados da combo de Plano e Cargos
    $tabela = new Pessoal();
    $result = $tabela->select('SELECT idPlano, 
                                      numDecreto
                                  FROM tbplano
                              ORDER BY idPlano desc');

    # Campos para o formulario
    $objeto->set_campos(array(
        array ('linha' => 1,
               'nome' => 'idPlano',
               'label' => 'Plano de Cargos:',
               'tipo' => 'combo',
               'array' => $result,
               'required' => true,
               'autofocus' => true,
               'size' => 20),    
         array ('linha' => 1,
                'nome' => 'nivel',
               'label' => 'Nível:',
               'tipo' => 'combo',
               'array' => array("Superior","Médio","Fundamental","Elementar"),
               'required' => true,
               'size' => 20),
        array ('linha' => 1,
               'nome' => 'faixa',
               'label' => 'Faixa:',
               'tipo' => 'texto',
	       'required' => true,
               'size' => 20),    
         array ('linha' => 1,
               'nome' => 'valor',
               'label' => 'Valor:',
               'tipo' => 'moeda',
	       'required' => true,
               'size' => 10)));

    # Matrícula para o Log
    $objeto->set_idusuario($idusuario);

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