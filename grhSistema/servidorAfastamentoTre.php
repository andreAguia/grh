<?php
/**
 * Histórico de Afastamentos para Serviço Eleitoral (TRE)
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
    $objeto->set_nome('Cadastro de afastamentos para prestar serviço ao TRE');

    # botão de voltar da lista
    $objeto->set_voltarLista('servidorMenu.php');

    # select da lista
    $objeto->set_selectLista('SELECT data,                                    
                                     dias,
                                     ADDDATE(data,dias-1),
                                     folgas,
                                     descricao,
                                     documento,
                                     idTrabalhoTre
                                FROM tbtrabalhotre
                          WHERE idServidor='.$idServidorPesquisado.'
                       ORDER BY data desc');

    # select do edita
    $objeto->set_selectEdita('SELECT data,
                                     dias,
                                     folgas,
                                     documento,
                                     descricao,
                                     idServidor
                                FROM tbtrabalhotre
                               WHERE idTrabalhoTre = '.$id);

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
    $objeto->set_label(array("Data Inícial do Trabalho","Dias Trabalhados","Data de Término do Trabalho","Folgas Concedidas","Descrição do Trabalho Efetuado","Documento"));
    $objeto->set_width(array(10,10,10,10,30,20));	
    $objeto->set_align(array('center','center','center','center','left','left'));
    $objeto->set_funcao(array ("date_to_php",NULL,"date_to_php"));

    # Classe do banco de dados
    $objeto->set_classBd('pessoal');

    # Nome da tabela
    $objeto->set_tabela('tbtrabalhotre');

    # Nome do campo id
    $objeto->set_idCampo('idTrabalhoTre');

    # Tipo de label do formulário
    $objeto->set_formLabelTipo(1);

    # Campos para o formulario
    $objeto->set_campos(array( array ( 'nome' => 'data',
                                       'label' => 'Data Inicial do Trabalho no TRE:',
                                       'tipo' => 'data',
                                       'size' => 20,                                
                                       'required' => TRUE,
                                       'autofocus' => TRUE,
                                       'title' => 'Data Inicial do trabalho no TRE.',
                                       'col' => 3,
                                       'linha' => 1),
                               array ( 'nome' => 'dias',
                                       'label' => 'Dias Trabalhados:',
                                       'tipo' => 'numero',
                                       'size' => 5,
                                       'col' => 2,
                                       'required' => TRUE,
                                       'title' => 'Quantidade em dias trabalhados.',
                                       'linha' => 1),
                               array ( 'nome' => 'folgas',
                                       'label' => 'Dias de folgas concedidas:',
                                       'tipo' => 'numero',
                                       'size' => 5,
                                       'col' => 3,
                                       'required' => TRUE,
                                       'title' => 'Quantidade (em dias) de folgas concedidas.',
                                       'linha' => 1), 
                               array ( 'nome' => 'documento',
                                       'label' => 'Documento:',
                                       'tipo' => 'texto',
                                       'size' => 50,                                   
                                       'title' => 'Documento',
                                       'col' => 4,
                                       'linha' => 1),
                               array ( 'nome' => 'descricao',
                                       'label' => 'Descrição do Trabalho Efetuado:',
                                       'tipo' => 'textarea',
                                       'required' => TRUE,
                                       'size' => array(80,5),                                                                 
                                       'title' => 'Descrição do Trabalho Efetuado',
                                       'col' => 12,
                                       'linha' => 2),                               
                               array ( 'nome' => 'idServidor',
                                       'label' => 'idServidor:',
                                       'tipo' => 'hidden',
                                       'padrao' => $idServidorPesquisado,
                                       'size' => 5,
                                       'title' => 'Matrícula',
                                       'linha' => 4)));
    
    # Relatório
    $imagem = new Imagem(PASTA_FIGURAS.'print.png',NULL,15,15);
    $botaoRel = new Button();
    $botaoRel->set_imagem($imagem);
    $botaoRel->set_title("Imprimir Relatório");
    $botaoRel->set_onClick("window.open('../grhRelatorios/servidorTreAfastamento.php','_blank','menubar=no,scrollbars=yes,location=no,directories=no,status=no,width=750,height=600');");
    
    $objeto->set_botaoListarExtra(array($botaoRel));

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
            Grh::listaFolgasTre($idServidorPesquisado);
            $objeto->listar();
            break;
        
        case "editar" : 
            Grh::listaFolgasTre($idServidorPesquisado);
        case "excluir" :
            if(Verifica::acesso($idUsuario,6)){
                $objeto->$fase($id);
            }else{
                $objeto->listar();
            }
            break;

        case "gravar" :
             if(Verifica::acesso($idUsuario,6)){
                 $objeto->gravar($id,"servidorAfastamentoTreExtra.php"); 
            }else{
                $objeto->listar();
            }	
            break;
    }
    $page->terminaPagina();
}else{
    loadPage("../../areaServidor/sistema/login.php");
}