<?php
/**
 * Área de Férias
 *  
 * By Alat
 */

# Reservado para o servidor logado
$idUsuario = null;

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
    $parametroLotacao = post('parametroLotacao',get_session('parametroLotacao','*'));
    
    # Agrupamento do Relatório
    $agrupamentoEscolhido = post('agrupamento',0);
    
    # Session do Relatório
    $select = get_session('sessionSelect');
    $titulo = get_session('sessionTitulo');
    $subTitulo = get_session('sessionSubTitulo');
        
    # Joga os parâmetros par as sessions    
    set_session('parametroAnoExercicio',$parametroAnoExercicio);
    set_session('parametroLotacao',$parametroLotacao);

    # Ordem da tabela
    $orderCampo = get('orderCampo');
    $orderTipo = get('orderTipo');
    
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
    $linkBotao1 = new Link("Voltar","grh.php");
    $linkBotao1->set_class('button');
    $linkBotao1->set_title('Voltar a página anterior');
    $linkBotao1->set_accessKey('V');
    $menu1->add_link($linkBotao1,"left");
    
    # Detalhe
    $linkBotao1 = new Link("Detalhe","?");
    $linkBotao1->set_class('button');
    $linkBotao1->set_title('Voltar a página anterior');
    $linkBotao1->set_accessKey('D');
    #$menu1->add_link($linkBotao1,"right");
    
    # Resumo
    $linkBotao1 = new Link("Resumo","?fase=resumo");
    $linkBotao1->set_class('button');
    $linkBotao1->set_title('Exibe as férias por dia');
    $linkBotao1->set_accessKey('R');
    #$menu1->add_link($linkBotao1,"right");
    
    # Por Mês
    $linkBotao1 = new Link("Por Mês","grh.php");
    $linkBotao1->set_class('button');
    $linkBotao1->set_title('Exibe as férias por mês');
    $linkBotao1->set_accessKey('P');
    #$menu1->add_link($linkBotao1,"right");

    # Relatórios
    $imagem = new Imagem(PASTA_FIGURAS.'print.png',null,15,15);
    $botaoRel = new Button();
    $botaoRel->set_title("Relatório dessa pesquisa");
    $botaoRel->set_onClick("window.open('?fase=relatorio','_blank','menubar=no,scrollbars=yes,location=no,directories=no,status=no,width=750,height=600');");
    $botaoRel->set_imagem($imagem);
    $menu1->add_link($botaoRel,"right");

    $menu1->show();
    
    # Título
    titulo("Área de Férias");
    
    ################################################################
    
    # Formulário de Pesquisa
    $form = new Form('?');

    # anoExercicio                
    $anoExercicio = $pessoal->select('SELECT DISTINCT anoExercicio, anoExercicio FROM tbferias ORDER BY 1');
    array_push($anoExercicio,date("Y"));

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
    array_unshift($result,array('*','Todas'));
    
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
            # Exibe aas férias
            if($parametroLotacao <> '*'){
                # lateral
                $grid2 = new Grid();
                $grid2->abreColuna(3);

                # Resumo Geral da lotação
                $lista1 = new listaFerias($parametroAnoExercicio);
                $lista1->set_lotacao($parametroLotacao);
                $lista1->showResumo();
                
                $grid2->fechaColuna();
                $grid2->abreColuna(9);
                
                # Resumo por servidor sa Lotação
                $lista1->showResumo(FALSE);

                # Detalhado da Lotação
                $lista2 = new listaFerias($parametroAnoExercicio);
                $lista2->set_lotacao($parametroLotacao);
                $lista2->showDetalhe();

                $grid2->fechaColuna();
                $grid2->fechaGrid();
            }else{
                # Resumo Geral de Todas as Lotações
                $lista1 = new listaFerias($parametroAnoExercicio);
                $lista1->set_lotacao($parametroLotacao);
                $lista1->showResumo();
            }
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
            # Lista de Servidores Ativos
            $lista = new listaFerias('Servidores');
            if($parametroNomeMat <> NULL){
                $lista->set_matNomeId($parametroNomeMat);
            }
            
            if($parametroAnoExercicio <> "*"){
                $lista->set_anoExercicio($parametroAnoExercicio);
            }

            if($parametroLotacao <> "*"){
                $lista->set_lotacao($parametroLotacao);
            }
            
            $lista->showRelatorio();
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
