<?php
/**
 * Histórico de Triênios
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
    
    # Pega os dados do último percentual
    $ultimoPercentual = $pessoal->get_trienioPercentual($idServidorPesquisado);
    $ultimoTrienio = $pessoal->get_trienioDataInicial($idServidorPesquisado);
    $dataAdmissao = $pessoal->get_dtAdmissao($idServidorPesquisado);
    if(is_null($ultimoTrienio))
        $proximoTrienio = addAnos($dataAdmissao, 3);
    else
        $proximoTrienio = addAnos($ultimoTrienio, 3);

    # retira o botão de incluir triênio quando estiver no máximo
    if ($ultimoPercentual == "60")
        $objeto->set_botaoIncluir(false);

    # Nome do Modelo (aparecerá nos fildset e no caption da tabela)
    $objeto->set_nome('Cadastro de Triênios do Servidor');

    # botão de voltar da lista
    $objeto->set_voltarLista('servidorMenu.php');

    # ordenação
    if(is_null($orderCampo))
        $orderCampo = "2";

    if(is_null($orderTipo))
        $orderTipo = 'desc';

    # select da lista
    $objeto->set_selectLista('SELECT dtInicial,
                                     percentual,
                                     dtInicioPeriodo,
                                     dtFimPeriodo,
                                     numProcesso,
                                     concat(date_format(dtPublicacao,"%d/%m/%Y")," - Pag ",pgPublicacao),
                                     documento,
                                     idTrienio
                                FROM tbtrienio
                               WHERE idServidor = '.$idServidorPesquisado.'
                            ORDER BY '.$orderCampo.' '.$orderTipo);

    # select do edita
    $objeto->set_selectEdita('SELECT percentual,
                                     dtInicial,
                                     dtInicioPeriodo,
                                     dtFimPeriodo,
                                     documento,
                                     numProcesso,
                                     dtPublicacao,
                                     pgPublicacao,
                                     obs,
                                     idServidor
                                FROM tbtrienio
                               WHERE idTrienio = '.$id);

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
    $objeto->set_label(array("a partir de","%","Início do Período Aquisitivo","Término do Período Aquisitivo","Processo","DOERJ","Documento"));
    $objeto->set_width(array(10,5,10,10,20,15,20));	
    $objeto->set_align(array("center"));
    $objeto->set_function(array ("date_to_php",null,"date_to_php","date_to_php"));

    # Classe do banco de dados
    $objeto->set_classBd('pessoal');

    # Nome da tabela
    $objeto->set_tabela('tbtrienio');

    # Nome do campo id
    $objeto->set_idCampo('idTrienio');

    # Tipo de label do formulário
    $objeto->set_formLabelTipo(1);

    # Monta o array para o campo percentual
    $percentuaisPossiveis = array ("10","15","20","25","30","35","40","45","50","55","60");

    if (is_null($id)) // se for novo triênio
    {

        if(is_null($ultimoPercentual))
            $percentuais = $percentuaisPossiveis;
        else
        {
            $percentuais = array();
            $indice = array_search($ultimoPercentual, $percentuaisPossiveis);

            if ($ultimoPercentual <> "60")
                array_push($percentuais,$percentuaisPossiveis[$indice+1]);
        }

    }
    else
        $percentuais = $percentuaisPossiveis;

    # Campos para o formulario
    $objeto->set_campos(array( array ( 'nome' => 'percentual',
                                       'label' => 'Percentual:',
                                       'tipo' => 'combo',
                                       'required' => true,
                                       'autofocus' => true,
                                       'array' => $percentuais,
                                       'size' => 20,
                                       'col' => 3,
                                       'title' => 'período de férias',
                                       'linha' => 1),     	 
                               array ( 'nome' => 'dtInicial',
                                       'label' => 'Data Inicial:',
                                       'tipo' => 'data',
                                       'size' => 20,
                                       'col' => 3,
                                       'required' => true,
                                       'padrao' => $proximoTrienio,
                                       'title' => 'Data inícial do Triênio.',
                                       'linha' => 1),
                               array ( 'nome' => 'dtInicioPeriodo',
                                       'label' => 'Início do período aquisitivo:',
                                       'fieldset' => 'Período Aquisitivo',
                                       'tipo' => 'data',
                                       'size' => 20,
                                       'col' => 3,
                                       'required' => true,                 
                                       'title' => 'Data de início do período aquisitivo',
                                       'linha' => 1),
                                array ( 'nome' => 'dtFimPeriodo',
                                       'label' => 'Término do período aquisitivo:',
                                       'tipo' => 'data',
                                        'col' => 3,
                                       'size' => 20,
                                       'required' => true,                 
                                       'title' => 'Data de término do período aquisitivo',
                                       'linha' => 1), 
                               array ( 'nome' => 'documento',
                                       'fieldset' => 'fecha',
                                       'label' => 'Documento:',
                                       'tipo' => 'texto',
                                       'size' => 30,
                                       'col' => 3,                                   
                                       'title' => 'Documento comunicando a nova progressão.',
                                       'linha' => 3),
                               array ( 'nome' => 'numProcesso',
                                       'label' => 'Processo:',
                                       'tipo' => 'processo',
                                       'size' => 30,
                                       'col' => 4,
                                       'title' => 'Número do Processo',
                                       'linha' => 3), 
                               array ( 'nome' => 'dtPublicacao',
                                       'label' => 'Data da Pub. no DOERJ:',
                                       'tipo' => 'data',
                                       'size' => 20,
                                        'col' => 3,
                                       'title' => 'Data da Publicação no DOERJ.',
                                       'linha' => 3),
                               array ( 'nome' => 'pgPublicacao',
                                       'label' => 'Pág:',
                                       'tipo' => 'texto',
                                       'col' => 2,
                                       'size' => 5,                         
                                       'title' => 'A Página do DOERJ',
                                       'linha' => 3),
                                array ('linha' => 4,
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
                                       'linha' => 5)));

    # Log
    $objeto->set_idUsuario($idUsuario);
    $objeto->set_idServidorPesquisado($idServidorPesquisado);

    ################################################################

    switch ($fase)
    {
        case "editar" :
            # Post it informando da porcentagem do triênio
            if (is_null($id)){ // se for inclusão
                if(!is_null($ultimoPercentual)){
                    $mensagem1 = 'O último triênio desse servidor foi de '.$ultimoPercentual.'%, o próximo percentual deverá ser de '.$percentuais[0].'%.';
                    $objeto->set_rotinaExtraEditar("callout");
                    $objeto->set_rotinaExtraEditarParametro($mensagem1);
                }
            }
            
            $objeto->editar($id);
            break;

        case "" :
        case "listar" :
            
            if ($ultimoPercentual == "60"){
                $mensagem2 = 'Servidor já alcançou o teto do triênio: 60%';
            }else{
                $mensagem2 = 'Próximo Triênio: '.$proximoTrienio;
                
                if(jaPassou($proximoTrienio)){
                    $mensagem2 .= ' (Atenção: A data já passou!)';
                }
            }
            
            $objeto->set_rotinaExtraListar("callout");
            $objeto->set_rotinaExtraListarParametro($mensagem2);
                    
            $objeto->$fase($id);
            break;          

        case "excluir" :	
        case "gravar" :		
            $objeto->$fase($id);
            break;
    }
    $page->terminaPagina();
}else{
    loadPage("../../areaServidor/sistema/login.php");
}
