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
	
    # Verifica a fase do programa
    $fase = get('fase','listaReducao');
    
    # Verifica se veio menu grh e registra o acesso no log
    $origem = get('origem',FALSE);
    if($origem){
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
    $parametroLotacao = post('parametroLotacao',get_session('parametroLotacao'));
        
    # Joga os parâmetros par as sessions    
    set_session('parametroNomeMat',$parametroNomeMat);
    set_session('parametroLotacao',$parametroLotacao);
    
    # Começa uma nova página
    $page = new Page();
    $page->iniciaPagina();
    
    # Cabeçalho da Página
    if($fase <> "relatorio"){
        AreaServidor::cabecalho();
    }
    
    switch ($fase){
        
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

            # Redução da Carga Horária
            $botaoRel = new Button('Redução da Carga Horária');
            $botaoRel->set_url("?fase=listaReducao");
            $menu1->add_link($botaoRel,"right");
            
            # Redução da Carga Horária
            $botaoRel = new Button('Redução da Carga Horária');
            $botaoRel->set_url("?fase=listaReducao");
            #$menu1->add_link($botaoRel,"right");
            
            # Redução da Carga Horária
            $botaoRel = new Button('Redução da Carga Horária');
            $botaoRel->set_url("?fase=listaReducao");
            #$menu1->add_link($botaoRel,"right");

            $menu1->show();
            
            ###
                
            # Pega o time inicial
            $time_start = microtime(TRUE);
            
            # Conecta com o banco de dados
            $servidor = new Pessoal();

            # Pega os dados
            $select = "SELECT idServidor,
                              tbpessoa.nome,
                              idReducao,
                              dtSolicitacao,
                              idReducao,
                              CASE
                                WHEN resultado = 1 THEN 'Deferido'
                                WHEN resultado = 2 THEN 'Indeferido'
                                ELSE '---'
                              END,
                              dtPublicacao,
                               idReducao,
                               idServidor
                          FROM tbservidor JOIN tbpessoa USING (idPessoa)
                                          JOIN tbreducao USING (idServidor)
                         WHERE NOT arquivado
                      ORDER BY dtSolicitacao desc";
            
            $resumo = $servidor->select($select);

            # Monta a tabela
            $tabela = new Tabela();
            $tabela->set_conteudo($resumo);
            $tabela->set_label(array("Id/Matrícula","Nome","Status","Solicitado em:","Pericia","Resultado","Publicação","Período"));
            $tabela->set_align(array("center","left","center","center","left","center","center","left"));
            $tabela->set_funcao(array("idMatricula",NULL,NULL,"date_to_php",NULL,NULL,"date_to_php"));
            $tabela->set_classe(array(NULL,NULL,"ReducaoCargaHoraria",NULL,"ReducaoCargaHoraria",NULL,NULL,"ReducaoCargaHoraria"));
            $tabela->set_metodo(array(NULL,NULL,"exibeStatus",NULL,"exibeDadosPericia",NULL,NULL,"exibePeriodo"));
            $tabela->set_titulo("Servidores com Solicitação de Redução de Carga Horária Em Aberto");
            
            $tabela->set_editar('?fase=editaServidor&id=');
            $tabela->set_nomeColunaEditar("Acessar");
            $tabela->set_editarBotao("ver.png");
            $tabela->set_idCampo('idServidor');
            $tabela->show();
            
            # Pega o time final
            $time_end = microtime(TRUE);
            $time = $time_end - $time_start;
            p(number_format($time, 4, '.', ',')." segundos","right","f10");
            
            $grid->fechaColuna();
            $grid->fechaGrid();
            break;
        
################################################################

        # Chama o menu do Servidor que se quer editar
        case "editaServidor" :
            set_session('idServidorPesquisado',$id);
            set_session('areaReducao',TRUE);
            loadPage('servidorReducao.php');
            break; 
        
################################################################
        
    }
    
    $page->terminaPagina();
}else{
    loadPage("../../areaServidor/sistema/login.php");
}


