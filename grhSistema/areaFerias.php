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
    
    # Pega os parâmetros
    $parametroNomeMat = retiraAspas(post('parametroNomeMat',get_session('parametroNomeMat')));
    $parametroAnoExercicio = post('parametroAnoExercicio',get_session('parametroAnoExercicio',date("Y")));
    $parametroLotacao = post('parametroLotacao',get_session('parametroLotacao','*'));
    
    # Agrupamento do Relatório
    $agrupamentoEscolhido = post('agrupamento',0);
    
    # Session do Relatório
    $select = get_session('sessionSelect');
    $titulo = get_session('sessionTitulo');
    $subTitulo = get_session('sessionSubTitulo');
        
    # Joga os parâmetros par as sessions
    set_session('parametroNomeMat',$parametroNomeMat);
    set_session('parametroAnoExercicio',$parametroAnoExercicio);
    set_session('parametroLotacao',$parametroLotacao);
    
    # Verifica a paginacão
    $paginacao = get('paginacao',get_session('parametroPaginacao',0));	// Verifica se a paginação vem por get, senão pega a session
    set_session('parametroPaginacao',$paginacao);  

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
    
    ################################################################
    
    switch ($fase)
    {
        # Lista os Servidores
        case "" :
            br(10);
            aguarde();
            br();            
            loadPage('?fase=pesquisar');            
            break;
        
        case "pesquisar" :
            # Cadastro de Servidores 
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
            br();

            # Parâmetros
            $form = new Form('?');

                # Nome ou Matrícula
                $controle = new Input('parametroNomeMat','texto','Nome, matrícula ou IdFuncional:',1);
                $controle->set_size(55);
                $controle->set_title('Nome, matrícula ou ID:');
                $controle->set_valor($parametroNomeMat);
                $controle->set_autofocus(true);
                $controle->set_onChange('formPadrao.submit();');
                $controle->set_linha(1);
                $controle->set_col(4);
                $form->add_item($controle);

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
                $controle->set_col(2);
                $form->add_item($controle);
                
                # Lotação
                $result = $pessoal->select('SELECT idlotacao, concat(IFNULL(tblotacao.DIR,"")," - ",IFNULL(tblotacao.GER,"")," - ",IFNULL(tblotacao.nome,"")) lotacao
                                              FROM tblotacao
                                             WHERE ativo
                                          ORDER BY ativo desc,lotacao');
                array_unshift($result,array('*','-- Todos --'));

                $controle = new Input('parametroLotacao','combo','Lotação:',1);
                $controle->set_size(30);
                $controle->set_title('Filtra por Lotação');
                $controle->set_array($result);
                $controle->set_valor($parametroLotacao);
                $controle->set_onChange('formPadrao.submit();');
                $controle->set_linha(1);
                $controle->set_col(6);
                $form->add_item($controle);
                
                # submit
                #$controle = new Input('submit','submit');
                #$controle->set_valor('Pesquisar');
                #$controle->set_size(20);
                #$controle->set_accessKey('P');
                #$controle->set_linha(3);
                #$controle->set_col(2);
                #$form->add_item($controle);

                $form->show();
                
                # Lista de Servidores Ativos
                $lista = new listaFerias("Férias");
                if($parametroNomeMat <> NULL){
                    $lista->set_matNomeId($parametroNomeMat);
                }
                
                if($parametroAnoExercicio <> "*"){
                    $lista->set_anoExercicio($parametroAnoExercicio);
                }

                if($parametroLotacao <> "*"){
                    $lista->set_lotacao($parametroLotacao);
                }
                
                # Paginação
                if($parametroLotacao == "*"){
                    $lista->set_paginacao(true);
                }
                $lista->set_paginacaoInicial($paginacao);
                $lista->set_paginacaoItens(30);               
                
                $grid3 = new Grid();
                $grid3->abreColuna(4);
                br();
                
                $lista->showResumo();
                
                $grid3->fechaColuna();
                $grid3->abreColuna(8);
                br();
                $lista->showTabela();
                
                $grid3->fechaColuna();
                $grid3->fechaGrid();

            $grid->fechaColuna();
            $grid->fechaGrid();
            break;
        
        ###############################

        # Chama o menu do Servidor que se quer editar
        case "editar" :
            br(8);
            aguarde();
            set_session('idServidorPesquisado',$id);
            loadPage('servidorMenu.php');
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
    }
    $page->terminaPagina();
}else{
    loadPage("../../areaServidor/sistema/login.php");
}
