<?php
/**
 * Cadastro de Tipos/Modalidades de Licença
 *  
 * By Alat
 */

# Reservado para o servidor logado
$idUsuario = null;

# Configuração
include ("_config.php");

# Permissão de Acesso
$acesso = Verifica::acesso($idUsuario,2);

if($acesso)
{    
    # Conecta ao Banco de Dados
    $intra = new Intra();
    $pessoal = new Pessoal();
	
    # Verifica a fase do programa
    $fase = get('fase','listar');

    # pega o id (se tiver)
    $id = soNumeros(get('id'));

    # Pega o parametro de pesquisa (se tiver)
    if (is_null(post('parametro')))					# Se o parametro n?o vier por post (for nulo)
        $parametro = retiraAspas(get_session('sessionParametro'));	# passa o parametro da session para a variavel parametro retirando as aspas
    else
    { 
        $parametro = post('parametro');                # Se vier por post, retira as aspas e passa para a variavel parametro
        set_session('sessionParametro',$parametro);    # transfere para a session para poder recuperá-lo depois
    }

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

    # Nome do Modelo (aparecerá nos fildset e no caption da tabela)
    $objeto->set_nome('Tipos de Licença');

    # botão salvar
    $objeto->set_botaoSalvarGrafico(false);

    # botão de voltar da lista
    $objeto->set_voltarLista('grh.php');

    # controle de pesquisa
    $objeto->set_parametroLabel('Pesquisar');
    $objeto->set_parametroValue($parametro);

    # ordenação
    if(is_null($orderCampo))
            $orderCampo = "2";

    if(is_null($orderTipo))
            $orderTipo = 'asc';

    # select da lista
    $objeto->set_selectLista ('SELECT idTpLicenca,
                                      nome,
                                      periodo,
                                      pericia,
                                      publicacao,
                                      processo,                                  
                                      dtPeriodo,
                                      limite_sexo,
                                      documentacao,
                                      idTpLicenca
                                 FROM tbtipolicenca
                                WHERE nome LIKE "%'.$parametro.'%"
                                   OR idTpLicenca LIKE "%'.$parametro.'%"
                             ORDER BY '.$orderCampo.' '.$orderTipo);

    # select do edita
    $objeto->set_selectEdita('SELECT nome,
                                     lei,
                                     periodo,
                                     pericia,
                                     publicacao,
                                     processo,                                  
                                     dtPeriodo,
                                     limite_sexo,
                                     documentacao,
                                     obs
                                FROM tbtipolicenca
                               WHERE idTpLicenca = '.$id);

    # ordem da lista
    $objeto->set_orderCampo($orderCampo);
    $objeto->set_orderTipo($orderTipo);
    $objeto->set_orderChamador('?fase=listar');

    # Caminhos
    $objeto->set_linkEditar('?fase=editar');
    #$objeto->set_linkExcluir('?fase=excluir');
    $objeto->set_linkGravar('?fase=gravar');
    $objeto->set_linkListar('?fase=listar');

    # Parametros da tabela
    $objeto->set_label(array("id","Licença","Período (em dias)","Perícia","Publicação","Processo","Período Aquisitivo","Permitido ao sexo","Documentação"));
    $objeto->set_width(array(5,20,7,7,7,7,7,7,28));
    $objeto->set_align(array("center","left","center","center","center","center","center","center","left"));
    #$objeto->set_function(array (null,null,null,null,null,null,"get_nome"));

    # Classe do banco de dados
    $objeto->set_classBd('Pessoal');

    # Nome da tabela
    $objeto->set_tabela('tbtipolicenca');

    # Nome do campo id
    $objeto->set_idCampo('idTpLicenca');

    # Tipo de label do formulário
    $objeto->set_formlabelTipo(1);

    # Campos para o formulario
    $objeto->set_campos(array(
        array ('linha' => 1,
               'nome' => 'nome',
               'title' => 'Nome da Licença',
               'label' => 'Nome da Licença:',
               'tipo' => 'texto',
               'autofocus' => true,
               'size' => 40),
        array ('linha' => 1,
               'nome' => 'lei',
               'title' => 'Lei',
               'label' => 'Lei:',
               'tipo' => 'texto',
               'size' => 40),
         array ('linha' => 1,
               'nome' => 'periodo',
               'title' => 'Período (em dias) da Licença',
               'label' => 'Período (em dias) da Licença:',
               'tipo' => 'texto',
               'size' => 10),
        array ('linha' => 4,
               'nome' => 'pericia',
               'title' => 'informa se essa licença necessita de perícia',
               'label' => 'Perícia:',
               'tipo' => 'combo',
               'array' => array("Sim","Não"),
               'size' => 10),
        array ('linha' => 4,
               'nome' => 'publicacao',
               'title' => 'informa se essa licença necessita de publicação',
               'label' => 'Publicação:',
               'tipo' => 'combo',
               'array' => array("Sim","Não"),
               'size' => 10),
        array ('linha' => 4,
               'nome' => 'processo',
               'title' => 'informa se essa licença necessita de processo',
               'label' => 'Processo:',
               'tipo' => 'combo',
               'array' => array("Sim","Não"),
               'size' => 10),
        array ('linha' => 4,
               'nome' => 'dtPeriodo',
               'title' => 'informa se essa licença necessita de período aquisitivo',
               'label' => 'Período Aquisitivo:',
               'tipo' => 'combo',
               'array' => array("Sim","Não"),
               'size' => 10),
         array ('linha' => 4,
               'nome' => 'limite_sexo',
               'title' => 'informa se essa licença é limitada a servidores de algum sexo',
               'label' => 'Somente ao sexo:',
               'tipo' => 'combo',
               'array' => array ("Todos","Masculino","Feminino"),
               'size' => 20),
         array ('linha' => 5,
               'nome' => 'documentacao',
               'label' => 'Documentação:',
               'tipo' => 'textarea',
               'size' => array(80,6)),
        array ('linha' => 6,
               'nome' => 'obs',
               'label' => 'Observação:',
               'tipo' => 'textarea',
               'size' => array(80,5))));

    # Matrícula para o Log
    $objeto->set_idUsuario($idUsuario);

    ################################################################
    switch ($fase)
    {
            case "" :
            case "listar" :

                    $objeto->listar();
                    break;

            case "editar" :	
            case "excluir" :	
            case "gravar" :

                    $objeto->$fase($id);
                    break;

    }									 	 		

    $page->terminaPagina();
}