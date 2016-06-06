<?php
/**
 * Histórico de Afastamentos para Serviço Eleitoral (TRE)
 *  
 * By Alat
 */

# Inicia as variáveis que receberão as sessions
$matricula = null;		  # Reservado para a matrícula do servidor logado
$matriculaGrh = null;		  # Reservado para a matrícula pesquisada

# Configuração
include ("_config.php");

# Permissão de Acesso
$acesso = Verifica::acesso($matricula,13);

if($acesso)
{    
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
    $objeto->set_rotinaExtraParametro($matriculaGrh); 

    # Nome do Modelo (aparecerá nos fildset e no caption da tabela)
    $objeto->set_nome('Cadastro de Afastamentos para Serviço Eleitoral (Folgas Concedidas)');

    # botão de voltar da lista
    $objeto->set_voltarLista('servidorMenu.php');

    # select da lista
    $objeto->set_selectLista('SELECT data,                                    
                                     dias,
                                     ADDDATE(data,dias-1),
                                     folgas,
                                     descricao,
                                     documento,
                                     idfolgatre
                                FROM tbfolgatre
                          WHERE matricula='.$matriculaGrh.'
                       ORDER BY data desc');

    # select do edita
    $objeto->set_selectEdita('SELECT data,
                                     dias,
                                     folgas,
                                     documento,
                                     descricao,
                                     matricula
                                FROM tbfolgatre
                               WHERE idfolgatre = '.$id);

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
    $objeto->set_label(array("Data Inícial do Trabalho","Dias Trabalhados","Data de Término do Trabalho","Folgas Concedidas","Descrição do Trabalho Efetuado","Documento"));
    $objeto->set_width(array(10,10,10,10,30,20));	
    $objeto->set_align(array("center"));
    $objeto->set_function(array ("date_to_php",null,"date_to_php"));

    # Classe do banco de dados
    $objeto->set_classBd('pessoal');

    # Nome da tabela
    $objeto->set_tabela('tbfolgatre');

    # Nome do campo id
    $objeto->set_idCampo('idfolgatre');

    # Tipo de label do formulário
    $objeto->set_formLabelTipo(1);

    # Campos para o formulario
    $objeto->set_campos(array( array ( 'nome' => 'data',
                                       'label' => 'Data Inicial do Trabalho no TRE:',
                                       'tipo' => 'data',
                                       'size' => 20,                                
                                       'required' => true,
                                       'autofocus' => true,
                                       'title' => 'Data Inicial do trabalho no TRE.',
                                       'col' => 3,
                                       'linha' => 1),
                               array ( 'nome' => 'dias',
                                       'label' => 'Dias Trabalhados:',
                                       'tipo' => 'numero',
                                       'size' => 5,
                                       'col' => 2,
                                       'required' => true,
                                       'title' => 'Quantidade em dias trabalhados.',
                                       'linha' => 1),
                               array ( 'nome' => 'folgas',
                                       'label' => 'Dias de folgas concedidas:',
                                       'tipo' => 'numero',
                                       'size' => 5,
                                       'col' => 3,
                                       'required' => true,
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
                                       'required' => true,
                                       'size' => array(80,5),                                                                 
                                       'title' => 'Descrição do Trabalho Efetuado',
                                       'col' => 12,
                                       'linha' => 2),                               
                               array ( 'nome' => 'matricula',
                                       'label' => 'Matrícula:',
                                       'tipo' => 'hidden',
                                       'padrao' => $matriculaGrh,
                                       'size' => 5,
                                       'title' => 'Matrícula',
                                       'linha' => 4)));

    # Matrícula para o Log
    $objeto->set_matricula($matricula);

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
            Grh::listaFolgasTre($matriculaGrh);
        case "excluir" :
            $objeto->$fase($id);  
            break;

        case "gravar" :
            $objeto->gravar($id,"servidorAfastamentoTreExtra.php"); 	
            break;
    }
    $page->terminaPagina();
}
