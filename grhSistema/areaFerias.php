<?php
/**
 * Área de Férias
 *  
 * By Alat
 */

# Reservado para o servidor logado
$idUsuario = NULL;

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
    $fase = get('fase','porDia');
    
    # Verifica se veio menu grh e registra o acesso no log
    $origem = get('origem',FALSE);
    if($origem){
        # Grava no log a atividade
        $atividade = "Visualizou a área de férias";
        $data = date("Y-m-d H:i:s");
        $intra->registraLog($idUsuario,$data,$atividade,NULL,NULL,7);
    }
    
    # pega o id (se tiver)
    $id = soNumeros(get('id'));
    set_session('areaFerias',FALSE);
    
    # Pega os parâmetros
    $parametroAnoExercicio = post('parametroAnoExercicio',get_session('parametroAnoExercicio',date("Y")));
    $parametroLotacao = post('parametroLotacao',get_session('parametroLotacao'));
    
    # Agrupamento do Relatório
    $agrupamentoEscolhido = post('agrupamento',0);
    
    # Session do Relatório
    $select = get_session('sessionSelect');
    $titulo = get_session('sessionTitulo');
    $subTitulo = get_session('sessionSubTitulo');
        
    # Joga os parâmetros par as sessions    
    set_session('parametroAnoExercicio',$parametroAnoExercicio);
    set_session('parametroLotacao',$parametroLotacao);
    
    # Começa uma nova página
    $page = new Page();
    $page->iniciaPagina();
    
    # Cabeçalho da Página
    if($fase <> "relatorio"){
        AreaServidor::cabecalho();
    }
    
    $grid = new Grid();
    $grid->abreColuna(12);

    # Cria um menu
    $menu1 = new MenuBar();

    # Voltar
    $botaoVoltar = new Link("Voltar","grh.php");
    $botaoVoltar->set_class('button');
    $botaoVoltar->set_title('Voltar a página anterior');
    $botaoVoltar->set_accessKey('V');
    $menu1->add_link($botaoVoltar,"left");
    
    # Agrupado por dia
    $botaoVoltar = new Link("por Dia","?fase=porDia");
    $botaoVoltar->set_class('button');
    $botaoVoltar->set_title('Lista de servidores agrupados por total de dias fruídos / Solicitados');
    $botaoVoltar->set_accessKey('D');
    $menu1->add_link($botaoVoltar,"right");
    
    # Por Solicitação
    $botaoVoltar = new Link("por Solicitação","?fase=porSolicitacao");
    $botaoVoltar->set_class('button');
    $botaoVoltar->set_title('Lista de solicitações de férias');
    $botaoVoltar->set_accessKey('S');
    $menu1->add_link($botaoVoltar,"right");

    $menu1->show();
    
    # Título
    titulo("Área de Férias");
    
    ################################################################
    
    # Formulário de Pesquisa
    $form = new Form('?fase='.substr($fase, 0, -1)); // retira o x da fase

    # anoExercicio                
    $anoExercicio = $pessoal->select('SELECT DISTINCT anoExercicio, anoExercicio FROM tbferias ORDER BY 1');
    
    # Verifica se existe o ano atual na combo e acrescenta caso não tenha
    if($anoExercicio[count($anoExercicio)-1][0] < date("Y")){
        array_push($anoExercicio,date("Y"));
    }

    $controle = new Input('parametroAnoExercicio','combo','Ano Exercício:',1);
    $controle->set_size(8);
    $controle->set_title('Filtra por Ano exercício');
    $controle->set_array($anoExercicio);
    $controle->set_valor(date("Y"));
    $controle->set_valor($parametroAnoExercicio);
    $controle->set_onChange('formPadrao.submit();');
    $controle->set_linha(1);
    $controle->set_col(3);
    $form->add_item($controle);

    # Lotação
    $result = $pessoal->select('SELECT idlotacao, concat(IFNULL(tblotacao.DIR,"")," - ",IFNULL(tblotacao.GER,"")," - ",IFNULL(tblotacao.nome,"")) lotacao
                                  FROM tblotacao
                                 WHERE ativo
                              ORDER BY ativo desc,lotacao');
    array_unshift($result,array("*",'Todas'));
    
    $controle = new Input('parametroLotacao','combo','Lotação:',1);
    $controle->set_size(30);
    $controle->set_title('Filtra por Lotação');
    $controle->set_array($result);
    $controle->set_valor($parametroLotacao);
    $controle->set_onChange('formPadrao.submit();');
    $controle->set_linha(1);
    $controle->set_col(9);
    $form->add_item($controle);

    $form->show();
            
    ################################################################
    
    switch ($fase)
    {
        case "" : 
        case "porDia" :
        case "porSolicitacao" :
            br(4);
            aguarde();
            br();
            
            # Limita a tela
            $grid1 = new Grid("center");
            $grid1->abreColuna(5);
                p("Aguarde...","center");
            $grid1->fechaColuna();
            $grid1->fechaGrid();

            loadPage('?fase='.$fase.'x');
            break;
        
        case "porDiax" :
        case "porSolicitacaox" :
        
            $grid2 = new Grid();
            
            # Área Lateral
            $grid2->abreColuna(3);
            
            # Informa a classe com os parâmetros
            $lista1 = new listaFerias($parametroAnoExercicio);
            $lista1->set_lotacao($parametroLotacao);
            
            # resumo geral
            $lista1->showResumoGeral();
            
            # por dias
            $lista1->showResumoPorDia();
            
            # por status
            $lista1->showResumoPorStatus();
            
            # Relatórios
            $menu = new Menu();
            #$menu->add_item('titulo','Relatórios');
            $menu->add_item('titulo','Relatórios Anuais');
            #if($parametroLotacao == 113){
            #    $menu->add_item('linkWindow','Escala Anual de Férias dos Tecnicos','../grhRelatorios/feriasEscalaAnualTecnicosCedidos.php');
            #}elseif((!is_null($parametroLotacao)) AND ($parametroLotacao <> "*")){
            #    $menu->add_item('linkWindow','Escala Anual de Férias dos Técnicos','../grhRelatorios/feriasEscalaAnualTecnicos.php?lotacaoArea='.$parametroLotacao);
            #}

            $menu->add_item('linkWindow','Relatório Anual de Férias Solicitadas','../grhRelatorios/feriasAnualStatus.php?parametroAnoExercicio='.$parametroAnoExercicio.'&status=s&lotacaoArea='.$parametroLotacao);
            $menu->add_item('linkWindow','Relatório Anual de Férias Confirmadas','../grhRelatorios/feriasAnualStatus.php?parametroAnoExercicio='.$parametroAnoExercicio.'&status=c&lotacaoArea='.$parametroLotacao);
            $menu->add_item('linkWindow','Relatório Anual de Férias Fruídas','../grhRelatorios/feriasAnualStatus.php?parametroAnoExercicio='.$parametroAnoExercicio.'&status=f&lotacaoArea='.$parametroLotacao);
            $menu->add_item('linkWindow','Resumo Anual de Férias','../grhRelatorios/feriasResumoAnual.php?parametroAnoExercicio='.$parametroAnoExercicio.'&lotacaoArea='.$parametroLotacao);
            $menu->add_item('titulo','Relatórios Mensais');
            $menu->add_item('linkWindow','Relatório Mensal de Férias Solicitadas','../grhRelatorios/feriasMensalStatus.php?parametroAnoExercicio='.$parametroAnoExercicio.'&status=s&lotacaoArea='.$parametroLotacao);
            $menu->add_item('linkWindow','Relatório Mensal de Férias Confirmadas','../grhRelatorios/feriasMensalStatus.php?parametroAnoExercicio='.$parametroAnoExercicio.'&status=c&lotacaoArea='.$parametroLotacao);
            $menu->add_item('linkWindow','Relatório Mensal de Férias Fruídas','../grhRelatorios/feriasMensalStatus.php?parametroAnoExercicio='.$parametroAnoExercicio.'&status=f&lotacaoArea='.$parametroLotacao);
                       
            #$menu->add_item('linkWindow','Escala Mensal Geral de Férias','../grhRelatorios/escalaMensalFeriasGeral.php');
            #$menu->add_item('linkWindow','Escala Mensal Geral de Férias Agrupados por Lotação','../grhRelatorios/escalaMensalFeriasGeralPorLotacao.php');
            #$menu->add_item('linkWindow','Escala Mensal Geral de Férias Agrupados por Lotação - Assinatura','../grhRelatorios/escalaMensalFeriasGeralPorLotacaoComAssinatura.php');
            #$menu->add_item('linkWindow','Escala Semestral de Férias (Fevereiro - Agosto)','../grhRelatorios/escalaSemestralFeriasGeralFevereiroAgosto.php');
            #$menu->add_item('linkWindow','Escala Semestral de Férias (Setembro - Janeiro)','../grhRelatorios/escalaSemestralFeriasGeralSetembroJaneiro.php');
            #$menu->add_item('linkWindow','Total de Férias por Ano do Exercício','../grhRelatorios/totalFeriasAnual.php');
            #$menu->add_item('linkWindow','Relatório Mensal de Servidores em Férias','../grhRelatorios/servidorEmFerias.php');
            $menu->show();
            
            #######################################
            
            # Área Principal            
            $grid2->fechaColuna();
            $grid2->abreColuna(9);
            
            if($fase == "porDiax"){
                $lista1->showPorDia();
            }else{
                $lista1->showPorSolicitacao();
            }
                        
            $grid2->fechaColuna();
            $grid2->fechaGrid();
            break;
            
        
        ###############################

        # Chama o menu do Servidor que se quer editar
        case "editaFerias" :
            $servidor = $pessoal->get_idServidorFerias($id);
            set_session('idServidorPesquisado',$servidor);
            set_session('areaFerias',TRUE);
            loadPage('servidorFerias.php?fase=editar&id='.$id);
            break; 
        
        ###############################

        # Chama o menu do Servidor que se quer editar
        case "editaServidorFerias" :
            set_session('idServidorPesquisado',$id);
            set_session('areaFerias',TRUE);
            loadPage('servidorFerias.php');
            break; 
        
        ###############################

        # Cria um relatório com a seleção atual
        case "relatorio" :
            include("grhRelatorios.php?fase=ferias");
            break;
            
        ###############################

            # Chama a rotina de férias do servidor
            case "rotinaFeriasServidor" :
                set_session('idServidorPesquisado',$id);
                set_session('areaFerias',TRUE);
                loadPage('servidorFerias.php');
                break; 

        ###############################
    }
    $grid->fechaColuna();
    $grid->fechaGrid();
    
    $page->terminaPagina();
}else{
    loadPage("../../areaServidor/sistema/login.php");
}
