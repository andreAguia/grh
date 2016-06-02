<?php
/**
 * Cadastro de Averbações no SAPE
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
    $objeto->set_rotinaExtraParametro($matriculaGrh); 

    # Nome do Modelo (aparecerá nos fildset e no caption da tabela)
    $objeto->set_nome('Cadastro de Tempo de Serviço Averbado e Cadastrado no SAPE');

    # botão de voltar da lista
    $objeto->set_voltarLista('servidorMenu.php');

    # ordenação
    if(is_null($orderCampo))
        $orderCampo = "1";

    if(is_null($orderTipo))
        $orderTipo = 'desc';

    # select da lista
    $objeto->set_selectLista('SELECT dtInicial,
                                     dtFinal,
                                     dias,
                                     empresa,
                                     CASE empresaTipo
                                        WHEN 1 THEN "Pública"
                                        WHEN 2 THEN "Privada"
                                     END,
                                     CASE regime
                                        WHEN 1 THEN "Celetista"
                                        WHEN 2 THEN "Estatutário"
                                     END,
                                     CASE cargo
                                        WHEN 1 THEN "Professor"
                                        WHEN 2 THEN "Outros"
                                     END,
                                     CONCAT(date_format(dtPublicacao,"%d/%m/%Y")," - Pag ",pgPublicacao),
                                     processo,
                                     idAverbacao
                                FROM tbaverbacao
                               WHERE matricula = '.$matriculaGrh.'
                            ORDER BY '.$orderCampo.' '.$orderTipo);

    # select do edita
    $objeto->set_selectEdita('SELECT empresa,
                                     empresaTipo,
                                     dtPublicacao,
                                     pgPublicacao,
                                     processo,
                                     dtInicial,
                                     dtFinal,
                                     dias,                                                                 
                                     regime,
                                     cargo,
                                     obs,
                                     matricula
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
    $objeto->set_label(array("Data Inicial","Data Final","Dias","Empresa","Tipo","Regime","Cargo","Publicação","Processo"));
    $objeto->set_width(array(10,10,5,25,5,5,8,10,12));	
    $objeto->set_align(array("center"));
    $objeto->set_function(array ("date_to_php","date_to_php"));

    # Classe do banco de dados
    $objeto->set_classBd('pessoal');

    # Nome da tabela
    $objeto->set_tabela('tbaverbacao');

    # Nome do campo id
    $objeto->set_idCampo('idAverbacao');

    # Tipo de label do formulário
    $objeto->set_formLabelTipo(1);

    # Campos para o formulario
    $objeto->set_campos(array( array ( 'nome' => 'empresa',
                                       'label' => 'Empresa:',
                                       'tipo' => 'texto',
                                       'required' => true,
                                       'autofocus' => true,
                                       'size' => 80,                                   
                                       'title' => 'Nome da Empresa.',
                                       'col' => 8,
                                       'linha' => 1),
                               array ( 'nome' => 'empresaTipo',
                                       'label' => 'Tipo:',
                                       'tipo' => 'combo',
                                       'required' => true,
                                       'array' => Array(Array(1,"Pública"),Array(2,"Privada")),
                                       'size' => 20,
                                       'col' => 4,
                                       'title' => 'Tipo da Empresa',
                                       'linha' => 1),
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
                                       'col' => 2,
                                       'size' => 5,                         
                                       'title' => 'A Página do DOERJ',
                                       'linha' => 2),
                               array ( 'nome' => 'processo',
                                       'label' => 'Processo:',
                                       'tipo' => 'processo',
                                       'required' => true,
                                       'size' => 30,
                                       'col' => 4,
                                       'title' => 'Número do Processo',
                                       'linha' => 2), 
                               array ( 'nome' => 'dtInicial',
                                       'label' => 'Data Inicial:',
                                       'tipo' => 'data',
                                       'notNull' => true,
                                       'size' => 20,
                                       'col' => 3,
                                       'required' => true,
                                       'title' => 'Data inícial do Período.',
                                       'linha' => 3),
                               array ( 'nome' => 'dtFinal',
                                       'label' => 'Data Final:',
                                       'tipo' => 'data',
                                       'required' => true,
                                       'size' => 20,
                                       'col' => 3,
                                       'notNull' => true,
                                       'title' => 'Data final do Período.',
                                       'linha' => 3),
                               array ( 'nome' => 'dias',
                                       'label' => 'Dias:',
                                       'tipo' => 'numero',
                                       'required' => true,
                                       'size' => 5,
                                       'col' => 2,
                                       'notNull' => true,
                                       'title' => 'Quantidade de Dias Averbado.',
                                       'linha' => 3),
                               array ( 'nome' => 'regime',
                                       'label' => 'Regime:',
                                       'tipo' => 'combo',
                                       'col' => 6,
                                       'required' => true,
                                       'array' => Array(Array(1,"Celetista"),Array(2,"Estatutário")),
                                       'size' => 20,                               
                                       'title' => 'Tipo do Regime',
                                       'linha' => 4),
                               array ( 'nome' => 'cargo',
                                       'label' => 'Cargo:',
                                       'tipo' => 'combo',
                                       'col' => 6,
                                       'required' => true,
                                       'array' => Array(Array(1,"Professor"),Array(2,"Outros")),
                                       'size' => 20,                               
                                       'title' => 'Cargo',
                                       'linha' => 4),
                                array ('linha' => 9,
                                       'nome' => 'obs',
                                       'label' => 'Observação:',
                                       'tipo' => 'textarea',
                                       'size' => array(110,10)),
                               array ( 'nome' => 'matricula',
                                       'label' => 'Matrícula:',
                                       'tipo' => 'hidden',
                                       'padrao' => $matriculaGrh,
                                       'size' => 5,
                                       'title' => 'Matrícula',
                                       'linha' => 10)));


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
?>
