<?php
/**
 * Cadastro de Cassão de Servidor Estatutário
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

if($acesso)
{    
    # Verifica a fase do programa
    $fase = get('fase','listar');
    
    # Conecta ao Banco de Dados
    $intra = new Intra();
    
    # Verifica se veio menu grh e registra o acesso no log
    $grh = get('grh',FALSE);
    if($grh){
        # Grava no log a atividade
        $atividade = "Cadastro do servidor - Histórico de cessão";
        $data = date("Y-m-d H:i:s");
        $intra->registraLog($idUsuario,$data,$atividade,NULL,NULL,7,$idServidorPesquisado);
    }

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
    $objeto->set_nome('Cadastro de Cessão');

    # botão de voltar da lista
    $objeto->set_voltarLista('servidorMenu.php');

    # select da lista
    $objeto->set_selectLista('SELECT dtInicio,
                                     dtFim,
                                     orgao,
                                     processo,
                                     dtPublicacao,
                                     idHistCessao
                                FROM tbhistcessao
                          WHERE idServidor='.$idServidorPesquisado.'
                       ORDER BY dtInicio desc');

    # select do edita
    $objeto->set_selectEdita('SELECT dtInicio,
                                     dtFim,
                                     processo,                                 
                                     dtPublicacao,
                                     orgao,
                                     obs,
                                     idServidor
                                FROM tbhistcessao
                               WHERE idHistCessao = '.$id);

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
    $objeto->set_label(array("Data Inicial","Data Término","Órgão Cessionário","Processo","Publicação no DOERJ"));
    $objeto->set_width(array(10,10,30,20,20));	
    $objeto->set_align(array("center"));
    $objeto->set_funcao(array ("date_to_php","date_to_php",NULL,NULL,"date_to_php"));

    # Classe do banco de dados
    $objeto->set_classBd('Pessoal');

    # Nome da tabela
    $objeto->set_tabela('tbhistcessao');

    # Nome do campo id
    $objeto->set_idCampo('idHistCessao');

    # Tipo de label do formulário
    $objeto->set_formLabelTipo(1);

    # Campos para o formulario
    $objeto->set_campos(array( array ( 'nome' => 'dtInicio',
                                       'label' => 'Data Inicial:',
                                       'tipo' => 'data',
                                       'size' => 20,
                                       'required' => TRUE,
                                       'autofocus' => TRUE,
                                       'title' => 'Data do início da cessão.',
                                       'col' => 3,
                                       'linha' => 1),
                               array ( 'nome' => 'dtFim',
                                       'label' => 'Data Final:',
                                       'tipo' => 'data',
                                       'size' => 20,
                                       'title' => 'Data do término da cessão.',
                                       'col' => 3,
                                       'linha' => 1),
                               array ( 'nome' => 'processo',
                                       'label' => 'Número do Processo de Cessão:',
                                       'tipo' => 'processo',
                                       'size' => 50,
                                       'col' => 3,
                                       'title' => 'O Número do processo de cessao',
                                       'linha' => 1),
                               array ( 'nome' => 'dtPublicacao',
                                       'label' => 'Data da Pub. no DOERJ:',
                                       'tipo' => 'data',
                                       'size' => 20,
                                       'col' => 3,
                                       'title' => 'Data da Publicação no DOERJ.',
                                       'linha' => 1),
                                array ( 'nome' => 'orgao',
                                       'label' => 'Órgão Cessionário:',
                                       'tipo' => 'texto',
                                       'required' => TRUE,
                                       'size' => 100,
                                       'col' => 12,
                                       'title' => 'O órgão cessionário',
                                       'linha' => 2),
                                array ('linha' => 3,
                                       'nome' => 'obs',
                                       'label' => 'Observação:',
                                       'tipo' => 'textarea',
                                        'col' => 12,
                                       'size' => array(80,5)),
                               array ( 'nome' => 'idServidor',
                                       'label' => 'idServidor:',
                                       'tipo' => 'hidden',
                                       'padrao' => $idServidorPesquisado,
                                       'size' => 5,
                                       'title' => 'idServidor',
                                       'linha' => 3)));
    
    # Relatório
    $imagem = new Imagem(PASTA_FIGURAS.'print.png',NULL,15,15);
    $botaoRel = new Button();
    $botaoRel->set_imagem($imagem);
    $botaoRel->set_title("Imprimir Relatório de Histórico de Cessão");
    $botaoRel->set_url("../grhRelatorios/servidorCessao.php");
    $botaoRel->set_target("_blank");
    
    $objeto->set_botaoListarExtra(array($botaoRel));
    
    # Log
    $objeto->set_idUsuario($idUsuario);
    $objeto->set_idServidorPesquisado($idServidorPesquisado);

    # Paginação
    #$objeto->set_paginacao(TRUE);
    #$objeto->set_paginacaoInicial($paginacao);
    #$objeto->set_paginacaoItens(20);


    ################################################################

    switch ($fase){
        
        case "" :
        case "listar" :
        case "editar" :			
        case "excluir" :
            $objeto->$fase($id); 
            break;

        case "gravar" :
            $objeto->gravar($id,'servidorCessaoExtra.php'); 	
            break;
    }
    $page->terminaPagina();
}else{
    loadPage("../../areaServidor/sistema/login.php");
}