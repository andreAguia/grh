<?php
/**
 * Histórico de Gratificações Especiais
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
    $objeto->set_rotinaExtraParametro($idServidorPesquisado); 

    # Nome do Modelo (aparecerá nos fildset e no caption da tabela)
    $objeto->set_nome('Cadastro de Gratificações Especiais do Servidor');

    # botão de voltar da lista
    $objeto->set_voltarLista('servidorMenu.php');

    # ordenação
    if(is_null($orderCampo))
        $orderCampo = "1";

    if(is_null($orderTipo))
        $orderTipo = 'desc';

    # Evita que um servidor que já esteja recebendo gratificação passe a receber outra.
    # Verifica-se se o servidor já recebe alguma gratificação (está em aberto)
    if(is_null($pessoal->get_gratificacaoDtFinal($idServidorPesquisado))){
        # Retira o botão de incluir
        $objeto->set_botaoIncluir(FALSE);
        
        # Informa o porquê
        $mensagem = "O botão de Incluir sumiu! Porque? Esse servidor ainda está recebendo uma gratificação.<br/>"
                   ."Somente será permitido a inserção de uma nova gratificação quanfo for informado a data de término da gratificação atual.";
        $objeto->set_rotinaExtraListar("callout");
        $objeto->set_rotinaExtraListarParametro($mensagem);
    }

    # select da lista
    $objeto->set_selectLista('SELECT dtInicial,
                                     dtFinal,
                                     valor,
                                     processo,
                                     idGratificacao
                                FROM tbgratificacao
                               WHERE idServidor = '.$idServidorPesquisado.'
                            ORDER BY '.$orderCampo.' '.$orderTipo);

    # select do edita
    $objeto->set_selectEdita('SELECT dtInicial,
                                     dtFinal,
                                     valor,
                                     processo,
                                     obs,
                                     idServidor
                                FROM tbgratificacao
                               WHERE idGratificacao = '.$id);

    # ordem da lista
    $objeto->set_orderCampo($orderCampo);
    $objeto->set_orderTipo($orderTipo);
    $objeto->set_orderChamador('?fase=listar');

    # Caminhos
    $objeto->set_linkEditar('?fase=editar');
    $objeto->set_linkExcluir('?fase=excluir');
    $objeto->set_linkGravar('?fase=gravar');
    $objeto->set_linkListar('?fase=listar');

    # Parametros da tabela
    $objeto->set_label(array("Data Inicial","Data Final","Valor","Processo"));
    $objeto->set_width(array(20,20,20,30));	
    $objeto->set_align(array("center"));
    $objeto->set_funcao(array ("date_to_php","date_to_php","formataMoeda"));

    # Classe do banco de dados
    $objeto->set_classBd('pessoal');

    # Nome da tabela
    $objeto->set_tabela('tbgratificacao');

    # Nome do campo id
    $objeto->set_idCampo('idGratificacao');

    # Tipo de label do formulário
    $objeto->set_formLabelTipo(1);

    # Campos para o formulario
    $objeto->set_campos(array( array ( 'nome' => 'dtInicial',
                                       'label' => 'Data Inicial:',
                                       'tipo' => 'data',
                                       'size' => 20,
                                       'col' => 3,
                                       'required' => TRUE,
                                       'autofocus' => TRUE,
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
                                       'required' => TRUE,
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
                                       'size' => array(80,5)),
                               array ( 'nome' => 'idServidor',
                                       'label' => 'idServidor:',
                                       'tipo' => 'hidden',
                                       'padrao' => $idServidorPesquisado,
                                       'size' => 5,
                                       'title' => 'Matrícula',
                                       'linha' => 5)));


    # Log
    $objeto->set_idUsuario($idUsuario);
    $objeto->set_idServidorPesquisado($idServidorPesquisado);

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
            $objeto->$fase($id,"servidorGratificacaoExtra.php"); 
            break;
    }
    $page->terminaPagina();
}else{
    loadPage("../../areaServidor/sistema/login.php");
}