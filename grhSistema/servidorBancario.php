<?php
/**
 * Dados Bancários do servidor
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
    $pessoal = new Pessoal();
	
    # Verifica a fase do programa
    $fase = get('fase','editar');
    
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
    $objeto->set_nome('Dados Bancários');

    # select do edita
    $objeto->set_selectEdita('SELECT banco,
                                     agencia,
                                     conta,
                                     obsFinanceiro
                                FROM tbpessoa
                               WHERE idPessoa = '.$idPessoa);


    # Caminhos
    $objeto->set_linkGravar('?fase=gravar');
    $objeto->set_linkListar('?');

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

    # Pega os dados da combo parentesco
    $bancos = new Pessoal();
    $result = $bancos->select('SELECT idBanco, banco FROM tbbanco order by banco');
    array_push($result, array(null,null)); # Adiciona o valor de nulo

    # Campos para o formulario
    $objeto->set_campos(array(
                        array ('linha' => 1,
                               'nome' => 'banco',
                               'label' => 'Banco:',
                               'tipo' => 'combo',
                               'array' => $result,
                               'required' => true,
                               'autofocus' => true,
                               'col' => 4,
                               'title' => 'Nome do Banco do Servidor',                           
                               'size' => 20),
                        array ('linha' => 1,
                               'nome' => 'agencia',
                               'label' => 'Agência:',
                               'tipo' => 'texto',
                               'required' => true,
                               'col' => 4,
                               'title' => 'Número da Agência',                           
                               'size' => 10),
                        array ('linha' => 1,
                               'nome' => 'conta',
                               'label' => 'Conta Corrente:',
                               'tipo' => 'texto',
                               'col' => 4,
                               'required' => true,
                               'title' => 'Número da conta corrente do servidor',                           
                               'size' => 20),
                        array ('linha' => 4,
                               'nome' => 'obsFinanceiro',
                               'label' => 'Observação:',
                               'tipo' => 'textarea',
                               'col' => 12,
                               'size' => array(80,5))));

    # Matrícula para o Log
    $objeto->set_matricula($matricula);

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
}
?>
