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
    $fase = get('fase');
    
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

    # Resumo
    $botaoResumo = new Link("Resumo","?fase=Resumo");
    if(($fase == "")OR($fase == "Resumo"))
    {
        $botaoResumo->set_class('disabled button');
    }else{
        $botaoResumo->set_class('button');
    }
    $botaoResumo->set_title('Voltar a página anterior');
    $botaoResumo->set_accessKey('R');
    $menu1->add_link($botaoResumo,"right");
    
    # Detalhe
    $botaoDetalhe = new Link("Detalhe","?fase=Detalhe");
    if($fase == "Detalhe")
    {
        $botaoDetalhe->set_class('disabled button');
    }else{
        $botaoDetalhe->set_class('button');
    }
    $botaoDetalhe->set_title('Voltar a página anterior');
    $botaoDetalhe->set_accessKey('D');
    $menu1->add_link($botaoDetalhe,"right");

    $menu1->show();
    
    # Título
    titulo("Área de Férias");
    
    ################################################################
    
    # Formulário de Pesquisa
    $form = new Form('?fase='.$fase);

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
    array_unshift($result,array(NULL,'Todas'));
    
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
        case "Resumo" :
        case "Detalhe" :    
            # lateral
            $grid2 = new Grid();
            $grid2->abreColuna(3);

            if($fase == "Detalhe"){
                $lista1 = new listaFerias($parametroAnoExercicio);
                $lista1->set_lotacao($parametroLotacao);
                $lista1->showResumoStatus();
            }else{
                # Resumo geral ou da lotação
                $lista1 = new listaFerias($parametroAnoExercicio);
                $lista1->set_lotacao($parametroLotacao);
                $lista1->showResumo();
            }
            
            # Relatórios
            if(!vazio($parametroLotacao)){
                $menu = new Menu();
                $menu->add_item('titulo','Relatórios');
                
                if($parametroLotacao == 113){
                    $menu->add_item('linkWindow','Escala Anual de Férias','../grhRelatorios/escalaAnualFeriasTecnicosSandraCedidos.php');
                }else{
                    $menu->add_item('linkWindow','Escala Anual de Férias','../grhRelatorios/escalaAnualFeriasTecnicosSandra.php?lotacaoArea='.$parametroLotacao);
                }
                
                #$menu->add_item('linkWindow','Escala Anual de Férias Solicitadas','../grhRelatorios/escalaAnualFeriasSolicitadas.php');
                #$menu->add_item('linkWindow','Escala Anual de Férias Confirmadas','../grhRelatorios/escalaAnualFeriasConfirmadas.php');
                #$menu->add_item('linkWindow','Escala Mensal de Férias Fruídas','../grhRelatorios/escalaMensalFeriasFruidas.php');
                #$menu->add_item('linkWindow','Escala Mensal de Férias Solicitadas','../grhRelatorios/escalaMensalFeriasSolicitadas.php');
                #$menu->add_item('linkWindow','Escala Mensal de Férias Confirmadas','../grhRelatorios/escalaMensalFeriasConfirmadas.php');
                #$menu->add_item('linkWindow','Escala Mensal Geral de Férias','../grhRelatorios/escalaMensalFeriasGeral.php');
                #$menu->add_item('linkWindow','Escala Mensal Geral de Férias Agrupados por Lotação','../grhRelatorios/escalaMensalFeriasGeralPorLotacao.php');
                #$menu->add_item('linkWindow','Escala Mensal Geral de Férias Agrupados por Lotação - Assinatura','../grhRelatorios/escalaMensalFeriasGeralPorLotacaoComAssinatura.php');
                #$menu->add_item('linkWindow','Escala Semestral de Férias (Fevereiro - Agosto)','../grhRelatorios/escalaSemestralFeriasGeralFevereiroAgosto.php');
                #$menu->add_item('linkWindow','Escala Semestral de Férias (Setembro - Janeiro)','../grhRelatorios/escalaSemestralFeriasGeralSetembroJaneiro.php');
                #$menu->add_item('linkWindow','Total de Férias por Ano do Exercício','../grhRelatorios/totalFeriasAnual.php');
                #$menu->add_item('linkWindow','Relatório Mensal de Servidores em Férias','../grhRelatorios/servidorEmFerias.php');
                $menu->show();
            }else{
                $menu = new Menu();
                $menu->add_item('titulo','Relatórios');
                $menu->add_item('linkWindow','Escala Anual de Férias Fruídas','../grhRelatorios/escalaAnualFeriasFruidas.php?parametroAnoExercicio='.$parametroAnoExercicio);
                $menu->add_item('linkWindow','Escala Anual de Férias Solicitadas','../grhRelatorios/escalaAnualFeriasSolicitadas.php?parametroAnoExercicio='.$parametroAnoExercicio);
                $menu->add_item('linkWindow','Escala Anual de Férias Confirmadas','../grhRelatorios/escalaAnualFeriasConfirmadas.php?parametroAnoExercicio='.$parametroAnoExercicio);
                #$menu->add_item('linkWindow','Escala Mensal de Férias Fruídas','../grhRelatorios/escalaMensalFeriasFruidas.php');
                #$menu->add_item('linkWindow','Escala Mensal de Férias Solicitadas','../grhRelatorios/escalaMensalFeriasSolicitadas.php');
                #$menu->add_item('linkWindow','Escala Mensal de Férias Confirmadas','../grhRelatorios/escalaMensalFeriasConfirmadas.php');
                #$menu->add_item('linkWindow','Escala Mensal Geral de Férias','../grhRelatorios/escalaMensalFeriasGeral.php');
                #$menu->add_item('linkWindow','Escala Mensal Geral de Férias Agrupados por Lotação','../grhRelatorios/escalaMensalFeriasGeralPorLotacao.php');
                #$menu->add_item('linkWindow','Escala Mensal Geral de Férias Agrupados por Lotação - Assinatura','../grhRelatorios/escalaMensalFeriasGeralPorLotacaoComAssinatura.php');
                #$menu->add_item('linkWindow','Escala Semestral de Férias (Fevereiro - Agosto)','../grhRelatorios/escalaSemestralFeriasGeralFevereiroAgosto.php');
                #$menu->add_item('linkWindow','Escala Semestral de Férias (Setembro - Janeiro)','../grhRelatorios/escalaSemestralFeriasGeralSetembroJaneiro.php');
                #$menu->add_item('linkWindow','Total de Férias por Ano do Exercício','../grhRelatorios/totalFeriasAnual.php');
                #$menu->add_item('linkWindow','Relatório Mensal de Servidores em Férias','../grhRelatorios/servidorEmFerias.php');
                $menu->show();
            }
            
            #######################################
            
            # Área Principal            
            $grid2->fechaColuna();
            $grid2->abreColuna(9);
            
            if($fase == "Detalhe"){
                $lista1->showDetalheServidor();
            }else{
                $lista1->showResumoServidor();
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
