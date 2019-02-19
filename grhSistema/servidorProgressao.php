<?php
/**
 * Histórico de Progressões e Enquadramentos
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
    $objeto->set_nome('Cadastro de Progressões e Enquadramentos do Servidor');

    # botão de voltar da lista
    $objeto->set_voltarLista('servidorMenu.php');

    # ordenação
    if(is_null($orderCampo))
        $orderCampo = "1";

    if(is_null($orderTipo))
        $orderTipo = 'desc';

    # select da lista
    $objeto->set_selectLista('SELECT tbprogressao.dtInicial,
                                     tbtipoprogressao.nome,
                                     CONCAT(tbclasse.faixa," - ",tbclasse.valor) as vv,
                                     numProcesso,
                                     dtPublicacao,
                                     documento,
                                     tbprogressao.idProgressao
                                FROM tbprogressao JOIN tbtipoprogressao ON (tbprogressao.idTpProgressao = tbtipoprogressao.idTpProgressao)
                                                  JOIN tbclasse ON (tbprogressao.idClasse = tbclasse.idClasse)
                               WHERE idServidor = '.$idServidorPesquisado.'
                            ORDER BY '.$orderCampo.' '.$orderTipo);

    # select do edita
    $objeto->set_selectEdita('SELECT dtInicial,
                                     idTpProgressao,
                                     idClasse,
                                     documento,
                                     numProcesso,
                                     dtPublicacao,
                                     obs,
                                     idServidor
                                FROM tbprogressao
                               WHERE idProgressao = '.$id);

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
    $objeto->set_label(array("Data Inicial","Tipo de aumento","Valor","Processo","DOERJ","Documento"));
    $objeto->set_width(array(10,20,15,15,15,15));	
    $objeto->set_align(array("center"));
    $objeto->set_funcao(array ("date_to_php",NULL,NULL,NULL,"date_to_php"));

    # Classe do banco de dados
    $objeto->set_classBd('pessoal');

    # Nome da tabela
    $objeto->set_tabela('tbprogressao');

    # Nome do campo id
    $objeto->set_idCampo('idProgressao');

    # Tipo de label do formulário
    $objeto->set_formLabelTipo(1);

    # Pega os dados da combo prograssao
    $lista = new Pessoal();
    $result1 = $lista->select('SELECT idTpProgressao, 
                                      nome
                                 FROM tbtipoprogressao
                             ORDER BY nome');
    array_push($result1, array(NULL,NULL)); # Adiciona o valor de nulo

    # Pega os dados da combo classe
    $nivel = $lista->get_nivelCargo($idServidorPesquisado);
    $result2 = $lista->select('SELECT idClasse,
                                      concat("R$ ",Valor," - ",faixa," ( ",tbplano.numdecreto," - ",DATE_FORMAT(tbplano.dtPublicacao,"%d/%m/%Y")," )") as classe 
                                 FROM tbclasse JOIN tbplano ON (tbplano.idPlano = tbclasse.idPlano)
                                WHERE nivel = "'.$nivel.'" 
                             ORDER BY tbplano.planoAtual desc, tbplano.dtPublicacao desc, tbplano.planoAtual, SUBSTRING(faixa, 1, 1), valor');
    array_push($result2, array(NULL,NULL)); # Adiciona o valor de nulo

    # Campos para o formulario
    $objeto->set_campos(array( array ( 'nome' => 'dtInicial',
                                       'label' => 'Data Inicial:',
                                       'tipo' => 'data',
                                       'size' => 20,
                                       'required' => TRUE,
                                       'autofocus' => TRUE,
                                       'col' => 3,
                                       'title' => 'Data inícial da Progressão ou Enquadramento.',
                                       'linha' => 1),
                               array ( 'nome' => 'idTpProgressao',
                                       'label' => 'Tipo:',
                                       'tipo' => 'combo',
                                       'col' => 3,
                                       'required' => TRUE,
                                       'array' => $result1,
                                       'size' => 20,                               
                                       'title' => 'Tipo de Progressão / Enquadramento',
                                       'linha' => 1), 
                               array ( 'nome' => 'idClasse',
                                       'label' => 'Classe:',
                                       'tipo' => 'combo',
                                       'array' => $result2,
                                       'size' => 20,
                                       'col' => 6,
                                       'required' => TRUE,
                                       'title' => 'Valor',
                                       'linha' => 1), 
                               array ( 'nome' => 'documento',
                                       'label' => 'Documento:',
                                       'tipo' => 'texto',
                                       'size' => 30,
                                       'col' => 4,
                                       'title' => 'Documento comunicando a nova progressão.',
                                       'linha' => 2),
                               array ( 'nome' => 'numProcesso',
                                       'label' => 'Processo:',
                                       'tipo' => 'processo',
                                       'size' => 30,
                                       'col' => 3,
                                       'title' => 'Número do Processo',
                                       'linha' => 2), 
                               array ( 'nome' => 'dtPublicacao',
                                       'label' => 'Data da Pub. no DOERJ:',
                                       'tipo' => 'data',
                                       'size' => 20,
                                       'col' => 3,
                                       'title' => 'Data da Publicação no DOERJ.',
                                       'linha' => 2),
                                array ('linha' => 3,
                                       'nome' => 'obs',
                                       'col' => 12,
                                       'label' => 'Observação:',
                                       'tipo' => 'textarea',
                                       'size' => array(80,5)),
                               array ( 'nome' => 'idServidor',
                                       'label' => 'idServidor:',
                                       'tipo' => 'hidden',
                                       'padrao' => $idServidorPesquisado,
                                       'size' => 5,
                                       'title' => 'Matrícula',
                                       'linha' => 8)));


    # Relatório
    $imagem = new Imagem(PASTA_FIGURAS.'print.png',NULL,15,15);
    $botaoRel = new Button();
    $botaoRel->set_imagem($imagem);
    $botaoRel->set_title("Imprimir Relatório de Histórico de Progressões e Enquadramentos");
    $botaoRel->set_url("../grhRelatorios/servidorProgressao.php");
    $botaoRel->set_target("_blank");
    
    $objeto->set_botaoListarExtra(array($botaoRel));
    
    # Log
    $objeto->set_idUsuario($idUsuario);
    $objeto->set_idServidorPesquisado($idServidorPesquisado);

    ################################################################

    switch ($fase){
        case "" :
        case "listar" :
        case "editar" :			
        case "excluir" :
            $objeto->$fase($id); 
            break;

        case "gravar" :		
            $objeto->gravar($id,"servidorProgressaoExtra.php"); 			
            break;
    }
    $page->terminaPagina();
}else{
    loadPage("../../areaServidor/sistema/login.php");
}