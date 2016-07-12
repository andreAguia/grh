<?php
/**
 * Histórico de Diarias
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
    $intra = new Intra();
    $pessoal = new Pessoal();
	
    # Verifica a fase do programa
    $fase = get('fase','listar');

    # pega o id (se tiver)
    $id = soNumeros(get('id'));

    # Ordem da tabela
    $orderCampo = get('orderCampo','YEAR(dataCi) desc');
    $orderTipo = get('orderTipo',',numeroCi desc');

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
    $objeto->set_nome('Cadastro de Diárias');

    # botão de voltar da lista
    $objeto->set_voltarLista('servidorMenu.php');

    # select da lista
    $objeto->set_selectLista('SELECT idDiaria,
                                CONCAT("CI Diária nº ",numeroCi,"/",YEAR(dataCi)),
                                origem,
                                 destino,
                                 dataSaida,
                                 dataChegada,
                                 valor,                                 
                                 iddiaria,
                                 iddiaria
                                FROM tbdiaria
                               WHERE idServidor = '.$idServidorPesquisado.'
                            ORDER BY '.$orderCampo.' '.$orderTipo);

    # select do edita
    $objeto->set_selectEdita('SELECT origem,
                                     destino,
                                     dataSaida,
                                     dataChegada,
                                     valor,
                                     dataCi,
                                     numeroCi,
                                     assuntoCi,
                                     obs,
                                     idServidor
                                FROM tbdiaria
                               WHERE iddiaria = '.$id);

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
    $objeto->set_label(array("id","CI","Origem","Destino","Saída","Chegada","Valor","Emitir CI"));
    $objeto->set_width(array(4,11,20,20,10,10,8,8));	
    $objeto->set_align(array("center"));
    $objeto->set_function(array (null,null,null,null,"date_to_php","date_to_php","formataMoeda"));

    # Link do CI
    $botao = new BotaoGrafico();
    $botao->set_url('?fase=diaria&id=');
    $botao->set_image(PASTA_FIGURAS.'printer.png',20,20);
    $objeto->set_link(array("","","","","","","",$botao));

    # Classe do banco de dados
    $objeto->set_classBd('pessoal');

    # Nome da tabela
    $objeto->set_tabela('tbdiaria');

    # Nome do campo id
    $objeto->set_idCampo('iddiaria');

    # Tipo de label do formulário
    $objeto->set_formLabelTipo(1);

    # Pega os dados da combo assundo de CI
    $result = $intra->select('SELECT codigo, 
                                     concat(Upper(substr(descricao, 1,1)), lower(substr(descricao, 2,length(descricao))))   
                                  FROM tbupo
                                 WHERE descricao LIKE "%diaria%"
                              ORDER BY descricao');

    array_push($result, array(null,null)); # Adiciona o valor de null

    # Campos para o formulario
    $objeto->set_campos(array( array ( 'nome' => 'origem',
                                       'label' => 'Origem:',
                                       'tipo' => 'texto',
                                       'required' => true,
                                       'size' => 50,
                                       'col' => 6,
                                       'padrao' => 'Campos dos Goytacazes',
                                       'title' => 'Local de Origem',
                                       'autofocus' => true,
                                       'linha' => 1),
                               array ( 'nome' => 'destino',
                                       'label' => 'Destino:',
                                       'tipo' => 'texto',
                                       'required' => true,
                                       'size' => 50,
                                       'col' => 6,
                                       'title' => 'Local de Destino',
                                       'linha' => 1),
                               array ( 'nome' => 'dataSaida',
                                       'label' => 'Data de Saída:',                                   
                                       'tipo' => 'data',
                                       'size' => 20,
                                       'col' => 3,
                                       'required' => true,
                                       'title' => 'Data da Saída.',
                                       'linha' => 2),
                               array ( 'nome' => 'dataChegada',
                                       'label' => 'Data de Chegada:',                                   
                                       'tipo' => 'data',
                                       'size' => 20,
                                       'col' => 3,
                                       'required' => true,
                                       'title' => 'Data de Chegada.',
                                       'linha' => 2),
                               array ( 'nome' => 'valor',
                                       'label' => 'Valor:',
                                       'tipo' => 'moeda',
                                       'size' => 10,
                                       'col' => 4,
                                       'required' => true,
                                       'title' => 'Valor da Diária',
                                       'linha' => 2), 
                               array ( 'nome' => 'dataCi',
                                       'required' => true,
                                       'label' => 'Data:',
                                       'tipo' => 'data',
                                       'col' => 3,
                                       'size' => 20,
                                       'title' => 'Data da CI.',
                                       'fieldset' => 'Dados da CI',
                                       'linha' => 3),
                               array ( 'nome' => 'numeroCi',
                                       'label' => 'Número:',
                                       'tipo' => 'texto',
                                       'size' => 5,
                                       'col' => 3,
                                       'title' => 'Número da CI',
                                       'linha' => 3),
                               array ( 'nome' => 'assuntoCi',                                   
                                       'label' => 'Assunto:',
                                       'required' => true,
                                       'tipo' => 'texto',
                                       'size' => 50,
                                       'col' => 6,
                                       'title' => 'Assunto.',
                                       'array' => $result,
                                       'linha' => 3),
                                array ('linha' => 5,
                                       'nome' => 'obs',
                                       'label' => 'Observação:',
                                       'tipo' => 'textarea',
                                       'col' => 12,
                                       'fieldset' => 'fecha',
                                       'size' => array(80,5)),                                   
                               array ( 'nome' => 'idServidor',
                                       'label' => 'idServidor:',
                                       'tipo' => 'hidden',
                                       'padrao' => $idServidorPesquisado,
                                       'size' => 5,
                                       'title' => 'idServidor',
                                       'linha' => 5)));


    # Log
    $objeto->set_idUsuario($idUsuario);
    $objeto->set_idServidorPesquisado($idServidorPesquisado);

    # Paginação
    #$objeto->set_paginacao(true);
    #$objeto->set_paginacaoInicial($paginacao);
    #$objeto->set_paginacaoItens(20);

    ################################################################

    switch ($fase)
    {
        case "" :
        case "listar" :
            $objeto->$fase($id);  
            break;

        case "editar" :
            $mensagem = 'Para que o sistema gere automaticamente o número da Ci, é só deixar o campo "Número" em branco.';
            $objeto->set_rotinaExtraEditar("callout");
            $objeto->set_rotinaExtraEditarParametro($mensagem);
            
            $objeto->editar($id);
            break;

        case "excluir" :
            $objeto->$fase($id);  
            break;

        case "gravar" :
            $objeto->gravar($id,'servidorDiariaExtra.php'); 	
            break;

        ################################################################

        case 'diaria':
            $id = get('id');
            loadPage('../grhRelatorios/ciDiaria.php?id='.$id,'_blank');
            
            # Log
            $atividade = "Emitiu CI de Diária de ".$pessoal->get_nome($idServidorPesquisado);
            $data = date("Y-m-d H:i:s");
            $intra->registraLog($idUsuario,$data,$atividade,null,null,4,$idServidorPesquisado);
            
            loadPage('?');            
            break;
    }
    $page->terminaPagina();
}
?>
