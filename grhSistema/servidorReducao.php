<?php
/**
 * Controle do Redução de Carega Horária
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
    $reducao = new ReducaoCargaHoraria($idServidorPesquisado);
	
    # Pega o número do processo (Quando tem)
    $processo = trataNulo($reducao->get_numProcesso());
    $processoAntigo = $reducao->get_numProcessoAntigo();
	
    # Verifica a fase do programa
    $fase = get('fase','listar');

    # pega o id (se tiver)
    $id = soNumeros(get('id'));
    
    # Verifica se veio da área de Redução
    $areaReducao = get_session("areaReducao");
    
    $jscript = '// Pega os valores da pendêencia
                var pendencia = $("#pendencia").val();
                
                
                // Verifica o valor da pendência quando o form é carregado
                if(pendencia == 1){
                    $("#dadosPendencia").show();
                    $("#dtEnvioPendencia").show();
                    $("#div8").show();
                }else{
                    $("#dadosPendencia").hide();
                    $("#dtEnvioPendencia").hide();
                    $("#div8").hide();
                }
                
                // Pega os valores do resultado
                var resultado = $("#resultado").val();

                // Verifica o valor do resultado quando o form é carregado
                if(resultado == 1){
                    $("#dtPublicacao").show();
                    $("#dtInicio").show();
                    $("#periodo").show();
                    $("#numCiInicio").show();
                    $("#numCiTermino").show();                    
                    $("#div10").show();
                }else{
                    $("#dtPublicacao").hide();
                    $("#dtInicio").hide();
                    $("#periodo").hide();
                    $("#numCiInicio").hide();
                    $("#numCiTermino").hide();                    
                    $("#div10").hide();
                }
        
                // Verifica o valor da pendência quando se muda o valor do campo
                $("#pendencia").change(function(){
                    var pendencia = $("#pendencia").val();
                    
                    if(pendencia == 1){
                        $("#dadosPendencia").show();
                        $("#dtEnvioPendencia").show();
                        $("#div8").show();
                    }else{
                        $("#dadosPendencia").hide();
                        $("#dtEnvioPendencia").hide();
                        $("#div8").hide();
                    }
                });
                
                // Verifica o valor do resultado quando se muda o valor do campo
                $("#resultado").change(function(){
                    var resultado = $("#resultado").val();
                    
                    if(resultado == 1){
                        $("#dtPublicacao").show();
                        $("#dtInicio").show();
                        $("#periodo").show();
                        $("#numCiInicio").show();
                        $("#numCiTermino").show();                    
                        $("#div10").show();
                    }else{
                        $("#dtPublicacao").hide();
                        $("#dtInicio").hide();
                        $("#periodo").hide();
                        $("#numCiInicio").hide();
                        $("#numCiTermino").hide();                    
                        $("#div10").hide();
                    }                
                });
                
';
    
    
    # Começa uma nova página
    $page = new Page();
    $page->set_ready($jscript);
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
        if($areaReducao){
            $voltar = 'areaBeneficios.php';
        }else{
            $voltar = 'servidorMenu.php';
        }
        
        # Cria um menu
        $menu = new MenuBar();

        # Botão voltar
        $linkBotao1 = new Link("Voltar",$voltar);
        $linkBotao1->set_class('button');
        $linkBotao1->set_title('Volta para a página anterior');
        $linkBotao1->set_accessKey('V');
        $menu->add_link($linkBotao1,"left");

        if($processo <> "--"){
            # Incluir
            $linkBotao2 = new Link("Incluir",'?fase=editar');
            $linkBotao2->set_class('button');
            $linkBotao2->set_title('Incluir uma nova solicitação de redução');
            $linkBotao2->set_accessKey('I');
            $menu->add_link($linkBotao2,"right");
        }
        
        # Site
        $botaoSite = new Button("Site da GRH");
        $botaoSite->set_target('_blank');
        $botaoSite->set_title("Pagina no site da GRH sobre Redução da Carga Horária");
        $botaoSite->set_url("http://uenf.br/dga/grh/gerencia-de-recursos-humanos/reducao-de-carga-horaria/");
        $menu->add_link($botaoSite,"right");

        # Legislação
        $botaoLegis = new Button("Legislação");
        $botaoLegis->set_disabled(TRUE);
        $botaoLegis->set_title('Exibe as Legislação pertinente');
        #$botaoLegis->set_onClick("window.open('https://docs.google.com/document/d/e/2PACX-1vRfb7P06MCBHAwd15hKm6KWV4-y0I8yBzlac58uAA-xCHeaL9aCbtSGCgGguZzaPQafvXYvGqWhwG0r/pub','_blank','menubar=no,scrollbars=yes,location=no,directories=no,status=no,width=750,height=600');");
        #$menu->add_link($botaoLegis,"right");

        # Relatório
        $imagem = new Imagem(PASTA_FIGURAS.'print.png',NULL,15,15);
        $botaoRel = new Button();
        $botaoRel->set_imagem($imagem);
        $botaoRel->set_title("Imprimir Relatório de Histórico de Processo de redução da carga horária");
        $botaoRel->set_url("../grhRelatorios/servidorReducao.php");
        $botaoRel->set_target("_blank");        
        $menu->add_link($botaoRel,"right");
        
        # Fluxograma
        $imagem = new Imagem(PASTA_FIGURAS.'fluxograma.png',NULL,15,15);
        $botaoFluxo = new Button();
        $botaoFluxo->set_imagem($imagem);
        $botaoFluxo->set_title("Exibe o Fluxograma de todo o processo redução da carga horária");
        $botaoFluxo->set_url("../_diagramas/reducao.jpg");
        $botaoFluxo->set_target("_blank");        
        $menu->add_link($botaoFluxo,"right");
        
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
    $objeto->set_nome('Controle de Redução da Carga Horária');

    # botão de voltar da lista
    $objeto->set_voltarLista('servidorMenu.php');

    # select da lista
    $objeto->set_selectLista('SELECT idReducao,
                                     dtSolicitacao,
                                     idReducao,
                                     idReducao,
                                     idReducao,
                                     idReducao,
                                     idReducao,                                   
                                     idReducao
                                FROM tbreducao
                               WHERE idServidor = '.$idServidorPesquisado.'
                            ORDER BY dtSolicitacao desc');

    # select do edita
    $objeto->set_selectEdita('SELECT dtSolicitacao,
                                     status,
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
                                     numCiInicio,
                                     dtCiInicio,
                                     numCiTermino,
                                     dtCiTermino,
                                     obs,
                                     idServidor
                                FROM tbreducao
                               WHERE idReducao = '.$id);

    # Caminhos
    $objeto->set_linkEditar('?fase=editar');
    $objeto->set_linkExcluir('?fase=excluir');
    $objeto->set_linkGravar('?fase=gravar');
    $objeto->set_linkListar('?fase=listar');
    
    $objeto->set_formatacaoCondicional(array( array('coluna' => 0,
                                                    'valor' => 'Em Aberto',
                                                    'operador' => '=',
                                                    'id' => 'emAberto'),  
                                              array('coluna' => 0,
                                                    'valor' => 'Arquivado',
                                                    'operador' => '=',
                                                    'id' => 'arquivado'),
                                              array('coluna' => 0,
                                                    'valor' => 'Vigente',
                                                    'operador' => '=',
                                                    'id' => 'vigenteReducao')   
                                                    ));

    # Parametros da tabela
    $objeto->set_label(array("Status","Solicitado em:","Pericia","Resultado","Publicação","Período","Documentos"));
    #$objeto->set_width(array(10,10,10,20,20,10,10));	
    $objeto->set_align(array("center","center","left","center","center","left","left"));
    $objeto->set_funcao(array(NULL,"date_to_php"));
    
    $objeto->set_classe(array("ReducaoCargaHoraria",NULL,"ReducaoCargaHoraria","ReducaoCargaHoraria","ReducaoCargaHoraria","ReducaoCargaHoraria","ReducaoCargaHoraria"));
    $objeto->set_metodo(array("exibeStatus",NULL,"exibeDadosPericia","exibeResultado","exibePublicacao","exibePeriodo","exibeBotaoDocumentos"));
    
    # Número de Ordem
    $objeto->set_numeroOrdem(TRUE);
    $objeto->set_numeroOrdemTipo("d");

    # Classe do banco de dados
    $objeto->set_classBd('pessoal');

    # Nome da tabela
    $objeto->set_tabela('tbreducao');

    # Nome do campo id
    $objeto->set_idCampo('idReducao');

    # Tipo de label do formulário
    $objeto->set_formLabelTipo(1);

    # Campos para o formulario
    $objeto->set_campos(array( array ( 'nome' => 'dtSolicitacao',
                                       'label' => 'Solicitado em:',
                                       'tipo' => 'data',
                                       'size' => 30,
                                       'required' => TRUE,
                                       'autofocus' => TRUE,
                                       'title' => 'A data da Solicitação.',
                                       'col' => 3,                                    
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
                                array ('linha' => 4,
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
                                       'fieldset' => 'Quando Deferido',
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
                               array ( 'nome' => 'numCiInicio',
                                       'label' => 'CI informando Início:',
                                       'tipo' => 'texto',
                                       'size' => 20,
                                       'col' => 3,
                                       'title' => 'Número da Ci informando a chefia imediata do servidor da data de início do benefício.',
                                       'linha' => 8),
                               array ( 'nome' => 'dtCiInicio',
                                       'label' => 'Data da Ci:',
                                       'tipo' => 'data',
                                       'size' => 10,
                                       'col' => 3,
                                       'title' => 'A data da CI de inicio.',
                                       'linha' => 8),        
                               array ( 'nome' => 'numCiTermino',
                                       'label' => 'CI informando Término:',
                                       'tipo' => 'texto',
                                       'size' => 20,
                                       'col' => 3,
                                       'title' => 'Número da Ci informando a chefia imediata do servidor da data de término do benefício.',
                                       'linha' => 9),
                               array ( 'nome' => 'dtCiTermino',
                                       'label' => 'Data da Ci:',
                                       'tipo' => 'data',
                                       'size' => 10,
                                       'col' => 3,
                                       'title' => 'A data da CI de término.',
                                       'linha' => 9),  
                               array ('linha' => 10,
                                       'col' => 12,
                                       'nome' => 'obs',
                                       'label' => 'Obs:',
                                       'tipo' => 'textarea',                                    
                                       'fieldset' => 'fecha',
                                       'title' => 'Observações.',
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

            # Processo
            $grid->abreColuna(12,4);
            
                #$processo = trataNulo($pessoal->get_numProcessoReducao($idServidorPesquisado));
                $painel = new Callout();
                $painel->abre();
                
                    tituloTable("N° do Processo:");
                    br();
                    p($processo,'f14',"center");
                    
                    # Verifica se tem processo antigo
                    if(!is_null($processoAntigo)){
                        p($processoAntigo."<br/>(Antigo)","processoAntigoReducao");
                    }
                    
                    $div = new Div("divEditaProcesso");
                    $div->abre();
                        if($processo == "--"){
                            $link = new Link("Incluir Processo",'servidorProcessoReducao.php',"Inclui o número do processo de redução");
                        }else{
                            $link = new Link("Editar Processo",'servidorProcessoReducao.php',"Edita o número do processo de redução");
                        }
                        $link->set_id("editaProcesso");
                        $link->show();
                    $div->fecha();  
                
                $painel->fecha();
                
            $grid->fechaColuna();
            
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
                                    
                    $div = new Div("divEditaProcesso");
                    $div->abre();
                        $link = new Link("Editar Contatos",'servidorContatos.php',"Edita os contatos do servidor");
                        $link->set_id("editaProcesso");
                        $link->show();
                    $div->fecha();  
                
                $painel->fecha();
                
            $grid->fechaColuna();
            
        #########################################################################################################
            
            # Documentos
            $grid->abreColuna(12,4);
                
                $painel = new Callout();
                $painel->abre();
                
                tituloTable("Documentos:");
                br();
                
                $menu = new Menu();
                #$menu->add_item('titulo','Documentos');
                $menu->add_item('linkWindow','Declaração de Atribuições','../grhRelatorios/ciReducaoInicio.php?id='.$id);
                $menu->add_item('linkWindow','Declaração de Inquérito Administrativo','../grhRelatorios/ciReducaotermino.php?id='.$id);
                $menu->show();
                
                $painel->fecha();
                
            $grid->fechaColuna();
            
        #########################################################################################################
            
            # tarefas
            /*
            $grid->abreColuna(12,12,6);
                
                $painel = new Callout();
                $painel->abre();
                
                    # Exibe o título
                    tituloTable("Tarefas:");
                    br();
                    
                    # Pega a ultima solicitação
                    $idReducao = $reducao->get_ultimaSolicitacaoAberto();
                    
                    # Pega as tarefas
                    if(is_null($idReducao)){
                        $mensagem = NULL;
                    }else{
                        $mensagem = $reducao->get_tarefas($idReducao);
                    }
                
                    # Verifica se tem mensagem a ser exibida
                    if(!is_null($mensagem)){
                        p($mensagem,'f13');
                    }else{
                        p('---','f14','center');
                    }        
                                    
                $painel->fecha();
                
            $grid->fechaColuna();
             
             */
            
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
            $objeto->gravar($id,"servidorReducaoExtra.php");
            break;
    }									 	 		

    $page->terminaPagina();
}else{
    loadPage("../../areaServidor/sistema/login.php");
}