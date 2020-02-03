<?php
/**
 * Controle de Readaptaçao do Servidor
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
    $intra = new Intra();
    $pessoal = new Pessoal();
    $readaptacao = new Readaptacao($idServidorPesquisado);
	
    # Verifica a fase do programa
    $fase = get('fase','listar');

    # pega o id (se tiver)
    $id = soNumeros(get('id'));
    
    # Verifica se veio da área de Redução
    $origem = get_session("origem");
    
    $jscript = '// Pega os valores da pendêencia
                var pendencia = $("#pendencia").val();
                
                // Verifica o valor da pendência quando o form é carregado
                if(pendencia == 1){
                    $("#dadosPendencia").show();
                    $("#dtEnvioPendencia").show();
                    $("#div11").show();
                }else{
                    $("#dadosPendencia").hide();
                    $("#dtEnvioPendencia").hide();
                    $("#div11").hide();
                }
                
                // Pega os valores do resultado
                var resultado = $("#resultado").val();

                // Verifica o valor do resultado quando o form é carregado
                if(resultado == 1){
                    $("#dtInicio").show();
                    $("#periodo").show();
                    $("#numCiInicio").show();
                    $("#numCiTermino").show();                    
                    $("#div15").show();
                }else{
                    $("#dtInicio").hide();
                    $("#periodo").hide();
                    $("#numCiInicio").hide();
                    $("#numCiTermino").hide();                    
                    $("#div15").hide();
                }
                
                // Pega o valor do origem
                var origem = $("#origem").val();
                
                // Verifica o valor do origem quando o form é carregado
                if(origem == 1){
                    $("#labeldtSolicitacao").hide();
                    $("#dtSolicitacao").hide();
                    $("#dtEnvioPericia").hide();
                    $("#dtChegadaPericia").hide();
                    $("#dtChegadaPericia").hide();
                    $("#dtAgendadaPericia").hide();
                    $("#pericia").hide();
                    $("#resultado").hide();
                    $("#dtCiencia").hide();
                    $("#div5").hide();
                    $("#dadosPendencia").hide();
                    $("#dtEnvioPendencia").hide();
                    $("#div11").hide();
                    $("#dtPublicacao").show();
                    $("#dtInicio").show();
                    $("#periodo").show();
                    $("#numCiInicio").show();
                    $("#numCiTermino").show();                    
                    $("#div15").show();
                }else{
                    $("#labeldtSolicitacao").show();
                    $("#dtSolicitacao").show();
                    $("#dtEnvioPericia").show();
                    $("#dtChegadaPericia").show();
                    $("#dtChegadaPericia").show();
                    $("#dtAgendadaPericia").show();
                    $("#pericia").show();
                    $("#resultado").show();
                    $("#dtCiencia").show();
                    $("#div5").show();                    
                }
        
                // Verifica o valor da pendência quando se muda o valor do campo
                $("#pendencia").change(function(){
                    var pendencia = $("#pendencia").val();
                    
                    if(pendencia == 1){
                        $("#dadosPendencia").show();
                        $("#dtEnvioPendencia").show();
                        $("#div11").show();
                    }else{
                        $("#dadosPendencia").hide();
                        $("#dtEnvioPendencia").hide();
                        $("#div11").hide();
                    }
                });
                
                // Verifica o valor do resultado quando se muda o valor do campo
                $("#resultado").change(function(){
                    var resultado = $("#resultado").val();
                    
                    if(resultado == 1){
                        $("#dtInicio").show();
                        $("#periodo").show();
                        $("#numCiInicio").show();
                        $("#numCiTermino").show();                    
                        $("#div15").show();
                    }else{
                        $("#dtInicio").hide();
                        $("#periodo").hide();
                        $("#numCiInicio").hide();
                        $("#numCiTermino").hide();                    
                        $("#div15").hide();
                    }                
                });
                
                // Verifica o valor do resultado quando se muda o valor do campo
                $("#origem").change(function(){
                
                    // Pega o valor do origem
                    var origem = $("#origem").val();

                    // Verifica o valor do origem quando o form é carregado
                    if(origem == 1){
                        $("#labeldtSolicitacao").hide();
                        $("#dtSolicitacao").hide();
                        $("#dtEnvioPericia").hide();
                        $("#dtChegadaPericia").hide();
                        $("#dtChegadaPericia").hide();
                        $("#dtAgendadaPericia").hide();
                        $("#pericia").hide();
                        $("#resultado").hide();
                        $("#dtCiencia").hide();
                        $("#div5").hide();
                        $("#dadosPendencia").hide();
                        $("#dtEnvioPendencia").hide();
                        $("#div11").hide();
                        $("#dtPublicacao").show();
                        $("#dtInicio").show();
                        $("#periodo").show();
                        $("#numCiInicio").show();
                        $("#numCiTermino").show();                    
                        $("#div15").show();
                    }else{
                        $("#labeldtSolicitacao").show();
                        $("#dtSolicitacao").show();
                        $("#dtEnvioPericia").show();
                        $("#dtChegadaPericia").show();
                        $("#dtChegadaPericia").show();
                        $("#dtAgendadaPericia").show();
                        $("#pericia").show();
                        $("#resultado").show();
                        $("#dtCiencia").show();
                        $("#div5").show();                    
                    }
                });
                
';
    
    # Começa uma nova página
    $page = new Page();
    
    if($fase == "editar"){
        $page->set_ready($jscript);
    }
    
    $page->iniciaPagina();

    # Cabeçalho da Página
    AreaServidor::cabecalho();

    # Abre um novo objeto Modelo
    $objeto = new Modelo();
    
    ################################################################
    
    if($fase == "listar"){
        # Limita o tamanho da tela
        $grid = new Grid();
        $grid->abreColuna(12);
        
        # botão de voltar da lista
        if(vazio($origem)){
            $voltar = 'servidorMenu.php';
        }else{
            $voltar = 'areaReadaptacao.php';
        }
        
        # Cria um menu
        $menu = new MenuBar();

        # Botão voltar
        $linkBotao1 = new Link("Voltar",$voltar);
        $linkBotao1->set_class('button');
        $linkBotao1->set_title('Volta para a página anterior');
        $linkBotao1->set_accessKey('V');
        $menu->add_link($linkBotao1,"left");

        # Incluir
        $linkBotao2 = new Link("Incluir",'?fase=editar');
        $linkBotao2->set_class('button');
        $linkBotao2->set_title('Incluir uma nova solicitação de redução');
        $linkBotao2->set_accessKey('I');
        $menu->add_link($linkBotao2,"right");

        # Relatório
        $imagem = new Imagem(PASTA_FIGURAS.'print.png',NULL,15,15);
        $botaoRel = new Button();
        $botaoRel->set_imagem($imagem);
        $botaoRel->set_title("Imprimir Relatório de Histórico de Processo de readaptação");
        $botaoRel->set_url("../grhRelatorios/servidorReadaptacao.php");
        $botaoRel->set_target("_blank");        
        $menu->add_link($botaoRel,"right");        
        $menu->show();
               
        $objeto->set_botaoVoltarLista(FALSE);
        $objeto->set_botaoIncluir(FALSE);

        $grid->fechaColuna();
        $grid->fechaGrid();
        
        get_DadosServidor($idServidorPesquisado);
    }else{
        # Exibe os dados do Servidor
        $objeto->set_rotinaExtra("get_DadosServidor");
        $objeto->set_rotinaExtraParametro($idServidorPesquisado); 
    }

    ################################################################

    # Nome do Modelo (aparecerá nos fildset e no caption da tabela)
    $objeto->set_nome('Controle de Readaptação de Servidor');

    # botão de voltar da lista
    $objeto->set_voltarLista('servidorMenu.php');

    # select da lista
    $objeto->set_selectLista('SELECT CASE origem
                                         WHEN 1 THEN "Ex-Ofício"
                                         WHEN 2 THEN "Solicitada"
                                         ELSE "--"
                                     END,
                                     CASE tipo
                                         WHEN 1 THEN "Inicial"
                                         WHEN 2 THEN "Renovação"
                                         ELSE "--"
                                     END,
                                     idReadaptacao,
                                     processo,
                                     idReadaptacao,                                     
                                     idReadaptacao,
                                     idReadaptacao,
                                     idReadaptacao,
                                     idReadaptacao,
                                     idReadaptacao,                                   
                                     idReadaptacao
                                FROM tbreadaptacao
                               WHERE idServidor = '.$idServidorPesquisado.'
                            ORDER BY status, dtInicio desc');

    # select do edita
    $objeto->set_selectEdita('SELECT origem,
                                     tipo,
                                     status,
                                     processo,
                                     dtSolicitacao,
                                     dtEnvioPericia,
                                     dtChegadaPericia,
                                     dtAgendadaPericia,
                                     pendencia,
                                     resultado,
                                     dtCiencia,
                                     dadosPendencia,
                                     dtEnvioPendencia,                                     
                                     dtPublicacao,
                                     pgPublicacao,
                                     dtInicio,
                                     periodo,
                                     parecer,
                                     idServidor
                                FROM tbreadaptacao
                               WHERE idReadaptacao = '.$id);

    # Caminhos
    $objeto->set_linkEditar('?fase=editar');
    $objeto->set_linkExcluir('?fase=excluir');
    $objeto->set_linkGravar('?fase=gravar');
    $objeto->set_linkListar('?fase=listar');
    
    $objeto->set_formatacaoCondicional(array( array('coluna' => 2,
                                                    'valor' => 'Em Aberto',
                                                    'operador' => '=',
                                                    'id' => 'emAberto'),  
                                              array('coluna' => 2,
                                                    'valor' => 'Arquivado',
                                                    'operador' => '=',
                                                    'id' => 'arquivado'),
                                              array('coluna' => 2,
                                                    'valor' => 'Vigente',
                                                    'operador' => '=',
                                                    'id' => 'vigenteReducao')   
                                                    ));

    # Parametros da tabela
    $objeto->set_label(array("Origem","Tipo","Status","Processo","Solicitado em:","Pericia","Resultado","Publicação","Período","Documentos"));
    #$objeto->set_width(array(10,10,10,20,20,10,10));	
    $objeto->set_align(array("center","center","center","center","center","left","center","center","left","left"));
    #$objeto->set_funcao(array(NULL,NULL,"date_to_php"));
    
    $objeto->set_classe(array(NULL,NULL,"Readaptacao",NULL,"Readaptacao","Readaptacao","Readaptacao","Readaptacao","Readaptacao","Readaptacao"));
    $objeto->set_metodo(array(NULL,NULL,"exibeStatus",NULL,"exibeSolicitacao","exibeDadosPericia","exibeResultado","exibePublicacao","exibePeriodo","exibeBotaoDocumentos"));
    
    # Número de Ordem
    $objeto->set_numeroOrdem(TRUE);
    $objeto->set_numeroOrdemTipo("d");

    # Classe do banco de dados
    $objeto->set_classBd('pessoal');

    # Nome da tabela
    $objeto->set_tabela('tbreadaptacao');

    # Nome do campo id
    $objeto->set_idCampo('idReadaptacao');

    # Tipo de label do formulário
    $objeto->set_formLabelTipo(1);

    # Campos para o formulario
    $objeto->set_campos(array( array ( 'nome' => 'origem',
                                       'label' => 'Origem:',
                                       'tipo' => 'combo',
                                       'array' => array(array(1,"Ex-Ofício"),array(2,"Solicitada")),
                                       'size' => 2,
                                       'autofocus' => TRUE,
                                       'valor' => 0,
                                       'col' => 2,
                                       'title' => 'Se a solicitação foi arquivada ou não.',
                                       'linha' => 1),
                               array ( 'nome' => 'tipo',
                                       'label' => 'Tipo:',
                                       'tipo' => 'combo',
                                       'array' => array(array(NULL,NULL),
                                                        array(1,"Inicial"),
                                                        array(2,"Renovação")),
                                       'required' => TRUE,
                                       'size' => 2,
                                       'valor' => 0,
                                       'col' => 2,
                                       'title' => 'Se é inicial ou renovação.',
                                       'linha' => 1),
                               array ( 'nome' => 'status',
                                       'label' => 'Status:',
                                       'tipo' => 'combo',
                                       'array' => array(array(1,"Em Aberto"),array(2,"Vigente"),array(3,"Arquivado")),
                                       'size' => 2,
                                       'valor' => 0,
                                       'col' => 2,
                                       'disabled' => TRUE,
                                       'title' => 'Se a solicitação foi arquivada ou não.',
                                       'linha' => 1),
                               array ( 'linha' => 1,
                                       'nome' => 'processo',
                                       'label' => 'Processo:',
                                       'tipo' => 'processo',
                                       'col' => 3,
                                       'size' => 25,
                                       'title' => 'Número do processo.'),
                               array ( 'nome' => 'dtSolicitacao',
                                       'label' => 'Solicitado em:',
                                       'tipo' => 'data',
                                       'size' => 30,                                  
                                       'title' => 'A data da Solicitação.',
                                       'col' => 3,                                    
                                       'linha' => 1),
                               array ( 'nome' => 'dtEnvioPericia',
                                       'label' => 'Data de Envio:',
                                       'tipo' => 'data',
                                       'size' => 10,
                                       'fieldset' => 'Da Perícia',
                                       'col' => 3,                                       
                                       'title' => 'A data do envio do processo à perícia.',
                                       'linha' => 2),
                               array ( 'nome' => 'dtChegadaPericia',
                                       'label' => 'Data da Chegada:',
                                       'tipo' => 'data',
                                       'size' => 10,
                                       'col' => 3,
                                       'title' => 'A data da chegada do processo à perícia.',
                                       'linha' => 2),
                               array ( 'nome' => 'dtAgendadaPericia',
                                       'label' => 'Data Agendada:',
                                       'tipo' => 'data',
                                       'size' => 10,
                                       'col' => 3,
                                       'title' => 'A data agendada pela perícia.',
                                       'linha' => 2),
                               array ( 'nome' => 'pendencia',
                                       'label' => 'Há pendências:',
                                       'tipo' => 'simnao',
                                       'size' => 5,
                                       'title' => 'Se há pendências',
                                       'col' => 3,
                                       'linha' => 3),
                               array ( 'nome' => 'resultado',
                                       'label' => 'Resultado:',
                                       'tipo' => 'combo',
                                       'array' => array(array(NULL,""),array(1,"Deferido"),array(2,"Indeferido")),
                                       'size' => 20,                               
                                       'title' => 'Se o processo foi deferido ou indeferido',
                                       'col' => 3,
                                       'linha' => 3), 
                               array ( 'nome' => 'dtCiencia',
                                       'label' => 'Data da Ciência:',
                                       'tipo' => 'data',
                                       'size' => 10,
                                       'col' => 3,
                                       'title' => 'A data da ciência do servidor.',
                                       'linha' => 3),
                               array ( 'linha' => 4,
                                       'col' => 9,
                                       'nome' => 'dadosPendencia',
                                       'label' => 'Pendências:',
                                       'tipo' => 'textarea',                                    
                                       'fieldset' => 'Das Pendências',
                                       'title' => 'Quais são as pendências.',
                                       'size' => array(80,3)),
                               array ( 'nome' => 'dtEnvioPendencia',
                                       'label' => 'Data de Envio:',
                                       'tipo' => 'data',
                                       'size' => 10,
                                       'col' => 3,
                                       'title' => 'Data de envio das pendências da Perícia.',
                                       'linha' => 5),                               
                               array ( 'nome' => 'dtPublicacao',
                                       'label' => 'Data da Publicação:',
                                       'tipo' => 'data',
                                       'size' => 10,
                                       'col' => 3,
                                       'title' => 'A Data da Publicação.',
                                       'fieldset' => 'Publicação:',
                                       'linha' => 6),
                               array ( 'nome' => 'pgPublicacao',
                                       'label' => 'Página:',
                                       'tipo' => 'texto',
                                       'size' => 5,
                                       'col' => 2,
                                       'title' => 'A página da Publicação no DOERJ.',
                                       'linha' => 6),
                               array ( 'nome' => 'dtInicio',
                                       'label' => 'Data de Inicio:',
                                       'fieldset' => 'Quando Deferido',
                                       'tipo' => 'data',
                                       'size' => 10,
                                       'col' => 3,
                                       'title' => 'A data em que o servidor passou a receber o benefício.',
                                       'linha' => 7),
                               array ( 'nome' => 'periodo',
                                       'label' => 'Período (Meses):',
                                       'tipo' => 'texto',
                                       'size' => 10,
                                       'col' => 2,
                                       'title' => 'O período em meses do benefício.',
                                       'linha' => 7),
                               array ( 'linha' => 8,
                                       'col' => 12,
                                       'nome' => 'parecer',
                                       'label' => 'Parecer:',
                                       'tipo' => 'textarea',                                    
                                       'fieldset' => 'fecha',
                                       'title' => 'Parecer descrevendo a readaptação do servidor.',
                                       'size' => array(80,4)),
                               array ( 'nome' => 'idServidor',
                                       'label' => 'idServidor',
                                       'tipo' => 'hidden',
                                       'padrao' => $idServidorPesquisado,
                                       'size' => 5,
                                       'linha' => 11)));
    
    # Log
    $objeto->set_idUsuario($idUsuario);
    $objeto->set_idServidorPesquisado($idServidorPesquisado);

    ################################################################

    switch ($fase){
        case "" :
        case "listar" :
            # Divide a página em 2 colunas
            $grid = new Grid();
            
        #########################################################################################################
            
            # Contatos
            $grid->abreColuna(12,4);
            
                # Pega os telefones
                $telefones = $pessoal->get_telefones($idServidorPesquisado);
                
                # Pega os Emails
                $emailPessoal = $pessoal->get_emailPessoal($idServidorPesquisado);
                $emailUenf = $pessoal->get_emailUenf($idServidorPesquisado);
                $emails = NULL;
                
                # junta os Emails
                if(!vazio($emailPessoal)){
                    $emails .= "$emailPessoal<br/>"; 
                }

                if(!vazio($emailUenf)){
                    $emails .= "$emailUenf<br/>"; 
                }
                
                #$emails = trataNulo($emails);
                
                $painel = new Callout();
                $painel->abre();
                
                    tituloTable("Contatos:");
                    br();
                    
                    #p("Telefone(s)","center","f12");
                    p($telefones,"center","f14");
                    #p("E-mail(s)","center","f12");
                    p($emails,"center","f14");
                                    
                    #$div = new Div("divEditaProcesso");
                    #$div->abre();
                    #    $link = new Link("Editar Contatos",'servidorContatos.php',"Edita os contatos do servidor");
                    #    $link->set_id("editaProcesso");
                    #    $link->show();
                    #$div->fecha();  
                
                $painel->fecha();
                
            $grid->fechaColuna();
            
            $grid->fechaGrid();        
            $objeto->listar(); 
            break;
            
        case "ver" :
            $menu = new Menu();
            $menu->add_item('titulo','Documentos');
            $menu->add_item('linkWindow','CI de Início do Benefício','../grhRelatorios/ciReducaoInicio.php?id='.$id);
            $menu->add_item('linkWindow','CI de Término do Benefício','../grhRelatorios/ciReducaotermino.php?id='.$id);
            
            $objeto->set_menuLateralEditar($menu);
            
            $objeto->ver($id); 
            break;
        
        case "editar" :
        case "excluir" :
            $objeto->$fase($id); 
            break;

        case "gravar" :
            $objeto->gravar($id,"servidorReadaptacaoExtra.php");
            break;
        
################################################################################################################
        
        # Ci Início        
        case "ciInicioForm" :
            
            # Voltar
            botaoVoltar("?");
            
            # Dados do Servidor
            get_DadosServidor($idServidorPesquisado);
            
            # Pega os Dados
            $dados = $readaptacao->get_dados($id);

            # Da Readaptação
            $numCiInicio = $dados['numCiInicio'];
            $dtCiInicio = $dados['dtCiInicio'];
            $dtInicio = date_to_php($dados['dtInicio']);
            $dtPublicacao = date_to_php($dados['dtPublicacao']);
            $pgPublicacao = $dados['pgPublicacao'];
            $periodo = $dados['periodo'];
            $processo = $dados['processo'];
            $tipo = $dados['tipo'];
            $parecer = $dados['parecer'];
            $textoCi = mb_strtolower($dados['textoCi']);
            
            # Servidor
            $nomeServidor = $pessoal->get_nome($idServidorPesquisado);
            $idFuncional = $pessoal->get_idFuncional($idServidorPesquisado);
            
            # Trata a publicação
            if(vazio($pgPublicacao)){
                $publicacao = $dtPublicacao;
            }else{
                $publicacao = "$dtPublicacao, pág. $pgPublicacao";
            }
            
            if(vazio($textoCi)){
                $textoCi = $parecer;
            }
                        
            # Chefia imediata desse servidor
            $idChefiaImediataDestino = $pessoal->get_chefiaImediata($idServidorPesquisado);              // idServidor do chefe
            $nomeGerenteDestino = $pessoal->get_nome($idChefiaImediataDestino);                          // Nome do chefe
            $gerenciaImediataDescricao = $pessoal->get_chefiaImediataDescricao($idServidorPesquisado);   // Descrição do cargo
                                    
            # Limita a tela
            $grid = new Grid("center");
            $grid->abreColuna(10);
            br(3);
            
            # Título
            titulo("Ci de início");
            $painel = new Callout();
            $painel->abre();
            
            # Monta o formulário para confirmação dos dados necessários a emissão da CI
            $form = new Form('?fase=ciInicioFormValida&id='.$id);        

            # numCi
            $controle = new Input('numCiInicio','texto','Ci n°:',1);
            $controle->set_size(20);
            $controle->set_linha(1);
            $controle->set_col(2);
            #$controle->set_required(TRUE);
            $controle->set_autofocus(TRUE);
            $controle->set_valor($numCiInicio);
            $controle->set_title('Número da Ci informando a chefia imediata do servidor da data de início do benefício.');
            $form->add_item($controle);

            # dtCi
            $controle = new Input('dtCiInicio','data','Data da Ci:',1);
            $controle->set_size(10);
            $controle->set_linha(1);
            $controle->set_col(4);
            $controle->set_valor($dtCiInicio);
            #$controle->set_required(TRUE);
            $controle->set_title('A data da CI de inicio.');
            $form->add_item($controle);
            
            # tipo
            $controle = new Input('tipo','combo','Tipo:',1);
            $controle->set_size(10);
            $controle->set_linha(1);
            $controle->set_col(4);
            $controle->set_array(array(array(NULL,NULL),
                                       array(1,"Inicial"),
                                       array(2,"Renovação")));
            $controle->set_valor($tipo);
            $controle->set_title('Se é Inicial ou Renovação.');
            $form->add_item($controle);
            
            # Chefia
            $controle = new Input('chefia','texto','Chefia:',1);
            $controle->set_size(200);
            $controle->set_linha(2);
            $controle->set_col(6);
            $controle->set_valor($nomeGerenteDestino);
            #$controle->set_required(TRUE);
            $controle->set_title('O nome da chefia imediata.');
            $form->add_item($controle);
            
            # Cargo
            $controle = new Input('cargo','texto','Cargo:',1);
            $controle->set_size(200);
            $controle->set_linha(2);
            $controle->set_col(6);
            $controle->set_valor($gerenciaImediataDescricao);
            #$controle->set_required(TRUE);
            $controle->set_title('O Cargo em comissão da chefia.');
            $form->add_item($controle);
            
            # texto da ci
            $controle = new Input('textoCi','textarea','Texto da Ci:',1);
            $controle->set_size(array(80,5));
            $controle->set_linha(3);
            $controle->set_col(12);
            $controle->set_valor($textoCi);
            #$controle->set_required(TRUE);
            $controle->set_title('O texto da CI.');
            $form->add_item($controle);
            
            # submit
            $controle = new Input('salvar','submit');
            $controle->set_valor('Salvar');
            $controle->set_linha(5);
            $controle->set_col(2);
            $form->add_item($controle);
            
            # submit
            $controle = new Input('imprimir','submit');
            $controle->set_valor('Salvar & Imprimir');
            $controle->set_linha(5);
            $controle->set_col(2);
            $form->add_item($controle);

            $form->show();
            
            $grid->fechaColuna();
            $grid->fechaGrid();
            break;
        
#########################################################
        
        case "ciInicioFormValida" :
            
            # Pega os Dados
            $dados = $readaptacao->get_dados($id);

            # Da Readaptação
            $numCiInicio = $dados['numCiInicio'];
            $dtCiInicio = $dados['dtCiInicio'];
            $dtInicio = date_to_php($dados['dtInicio']);
            $dtPublicacao = date_to_php($dados['dtPublicacao']);
            $pgPublicacao = $dados['pgPublicacao'];
            $periodo = $dados['periodo'];
            $processo = $dados['processo'];
            $tipo = $dados['tipo'];
            
            # Pega os dados Digitados
            $botaoEscolhido = get_post_action("salvar","imprimir");
            $numCiInicioDigitados = vazioPraNulo(post("numCiInicio"));
            $dtCiInicioDigitado = vazioPraNulo(post("dtCiInicio"));
            $tipo = vazioPraNulo(post("tipo"));
            
            $chefeDigitado = post("chefia");
            $cargoDigitado = post("cargo");
            $textoCi = post("textoCi");
            
            # Prepara para enviar por get
            $array = array($chefeDigitado,$cargoDigitado);
            $array = serialize($array);
            
            # Verifica se houve alterações
            $alteracoes = NULL;
            $atividades = NULL;
            
            # Verifica as alterações para o log
            if($numCiInicio <> $numCiInicioDigitados){
                $alteracoes .= '[numCiInicio] '.$numCiInicio.'->'.$numCiInicioDigitados.'; ';
            }
            if($dtCiInicio <> $dtCiInicioDigitado){
                $alteracoes .= '[dtCiInicio] '.date_to_php($dtCiInicio).'->'.date_to_php($dtCiInicioDigitado).'; ';
            }
            
            # Erro
            $msgErro = NULL;
            $erro = 0;
            
            # Verifica o número da Ci
            if(vazio($numCiInicioDigitados)){
                $msgErro.='Não tem número de Ci de Início cadastrada!\n';
                $erro = 1;
            }
            
            # Verifica a data da CI
            if(vazio($dtCiInicioDigitado)){
                $msgErro.='Não tem data da Ci de Início cadastrada!\n';
                $erro = 1;
            }
            
            # Verifica a data da Publicação
            if(vazio($dtPublicacao)){
                $msgErro.='Não tem data da Publicação cadastrada!\n';
                $erro = 1;
            }
            
            # Verifica a data de Início
            if(vazio($dtInicio)){
                $msgErro.='Não tem data de início do benefício cadastrada!\n';
                $erro = 1;
            }
            
            # Verifica o período
            if(vazio($periodo)){
                $msgErro.='O período não foi cadastrado!\n';
                $erro = 1;
            }              
            
            # Verifica o período
            if(vazio($tipo)){
                $msgErro.='Deve-se informar se é inicial ou renovação!\n';
                $erro = 1;
            }              
            
            # Salva as alterações
            $pessoal->set_tabela("tbreadaptacao");
            $pessoal->set_idCampo("idReadaptacao");
            $campoNome = array('numCiInicio','dtCiInicio','tipo','textoCi');
            $campoValor = array($numCiInicioDigitados,$dtCiInicioDigitado,$tipo,$textoCi);
            $pessoal->gravar($campoNome,$campoValor,$id);
            $data = date("Y-m-d H:i:s");                
                
            # Grava o log das alterações caso tenha
            if(!is_null($alteracoes)){
                $atividades .= 'Alterou: '.$alteracoes;
                $tipoLog = 2;
                $intra->registraLog($idUsuario,$data,$atividades,"tbreadaptacao",$id,$tipoLog,$idServidorPesquisado);
            }
            
            # Exibe o relatório ou salva de acordo com o botão pressionado
            if($botaoEscolhido == "imprimir"){
                if($erro == 0){
                    # Exibe o relatório
                    if($tipo == 1){                        
                        loadPage("../grhRelatorios/readaptacaoCiInicio.php?id=$id&array=$array","_blank");
                    }else{
                        loadPage("../grhRelatorios/readaptacaoCiProrrogacao.php?id=$id&array=$array","_blank");
                    }
                    loadPage("?");
                }else{
                    alert($msgErro);
                    back(1);
                }            
            }else{
                loadPage("?");
            }
            break;
            
################################################################################################################
        
        # Ci 90 Dias
        case "ci90Form" :
            
            # Voltar
            botaoVoltar("?");
            
            # Dados do Servidor
            get_DadosServidor($idServidorPesquisado);

            # Pega os Dados do Banco
            $dados = $readaptacao->get_dados($id);
            $numCi90 = $dados["numCi90"];
            $dtCi90= $dados["dtCi90"];
            $dtPublicacao = $dados["dtPublicacao"];
            $pgPublicacao = $dados["pgPublicacao"];
            
            # Chefia imediata desse servidor
            #$idChefiaImediataDestino = $pessoal->get_chefiaImediata($idServidorPesquisado);              // idServidor do chefe
            #$nomeGerenteDestino = $pessoal->get_nome($idChefiaImediataDestino);                          // Nome do chefe
            #$gerenciaImediataDescricao = $pessoal->get_chefiaImediataDescricao($idServidorPesquisado);   // Descrição do cargo
            
            # Limita a tela
            $grid = new Grid("center");
            $grid->abreColuna(10);
            br(3);
            
            # Título
            tituloTable("Controle de Redução da Carga Horária<br/>Ci de 90 Dias (ou menos)");
            $painel = new Callout();
            $painel->abre();
            
            # Monta o formulário para confirmação dos dados necessários a emissão da CI
            $form = new Form('?fase=ci90FormValida&id='.$id);        

            # numCiInicio
            $controle = new Input('numCi90','texto','Ci n°:',1);
            $controle->set_size(20);
            $controle->set_linha(1);
            $controle->set_col(3);
            #$controle->set_required(TRUE);
            $controle->set_autofocus(TRUE);
            $controle->set_valor($numCi90);
            $controle->set_title('Número da Ci informando que em 90 dias o benefício irá terminar.');
            $form->add_item($controle);

            # dtCiInicio
            $controle = new Input('dtCi90','data','Data da Ci:',1);
            $controle->set_size(10);
            $controle->set_linha(1);
            $controle->set_col(3);
            $controle->set_valor($dtCi90);
            #$controle->set_required(TRUE);
            $controle->set_title('A data da CI de 90 dias.');
            $form->add_item($controle);
            
            # submit
            $controle = new Input('salvar','submit');
            $controle->set_valor('Salvar');
            $controle->set_linha(5);
            $controle->set_col(2);
            $form->add_item($controle);
            
            # submit
            $controle = new Input('imprimir','submit');
            $controle->set_valor('Salvar & Imprimir');
            $controle->set_linha(5);
            $controle->set_col(2);
            $form->add_item($controle);

            $form->show();
            $painel->fecha();
            
            $grid->fechaColuna();
            $grid->fechaGrid();
            break;
        
        case "ci90FormValida" :
            
            # Pega os Dados do Banco
            $dados = $readaptacao->get_dados($id);
            $numCi90 = $dados["numCi90"];
            $dtCi90= $dados["dtCi90"];
            $dtPublicacao = $dados["dtPublicacao"];
            $pgPublicacao = $dados["pgPublicacao"];
            
            # Pega os dados Digitados
            $botaoEscolhido = get_post_action("salvar","imprimir");
            $numCi90Digitados = vazioPraNulo(post("numCi90"));
            $dtCi90Digitado = vazioPraNulo(post("dtCi90"));
             
            # Verifica se houve alterações
            $alteracoes = NULL;
            $atividades = NULL;
            
            # Verifica as alterações para o log
            if($numCi90 <> $numCi90Digitados){
                $alteracoes .= '[numCi90] '.$numCi90.'->'.$numCi90Digitados.'; ';
            }
            if($dtCi90 <> $dtCi90Digitado){
                if(vazio($dtCi90Digitado)){
                    $alteracoes .= '[dtCi90] '.date_to_php($dtCi90).'->  ; ';
                }else{
                    $alteracoes .= '[dtCi90] '.date_to_php($dtCi90).'->'.date_to_php($dtCi90Digitado).'; ';
                }
            }
            
            # Erro
            $msgErro = NULL;
            $erro = 0;
            
            # Verifica se apertou o imprimir
            if($botaoEscolhido == "imprimir"){
            
                # Verifica o número da Ci
                if(vazio($numCi90Digitados)){
                    $msgErro.='Não tem número de Ci de 90 dias cadastrada!\n';
                    $erro = 1;
                }

                # Verifica a data da CI
                if(vazio($dtCi90Digitado)){
                    $msgErro.='Não tem data da Ci de 90 dias cadastrada!\n';
                    $erro = 1;
                }

                # Verifica a data da Publicação
                if(vazio($dtPublicacao)){
                    $msgErro.='Não tem data da Publicação cadastrada!\n';
                    $erro = 1;
                }
            }
            
            # Salva as alterações
            $pessoal->set_tabela("tbreadaptacao");
            $pessoal->set_idCampo("idReadaptacao");
            $campoNome = array('numCi90','dtCi90');
            $campoValor = array($numCi90Digitados,$dtCi90Digitado);
            $pessoal->gravar($campoNome,$campoValor,$id);
            $data = date("Y-m-d H:i:s");

            # Grava o log das alterações caso tenha
            if(!is_null($alteracoes)){
                $atividades .= 'Alterou: '.$alteracoes;
                $tipoLog = 2;
                $intra->registraLog($idUsuario,$data,$atividades,"tbreadaptacao",$id,$tipoLog,$idServidorPesquisado);
            }                
               
            # Exibe o relatório ou salva de acordo com o botão pressionado
            if($botaoEscolhido == "imprimir"){
                if($erro == 0){
                    loadPage('../grhRelatorios/readaptacaoCi90.php?id='.$id,"_blank");
                    loadPage("?");
                }else{
                    alert($msgErro);
                    back(1);
                }            
            }else{
                loadPage("?");
            }
            break;
            
################################################################################################################
        
        # Ci Término
        
        case "ciTerminoForm" :
            
            # Voltar
            botaoVoltar("?");
            
            # Dados do Servidor
            get_DadosServidor($idServidorPesquisado);
            
            # Pega os Dados
            $dados = $readaptacao->get_dados($id);

            # Da Redução
            $numCitermino = $dados['numCiTermino'];
            $dtCitermino = $dados['dtCiTermino'];
            $dtInicio = date_to_php($dados['dtInicio']);
            $dtPublicacao = date_to_php($dados['dtPublicacao']);
            $pgPublicacao = $dados['pgPublicacao'];
            $periodo = $dados['periodo'];
            $processo = $dados['processo'];
            
            # Chefia imediata desse servidor
            $idChefiaImediataDestino = $pessoal->get_chefiaImediata($idServidorPesquisado);              // idServidor do chefe
            $nomeGerenteDestino = $pessoal->get_nome($idChefiaImediataDestino);                          // Nome do chefe
            $gerenciaImediataDescricao = $pessoal->get_chefiaImediataDescricao($idServidorPesquisado);   // Descrição do cargo
            
            # Limita a tela
            $grid = new Grid("center");
            $grid->abreColuna(10);
            br(3);
            
            # Título
            titulo("Ci de Término");
            $painel = new Callout();
            $painel->abre();
            
            # Monta o formulário para confirmação dos dados necessários a emissão da CI
            $form = new Form('?fase=ciTerminoFormValida&id='.$id);        

            # numCi
            $controle = new Input('numCiTermino','texto','Ci n°:',1);
            $controle->set_size(20);
            $controle->set_linha(1);
            $controle->set_col(4);
            $controle->set_required(TRUE);
            $controle->set_autofocus(TRUE);
            $controle->set_valor($numCitermino);
            $controle->set_title('Número da Ci informando a chefia imediata do servidor da data de Término do benefício.');
            $form->add_item($controle);

            # dtCi
            $controle = new Input('dtCiTermino','data','Data da Ci:',1);
            $controle->set_size(10);
            $controle->set_linha(1);
            $controle->set_col(4);
            $controle->set_valor($dtCitermino);
            $controle->set_required(TRUE);
            $controle->set_title('A data da CI de término.');
            $form->add_item($controle);
            
            # Chefia
            $controle = new Input('chefia','texto','Chefia:',1);
            $controle->set_size(200);
            $controle->set_linha(2);
            $controle->set_col(12);
            $controle->set_valor($nomeGerenteDestino);
            #$controle->set_required(TRUE);
            $controle->set_title('O nome da chefia imediata.');
            $form->add_item($controle);
            
            # Cargo
            $controle = new Input('cargo','texto','Cargo:',1);
            $controle->set_size(200);
            $controle->set_linha(3);
            $controle->set_col(12);
            $controle->set_valor($gerenciaImediataDescricao);
            #$controle->set_required(TRUE);
            $controle->set_title('O Cargo em comissão da chefia.');
            $form->add_item($controle);

            # submit
            $controle = new Input('salvar','submit');
            $controle->set_valor('Salvar');
            $controle->set_linha(5);
            $controle->set_col(2);
            $form->add_item($controle);
            
            # submit
            $controle = new Input('imprimir','submit');
            $controle->set_valor('Salvar & Imprimir');
            $controle->set_linha(5);
            $controle->set_col(2);
            $form->add_item($controle);

            $form->show();
            
            $grid->fechaColuna();
            $grid->fechaGrid();
            break;

#########################################################
        
        case "ciTerminoFormValida" :
            
            # Pega os Dados
            $dados = $readaptacao->get_dados($id);
            $numCiTermino = $dados['numCiTermino'];
            $dtCiTermino = $dados['dtCiTermino'];
            $dtInicio = date_to_php($dados['dtInicio']);
            $dtPublicacao = date_to_php($dados['dtPublicacao']);
            $pgPublicacao = $dados['pgPublicacao'];
            $periodo = $dados['periodo'];
            $processo = $dados['processo'];
            
            # Pega os dados Digitados
            $botaoEscolhido = get_post_action("salvar","imprimir");
            $numCiTerminoDigitados = vazioPraNulo(post("numCiTermino"));
            $dtCiTerminoDigitado = vazioPraNulo(post("dtCiTermino"));
            
            $chefeDigitado = post("chefia");
            $cargoDigitado = post("cargo");
            
            # Prepara para enviar por get
            $array = array($chefeDigitado,$cargoDigitado);
            $array = serialize($array);
            
            # Verifica se houve alterações
            $alteracoes = NULL;
            $atividades = NULL;
            
            # Verifica as alterações para o log
            if($numCiTermino <> $numCiTerminoDigitados){
                $alteracoes .= '[numCiTermino] '.$numCiTermino.'->'.$numCiTerminoDigitados.'; ';
            }
            if($dtCiTermino <> $dtCiTerminoDigitado){
                $alteracoes .= '[dtCiTermino] '.date_to_php($dtCiTermino).'->'.date_to_php($dtCiTerminoDigitado).'; ';
            }
            
            # Erro
            $msgErro = NULL;
            $erro = 0;
            
            # Verifica o número da Ci
            if(vazio($numCiTerminoDigitados)){
                $msgErro.='Não tem número de Ci de Término cadastrada!\n';
                $erro = 1;
            }
            
            # Verifica a data da CI
            if(vazio($dtCiTerminoDigitado)){
                $msgErro.='Não tem data da Ci de Término cadastrada!\n';
                $erro = 1;
            }
            
            # Verifica a data da Publicação
            if(vazio($dtPublicacao)){
                $msgErro.='Não tem data da Publicação cadastrada!\n';
                $erro = 1;
            }
            
            # Verifica a data de Início
            if(vazio($dtInicio)){
                $msgErro.='Não tem data de início do benefício cadastrada!\n';
                $erro = 1;
            }
            
            # Verifica o período
            if(vazio($periodo)){
                $msgErro.='O período não foi cadastrado!\n';
                $erro = 1;
            }              
            
            # Salva as alterações
            $pessoal->set_tabela("tbreadaptacao");
            $pessoal->set_idCampo("idReadaptacao");
            $campoNome = array('numCiTermino','dtCiTermino');
            $campoValor = array($numCiTerminoDigitados,$dtCiTerminoDigitado);
            $pessoal->gravar($campoNome,$campoValor,$id);
            $data = date("Y-m-d H:i:s");                
                
            # Grava o log das alterações caso tenha
            if(!is_null($alteracoes)){
                $atividades .= 'Alterou: '.$alteracoes;
                $tipoLog = 2;
                $intra->registraLog($idUsuario,$data,$atividades,"tbreadaptacao",$id,$tipoLog,$idServidorPesquisado);
            }
            
            # Exibe o relatório ou salva de acordo com o botão pressionado
            if($botaoEscolhido == "imprimir"){
                if($erro == 0){
                    loadPage("../grhRelatorios/readaptacaoCiTermino.php?id=$id&array=$array","_blank");
                    loadPage("?");
                }else{
                    alert($msgErro);
                    back(1);
                }            
            }else{
                loadPage("?");
            }
            break;

        ########################################################33
    }									 	 		

    $page->terminaPagina();
}else{
    loadPage("../../areaServidor/sistema/login.php");
}