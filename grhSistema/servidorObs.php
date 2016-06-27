<?php
/**
 * Observações do servidor
 *  
 * By Alat
 */

# Inicia as variáveis que receberão as sessions
$matricula = null;		  # Reservado para a matrícula do servidor logado
$matriculaGrh = null;		  # Reservado para a matrícula pesquisada

# Configuração
include ("_config.php");

# Permissão de Acesso
$acesso = Verifica::acesso($idusuario,2);

if($acesso)
{    
    # Verifica a fase do programa
    $fase = get('fase','editar');

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
    #$objeto->set_nome('');

    # select do edita
    $objeto->set_selectEdita('SELECT obs
                                FROM tbfuncionario
                               WHERE matricula = '.$matriculaGrh);


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
    $objeto->set_tabela('tbfuncionario');

    # Nome do campo id
    $objeto->set_idCampo('matricula');

    # Tipo de label do formulário
    $objeto->set_formlabelTipo(1);

    # Campos para o formulario
    $objeto->set_campos(array(
                        array ('linha' => 1,
                               'nome' => 'obs',
                               'autofocus' => true,
                               'label' => 'Observações do Servidor',
                               'tipo' => 'textarea',
                               'size' => array(255,20))));

    # Matrícula para o Log
    $objeto->set_idusuario($idusuario);

    ################################################################
    switch ($fase)
    {
        case "editar" :            
        case "excluir" :	
        case "gravar" :
            $objeto->$fase($matriculaGrh);
            break;	
    }									 	 		

    $page->terminaPagina();
}
?>
