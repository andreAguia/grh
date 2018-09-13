<?php
/**
 * Controle de Readaptaçao do Servidor
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
    $objeto->set_nome('Cadastro de Readaptação do Servidor');

    # botão de voltar da lista
    $objeto->set_voltarLista('servidorMenu.php');

    # select da lista
    $objeto->set_selectLista('SELECT tipo,
                                     processo,
                                     dtProcesso,
                                     dtPericia,
                                     dtInicial,
                                     ADDDATE(dtInicial,INTERVAL anos-1 YEAR),
                                     anos,
                                     obs,
                                     idReadaptacao
                                FROM tbreadaptacao
                               WHERE idServidor = '.$idServidorPesquisado);

    # select do edita
    $objeto->set_selectEdita('SELECT tipo,
                                     processo,
                                     dtProcesso,
                                     dtPericia,
                                     dtInicial,
                                     anos,
                                     obs,
                                     idServidor
                                FROM tbreadaptacao
                               WHERE idReadaptacao = '.$id);

    # Caminhos
    $objeto->set_linkEditar('?fase=editar');
    $objeto->set_linkExcluir('?fase=excluir');
    $objeto->set_linkGravar('?fase=gravar');
    $objeto->set_linkListar('?fase=listar');

    # Parametros da tabela
    $objeto->set_label(array("Tipo","Processo","Data do Processo","Data da Pericia","Data Inicial","Data Final","Anos"));
    #$objeto->set_width(array(20,20,20,30));	
    $objeto->set_align(array("center"));
    $objeto->set_funcao(array(NULL,NULL,"date_to_php","date_to_php","date_to_php","date_to_php"));

    # Classe do banco de dados
    $objeto->set_classBd('pessoal');

    # Nome da tabela
    $objeto->set_tabela('tbreadaptacao');

    # Nome do campo id
    $objeto->set_idCampo('idReadaptacao');

    # Tipo de label do formulário
    $objeto->set_formLabelTipo(1);

    # Campos para o formulario
    $objeto->set_campos(array( array ( 'nome' => 'tipo',
                                       'label' => 'Tipo:',
                                       'tipo' => 'combo',
                                       'array' => array(array(1,"Inicial"),array(2,"Prorrogação")),
                                       'col' => 3,
                                       'size' => 12,
                                       'required' => TRUE,
                                       'title' => 'O Tipo da solicitaçao de readaptaçao.',
                                       'linha' => 1),
                               array ( 'nome' => 'processo',
                                       'label' => 'Processo:',
                                       'tipo' => 'processo',
                                       'size' => 30,
                                       'col' => 3,
                                       'title' => 'Número do Processo',
                                       'linha' => 1),
                               array ( 'nome' => 'dtProcesso',
                                       'label' => 'Data do Processo:',
                                       'tipo' => 'data',
                                       'size' => 20,
                                       'col' => 3,
                                       'title' => 'Data do Processo.',
                                       'linha' => 1),
                               array ( 'nome' => 'dtPericia',
                                       'label' => 'Data da Pericia:',
                                       'tipo' => 'data',
                                       'size' => 20,
                                       'col' => 3,
                                       'title' => 'Data da Pericia.',
                                       'linha' => 1),
                               array ( 'nome' => 'dtInicial',
                                       'label' => 'Data Inicial:',
                                       'tipo' => 'data',
                                       'size' => 20,
                                       'col' => 3,
                                       'title' => 'Data de Inicio da readaptaçao.',
                                       'linha' => 2),
                               array ( 'nome' => 'anos',
                                       'label' => 'Anos:',
                                       'tipo' => 'processo',
                                       'size' => 5,
                                       'col' => 2,
                                       'title' => 'Quantidade de anos definidos para ser readaptado',
                                       'linha' => 2),
                                array ('linha' => 2,
                                       'col' => 12,
                                       'nome' => 'obs',
                                       'label' => 'Observação:',
                                       'tipo' => 'textarea',
                                       'size' => array(80,5)),
                               array ( 'nome' => 'idServidor',
                                       'label' => 'idServidor:',
                                       'tipo' => 'hidden',
                                       'padrao' => $idServidorPesquisado,
                                       'size' => 5,
                                       'title' => 'idServidor',
                                       'linha' => 5)));

    # Relatório
    $imagem = new Imagem(PASTA_FIGURAS.'print.png',NULL,15,15);
    $botaoRel = new Button();
    $botaoRel->set_imagem($imagem);
    $botaoRel->set_title("Imprimir Relatório de Histórico de Gratificação Especial");
    $botaoRel->set_onClick("window.open('../grhRelatorios/servidorGratificacao.php','_blank','menubar=no,scrollbars=yes,location=no,directories=no,status=no,width=750,height=600');");
    
    $objeto->set_botaoListarExtra(array($botaoRel));
    
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
            $objeto->$fase($id); 
            break;
    }
    $page->terminaPagina();
}else{
    loadPage("../../areaServidor/sistema/login.php");
}