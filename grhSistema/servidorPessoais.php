<?php
/**
 * Dados Gerais do servidor
 *  
 * By Alat
 */

# Inicia as variáveis que receberão as sessions
$idUsuario = null;              # Servidor logado
$idServidorPesquisado = null;	# Servidor Editado na pesquisa do sistema do GRH

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
    
    # Pega o idPessoa
    $idPessoa = $pessoal->get_idPessoa($idServidorPesquisado);

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
    $objeto->set_nome('Dados Pessoais');

    # select do edita
    $objeto->set_selectEdita('SELECT nome,
                                     dtNasc,
                                     sexo,
                                     estCiv,
                                     naturalidade,
                                     nacionalidade,
                                     paisOrigem,
                                     anoChegada,
                                     endereco,
                                     complemento,
                                     bairro,
                                     cidade,
                                     uf,
                                     cep,
                                     nomePai,
                                     nomeMae
                                FROM tbpessoa
                               WHERE idPessoa = '.$idPessoa);


    # Caminhos
    $objeto->set_linkGravar('?fase=gravar');
    #$objeto->set_linkListar('?');
    $objeto->set_linkListar('servidorMenu.php');

    # botão salvar
    $objeto->set_botaoSalvarGrafico(false);

    # botão voltar
    $objeto->set_voltarForm('servidorMenu.php');

    # retira o botão incluir
    $objeto->set_botaoIncluir(false);

    # Classe do banco de dados
    $objeto->set_classBd('Pessoal');

    # Nome da tabela
    $objeto->set_tabela('tbpessoa');

    # Nome do campo id
    $objeto->set_idCampo('idPessoa');

    # Tipo de label do formulário
    $objeto->set_formlabelTipo(1);

    # Pega os dados da combo de estado civil
    $estadoCivil = $pessoal->select('SELECT idestCiv,
                                       estciv
                                  FROM tbestciv
                              ORDER BY estciv');
    array_unshift($estadoCivil, array(null,null)); # Adiciona o valor de nulo
    
    # Pega os dados da combo de nacionalidade
    $nacionalidade = $pessoal->select('SELECT idNacionalidade,
                                       nacionalidade
                                  FROM tbnacionalidade
                              ORDER BY nacionalidade');
    array_unshift($nacionalidade, array(null,null)); # Adiciona o valor de nulo
    
    # Pega os dados da combo de país de origem
    $paisOrigem = $pessoal->select('SELECT idPais,
                                       pais
                                  FROM tbpais
                              ORDER BY pais');
    array_unshift($paisOrigem, array(null,null)); # Adiciona o valor de nulo

     

    # Campos para o formulario
    $objeto->set_campos(array(
                        array ('linha' => 1,
                               'nome' => 'nome',
                               'label' => 'Nome:',
                               'tipo' => 'texto',
                               'required' => true,
                               'autofocus' => true,
                               'title' => 'Nome do servidor',
                               'col' => 5,
                               'size' => 50),
                       array ( 'linha' => 1,
                               'nome' => 'dtNasc',
                               'label' => 'Data de Nascimento:',
                               'tipo' => 'data',                           
                               'size' => 20,
                               'col' => 2,
                               'title' => 'Data de Nascimento.'),
                        array ('linha' => 1,
                               'nome' => 'sexo',
                               'label' => 'Sexo:',
                               'tipo' => 'combo',
                               'array' => array("Masculino","Feminino"),
                               'title' => 'Sexo do Servidor',
                               'required' => true,
                               'col' => 2,
                               'size' => 15),
                        array ('linha' => 1,
                               'nome' => 'estCiv',
                               'label' => 'Estado Civil:',
                               'tipo' => 'combo',
                               'array' => $estadoCivil,
                               'title' => 'Estado Civil do Servidor',
                               'col' => 3,
                               'size' => 15),        
                        array ('linha' => 2,
                               'nome' => 'naturalidade',
                               'label' => 'Naturalidade:',
                               'tipo' => 'texto',
                               'col' => 3,
                               'title' => 'Servidor natural da cidade de',                           
                               'size' => 30),
                        array ('linha' => 2,
                               'nome' => 'nacionalidade',
                               'label' => 'Nacionalidade:',
                               'tipo' => 'combo',
                               'array' => $nacionalidade,                        
                               'title' => 'Nacionalidade do Servido',                           
                               'col' => 3,
                               'size' => 30),
                        array ('linha' => 2,
                               'nome' => 'paisOrigem',
                               'label' => 'País de Origem:',
                               'tipo' => 'combo',
                               'array' => $paisOrigem,
                               'title' => 'País de Origem do Servidor',
                               'col' => 3,
                               'size' => 15),
                        array ('linha' => 2,
                               'nome' => 'anoChegada',
                               'label' => 'Ano de Chegada:',
                               'tipo' => 'texto',
                               'title' => 'Ano de chegada ao Brasil',
                               'col' => 3,
                               'size' => 5),                                        
                        array ('linha' => 5,
                               'nome' => 'endereco',
                               'label' => 'Endereço:',
                               'tipo' => 'texto',                          
                               'title' => 'Endereço do Servidor',
                               'fieldset' => 'Endereço',
                               'col' => 6,
                               'size' => 50),
                        array ('linha' => 5,
                               'nome' => 'complemento',
                               'label' => 'Complemento:',
                               'tipo' => 'texto',                          
                               'title' => 'Complemento do endereço',
                               'col' => 6,
                               'size' => 50),
                        array ('linha' => 6,
                               'nome' => 'bairro',
                               'label' => 'Bairro:',
                               'tipo' => 'texto',                          
                               'title' => 'Bairro',
                               'col' => 4,
                               'size' => 50),
                        array ('linha' => 6,
                               'nome' => 'cidade',
                               'label' => 'Cidade:',
                               'tipo' => 'texto',
                               'col' => 4,
                               'title' => 'Cidade do endereço',                           
                               'size' => 50),
                        array ('linha' => 6,
                               'nome' => 'uf',
                               'label' => 'UF:',
                               'tipo' => 'texto',                          
                               'title' => 'UF',
                               'col' => 2,
                               'size' => 3),
                        array ('linha' => 6,
                               'nome' => 'cep',
                               'label' => 'Cep:',
                               'tipo' => 'cep',                          
                               'title' => 'Cep',
                               'col' => 2,
                               'size' => 10),
                        array ('linha' => 8,
                               'nome' => 'nomePai',
                               'label' => 'Nome do Pai:',
                               'tipo' => 'texto',                          
                               'title' => 'Nome do Pai do Servidor',
                               'fieldset' => 'Filiação',
                               'col' => 6,
                               'size' => 50),
                        array ('linha' => 8,
                               'nome' => 'nomeMae',
                               'label' => 'Nome da Mãe:',
                               'tipo' => 'texto',                          
                               'title' => 'Nome da Mãe do Servidor',
                               'col' => 6,
                               'size' => 50)
                                ));

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