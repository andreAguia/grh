<?php
/**
 * Controle do Abono de Permanencia
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
    $intra = new Intra();
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
    $objeto->set_nome('Cadastro de Abono Permanencia');

    # botão de voltar da lista
    $objeto->set_voltarLista('servidorMenu.php');

    # select da lista
    $objeto->set_selectLista('SELECT processo,
                                     dtPublicacao,
                                     if(status = 1,"Deferido","Indeferido"),
                                     data,
                                     idServidor,
                                     idAbono
                                FROM tbabono
                               WHERE idServidor = '.$idServidorPesquisado.'
                            ORDER BY dtPublicacao desc');

    # select do edita
    $objeto->set_selectEdita('SELECT processo,
                                     dtPublicacao,
                                     status,
                                     data,
                                     idServidor
                                FROM tbabono
                               WHERE idAbono = '.$id);

    # Caminhos
    $objeto->set_linkEditar('?fase=editar');
    $objeto->set_linkExcluir('?fase=excluir');
    $objeto->set_linkGravar('?fase=gravar');
    $objeto->set_linkListar('?fase=listar');

    # Parametros da tabela
    $objeto->set_label(array("Processo","Publicaçao","Status","Data"));
    #$objeto->set_width(array(10,10,10,20,20,10,10));	
    $objeto->set_align(array("center"));
    $objeto->set_funcao(array (NULL,"date_to_php",NULL,"date_to_php"));

    # Classe do banco de dados
    $objeto->set_classBd('pessoal');

    # Nome da tabela
    $objeto->set_tabela('tbabono');

    # Nome do campo id
    $objeto->set_idCampo('idAbono');

    # Tipo de label do formulário
    $objeto->set_formLabelTipo(1);

    # Campos para o formulario
    $objeto->set_campos(array( array ( 'nome' => 'processo',
                                       'label' => 'Processo:',
                                       'tipo' => 'processo',
                                       'size' => 30,
                                       'required' => TRUE,
                                       'autofocus' => TRUE,
                                       'title' => 'O numero do processo.',
                                       'col' => 3,
                                       'linha' => 1),
                               array ( 'nome' => 'dtPublicacao',
                                       'label' => 'Publicaçao:',
                                       'tipo' => 'data',
                                       'size' => 10,
                                       'col' => 3,
                                       'required' => TRUE,
                                       'title' => 'A data da publicaçao no DOERJ.',
                                       'linha' => 1),
                               array ( 'nome' => 'status',
                                       'label' => 'Status:',
                                       'tipo' => 'combo',
                                       'array' => array(array(NULL,""),array(1,"Deferido"),array(2,"Indeferido")),
                                       'size' => 20,                               
                                       'title' => 'Se o processo foi deferido ou indeferido',
                                       'col' => 3,
                                       'required' => TRUE,
                                       'linha' => 1), 
                               array ( 'nome' => 'data',
                                       'label' => 'Data:',
                                       'tipo' => 'data',
                                       'size' => 10,
                                       'col' => 3,
                                       'title' => 'A data em que o servidor passou a receber.',
                                       'linha' => 1),
                               array ( 'nome' => 'idServidor',
                                       'label' => 'idServidor',
                                       'tipo' => 'hidden',
                                       'padrao' => $idServidorPesquisado,
                                       'size' => 5,
                                       'title' => 'Matrícula',
                                       'linha' => 6)));
    
    # Alterar Senha
    $botao1 = new Link("Site da GRH");
    $botao1->set_class('button');
    $botao1->set_title("Pagina no site da GRH sobre Abono Permanencia");
    $botao1->set_onClick("window.open('http://uenf.br/dga/grh/gerencia-de-recursos-humanos/abono-de-permanencia/','_blank','menubar=no,scrollbars=yes,location=no,directories=no,status=no,width=1000,height=600');");
    
    $objeto->set_botaoListarExtra(array($botao1));
    
    # Log
    $objeto->set_idUsuario($idUsuario);
    $objeto->set_idServidorPesquisado($idServidorPesquisado);

    ################################################################

    switch ($fase){
        case "" :
        case "listar" :
        case "editar" :			
        case "excluir" :
            $objeto->$fase($id); 
            break;

        case "gravar" :
            $objeto->gravar($id,"servidorAbonoExtra.php");              
            break;

    }									 	 		

    $page->terminaPagina();
}else{
    loadPage("../../areaServidor/sistema/login.php");
}