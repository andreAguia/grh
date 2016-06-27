<?php
/**
 * Histórico de Lotações de um servidor
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
    # Verifica a fase do programa
    $fase = get('fase','listar');
    
    # Conecta ao Banco de Dados
    $pessoal = new Pessoal();

    # pega o id (se tiver)
    $id = soNumeros(get('id'));

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
    $objeto->set_nome('Histórico de Lotações');

    # botão de voltar da lista
    $objeto->set_voltarLista('servidorMenu.php');

    # select da lista
    $objeto->set_selectLista('SELECT data,
                                     lotacao,
                                     motivo,
                                     idHistLot
                                FROM tbhistLot
                          WHERE matricula='.$matriculaGrh.'
                       ORDER BY data desc');

    # select do edita
    $objeto->set_selectEdita('SELECT data,
                                     lotacao,
                                     motivo,
                                     matricula
                                FROM tbhistLot
                               WHERE idHistLot = '.$id);

    # ordem da lista
    #$objeto->set_orderCampo($orderCampo);
    #$objeto->set_orderTipo($orderTipo);
    #$objeto->set_orderChamador('?fase=listar');

    # botão salvar
    $objeto->set_botaoSalvarGrafico(false);

    # Caminhos
    $objeto->set_linkEditar('?fase=editar');
    $objeto->set_linkExcluir('?fase=excluir');
    $objeto->set_linkGravar('?fase=gravar');
    $objeto->set_linkListar('?fase=listar');

    # Parametros da tabela
    $objeto->set_label(array("Data","Lotação","Motivo"));
    $objeto->set_width(array(15,35,40));	
    $objeto->set_align(array("center"));
    $objeto->set_function(array ("date_to_php"));
    
    $objeto->set_classe(array (null,"pessoal"));
    $objeto->set_metodo(array (null,"get_nomelotacao"));
    

    # Classe do banco de dados
    $objeto->set_classBd('pessoal');

    # Nome da tabela
    $objeto->set_tabela('tbhistLot');

    # Nome do campo id
    $objeto->set_idCampo('idHistLot');

    # Tipo de label do formulário
    $objeto->set_formLabelTipo(1);

    # Pega os dados da combo lotacao
    $result = $pessoal->select('SELECT idlotacao, 
                                       concat(UADM,"-",DIR,"-",GER) as lotacao
                                  FROM tblotacao
                                 WHERE ativo = "Sim"
                              ORDER BY lotacao');
    array_push($result, array(null,null)); # Adiciona o valor de nulo


    # Campos para o formulario
    $objeto->set_campos(array( array ( 'nome' => 'data',
                                       'label' => 'Data:',
                                       'tipo' => 'data',
                                       'size' => 20,
                                       'maxLength' => 20,                                   
                                       'required' => true,
                                       'autofocus' => true,
                                       'col' => 3,
                                       'title' => 'Data do início da exibição da notícia.',
                                       'linha' => 1),
                               array ( 'nome' => 'lotacao',
                                       'label' => 'Lotacão:',
                                       'tipo' => 'combo',
                                       'required' => true,
                                       'array' => $result,
                                       'size' => 20,
                                       'col' => 9,
                                       'title' => 'Em qual setor o servidor está lotado',
                                       'linha' => 1),        	 
                               array ( 'nome' => 'motivo',
                                       'label' => 'Motivo:',
                                       'tipo' => 'texto',
                                       'size' => 50,
                                       'col' => 6,                                   
                                       'title' => 'Motivo da mudança de lotação.',
                                       'linha' => 2),
                               array ( 'nome' => 'matricula',
                                       'label' => 'Matrícula:',
                                       'tipo' => 'hidden',
                                       'padrao' => $matriculaGrh,
                                       'size' => 5,
                                       'title' => 'Matrícula',
                                       'linha' => 4)));

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
                $objeto->gravar($id,'servidorLotacaoExtra.php'); 	
                break;
    }									 	 		

    $page->terminaPagina();
}
?>
