<?php
/**
 * Cadastro de Suspensões de Servidor
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
    $objeto->set_rotinaExtraParametro($idServidorPesquisado); 
    
    # Nome do Modelo (aparecerá nos fildset e no caption da tabela)
    $objeto->set_nome('Cadastro de Suspensões de Servidor');

    # botão de voltar da lista
    $objeto->set_voltarLista('servidorMenu.php');

    # ordenação
    if(is_null($orderCampo))
        $orderCampo = "1";

    if(is_null($orderTipo))
        $orderTipo = 'desc';

    # select da lista
    $objeto->set_selectLista('SELECT dtInicial,                                 
                                     dias,
                                     ADDDATE(dtInicial,dias-1),
                                     processo,
                                     CONCAT(date_format(dtPublicacao,"%d/%m/%Y")," - Pag ",pgPublicacao),                                 
                                     idSuspensao
                                FROM tbsuspensao
                               WHERE idServidor = '.$idServidorPesquisado.'
                            ORDER BY '.$orderCampo.' '.$orderTipo);

    # select do edita
    $objeto->set_selectEdita('SELECT dtInicial,
                                     dias,
                                     processo,
                                     dtPublicacao,
                                     pgPublicacao,
                                     obs,
                                     idServidor
                                FROM tbaverbacao
                               WHERE idAverbacao = '.$id);
    ####### Parei Aqui /########

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
    $objeto->set_label(array("Data Inicial","Dias","Data Final","Processo","Publicação"));
    $objeto->set_width(array(20,10,20,20,30));	
    $objeto->set_align(array("center"));
    $objeto->set_function(array ("date_to_php",null,"date_to_php"));

    # Classe do banco de dados
    $objeto->set_classBd('pessoal');

    # Nome da tabela
    $objeto->set_tabela('tbsuspensao');

    # Nome do campo id
    $objeto->set_idCampo('idSuspensao');

    # Tipo de label do formulário
    $objeto->set_formLabelTipo(1);

    # Campos para o formulario
    $objeto->set_campos(array( array ( 'nome' => 'dtInicial',
                                       'label' => 'Data Inicial:',
                                       'tipo' => 'data',
                                       'notNull' => true,
                                       'size' => 20,
                                       'required' => true,
                                       'autofocus' => true,
                                       'title' => 'Data inícial da Suspensão.',
                                       'col' => 3,
                                       'linha' => 1),
                               array ( 'nome' => 'dias',
                                       'label' => 'Dias:',
                                       'tipo' => 'numero',
                                       'required' => true,
                                       'size' => 5,
                                       'col' => 2,
                                       'title' => 'Quantidade de Dias da Suspensão.',
                                       'linha' => 1),
                               array ( 'nome' => 'processo',
                                       'label' => 'Processo:',
                                       'tipo' => 'processo',
                                       'required' => true,
                                       'size' => 30,
                                       'col' => 5,
                                       'title' => 'Número do Processo',
                                       'linha' => 2),
                               array ( 'nome' => 'dtPublicacao',
                                       'label' => 'Data da Pub. no DOERJ:',
                                       'tipo' => 'data',
                                       'required' => true,
                                       'size' => 20,
                                       'col' => 3,
                                       'title' => 'Data da Publicação no DOERJ.',
                                       'linha' => 2),
                               array ( 'nome' => 'pgPublicacao',
                                       'label' => 'Pág:',
                                       'tipo' => 'texto',
                                       'required' => true,
                                       'size' => 5,
                                       'col' => 3,
                                       'title' => 'A Página do DOERJ',
                                       'linha' => 2),
                                array ('linha' => 4,
                                       'nome' => 'obs',
                                       'label' => 'Observação:',
                                       'tipo' => 'textarea',
                                       'size' => array(80,5)),
                               array ( 'nome' => 'idServidor',
                                       'label' => 'idServidor:',
                                       'tipo' => 'hidden',
                                       'padrao' => $idServidorPesquisado,
                                       'size' => 5,
                                       'title' => 'Matrícula',
                                       'linha' => 5)));


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
            $objeto->$fase($id,"servidorSuspensaoExtra.php");
            break;
    }
    $page->terminaPagina();
}else{
    loadPage("../../areaServidor/sistema/login.php");
}
