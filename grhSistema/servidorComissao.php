<?php
/**
 * Histórico de Cargos em Comissão
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
    $objeto->set_nome('Cadastro de Cargos em Comissão');

    # botão de voltar da lista
    $objeto->set_voltarLista('servidorMenu.php');

    # ordenação
    if(is_null($orderCampo))
        $orderCampo = "3";

    if(is_null($orderTipo))
        $orderTipo = 'desc';

    # select da lista
    $objeto->set_selectLista('SELECT concat(tbtipocomissao.descricao," - (",tbtipocomissao.simbolo,")") as comissao,
                                     tbcomissao.descricao,
                                     tbcomissao.dtNom,
                                     tbcomissao.dtExo,
                                     idComissao
                                FROM tbcomissao, tbtipocomissao
                               WHERE tbcomissao.idTipoComissao = tbtipocomissao.idTipoComissao 
                                 AND matricula = '.$matriculaGrh.'
                            ORDER BY '.$orderCampo.' '.$orderTipo);

    # select do edita
    $objeto->set_selectEdita('SELECT idTipoComissao,
                                     descricao,
                                     dtNom,
                                     numProcNom,
                                     dtPublicNom,
                                     pgPublicNom,
                                     ciGepagNom,
                                     dtExo,
                                     numProcExo,
                                     dtPublicExo,
                                     pgPublicExo,
                                     ciGepagExo,
                                     obs,
                                     matricula
                                FROM tbcomissao
                               WHERE idComissao = '.$id);

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
    $objeto->set_label(array("Cargo","Descrição","Data de Nomeação","Data de Exoneração"));
    $objeto->set_width(array(30,40,10,10));	
    $objeto->set_align(array("center"));
    $objeto->set_function(array (null,null,"date_to_php","date_to_php"));

    # Classe do banco de dados
    $objeto->set_classBd('pessoal');

    # Nome da tabela
    $objeto->set_tabela('tbcomissao');

    # Nome do campo id
    $objeto->set_idCampo('idComissao');

    # Tipo de label do formulário
    $objeto->set_formLabelTipo(1);

    # Pega os dados da combo tipo de Comissão
    $result = $pessoal->select('SELECT idTipoComissao,
                                       CONCAT(tbtipocomissao.descricao," - (",tbtipocomissao.simbolo,")") as comissao
                                  FROM tbtipocomissao 
                              ORDER BY comissao');    
    
    # Verifica cada cargo se tem vagas disponíveis se não tem retira do array
    $novaLista = array();
    foreach ($result as $listaCargo)
    {
        $vagas = $pessoal->get_cargoComissaoVagasDisponiveis($listaCargo[0]);
        if($vagas > 0)
            array_push($novaLista, array($listaCargo[0], $listaCargo[1]));
    }
    
    # Se for edição
    if(is_numeric($id))
    {   
        $cargoEditado = $pessoal->get_cargoComissaoPorId($id);
        array_push($novaLista, $cargoEditado);
    }           
     
    $quantidadeCargos = count($novaLista);          // pega a quantidade de cargos vagos
    array_push($novaLista, array(null,null));       // adiciona o valor de nulo
        
    # Campos para o formulario
    $objeto->set_campos(array( array ( 'nome' => 'idTipoComissao',
                                       'label' => 'Tipo da Cargo em Comissão:',
                                       'tipo' => 'combo',
                                       'required' => true,
                                       'autofocus' => true,
                                       'array' => $novaLista,
                                       'size' => 20,
                                       'col' => 4,
                                       'title' => 'período de férias',
                                       'linha' => 1),
                               array ( 'nome' => 'descricao',                                   
                                       'label' => 'Descrição:',
                                       'tipo' => 'texto',
                                       'required' => true,
                                       'size' => 80,
                                       'col' => 8,
                                       'title' => 'Descrição do Cargo.',
                                       'linha' => 1),
                               array ( 'nome' => 'dtNom',
                                       'label' => 'Data da Nomeação:',
                                       'fieldset' => 'Dados da Nomeação',
                                       'tipo' => 'data',
                                       'size' => 20,
                                       'required' => true,
                                       'title' => 'Data da Nomeação.',
                                       'col' => 3,
                                       'linha' => 3),
                               array ( 'nome' => 'numProcNom',
                                       'label' => 'Processo:',
                                       'tipo' => 'processo',
                                       'size' => 30,                              
                                       'title' => 'Número do Processo',
                                       'col' => 4,
                                       'linha' => 3), 
                               array ( 'nome' => 'dtPublicNom',
                                       'label' => 'Data da Pub. no DOERJ:',
                                       'tipo' => 'data',
                                       'size' => 20,
                                       'col' => 3,
                                       'title' => 'Data da Publicação no DOERJ.',
                                       'linha' => 3),
                               array ( 'nome' => 'pgPublicNom',
                                       'label' => 'Pág:',
                                       'tipo' => 'texto',
                                       'col' => 2,
                                       'size' => 5,                         
                                       'title' => 'A Página do DOERJ',
                                       'linha' => 3),
                               array ( 'nome' => 'ciGepagNom',                                   
                                       'label' => 'Documento:',
                                       'tipo' => 'texto',
                                       'col' => 6,
                                       'size' => 30,                                   
                                       'title' => 'Documento.',
                                       'linha' => 4),
                               array ( 'nome' => 'dtExo',
                                       'label' => 'Data da Exoneração:',
                                       'fieldset' => 'Dados da Exoneração',
                                       'tipo' => 'data',
                                       'col' => 3,
                                       'size' => 20,
                                       'title' => 'Data da Exoneração.',
                                       'linha' => 5),
                               array ( 'nome' => 'numProcExo',
                                       'label' => 'Processo:',
                                       'tipo' => 'processo',
                                       'size' => 30,
                                       'col' => 4,
                                       'title' => 'Processo de Exoneração',
                                       'linha' => 5), 
                               array ( 'nome' => 'dtPublicExo',
                                       'label' => 'Data da Pub. no DOERJ:',
                                       'tipo' => 'data',
                                       'size' => 20,
                                       'col' => 3,
                                       'title' => 'Data da Publicação no DOERJ.',
                                       'linha' => 5),
                               array ( 'nome' => 'pgPublicExo',
                                       'label' => 'Pág:',
                                       'tipo' => 'texto',
                                       'size' => 5,
                                       'col' => 2,
                                       'title' => 'A Página do DOERJ',
                                       'linha' => 5),
                               array ( 'nome' => 'ciGepagExo',                                   
                                       'label' => 'Documento:',
                                       'tipo' => 'texto',
                                       'size' => 30,
                                       'col' => 6,                                   
                                       'title' => 'Documento.',
                                       'linha' => 6), 
                                array ('linha' => 5,
                                       'nome' => 'obs',
                                       'col' => 12,
                                       'label' => 'Observação:',
                                       'tipo' => 'textarea',
                                       'fieldset' => 'fecha',
                                       'size' => array(110,10)),                                   
                               array ( 'nome' => 'matricula',
                                       'label' => 'Matrícula:',
                                       'tipo' => 'hidden',
                                       'padrao' => $matriculaGrh,
                                       'size' => 5,
                                       'title' => 'Matrícula',
                                       'linha' => 7)));

    # Matrícula para o Log
    $objeto->set_matricula($matricula);
    
    # Botão Extra
    $botaoVagas = new Button("Vagas","?fase=vagas");
    $botaoVagas->set_title('Exibe a disponibilidade dos cargos em comissão');
    $botaoVagas->set_accessKey('V');
    $objeto->add_botaoListar($botaoVagas);
    

    # Paginação
    #$objeto->set_paginacao(true);
    #$objeto->set_paginacaoInicial($paginacao);
    #$objeto->set_paginacaoItens(20);

    ################################################################

    switch ($fase)
    {
        
        case "" :
        case "listar" :
           $objeto->listar();
           break;
           
        case "editar" :            
            # Limita o tamanho da tela
            $grid = new Grid();
            $grid->abreColuna(12);
           
            # Verifica se é incluir
            if(is_null($id)){
                br(2);              
                # Verifica se o número de vagas é 0
                if($quantidadeCargos == 0){
                    $msg = new Alert('Não há vagas disponíveis. Todas as vagas estão ocupadas. <br>'
                                   . 'Primeiro faça a exoneração para depois fazer a nomeação.','center');
                    $msg->set_tipo('alert');
                    $msg->set_title('Informação do Sistema');
                    $msg->set_page('?');
                    $msg->show();
                }
                elseif(!is_null($pessoal->get_cargoComissao($matriculaGrh)))  // se o servidor já possui cargo
                {
                    Visual::botaoVoltar('?');
                    br(2);
                    $box = new Box('alert');
                    $box->abre();        
                        $p = new P('Esse servidor já ocupa um cargo em comissão na data de hoje.<br/>
                                    Somente é permitido nomeação de servidor que não esteja ocupando cargo em comissão.','pQuantidadeCargos');
                        $p->show();
                        #Grh::quadroVagasCargoComissao();
                    $box->fecha();
                }                    
                else 
                    $objeto->editar();
            }
            else // se é editar
                $objeto->editar($id);
    
            # Botões 
            $menu = new MenuGrafico(5,'servicoBotoes');
            
            $grid->fechaColuna();
            $grid->fechaGrid();
            break;
        
        case "excluir" :
            $objeto->$fase($id);  
            break;

        case "gravar" :
            #$objeto->gravar($id,'servidorComissaoValidacaoExtra.php');  Verificar erro
	    $objeto->gravar($id); 	
            break;
        
        case "vagas" :
            # Limita o tamanho da tela
            $grid = new Grid();
            $grid->abreColuna(12);
            
            botaoVoltar("?");
            titulo("Vagas dos Cargos em Comissão");
                        
            Grh::quadroVagasCargoComissao();
            $grid->fechaColuna();
            $grid->fechaGrid();            
            break;
    }
    $page->terminaPagina();
}