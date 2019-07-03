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

if($acesso){    
    # Conecta ao Banco de Dados    
    $pessoal = new Pessoal();
    $cargoComissao = new CargoComissao();
    
    # Verifica a fase do programa
    $fase = get('fase','listar');

    # pega o id (se tiver)
    $id = soNumeros(get('id'));
    
    # Pega o origem quando vier do cadastro de Cargo em comissão
    $origem = get_session('origem'); 
    
    # Ordem da tabela
    $orderCampo = get('orderCampo');
    $orderTipo = get('orderTipo');
    
    # Rotina em jscript
    $script = '
            <script type="text/javascript" language="javascript">

                    $(document).ready(function(){
                        $("#idTipoComissao").change(function(){
                            $("#idDescricaoComissao").load("servidorComissaoExtraCombo.php?tipo="+$("#idTipoComissao").val());
                        })
                    })

            </script>';

    # Começa uma nova página
    $page = new Page();
    if($fase == "editar"){
        $page->set_jscript($script);
    }
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
    $objeto->set_selectLista('SELECT CONCAT(simbolo," - ",tbtipocomissao.descricao),
                                     idComissao,
                                     tbcomissao.dtNom,
                                     tbcomissao.dtExo,
                                     idComissao,
                                     idComissao
                                FROM tbcomissao JOIN tbtipocomissao USING (idTipoComissao)
                               WHERE idServidor = '.$idServidorPesquisado.'
                            ORDER BY 3 desc');

    # select do edita
    $objeto->set_selectEdita('SELECT idTipoComissao,
                                     idDescricaoComissao,
                                     tipo,
                                     dtNom,
                                     dtAtoNom,
                                     numProcNom,
                                     dtPublicNom,
                                     dtExo,
                                     dtAtoExo,
                                     numProcExo,
                                     dtPublicExo,
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
    
    # Ato de Nomeação
    $botao1 = new Link(NULL,'?fase=atoNomeacao&id=','Imprime o Ato de Nomeação');
    $botao1->set_imagem(PASTA_FIGURAS_GERAIS.'relatorio.png',20,20);
    
    # Termo de Posse
    $botao2 = new Link(NULL,'?fase=termoPosse&id=','Imprime o Termo de Posse');
    $botao2->set_imagem(PASTA_FIGURAS_GERAIS.'relatorio.png',20,20);
    
    # Ato de Exoneração
    $botao3 = new Link(NULL,'?fase=atoExoneracao&id=','Imprime o Ato de Exoneração');
    $botao3->set_imagem(PASTA_FIGURAS_GERAIS.'relatorio.png',20,20);
    
    # Coloca o objeto link na tabela			
    $objeto->set_link(array("","","","",$botao1,$botao2,$botao3));

    # Parametros da tabela
    $objeto->set_label(array("Cargo","Descrição","Nomeação","Exoneração","Ato de<br/>Nomeaçao","Termo de<br/>Posse","Ato de<br/>Exoneração"));
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
    $tipoComissao = $pessoal->select('SELECT idTipoComissao,
                                         CONCAT(tbtipocomissao.simbolo," - (",tbtipocomissao.descricao,")") as comissao
                                    FROM tbtipocomissao
                                ORDER BY ativo desc, simbolo');    
    
    array_unshift($tipoComissao, array(NULL,NULL));
    
    # Pega os dados da descrição
    if(is_null($id)){
        $descricao = $pessoal->select('SELECT idDescricaoComissao,
                                              tbdescricaocomissao.descricao
                                         FROM tbdescricaocomissao JOIN tbtipocomissao USING (idTipoComissao)
                                     ORDER BY tbtipocomissao.simbolo, tbtipocomissao.descricao,  tbdescricaocomissao.descricao');
    
    }else{
        $comissao = $cargoComissao->get_dados($id);
        
        $descricao = $pessoal->select('SELECT idDescricaoComissao,
                                              tbdescricaocomissao.descricao
                                         FROM tbdescricaocomissao JOIN tbtipocomissao USING (idTipoComissao)
                                        WHERE tbdescricaocomissao.idTipoComissao = '.$comissao["idTipoComissao"].' 
                                     ORDER BY tbtipocomissao.simbolo, tbtipocomissao.descricao,  tbdescricaocomissao.descricao');
    
    }
    
    array_unshift($descricao, array(NULL,NULL));
    
    # Label
    $labelDescricao = 'Descrição do Cargo:';
    
    # Exibe antiga descrição do cargo - Temporariamente
    if(Verifica::acesso($idUsuario,1)){
        if(!is_null($id)){
            $comissao = $cargoComissao->get_dados($id);
            $labelDescricao .= " (".$comissao['descricao']." )";
        }
    }
            
    # Campos para o formulario
    $objeto->set_campos(array( array ( 'nome' => 'idTipoComissao',
                                       'label' => 'Tipo da Cargo em Comissão:',
                                       'tipo' => 'combo',
                                       'required' => TRUE,
                                       'autofocus' => TRUE,
                                       'array' => $tipoComissao,
                                       'size' => 20,
                                       'col' => 4,
                                       'title' => 'Tipo dp Cargo em Comissão',
                                       'linha' => 1),
                                array ('linha' => 1,
                                       'col' => 6,
                                       'nome' => 'idDescricaoComissao',
                                       'label' => $labelDescricao,
                                       'tipo' => 'combo',
                                       'array' => $descricao,
                                       'size' => 100),
                               array ( 'nome' => 'tipo',
                                       'label' => 'Tipo:',
                                       'tipo' => 'combo',
                                       'array' => array(array(0,"Padrão"),array(1,"Pro Tempore"), array(2,"Designado")),
                                       'required' => TRUE,
                                       'size' => 20,
                                       'col' => 2,
                                       'title' => 'Informa se é pro tempore, ou seja, temporário para terminar mandato. (mandato tampão)',
                                       'linha' => 1),
                               array ( 'nome' => 'dtNom',
                                       'label' => 'Data da Nomeação:',
                                       'fieldset' => 'Nomeação',
                                       'tipo' => 'data',
                                       'size' => 20,
                                       'required' => TRUE,
                                       'title' => 'Data da Nomeação.',
                                       'col' => 3,
                                       'linha' => 2),
                               array ( 'nome' => 'dtAtoNom',
                                       'label' => 'Data do Ato do Reitor:',
                                       'title' => 'Data do Ato do Reitor da Nomeação',
                                       'tipo' => 'data',
                                       'size' => 20,
                                       'col' => 3,
                                       'linha' => 2),
                               array ( 'nome' => 'numProcNom',
                                       'label' => 'Processo:',
                                       'tipo' => 'processo',
                                       'size' => 30,                              
                                       'title' => 'Número do Processo',
                                       'col' => 3,
                                       'linha' => 2), 
                               array ( 'nome' => 'dtPublicNom',
                                       'label' => 'Data da Publicação:',
                                       'tipo' => 'data',
                                       'size' => 20,
                                       'col' => 3,
                                       'title' => 'Data da Publicação no DOERJ.',
                                       'linha' => 2),
                               array ( 'nome' => 'dtExo',
                                       'label' => 'Data da Exoneração:',
                                       'fieldset' => 'Exoneração',
                                       'tipo' => 'data',
                                       'col' => 3,
                                       'size' => 20,
                                       'title' => 'Data da Exoneração.',
                                       'linha' => 3),
                               array ( 'nome' => 'dtAtoExo',
                                       'label' => 'Data do Ato do Reitor:',
                                       'title' => 'Data do Ato do Reitor da Exoneraçao',
                                       'tipo' => 'data',
                                       'size' => 20,
                                       'col' => 3,
                                       'linha' => 3),
                               array ( 'nome' => 'numProcExo',
                                       'label' => 'Processo:',
                                       'tipo' => 'processo',
                                       'size' => 30,
                                       'col' => 3,
                                       'title' => 'Processo de Exoneração',
                                       'linha' => 3), 
                               array ( 'nome' => 'dtPublicExo',
                                       'label' => 'Data da Publicação:',
                                       'tipo' => 'data',
                                       'size' => 20,
                                       'col' => 3,
                                       'title' => 'Data da Publicação no DOERJ.',
                                       'linha' => 3), 
                                array ('linha' => 4,
                                       'nome' => 'obs',
                                       'col' => 12,
                                       'label' => 'Observação:',
                                       'tipo' => 'textarea',
                                       'fieldset' => 'fecha',
                                       'size' => array(80,2)),                                   
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
    
    # Botão Extra
    $botaoVagas = new Button("Vagas","?fase=vagas");
    $botaoVagas->set_title('Exibe a disponibilidade dos cargos em comissão');
    $botaoVagas->set_accessKey('a');    
    
    # Relatório
    $imagem = new Imagem(PASTA_FIGURAS.'print.png',NULL,15,15);
    $botaoRel = new Button();
    $botaoRel->set_imagem($imagem);
    $botaoRel->set_title("Imprimir Relatório de Histórico de Cargo em Comissão");
    $botaoRel->set_url("../grhRelatorios/servidorComissao.php");
    $botaoRel->set_target("_blank");
        
    $objeto->set_botaoListarExtra(array($botaoRel,$botaoVagas));
    
    # Constroi o link de voltar de acordo com a origem
    if(!vazio($origem)){
        $objeto->set_linkListar($origem);
        $objeto->set_voltarForm($origem);
    }
    
    
    ################################################################

    switch ($fase){
        
        case "" :
        case "listar" :
           $objeto->listar();
           break;
       
    ######################################   
           
        case "editar" :            
            # Limita o tamanho da tela
            $grid = new Grid();
            $grid->abreColuna(12);
            
            # Cadastrar Descrição - visivel somente para adm
            if(Verifica::acesso($idUsuario,1)){
                
                $botao1 = new Button("Descrição");
                $botao1->set_title("Cadastra uma nova Descrição");
                $botao1->set_target("_blank");
                $botao1->set_url("cadastroDescricaoComissao.php?fase=editar");

                $objeto->set_botaoEditarExtra(array($botao1));
            }

            $objeto->editar($id);
            
            $grid->fechaColuna();
            $grid->fechaGrid();
            break;
    
    ######################################
            
        case "excluir" :
            $objeto->$fase($id);  
            break;
        
    ######################################

        case "gravar" :
            $objeto->gravar($id,'servidorComissaoExtra.php');
            break;
        
    ######################################
        
        case "atoNomeacao" :
            # Verifica se o campo ocupante anterior foi preenchido
            $comissao = $cargoComissao->get_dados($id);
            $ocupanteAnterior = $comissao['ocupanteAnterior'];
            $dtAtoNom = date_to_php($comissao['dtAtoNom']);
            $msgErro = NULL;
            $erro = 0;
            
            # Verifica se tem ocupante anterior esta preenchido
            if(is_null($ocupanteAnterior)){
                $msgErro.='O campo Ocupante Anterior deve estar preenchido!\n';
                $erro = 1;
            }
            
            # Verifica se a data do ato do reitor de nomeaçao esta preenchido
            if(is_null($dtAtoNom)){
                $msgErro.='A data do ato do reitor de nomeaçao deve estar preenchida!\n';
                $erro = 1;
            }
            
            # Verifica se tem algum erro
            if($erro == 0){
                loadPage('../grhRelatorios/comissao.AtoNomeacao.php?id='.$id,'_blank');
            }else{
                alert($msgErro);
                back(1);
            }	
            
            loadPage('?');
            break;
            
    ######################################
            
        case "termoPosse" :
            # Verifica se o campo ocupante anterior foi preenchido
            $comissao = $cargoComissao->get_dados($id);
            $publicacao = $comissao['dtPublicNom'];
            $dtAtoNom = $comissao['dtAtoNom'];
            $msgErro = NULL;
            
            # Verifica se tem ocupante anterior esta preenchido
            if(is_null($publicacao)){
                $msgErro.='O campo da data de publicaçao da nomeaçao deve estar preenchido!\n';
                $erro = 1;
            }
            
            # Verifica se a data do ato do reitor de nomeaçao esta preenchido
            if(is_null($dtAtoNom)){
                $msgErro.='A data do ato do reitor de nomeaçao deve estar preenchida!\n';
                $erro = 1;
            }
            
            # Verifica se tem algum erro
            if ($erro == 0){
                
                loadPage('../grhRelatorios/comissao.TermodePosse.php?id='.$id,'_blank');
            }else{
                alert($msgErro);
                back(1);
            }	
            
            loadPage('?');
            break;
            
    ######################################
        
        case "atoExoneracao" :
            # Verifica se o campo ocupante anterior foi preenchido
            $comissao = $cargoComissao->get_dados($id);
            $dtExo = $comissao['dtExo'];
            $dtAtoExo = date_to_php($comissao['dtAtoExo']);
            $msgErro = NULL;
            
            # Verifica se tem ocupante anterior esta preenchido
            if(is_null($dtExo)){
                $msgErro.='A data de exoneração esta em branco!!\n';
                $erro = 1;
            }
            
            # Verifica se a data do ato do reitor de nomeaçao esta preenchido
            if(is_null($dtAtoExo)){
                $msgErro.='A data do ato do reitor de exoneraçao esta em branco!\n';
                $erro = 1;
            }
            
            # Verifica se tem algum erro
            if ($erro == 0){
                loadPage('../grhRelatorios/comissao.AtoExoneracao.php?id='.$id,'_blank');
            }else{
                alert($msgErro);
                back(1);
            }	
            
            loadPage('?');
            break;
            
    ######################################
        
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