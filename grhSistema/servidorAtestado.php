<?php
/**
 * Histórico de Atestados do Servidor
 *  
 * By Alat
 */

# Inicia as variáveis que receberão as sessions
$matricula = null;		  # Reservado para a matrícula do servidor logado
$matriculaGrh = null;		  # Reservado para a matrícula pesquisada

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

    # Exibe os dados do Servidor
    $objeto->set_rotinaExtra("get_DadosServidor");
    $objeto->set_rotinaExtraParametro($matriculaGrh); 

    # Nome do Modelo (aparecerá nos fildset e no caption da tabela)
    $objeto->set_nome('Cadastro de Atestados Médicos do Servidor');

    # botão de voltar da lista
    $objeto->set_voltarLista('servidorMenu.php');

    # ordenação
    if(is_null($orderCampo))
        $orderCampo = "1";

    if(is_null($orderTipo))
        $orderTipo = 'desc';

    # select da lista
    $objeto->set_selectLista('SELECT dtInicio,
                                     numDias,
                                     ADDDATE(dtInicio,numDias-1),
                                     nome_medico,
                                     especi_medico,
                                     tipo,
                                     tbparentesco.Parentesco,
                                     idAtestado
                                FROM tbatestado LEFT JOIN tbparentesco ON (tbatestado.parentesco = tbparentesco.idParentesco)
                               WHERE matricula = '.$matriculaGrh.'
                            ORDER BY '.$orderCampo.' '.$orderTipo);

    # select do edita
    $objeto->set_selectEdita('SELECT dtInicio,
                                     numDias,
                                     tipo,
                                     parentesco,
                                     nome_medico,
                                     especi_medico,                                     
                                     obs,
                                     matricula
                                FROM tbatestado
                               WHERE idAtestado = '.$id);

    # ordem da lista
    $objeto->set_orderCampo($orderCampo);
    $objeto->set_orderTipo($orderTipo);
    $objeto->set_orderChamador('?fase=listar');

    # botão salvar
    $objeto->set_botaoSalvarGrafico(false);

    # Caminhos
    $objeto->set_linkEditar('?fase=editar');
    $objeto->set_linkExcluir('?fase=excluir');
    $objeto->set_linkGravar('?fase=gravar');
    $objeto->set_linkListar('?fase=listar');

    # Parametros da tabela
    $objeto->set_label(array("Data Inicial","Dias","Data Término","Médico","Especialidade","Tipo","Parentesco"));
    $objeto->set_width(array(10,10,10,20,20,10,10));	
    $objeto->set_align(array("center"));
    $objeto->set_function(array ("date_to_php",null,"date_to_php"));

    # Classe do banco de dados
    $objeto->set_classBd('pessoal');

    # Nome da tabela
    $objeto->set_tabela('tbatestado');

    # Nome do campo id
    $objeto->set_idCampo('idAtestado');

    # Tipo de label do formulário
    $objeto->set_formLabelTipo(1);

    # Pega os dados da combo parentesco
    $lista = new Pessoal();
    $result = $lista->select('SELECT idParentesco, 
                                     Parentesco
                                FROM tbparentesco
                            ORDER BY parentesco');
    array_push($result, array(0,null)); # Adiciona o valor de nulo

    # Campos para o formulario
    $objeto->set_campos(array( array ( 'nome' => 'dtInicio',
                                       'label' => 'Data Inicial:',
                                       'tipo' => 'data',
                                       'size' => 20,
                                       'required' => true,
                                       'autofocus' => true,
                                       'title' => 'Data inícial do atestado.',
                                       'col' => 3,
                                       'linha' => 1),
                               array ( 'nome' => 'numDias',
                                       'label' => 'Dias:',
                                       'tipo' => 'numero',
                                       'size' => 5,
                                       'col' => 2,
                                       'required' => true,
                                       'title' => 'Quantidade de dias do atestado.',
                                       'linha' => 1),
                               array ( 'nome' => 'tipo',
                                       'label' => 'Tipo:',
                                       'tipo' => 'combo',
                                       'array' => array ("Próprio","Acompanhante"),
                                       'size' => 20,                               
                                       'title' => 'tipo de atestado',
                                       'col' => 3,
                                       'linha' => 1), 
                               array ( 'nome' => 'parentesco',
                                       'label' => 'Parentesco:',
                                       'tipo' => 'combo',
                                       'array' => $result,
                                       'size' => 20,                               
                                       'title' => 'Parentesco',
                                       'col' => 4,
                                       'linha' => 1), 
                               array ( 'nome' => 'nome_medico',
                                       'label' => 'Nome do Médico:',
                                       'tipo' => 'texto',
                                       'size' => 80,
                                       'col' => 6,
                                       'required' => true,
                                       'title' => 'Nome do Médico.',
                                       'linha' => 2),
                               array ( 'nome' => 'especi_medico',
                                       'label' => 'Especialidade:',
                                       'tipo' => 'texto',
                                       'size' => 80,
                                       'col' => 6,
                                       'required' => true,
                                       'title' => 'Especialidade do Médico.',
                                       'linha' => 2),                               
                                array ('linha' => 5,
                                       'nome' => 'obs',
                                       'label' => 'Observação:',
                                       'tipo' => 'textarea',
                                       'size' => array(80,5)),
                               array ( 'nome' => 'matricula',
                                       'label' => 'Matrícula:',
                                       'tipo' => 'hidden',
                                       'padrao' => $matriculaGrh,
                                       'size' => 5,
                                       'title' => 'Matrícula',
                                       'linha' => 6)));

    # Matrícula para o Log
    $objeto->set_idusuario($idusuario);

    # Paginação
    #$objeto->set_paginacao(true);
    #$objeto->set_paginacaoInicial($paginacao);
    #$objeto->set_paginacaoItens(20);


    ################################################################

    switch ($fase)
    {
        case "" :
        case "listar" :
        case "editar" :			
        case "excluir" :
            $objeto->$fase($id); 
            break;

        case "gravar" :
            $objeto->gravar($id,'servidorAtestadoExtra.php');              
            break;

    }									 	 		

    $page->terminaPagina();
}
