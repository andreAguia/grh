<?php
/**
 * Dados de Cargos em Comissão
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
    $objeto->set_nome('Cadastro de Cargos em Comissão');

    # botão de voltar da lista
    $objeto->set_voltarLista('servidorMenu.php');

    # ordenação
    if(is_null($orderCampo)){
        $orderCampo = "3";
    }

    if(is_null($orderTipo)){
        $orderTipo = 'desc';
    }
    
    ## Rotina abaixo retirado para "tirar o gesso" 
    
    # Retira o botão de inclusão quando o servidor já tem cargo em comissão em aberto.
    #if(!is_null($pessoal->get_cargoComissao($idServidorPesquisado))){
    #    # Retira o botão de incluir
    #    $objeto->set_botaoIncluir(FALSE);
    #    
    #    # Informa o porquê
    #    $mensagem = "O botão de Incluir sumiu! Porque? Esse servidor já tem um cargo em comissão.<br/>"
    #               ."Somente será permitido a inserção de um novo cargo quanfo for informado a data de término do cargo atual.";
    #    $objeto->set_rotinaExtraListar("callout");
    #    $objeto->set_rotinaExtraListarParametro($mensagem);
    #}

    # select da lista
    $objeto->set_selectLista('SELECT CONCAT(tbtipocomissao.descricao," - (",tbtipocomissao.simbolo,")") as comissao,
                                     idComissao,
                                     tbcomissao.dtNom,
                                     tbcomissao.dtExo,
                                     idComissao
                                FROM tbcomissao LEFT JOIN tbtipocomissao USING (idTipoComissao)
                               WHERE idServidor = '.$idServidorPesquisado.'
                            ORDER BY 3 desc');

    # select do edita
    $objeto->set_selectEdita('SELECT idTipoComissao,
                                     descricao,
                                     protempore,
                                     dtNom,
                                     numProcNom,
                                     dtPublicNom,
                                     ciGepagNom,
                                     dtExo,
                                     numProcExo,
                                     dtPublicExo,
                                     ciGepagExo,
                                     obs,
                                     idServidor
                                FROM tbcomissao
                               WHERE idComissao = '.$id);

    # ordem da lista
    #$objeto->set_orderCampo($orderCampo);
    #$objeto->set_orderTipo($orderTipo);
    #$objeto->set_orderChamador('?fase=listar');

    # Caminhos
    $objeto->set_linkEditar('?fase=editar');
    $objeto->set_linkExcluir('?fase=excluir');
    $objeto->set_linkGravar('?fase=gravar');
    $objeto->set_linkListar('?fase=listar');

    # Parametros da tabela
    $objeto->set_label(array("Cargo","Descrição","Data de Nomeação","Data de Exoneração"));
    #$objeto->set_width(array(30,45,10,10));	
    $objeto->set_align(array("left","left","center"));
    $objeto->set_funcao(array(NULL,"descricaoComissao","date_to_php","date_to_php"));
    #$objeto->set_classe(array(NULL,"pessoal"));
    #$objeto->set_metodo(array(NULL,"get_nomeCompletoLotacao"));

    # Classe do banco de dados
    $objeto->set_classBd('pessoal');

    # Nome da tabela
    $objeto->set_tabela('tbcomissao');

    # Nome do campo id
    $objeto->set_idCampo('idComissao');

    # Tipo de label do formulário
    $objeto->set_formLabelTipo(1);

    # Pega os dados da combo tipo de Comissão
    $comissao = $pessoal->select('SELECT idTipoComissao,
                                       CONCAT(tbtipocomissao.simbolo," - (",tbtipocomissao.descricao,")") as comissao
                                  FROM tbtipocomissao WHERE ativo
                              ORDER BY simbolo');    
            
    # Campos para o formulario
    $objeto->set_campos(array( array ( 'nome' => 'idTipoComissao',
                                       'label' => 'Tipo da Cargo em Comissão:',
                                       'tipo' => 'combo',
                                       'required' => TRUE,
                                       'autofocus' => TRUE,
                                       'array' => $comissao,
                                       'size' => 20,
                                       'col' => 4,
                                       'title' => 'Tipo dp Cargo em Comissão',
                                       'linha' => 1),
                                array ('linha' => 1,
                                       'col' => 6,
                                       'nome' => 'descricao',
                                       'label' => 'Descrição do Cargo:',
                                       'tipo' => 'texto',
                                       'title' => 'Em alguns cargos é necessário uma descrição mais detalhada.&#013;Exemplo: Coordenador de curso.',
                                       'size' => 100),
                               array ( 'nome' => 'protempore',
                                       'label' => 'Pro Tempore:',
                                       'tipo' => 'combo',
                                       'array' => array(array(0,"Não"),array(1,"Sim")),
                                       'required' => TRUE,
                                       'size' => 20,
                                       'col' => 2,
                                       'title' => 'Informa se é pro tempore, ou seja, temporário para terminar mandato. (mandato tampão)',
                                       'linha' => 1),
                               array ( 'nome' => 'dtNom',
                                       'label' => 'Data da Nomeação:',
                                       'fieldset' => 'Dados da Nomeação',
                                       'tipo' => 'data',
                                       'size' => 20,
                                       'required' => TRUE,
                                       'title' => 'Data da Nomeação.',
                                       'col' => 3,
                                       'linha' => 3),
                               array ( 'nome' => 'numProcNom',
                                       'label' => 'Processo:',
                                       'tipo' => 'texto',
                                       'size' => 30,                              
                                       'title' => 'Número do Processo',
                                       'col' => 3,
                                       'linha' => 3), 
                               array ( 'nome' => 'dtPublicNom',
                                       'label' => 'Data da Pub. no DOERJ:',
                                       'tipo' => 'data',
                                       'size' => 20,
                                       'col' => 3,
                                       'title' => 'Data da Publicação no DOERJ.',
                                       'linha' => 3),
                               array ( 'nome' => 'ciGepagNom',                                   
                                       'label' => 'Documento:',
                                       'tipo' => 'texto',
                                       'col' => 3,
                                       'size' => 30,                                   
                                       'title' => 'Documento.',
                                       'linha' => 3),
                               array ( 'nome' => 'dtExo',
                                       'label' => 'Data da Exoneração:',
                                       'fieldset' => 'Dados da Exoneração',
                                       'tipo' => 'data',
                                       'col' => 3,
                                       'size' => 20,
                                       'title' => 'Data da Exoneração.',
                                       'linha' => 4),
                               array ( 'nome' => 'numProcExo',
                                       'label' => 'Processo:',
                                       'tipo' => 'texto',
                                       'size' => 30,
                                       'col' => 3,
                                       'title' => 'Processo de Exoneração',
                                       'linha' => 4), 
                               array ( 'nome' => 'dtPublicExo',
                                       'label' => 'Data da Pub. no DOERJ:',
                                       'tipo' => 'data',
                                       'size' => 20,
                                       'col' => 3,
                                       'title' => 'Data da Publicação no DOERJ.',
                                       'linha' => 4),
                               array ( 'nome' => 'ciGepagExo',                                   
                                       'label' => 'Documento:',
                                       'tipo' => 'texto',
                                       'size' => 30,
                                       'col' => 3,                                   
                                       'title' => 'Documento.',
                                       'linha' => 4), 
                                array ('linha' => 5,
                                       'nome' => 'obs',
                                       'col' => 12,
                                       'label' => 'Observação:',
                                       'tipo' => 'textarea',
                                       'fieldset' => 'fecha',
                                       'size' => array(80,5)),                                   
                               array ( 'nome' => 'idServidor',
                                       'label' => 'idServidor:',
                                       'tipo' => 'hidden',
                                       'padrao' => $idServidorPesquisado,
                                       'size' => 5,
                                       'title' => 'Matrícula',
                                       'linha' => 7)));

    # Log
    $objeto->set_idUsuario($idUsuario);
    $objeto->set_idServidorPesquisado($idServidorPesquisado);
    
    # Botão Extra
    $botaoVagas = new Button("Vagas","?fase=vagas");
    $botaoVagas->set_title('Exibe a disponibilidade dos cargos em comissão');
    $botaoVagas->set_accessKey('a');
    
    # Relatório
    $imagem = new Imagem(PASTA_FIGURAS.'print.png',NULL,15,15);
    $botaoRel = new Button();
    $botaoRel->set_imagem($imagem);
    $botaoRel->set_title("Imprimir Relatório de Histórico de Cargo em Comissão");
    $botaoRel->set_onClick("window.open('../grhRelatorios/servidorComissao.php','_blank','menubar=no,scrollbars=yes,location=no,directories=no,status=no,width=750,height=600');");
        
    $objeto->set_botaoListarExtra(array($botaoRel,$botaoVagas));
    
    ################################################################

    switch ($fase){
        
        case "" :
        case "listar" :
           $objeto->listar();
           break;
           
        case "editar" :            
            # Limita o tamanho da tela
            $grid = new Grid();
            $grid->abreColuna(12);
           
            ## Rotina abaixo retirado para "tirar o gesso" 
            # Verifica se é incluir
            #if(is_null($id)){
            #    br(2);              
            #    # Verifica se o número de vagas é 0
            #    if($quantidadeCargos == 0){
            #        $msg = 'Não há vagas disponíveis nos cargos em Comissão.\nTodas as vagas estão ocupadas.';
            #        alert($msg);
            #        back(1);
            #    }elseif(!is_null($pessoal->get_cargoComissao($idServidorPesquisado))){  // se o servidor já possui cargo
            #        $msg = 'Esse servidor já ocupa um cargo em comissão na data de hoje.\nSomente é permitido nomeação de servidor que não esteja ocupando cargo em comissão.';
            #        alert($msg);
            #        back(1);
            #    }                    
            #    else 
            #        $objeto->editar();
            #}
            #else // se é editar
            
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
            $objeto->gravar($id,'servidorComissaoExtra.php');
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
}else{
    loadPage("../../areaServidor/sistema/login.php");
}