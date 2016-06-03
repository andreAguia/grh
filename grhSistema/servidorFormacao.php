<?php
/**
 * Histórico de Formação Escolar do Servidor
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

    # Pega o idPessoa
    $idPessoa = $pessoal->get_idPessoa($matriculaGrh);

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
    $objeto->set_nome('Cadastro da Formação Escolar do Servidor');

    # botão de voltar da lista
    $objeto->set_voltarLista('servidorMenu.php');

    # select da lista
    $objeto->set_selectLista('SELECT tbescolaridade.escolaridade,
                                     habilitacao,
                                     instEnsino,
                                     anoTerm,                              
                                     idFormacao
                                FROM tbformacao JOIN tbescolaridade ON (tbformacao.escolaridade = tbescolaridade.idEscolaridade)
                          WHERE idPessoa='.$idPessoa.'
                       ORDER BY anoTerm');

    # select do edita
    $objeto->set_selectEdita('SELECT escolaridade,
                                     habilitacao,
                                     instEnsino,                                     
                                     horas,
                                     anoTerm,
                                     obs,
                                     idPessoa
                                FROM tbformacao
                               WHERE idFormacao = '.$id);

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
    $objeto->set_label(array("Nível","Curso","Instituição","Ano de Término"));
    $objeto->set_width(array(15,30,35,10));	
    $objeto->set_align(array("center"));
    #$objeto->set_function(array (null,"date_to_php"));

    # Classe do banco de dados
    $objeto->set_classBd('pessoal');

    # Nome da tabela
    $objeto->set_tabela('tbformacao');

    # Nome do campo id
    $objeto->set_idCampo('idFormacao');

    # Tipo de label do formulário
    $objeto->set_formLabelTipo(1);

    # Pega os dados da combo escolaridade
    $escolaridade = new Pessoal();
    $result = $escolaridade->select('SELECT idEscolaridade, 
                                            Escolaridade
                                       FROM tbescolaridade
                                   ORDER BY idEscolaridade');
    array_push($result, array(null,null)); # Adiciona o valor de nulo

    # Campos para o formulario
    $objeto->set_campos(array( array ( 'nome' => 'escolaridade',
                                       'label' => 'Nível:',
                                       'tipo' => 'combo',
                                       'array' => $result,
                                       'required' => true,
                                       'autofocus' => true,
                                       'size' => 20,
                                       'col' => 4,
                                       'title' => 'Nível do Curso.',
                                       'linha' => 1),
                               array ( 'nome' => 'habilitacao',
                                       'label' => 'Curso:',
                                       'tipo' => 'texto',
                                       'size' => 80,
                                       'col' => 8,
                                       'required' => true,
                                       'title' => 'Nome do curso.',
                                       'linha' => 1),
                               array ( 'nome' => 'instEnsino',
                                       'label' => 'Instituição de Ensino:',
                                       'tipo' => 'texto',
                                       'size' => 80,
                                       'col' => 7,
                                       'required' => true,
                                       'title' => 'Nome da Instituição de Ensino.',
                                       'linha' => 2),                               
                               array ( 'nome' => 'horas',
                                       'label' => 'Carga Horária:',
                                       'tipo' => 'numero',
                                       'size' => 10,
                                       'col' => 2,
                                       'title' => 'Carga Horária do Curso.',
                                       'linha' => 2),
                               array ( 'nome' => 'anoTerm',
                                       'label' => 'Ano de Término:',
                                       'tipo' => 'numero',
                                       'size' => 5,
                                       'col' => 3,
                                       'required' => true,
                                       'title' => 'Nome da Instituição de Ensino.',
                                       'linha' => 2),
                                array ('linha' => 3,
                                       'nome' => 'obs',
                                       'col' => 12,
                                       'label' => 'Observação:',
                                       'tipo' => 'textarea',
                                       'size' => array(80,5)),
                               array ( 'nome' => 'idPessoa',
                                       'label' => 'idPessoa:',
                                       'tipo' => 'hidden',
                                       'padrao' => $idPessoa,
                                       'size' => 6,
                                       'title' => 'idPessoa',
                                       'linha' => 5)));

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
        case "gravar" :
            $objeto->$fase($id);
            break;
    }
    $page->terminaPagina();
}
