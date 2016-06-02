<?php
/**
 * Histórico de Gratificações Especiais
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
    $objeto->set_nome('Cadastro de Gratificações Especiais do Servidor');

    # botão de voltar da lista
    $objeto->set_voltarLista('servidorMenu.php');

    # ordenação
    if(is_null($orderCampo))
        $orderCampo = "1";

    if(is_null($orderTipo))
        $orderTipo = 'desc';

    # Evita que um servidor que já estaja recebendo gratificação passe a receber outra.
    # Verifica-se se o servidor já recebe alguma gratificação (está em aberto)
    if(is_null($pessoal->get_gratificacaoDtFinal($matriculaGrh)))
        $objeto->set_botaoIncluir(false);

    # select da lista
    $objeto->set_selectLista('SELECT dtInicial,
                                     dtFinal,
                                     valor,
                                     processo,
                                     idGratif
                                FROM tbgratif
                               WHERE matricula = '.$matriculaGrh.'
                            ORDER BY '.$orderCampo.' '.$orderTipo);

    # select do edita
    $objeto->set_selectEdita('SELECT dtInicial,
                                     dtFinal,
                                     valor,
                                     processo,
                                     obs,
                                     matricula
                                FROM tbgratif
                               WHERE idGratif = '.$id);

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
    $objeto->set_label(array("Data Inicial","Data Final","Valor","Processo"));
    $objeto->set_width(array(20,20,20,30));	
    $objeto->set_align(array("center"));
    $objeto->set_function(array ("date_to_php","date_to_php","formataMoeda"));

    # Classe do banco de dados
    $objeto->set_classBd('pessoal');

    # Nome da tabela
    $objeto->set_tabela('tbgratif');

    # Nome do campo id
    $objeto->set_idCampo('idGratif');

    # Tipo de label do formulário
    $objeto->set_formLabelTipo(1);

    # Campos para o formulario
    $objeto->set_campos(array( array ( 'nome' => 'dtInicial',
                                       'label' => 'Data Inicial:',
                                       'tipo' => 'data',
                                       'size' => 20,
                                       'col' => 3,
                                       'required' => true,
                                       'autofocus' => true,
                                       'title' => 'Data inícial da Gratificação.',
                                       'linha' => 1),
                               array ( 'nome' => 'dtFinal',
                                       'label' => 'Data Final:',
                                       'tipo' => 'data',
                                       'size' => 20,
                                       'col' => 3,
                                       'title' => 'Data final da gratificação.',
                                       'linha' => 1),
                               array ( 'nome' => 'valor',
                                       'label' => 'Valor:',
                                       'tipo' => 'moeda',
                                       'size' => 20,
                                       'col' => 3,
                                       'title' => 'Valor da Gratificação.',
                                       'linha' => 1),
                               array ( 'nome' => 'processo',
                                       'label' => 'Processo:',
                                       'tipo' => 'processo',
                                       'size' => 30,
                                       'col' => 5,
                                       'title' => 'Número do Processo',
                                       'linha' => 2),
                                array ('linha' => 3,
                                       'col' => 12,
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
                                       'linha' => 5)));


    # Matrícula para o Log
    $objeto->set_matricula($matricula);

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
