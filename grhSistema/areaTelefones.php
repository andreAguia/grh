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
    $fase = get('fase');
    
    # Verifica se veio menu grh e registra o acesso no log
    $grh = get('grh',FALSE);
    if($grh){
        # Grava no log a atividade
        $atividade = "Visualizou a área de Acumulação";
        $data = date("Y-m-d H:i:s");
        $intra->registraLog($idUsuario,$data,$atividade,NULL,NULL,7);
    }
    
    # pega o id (se tiver)
    $id = soNumeros(get('id'));
    
    # Pega o parametro de pesquisa (se tiver)
    if (is_null(post('parametro')))					# Se o parametro n?o vier por post (for nulo)
        $parametro = retiraAspas(get_session('sessionParametro'));	# passa o parametro da session para a variavel parametro retirando as aspas
    else{ 
        $parametro = post('parametro');                # Se vier por post, retira as aspas e passa para a variavel parametro
        set_session('sessionParametro',$parametro);    # transfere para a session para poder recuperá-lo depois
    }
    
    # Começa uma nova página
    $page = new Page();
    $page->iniciaPagina();
    
    # Cabeçalho da Página
    if($fase <> "relatorio"){
        AreaServidor::cabecalho();
    }
    
################################################################
    
    switch ($fase){
        
        case "" :
        case "listaAcumulacao" :
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
            $botaoRel->set_url("../grhRelatorios/acumulacao.geral.php");
            $botaoRel->set_target("_blank");
            $botaoRel->set_imagem($imagem);
            #$menu1->add_link($botaoRel,"right");

            $menu1->show();
            
            ###
            
            # Formulário de Pesquisa
            $form = new Form('?'); 

            # Nome    
            $controle = new Input('parametro','texto','Pesquisar:',1);
            $controle->set_size(100);
            $controle->set_title('Filtra por Nome');
            $controle->set_valor($parametro);
            $controle->set_onChange('formPadrao.submit();');
            $controle->set_linha(1);
            $controle->set_col(6);
            $controle->set_autofocus(TRUE);
            $form->add_item($controle);

            $form->show();
            
            ###
                
            # Pega o time inicial
            $time_start = microtime(TRUE);
            
            # Pega os dados
            $select = " SELECT DIR,
                               GER,
                               nome,
                               ramais,
                               email
                          FROM tblotacao
                         WHERE ativo
                      ORDER BY DIR asc, GER asc, nome asc";
            
            $resumo = $pessoal->select($select);
            
            # Monta a tabela
            $tabela = new Tabela();
            $tabela->set_conteudo($resumo);
            $tabela->set_label(array("Diretoria","Gerência","Nome","Telefones","Email"));
            $tabela->set_align(array("center","center","left","left","left"));
            
            $tabela->set_rowspan(0);
            $tabela->set_grupoCorColuna(0);
            
            $tabela->set_titulo("Telefones e Emails da UENF");
            #$tabela->set_idCampo('idServidor');
            #$tabela->set_editar('?fase=editaServidor');            
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
            set_session('origem','areaAcumulacao.php');
            
            # Carrega a página específica
            loadPage('servidorAcumulacao.php');
            break; 
        
    ################################################################
    }
    
    $page->terminaPagina();
}else{
    loadPage("../../areaServidor/sistema/login.php");
}


