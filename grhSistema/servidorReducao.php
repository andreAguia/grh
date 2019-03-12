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
	
    # Verifica a fase do programa
    $fase = get('fase','listar');

    # pega o id (se tiver)
    $id = soNumeros(get('id'));
    
    # Começa uma nova página
    $page = new Page();			
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
        
        # Cria um menu
        $menu = new MenuBar();

        # Botão voltar
        $linkBotao1 = new Link("Voltar",'servidorMenu.php');
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
        $menu->add_link($botaoLegis,"right");

        # Relatório
        $imagem = new Imagem(PASTA_FIGURAS.'print.png',NULL,15,15);
        $botaoRel = new Button();
        $botaoRel->set_imagem($imagem);
        $botaoRel->set_title("Imprimir Relatório de Histórico de Processo de redução da carga horária");
        $botaoRel->set_onClick("window.open('../grhRelatorios/servidorReducao.php','_blank','menubar=no,scrollbars=yes,location=no,directories=no,status=no,width=750,height=600');");
        $menu->add_link($botaoRel,"right");
        
        # Fluxograma
        $imagem = new Imagem(PASTA_FIGURAS.'fluxograma.png',NULL,15,15);
        $botaoFluxo = new Button();
        $botaoFluxo->set_imagem($imagem);
        $botaoFluxo->set_title("Exibe o Fluxograma de todo o processo redução da carga horária");
        $botaoFluxo->set_onClick("window.open('../_diagramas/reducao.png','_blank','menubar=no,scrollbars=yes,location=no,directories=no,status=no,width=1300,height=700');");
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
    $objeto->set_selectLista('SELECT CASE
                                     WHEN arquivado = 0 THEN "Em Aberto"
                                     WHEN arquivado = 1 THEN "Arquivado"
                                     END,
                                     dtSolicitacao,
                                     idReducao,
                                     CASE
                                     WHEN resultado = 1 THEN "Deferido"
                                     WHEN resultado = 2 THEN "Indeferido"
                                     ELSE "---"
                                     END,
                                     dtPublicacao,
                                     idReducao,
                                     idReducao,                                   
                                     idReducao
                                FROM tbreducao
                               WHERE idServidor = '.$idServidorPesquisado.'
                            ORDER BY dtSolicitacao desc');

    # select do edita
    $objeto->set_selectEdita('SELECT dtSolicitacao,
                                     arquivado,
                                     dtEnvioPericia,
                                     dtChegadaPericia,
                                     dtAgendadaPericia,
                                     resultado,
                                     pendencia,
                                     dtEnvioPendencia,
                                     dtPublicacao,
                                     dtInicio,
                                     periodo,
                                     numCiInicio,
                                     numCiTermino,
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
                                                    'id' => 'arquivado')                                           
                                                    ));

    # Parametros da tabela
    $objeto->set_label(array("Status","Solicitado em:","Pericia","Resultado","Publicação","Período","CI"));
    #$objeto->set_width(array(10,10,10,20,20,10,10));	
    $objeto->set_align(array("center","center","left","center","center","left"));
    $objeto->set_funcao(array(NULL,"date_to_php",NULL,NULL,"date_to_php"));
    
    $objeto->set_classe(array(NULL,NULL,"ReducaoCargaHoraria",NULL,NULL,"ReducaoCargaHoraria","ReducaoCargaHoraria"));
    $objeto->set_metodo(array(NULL,NULL,"exibeDadorPericia",NULL,NULL,"exibePeriodo","exibeCi"));
    
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
                               array ( 'nome' => 'arquivado',
                                       'label' => 'Arquivado:',
                                       'tipo' => 'simnao',
                                       'size' => 2,
                                       'valor' => 0,
                                       'col' => 2,
                                       'title' => 'Se a solicitação foi arquivada ou não.',
                                       'linha' => 1),
                               array ( 'nome' => 'dtEnvioPericia',
                                       'label' => 'Data de Envio à Perícia:',
                                       'tipo' => 'data',
                                       'size' => 10,
                                       'col' => 3,
                                       'fieldset' => 'Da Perícia',
                                       'title' => 'A data do envio do processo à perícia.',
                                       'linha' => 2),
                               array ( 'nome' => 'dtChegadaPericia',
                                       'label' => 'Data da Chegada à Perícia:',
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
                               array ( 'nome' => 'resultado',
                                       'label' => 'Resultado:',
                                       'tipo' => 'combo',
                                       'array' => array(array(NULL,""),array(1,"Deferido"),array(2,"Indeferido")),
                                       'size' => 20,                               
                                       'title' => 'Se o processo foi deferido ou indeferido',
                                       'col' => 2,
                                       'linha' => 2),             
                               array ( 'nome' => 'pendencia',
                                       'label' => 'Há pendências:',
                                       'tipo' => 'simnao',
                                       'size' => 5,
                                       'fieldset' => 'Das Pendências',
                                       'title' => 'Se há pendências',
                                       'col' => 2,
                                       'linha' => 3),
                               array ( 'nome' => 'dtEnvioPendencia',
                                       'label' => 'Data de Envio das Pendências:',
                                       'tipo' => 'data',
                                       'size' => 10,
                                       'col' => 3,
                                       'title' => 'Data de envio das pendências da Perícia.',
                                       'linha' => 3),
                               array ( 'nome' => 'dtPublicacao',
                                       'label' => 'Data da Publicação:',
                                       'tipo' => 'data',
                                       'size' => 10,
                                       'col' => 3,
                                       'title' => 'A Data da Publicação no DOERJ.',
                                       'fieldset' => 'Quando Deferido',
                                       'linha' => 5),
                               array ( 'nome' => 'dtInicio',
                                       'label' => 'Data do Inicio do Benefício:',
                                       'tipo' => 'data',
                                       'size' => 10,
                                       'col' => 3,
                                       'title' => 'A data em que o servidor passou a receber o benefício.',
                                       'linha' => 5),
                               array ( 'nome' => 'periodo',
                                       'label' => 'Período em Meses:',
                                       'tipo' => 'texto',
                                       'size' => 10,
                                       'col' => 2,
                                       'title' => 'O período em meses do benefício.',
                                       'linha' => 5),
                               array ( 'nome' => 'numCiInicio',
                                       'label' => 'CI informando Início:',
                                       'tipo' => 'texto',
                                       'size' => 20,
                                       'col' => 3,
                                       'title' => 'Número da Ci informando a chefia imediata do servidor da data de início do benefício.',
                                       'linha' => 6),
                               array ( 'nome' => 'numCiTermino',
                                       'label' => 'CI informando Término:',
                                       'tipo' => 'texto',
                                       'size' => 20,
                                       'col' => 3,
                                       'title' => 'Número da Ci informando a chefia imediata do servidor da data de término do benefício.',
                                       'linha' => 6),
                               array ( 'nome' => 'idServidor',
                                       'label' => 'idServidor',
                                       'tipo' => 'hidden',
                                       'padrao' => $idServidorPesquisado,
                                       'size' => 5,
                                       'linha' => 6)));
    
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
            $grid->abreColuna(12,6,3);
            
                #$processo = trataNulo($pessoal->get_numProcessoReducao($idServidorPesquisado));
                $painel = new Callout();
                $painel->abre();
                
                    tituloTable("N° do Processo:");
                    br();
                    p($processo,'f14',"center");
                    
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
            $grid->abreColuna(12,6,3);
            
                # Pega os telefones
                $telefones = $pessoal->get_telefones($idServidorPesquisado);
                
                # Pega os Emails
                $emailPessoal = $pessoal->get_emailPessoal($idServidorPesquisado);
                $emailUenf = $pessoal->get_emailUenf($idServidorPesquisado);
                $emails = NULL;
                
                # Junta os emails
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
                    p($telefones,"center","f14");
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
            
            # tarefas
            $grid->abreColuna(12,12,6);
                
                $painel = new Callout();
                $painel->abre();
                
                    # Exibe o título
                    tituloTable("Tarefas:");
                    br();
                    
                    # Pega as tarefas
                    $mensagem = $reducao->get_tarefas();
                
                    # Verifica se tem mensagem a ser exibida
                    if(!is_null($mensagem)){
                        p($mensagem,'f13');
                    }else{
                        p('---','f14','center');
                    }        
                                    
                $painel->fecha();
                
            $grid->fechaColuna();
            
            $grid->fechaGrid();        
            $objeto->listar(); 
            break;
        case "editar" :			
        case "excluir" :
            $objeto->$fase($id); 
            break;

        case "gravar" :
            $objeto->gravar($id);              
            break;

    }									 	 		

    $page->terminaPagina();
}else{
    loadPage("../../areaServidor/sistema/login.php");
}