<?php
/**
 * Dados da Documentação do servidor
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
    # Conecta ao Banco de Dados
    $pessoal = new Pessoal();
	
    # Verifica a fase do programa
    $fase = get('fase','editar');
    
    # Pega dados dessa matrícula
    $idPessoa = $pessoal->get_idPessoa($idServidorPesquisado);
    $idCargo = $pessoal->get_idCargo($idServidorPesquisado);

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
    $objeto->set_nome('Documentação');

    $selectEdita = 'SELECT cpf,
                         pisPasep,
                         reservista,
                         reservistaCateg,
                         identidade,
                         orgaoId,
                         dtId,
                         titulo,
                         zona,
                         secao,
                         tituloUf,';

    if($idCargo == 63){ // id do motorista
        $selectEdita.='motorista,dtVencMotorista,';
    }


    $selectEdita.='conselhoClasse,
                   registroClasse
              FROM tbdocumentacao
             WHERE idPessoa = '.$idPessoa;


    # select do edita
    $objeto->set_selectEdita($selectEdita);


    # Caminhos
    $objeto->set_linkGravar('?fase=gravar');
    #$objeto->set_linkListar('?');
    $objeto->set_linkListar('servidorMenu.php');

    # botão voltar
    $objeto->set_voltarForm('servidorMenu.php');

    # retira o botão incluir
    $objeto->set_botaoIncluir(FALSE);

    # Classe do banco de dados
    $objeto->set_classBd('Pessoal');

    # Nome da tabela
    $objeto->set_tabela('tbdocumentacao');

    # Nome do campo id
    $objeto->set_idCampo('idPessoa');

    # Tipo de label do formulário
    $objeto->set_formlabelTipo(1);
    
    # Pega os dados da combo de cidade
    $estado = $pessoal->select('SELECT idEstado,
                                       uf
                                  FROM tbestado
                              ORDER BY 2');
    array_unshift($estado, array(NULL,NULL)); # Adiciona o valor de nulo

    # Campos para o formulario
    $campos = array(array('linha' => 1,
                          'col' => 3,
                          'nome' => 'cpf',
                          'label' => 'CPF:',
                          'tipo' => 'cpf',
                          'required' => TRUE,
                          'autofocus' => TRUE,
                          'title' => 'CPF do servidor',                           
                          'size' => 20),
                   array ('linha' => 1,
                          'col' => 3,
                          'nome' => 'pisPasep',
                          'label' => 'Pis/Pasep:',
                          'tipo' => 'texto',                               
                          'title' => 'Pis Pasep do Servidor',                           
                          'size' => 15),
                   array ('linha' => 1,
                          'col' => 3,
                          'nome' => 'reservista',
                          'label' => 'Reservista:',
                          'tipo' => 'texto',
                          'title' => 'Reservista',                           
                          'size' => 15),
                   array ('linha' => 1,
                          'col' => 3,
                          'nome' => 'reservistaCateg',
                          'label' => 'Categoria:',
                          'tipo' => 'texto',
                          'title' => 'Categoria de reservista',                           
                          'size' => 5),       
                   array ('linha' => 2,
                          'col' => 3,
                          'nome' => 'identidade',
                          'label' => 'Número:',
                          'tipo' => 'texto',
                          'fieldset' => 'Identidade',
                          'title' => 'Identidade do servidor',                           
                          'size' => 20),
                   array ('linha' => 2,
                          'col' => 3,
                          'nome' => 'orgaoId',
                          'label' => 'Órgão:',
                          'tipo' => 'texto', 
                          'title' => 'Órgão da identidade',                           
                          'size' => 10),
                  array ( 'linha' => 2,
                          'col' => 3,
                          'nome' => 'dtId',
                          'label' => 'Data de Emissão:',
                          'tipo' => 'data',                           
                          'size' => 15,
                          'fieldset' => 'fecha', 
                          'title' => 'Data de Emissão.'),
                   array ('linha' => 3,
                          'col' => 3,                            
                          'nome' => 'titulo',
                          'label' => 'Título:',
                          'tipo' => 'texto',                          
                          'title' => 'Número do Título Eleitoral',
                          'fieldset' => 'Título Eleitoral',
                          'size' => 15),
                   array ('linha' => 3,
                          'col' => 2,
                          'nome' => 'zona',
                          'label' => 'Zona:',
                          'tipo' => 'texto',                          
                          'title' => 'Zona Eleitoral',
                          'size' => 5),
                   array ('linha' => 3,
                          'col' => 2,
                          'nome' => 'secao',
                          'label' => 'Seção:',
                          'tipo' => 'texto',                          
                          'title' => 'Seção Eleitoral',
                          'size' => 5),
                   array ('linha' => 3,
                          'col' => 2,
                          'nome' => 'tituloUf',
                          'label' => 'UF:',
                          'tipo' => 'combo',
                          'array' => $estado,                           
                          'title' => 'Unidade de Federaçao do Titulo Eleitoral',
                          'fieldset' => 'fecha', 
                          'size' => 3));

    if($idCargo == 63){ // id do motorista
    
    array_push($campos, array ('linha' => 4,
                               'nome' => 'motorista',
                               'label' => 'Número:',
                               'tipo' => 'texto',                          
                               'title' => 'Carteira de Habilitação',
                               'fieldset' => 'Carteira de Habilitação',
                               'size' => 10),
                       array ( 'linha' => 4,
                               'nome' => 'dtVencMotorista',
                               'label' => 'Data de Vencimento:',
                               'tipo' => 'data',                           
                               'size' => 15,
                               'title' => 'Data de Vencimento da Carteira de Habilitação.'));                     

    }
    # os demais
    array_push($campos,array  ('linha' => 7,
                                'col' => 4,
                               'nome' => 'conselhoClasse',
                               'label' => 'Conselho de Classe:',
                               'tipo' => 'texto',                          
                               'title' => 'Nome do Conselho de Classe',
                               'fieldset' => 'Conselho de Classe',
                               'size' => 50),
                        array ('linha' => 7,
                               'nome' => 'registroClasse',
                                'col' => 4,
                               'label' => 'Número:',
                               'tipo' => 'texto',                          
                               'title' => 'Número do registro',
                             'fieldset' => 'fecha', 
                               'size' => 20)
                                );

    $objeto->set_campos($campos);

    # Log
    $objeto->set_idUsuario($idUsuario);
    $objeto->set_idServidorPesquisado($idServidorPesquisado);

    ################################################################
    switch ($fase)
    {	
        case "editar" :            
        case "excluir" :	
        case "gravar" :
            $objeto->$fase($idPessoa);
            break;
    }				
    $page->terminaPagina();
}else{
    loadPage("../../areaServidor/sistema/login.php");
}