<?php
/**
 * Histórico de Folgas
 *  
 * By Alat
 */

# Inicia as variáveis que receberão as sessions
$idUsuario = NULL;              # Servidor logado
$idServidorPesquisado = NULL;	# Servidor Editado na pesquisa do sistema do GRH

# Configuração
include ("_config.php");

# Permissão de Acesso
$acesso = Verifica::acesso($idUsuario,2);

if($acesso){    
    # Conecta ao Banco de Dados   
    $pessoal = new Pessoal();
	
    # Verifica a fase do programa
    $fase = get('fase','listar');

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
    $objeto->set_nome('Cadastro de folgas fruídas por ter prestado serviço ao TRE');

    # botão de voltar da lista
    $objeto->set_voltarLista('servidorMenu.php');

    # select da lista
    $objeto->set_selectLista('SELECT data,
                                     ADDDATE(data,dias-1),                                 
                                     dias,
                                     idFolga
                                FROM tbfolga
                          WHERE idServidor='.$idServidorPesquisado.'
                       ORDER BY data desc');

    # select do edita
    $objeto->set_selectEdita('SELECT data,
                                     dias,
                                     idServidor
                                FROM tbfolga
                               WHERE idFolga = '.$id);

    # ordem da lista
    #$objeto->set_orderCampo($orderCampo);
    #$objeto->set_orderTipo($orderTipo);
    #$objeto->set_orderChamador('?fase=listar');

    # Caminhos
    $objeto->set_linkEditar('?fase=editar');
    $objeto->set_linkExcluir('?fase=excluir');
    $objeto->set_linkGravar('?fase=gravar');
    $objeto->set_linkListar('?fase=listar');

    # Parametros da tabela
    $objeto->set_label(array("Data do Início da Folga","Data do Término da Folga","Dias Folgados"));
    $objeto->set_width(array(30,30,30));	
    $objeto->set_align(array("center"));
    $objeto->set_funcao(array ("date_to_php","date_to_php",NULL));

    # Classe do banco de dados
    $objeto->set_classBd('pessoal');

    # Nome da tabela
    $objeto->set_tabela('tbfolga');

    # Nome do campo id
    $objeto->set_idCampo('idFolga');

    # Tipo de label do formulário
    $objeto->set_formLabelTipo(1);
    # Campos para o formulario
    $objeto->set_campos(array( array ( 'nome' => 'data',
                                       'label' => 'Data do Início da Folga:',
                                       'tipo' => 'data',
                                       'size' => 20,                                
                                       'required' => TRUE,
                                       'autofocus' => TRUE,
                                       'title' => 'Data da Fola ou do início da folga.',
                                       'col' => 3,
                                       'linha' => 1),
                               array ( 'nome' => 'dias',
                                       'label' => 'Dias:',
                                       'tipo' => 'numero',
                                       'size' => 5,
                                       'col' => 3,
                                       'required' => TRUE,
                                       'title' => 'Quantidade de dias folgados.',
                                       'linha' => 1),
                               array ( 'nome' => 'idServidor',
                                       'label' => 'idServidor:',
                                       'tipo' => 'hidden',
                                       'padrao' => $idServidorPesquisado,
                                       'size' => 5,
                                       'title' => 'Matrícula',
                                       'linha' => 4)));

    # Log
    $objeto->set_idUsuario($idUsuario);
    $objeto->set_idServidorPesquisado($idServidorPesquisado);
    
    # Libera Inclusao, ediçao e exclusao somente para servidores autorizados na regra 6
    if(Verifica::acesso($idUsuario,6)){
        $objeto->set_botaoIncluir(TRUE);
        $objeto->set_botaoEditar(TRUE);
        $objeto->set_botaoExcluir(TRUE);
    }else{
        $objeto->set_botaoIncluir(FALSE);
        $objeto->set_botaoEditar(FALSE);
        $objeto->set_botaoExcluir(FALSE);
    }
    
    ################################################################

    switch ($fase){
        case "" :
        case "listar" :
        case "editar" :
            Grh::listaFolgasTre($idServidorPesquisado);
        case "excluir" :
            $objeto->$fase($id);  
            break;

        case "gravar" :
            $objeto->gravar($id,"servidorFolgaExtra.php"); 	
            break;
    }									 	 		

    $page->terminaPagina();
}else{
    loadPage("../../areaServidor/sistema/login.php");
}