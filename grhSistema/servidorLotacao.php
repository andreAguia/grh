<?php
/**
 * Histórico de Lotações de um servidor
 *  
 * By Alat
 */

# Inicia as variáveis que receberão as sessions
$idUsuario = null;              # Servidor logado
$idServidorPesquisado = null;	# Servidor Editado na pesquisa do sistema do GRH

# Configuração
include ("_config.php");

# Permissão de Acesso
$acesso = Verifica::acesso($idUsuario,2);

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
    $objeto->set_rotinaExtraParametro($idServidorPesquisado); 

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
                          WHERE idServidor='.$idServidorPesquisado.'
                       ORDER BY data desc');

    # select do edita
    $objeto->set_selectEdita('SELECT data,
                                     lotacao,
                                     motivo,
                                     idServidor
                                FROM tbhistLot
                               WHERE idHistLot = '.$id);

    # ordem da lista
    #$objeto->set_orderCampo($orderCampo);
    #$objeto->set_orderTipo($orderTipo);
    #$objeto->set_orderChamador('?fase=listar');

    # botão salvar
    #$objeto->set_botaoSalvarGrafico(false);

    # Caminhos
    $objeto->set_linkEditar('?fase=editar');
    $objeto->set_linkExcluir('?fase=excluir');
    $objeto->set_linkGravar('?fase=gravar');
    $objeto->set_linkListar('?fase=listar');

    # Parametros da tabela
    $objeto->set_label(array("Data","Lotação","Motivo"));
    $objeto->set_width(array(10,30,50));	
    $objeto->set_align(array("center","left","left"));
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
    $selectLotacao = 'SELECT idlotacao, 
                             concat(IFNULL(tblotacao.UADM,"")," - ",IFNULL(tblotacao.DIR,"")," - ",IFNULL(tblotacao.GER,"")," - ",IFNULL(tblotacao.nome,"")) as lotacao
                        FROM tblotacao';
    
    if(is_null($id)){
        $selectLotacao .= ' WHERE ativo = "Sim"';
    }
                       
    $selectLotacao .= ' ORDER BY lotacao';
    
    $result = $pessoal->select($selectLotacao);
    array_unshift($result, array(null,null)); # Adiciona o valor de nulo

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
                                       'size' => 100,
                                       'col' => 12,                                   
                                       'title' => 'Motivo da mudança de lotação.',
                                       'linha' => 2),
                               array ( 'nome' => 'idServidor',
                                       'label' => 'idServidor:',
                                       'tipo' => 'hidden',
                                       'padrao' => $idServidorPesquisado,
                                       'size' => 5,
                                       'title' => 'idServidor',
                                       'linha' => 4)));

    # Log
    $objeto->set_idUsuario($idUsuario);
    $objeto->set_idServidorPesquisado($idServidorPesquisado);

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
}else{
    loadPage("../../areaServidor/sistema/login.php");
}