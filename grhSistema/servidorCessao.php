<?php
/**
 * Cadastro de Cassão de Servidor Estatutário
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
    $objeto->set_nome('Cadastro de Cessão');

    # botão de voltar da lista
    $objeto->set_voltarLista('servidorMenu.php');

    # select da lista
    $objeto->set_selectLista('SELECT dtInicio,
                                     dtFim,
                                     orgao,
                                     processo,
                                     CONCAT(date_format(dtPublicacao,"%d/%m/%Y")," - Pag ",pgPublicacao),
                                     idHistCessao
                                FROM tbhistcessao
                          WHERE matricula='.$matriculaGrh.'
                       ORDER BY dtInicio desc');

    # select do edita
    $objeto->set_selectEdita('SELECT dtInicio,
                                     dtFim,
                                     orgao,
                                     processo,                                 
                                     dtPublicacao,
                                     pgPublicacao,
                                     obs,
                                     matricula
                                FROM tbhistcessao
                               WHERE idHistCessao = '.$id);

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
    $objeto->set_label(array("Data Inicial","Data Término","Órgão Cessionário","Processo","Publicação no DOERJ"));
    $objeto->set_width(array(10,10,30,20,20));	
    $objeto->set_align(array("center"));
    $objeto->set_function(array ("date_to_php","date_to_php"));

    # Classe do banco de dados
    $objeto->set_classBd('pessoal');

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
                                       'required' => true,
                                       'autofocus' => true,
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
                               array ( 'nome' => 'orgao',
                                       'label' => 'Órgão Cessionário:',
                                       'tipo' => 'texto',
                                       'required' => true,
                                       'size' => 50,
                                       'col' => 6,
                                       'title' => 'O órgão cessionário',
                                       'linha' => 1),
                               array ( 'nome' => 'processo',
                                       'label' => 'Número do Processo de Cessão:',
                                       'tipo' => 'texto',
                                       'size' => 50,
                                       'col' => 6,
                                       'title' => 'O órgão cessionário',
                                       'linha' => 2),
                               array ( 'nome' => 'dtPublicacao',
                                       'label' => 'Data da Pub. no DOERJ:',
                                       'tipo' => 'data',
                                       'size' => 20,
                                       'col' => 3,
                                       'title' => 'Data da Publicação no DOERJ.',
                                       'linha' => 2),
                               array ( 'nome' => 'pgPublicacao',
                                       'label' => 'Pág:',
                                       'tipo' => 'texto',
                                       'size' => 5,
                                       'col' => 3,
                                       'title' => 'A Página do DOERJ',
                                       'linha' => 2),
                                array ('linha' => 5,
                                       'nome' => 'obs',
                                       'label' => 'Observação:',
                                       'tipo' => 'textarea',
                                       'size' => array(80,6)),
                               array ( 'nome' => 'matricula',
                                       'label' => 'Matrícula:',
                                       'tipo' => 'hidden',
                                       'padrao' => $matriculaGrh,
                                       'size' => 5,
                                       'title' => 'Matrícula',
                                       'linha' => 3)));
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
            case "excluir" :
                $objeto->$fase($id); 
                break;

            case "gravar" :
                $objeto->gravar($id,'servidorCessaoValidacaoExtra.php'); 	
                break;
    }									 	 		

    $page->terminaPagina();
}