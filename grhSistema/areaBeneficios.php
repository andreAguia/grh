<?php
/**
 * Área de Licença Prêmio
 *  
 * By Alat
 */

# Reservado para o servidor logado
$idUsuario = NULL;

# Configuração
include ("_config.php");

# Permissão de Acesso
$acesso = Verifica::acesso($idUsuario,2);

if($acesso){   
    # Conecta ao Banco de Dados
    $intra = new Intra();
    $pessoal = new Pessoal();
    
    # Roda a rotina que verifica os status
    $reducao = new ReducaoCargaHoraria();
    $reducao->mudaStatus();
	
    # Verifica a fase do programa
    $fase = get('fase','listaReducao');
    
    # Verifica se veio menu grh e registra o acesso no log
    $grh = get('grh',FALSE);
    if($grh){
        # Grava no log a atividade
        $atividade = "Visualizou a área de Benefícios";
        $data = date("Y-m-d H:i:s");
        $intra->registraLog($idUsuario,$data,$atividade,NULL,NULL,7);
    }
    
    # pega o id (se tiver)
    $id = soNumeros(get('id'));
    set_session('areaPremio',FALSE);
    
    # Pega os parâmetros
    $parametroNomeMat = post('parametroNomeMat',get_session('parametroNomeMat'));
    $parametroStatus = post('parametroStatus',get_session('parametroStatus',0));
        
    # Joga os parâmetros par as sessions    
    set_session('parametroNomeMat',$parametroNomeMat);
    set_session('parametroStatus',$parametroStatus);
    
    # Começa uma nova página
    $page = new Page();
    $page->iniciaPagina();
    
    # Cabeçalho da Página
    if($fase <> "relatorio"){
        AreaServidor::cabecalho();
    }
    
    # Variáveis
    $statusPossiveis = array(array(0,"-- Todos --"),array(1,"Em Aberto"),array(2,"Vigente"),array(3,"Arquivado"));
            
################################################################
    
    switch ($fase){
        
        case "listaReadaptacao" :
            $grid = new Grid();
            $grid->abreColuna(12);
            br();

            # Cria um menu
            $menu1 = new MenuBar();

            # Voltar
            $botaoVoltar = new Link("Voltar","grh.php");
            $botaoVoltar->set_class('button');
            $botaoVoltar->set_title('Voltar a página anterior');
            $botaoVoltar->set_accessKey('V');
            $menu1->add_link($botaoVoltar,"left");
            
            # Relatórios
            $imagem = new Imagem(PASTA_FIGURAS.'print.png',NULL,15,15);
            $botaoRel = new Button();
            $botaoRel->set_title("Relatório dessa pesquisa");
            $botaoRel->set_url("../grhRelatorios/readaptacao.geral.php");
            $botaoRel->set_target("_blank");
            $botaoRel->set_imagem($imagem);
            $menu1->add_link($botaoRel,"right");

            # Redução da Carga Horária
            $botaoRel = new Button('Redução da Carga Horária');
            $botaoRel->set_url("?fase=listaReducao");
            $menu1->add_link($botaoRel,"right");

            $menu1->show();
            
            ###
            
            # Formulário de Pesquisa
            $form = new Form('?fase=listaReadaptacao'); 

            # Nome    
            $controle = new Input('parametroNomeMat','texto','Servidor:',1);
            $controle->set_size(100);
            $controle->set_title('Filtra por Nome');
            $controle->set_valor($parametroNomeMat);
            $controle->set_onChange('formPadrao.submit();');
            $controle->set_linha(1);
            $controle->set_col(8);
            $controle->set_autofocus(TRUE);
            $form->add_item($controle);

            # Status    
            $controle = new Input('parametroStatus','combo','Status:',1);
            $controle->set_size(30);
            $controle->set_title('Filtra por Status');
            $controle->set_array($statusPossiveis);
            $controle->set_valor($parametroStatus);
            $controle->set_onChange('formPadrao.submit();');
            $controle->set_linha(1);
            $controle->set_col(4);
            $form->add_item($controle);

            $form->show();
            
            ###
                
            # Pega o time inicial
            $time_start = microtime(TRUE);
            
            # Pega os dados
            $select = "SELECT idServidor,
                              tbpessoa.nome,
                              CASE tipo
                                WHEN 1 THEN 'Ex-Ofício'
                                WHEN 2 THEN 'Solicitada'
                                ELSE '--'
                              END,
                              idReadaptacao,
                              idReadaptacao,
                              idReadaptacao,
                              idReadaptacao,
                              idReadaptacao,
                              idReadaptacao,
                              idReadaptacao,
                              idReadaptacao,                                   
                              idReadaptacao
                         FROM tbservidor JOIN tbpessoa USING (idPessoa)
                                         JOIN tbreadaptacao USING (idServidor)
                        WHERE tbservidor.idPerfil <> 10";
            
            # status
            if($parametroStatus <> 0){
                $select .= " AND status = ".$parametroStatus;
            }
            
            # status
            if(!is_null($parametroNomeMat)){
                $select .= " AND tbpessoa.nome LIKE '%$parametroNomeMat%'";
            }
                    
                    
            $select .= " ORDER BY status, dtInicio";
            
            $resumo = $pessoal->select($select);
            
            # Monta a tabela
            $tabela = new Tabela();
            $tabela->set_conteudo($resumo);
            $tabela->set_label(array("idServidor","Nome","Tipo","Status","Solicitado em:","Pericia","Resultado","Publicação","Período"));
            $tabela->set_align(array("center","left","center","center","center","left","center","center","left"));
            $tabela->set_funcao(array("idMatricula"));
            
            $tabela->set_classe(array(NULL,NULL,NULL,"Readaptacao","Readaptacao","Readaptacao","Readaptacao","Readaptacao","Readaptacao"));
            $tabela->set_metodo(array(NULL,NULL,NULL,"exibeStatus","exibeSolicitacao","exibeDadosPericia","exibeResultado","exibePublicacao","exibePeriodo"));
            
            $tabela->set_titulo("Servidores com Solicitação de Readaptação");
            
            $tabela->set_idCampo('idServidor');
            $tabela->set_editar('?fase=editaServidor2');
            
            $tabela->set_formatacaoCondicional(array( array('coluna' => 3,
                                                    'valor' => 'Em Aberto',
                                                    'operador' => '=',
                                                    'id' => 'emAberto'),  
                                              array('coluna' => 3,
                                                    'valor' => 'Arquivado',
                                                    'operador' => '=',
                                                    'id' => 'arquivado'),
                                              array('coluna' => 3,
                                                    'valor' => 'Vigente',
                                                    'operador' => '=',
                                                    'id' => 'vigenteReducao')   
                                                    ));
            
            $tabela->show();
            
            # Pega o time final
            $time_end = microtime(TRUE);
            $time = $time_end - $time_start;
            p(number_format($time, 4, '.', ',')." segundos","right","f10");
            
            $grid->fechaColuna();
            $grid->fechaGrid();
            break;
        
    ################################################################
        
        case "listaReducao" :
            $grid = new Grid();
            $grid->abreColuna(12);
            br();

            # Cria um menu
            $menu1 = new MenuBar();

            # Voltar
            $botaoVoltar = new Link("Voltar","grh.php");
            $botaoVoltar->set_class('button');
            $botaoVoltar->set_title('Voltar a página anterior');
            $botaoVoltar->set_accessKey('V');
            $menu1->add_link($botaoVoltar,"left");
            
            # Relatórios
            $imagem = new Imagem(PASTA_FIGURAS.'print.png',NULL,15,15);
            $botaoRel = new Button();
            $botaoRel->set_title("Relatório dessa pesquisa");
            $botaoRel->set_url("../grhRelatorios/reducao.geral.php");
            $botaoRel->set_target("_blank");
            $botaoRel->set_imagem($imagem);
            $menu1->add_link($botaoRel,"right");
            
            # Redução da Carga Horária
            $botaoRel = new Button('Readaptação');
            $botaoRel->set_url("?fase=listaReadaptacao");
            $menu1->add_link($botaoRel,"right");

            $menu1->show();
            
            ###
            
            # Formulário de Pesquisa
             $form = new Form('?fase=listaReducao'); 

            # Nome    
            $controle = new Input('parametroNomeMat','texto','Servidor:',1);
            $controle->set_size(100);
            $controle->set_title('Filtra por Nome');
            $controle->set_valor($parametroNomeMat);
            $controle->set_onChange('formPadrao.submit();');
            $controle->set_linha(1);
            $controle->set_col(8);
            $controle->set_autofocus(TRUE);
            $form->add_item($controle);

            # Status    
            $controle = new Input('parametroStatus','combo','Status:',1);
            $controle->set_size(30);
            $controle->set_title('Filtra por Status');
            $controle->set_array($statusPossiveis);
            $controle->set_valor($parametroStatus);
            $controle->set_onChange('formPadrao.submit();');
            $controle->set_linha(1);
            $controle->set_col(4);
            $form->add_item($controle);

            $form->show();
            
            ###
                
            # Pega o time inicial
            $time_start = microtime(TRUE);

            # Pega os dados
            $select = "SELECT idServidor,
                              tbpessoa.nome,
                              idReducao,
                              dtSolicitacao,
                              idReducao,
                              idReducao,
                              idReducao,
                              idReducao,
                              idServidor
                         FROM tbservidor JOIN tbpessoa USING (idPessoa)
                                         JOIN tbreducao USING (idServidor)
                        WHERE tbservidor.idPerfil <> 10";
            
            # status
            if($parametroStatus <> 0){
                $select .= " AND status = ".$parametroStatus;
            }
            
            # status
            if(!is_null($parametroNomeMat)){
                $select .= " AND tbpessoa.nome LIKE '%$parametroNomeMat%'";
            }
                    
                    
            $select .= " ORDER BY status, dtInicio";
            
            $resumo = $pessoal->select($select);

            # Monta a tabela
            $tabela = new Tabela();
            $tabela->set_conteudo($resumo);
            $tabela->set_label(array("Id/Matrícula","Nome","Status","Solicitado em:","Pericia","Resultado","Publicação","Período"));
            $tabela->set_align(array("center","left","center","center","left","center","center","left"));
            $tabela->set_funcao(array("idMatricula",NULL,NULL,"date_to_php"));
            
            $tabela->set_classe(array(NULL,NULL,"ReducaoCargaHoraria",NULL,"ReducaoCargaHoraria","ReducaoCargaHoraria","ReducaoCargaHoraria","ReducaoCargaHoraria"));
            $tabela->set_metodo(array(NULL,NULL,"exibeStatus",NULL,"exibeDadosPericia","exibeResultado","exibePublicacao","exibePeriodo"));
            
            $tabela->set_titulo("Servidores com Solicitação de Redução de Carga Horária");
            
            $tabela->set_idCampo('idServidor');
            $tabela->set_editar('?fase=editaServidor');
            
            $tabela->set_formatacaoCondicional(array( array('coluna' => 2,
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
            
            $tabela->show();
            
            # Pega o time final
            $time_end = microtime(TRUE);
            $time = $time_end - $time_start;
            p(number_format($time, 4, '.', ',')." segundos","right","f10");
            
            $grid->fechaColuna();
            $grid->fechaGrid();
            break;
        
    ################################################################
        
        case "editaServidor" :
            br(8);
            aguarde();
            
            # Informa o $id Servidor
            set_session('idServidorPesquisado',$id);
            
            # Informa a origem
            set_session('origem','areaBeneficios');
            
            # Carrega a página específica
            loadPage('servidorReducao.php');
            break; 
        
    ################################################################
        
        case "editaServidor2" :
            br(8);
            aguarde();
            
            # Informa o $id Servidor
            set_session('idServidorPesquisado',$id);
            
            # Informa a origem
            set_session('origem','areaBeneficios');
            
            # Carrega a página específica
            loadPage('servidorReadaptacao.php');
            break; 
        
    ################################################################
    }
    
    $page->terminaPagina();
}else{
    loadPage("../../areaServidor/sistema/login.php");
}


