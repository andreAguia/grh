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
    
    # Pega os parâmetros
    $parametroNomeMat = post('parametroNomeMat',get_session('parametroNomeMat'));
        
    # Joga os parâmetros par as sessions    
    set_session('parametroNomeMat',$parametroNomeMat);
    
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
            $menu1->add_link($botaoRel,"right");
            
            # Normas
            $botao2 = new Button("Regras","servidorAcumulacao.php?fase=regras");
            $botao2->set_title("Exibe as regras da acumulação");    
            #$botao2->set_url("../grhRelatorios/servidorGratificacao.php");
            $botao2->set_target("_blank");
            $menu1->add_link($botao2,"right");

            $menu1->show();
            
            ###
            
            # Formulário de Pesquisa
            $form = new Form('?'); 

            # Nome    
            $controle = new Input('parametroNomeMat','texto','Servidor:',1);
            $controle->set_size(100);
            $controle->set_title('Filtra por Nome');
            $controle->set_valor($parametroNomeMat);
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
            $select = "SELECT idAcumulacao,
                              idFuncional,
                              tbpessoa.nome,
                              dtProcesso,
                              processo,
                              instituicao,
                              cargo,
                              tbacumulacao.matricula,                              
                              tbservidor.idServidor
                         FROM tbacumulacao JOIN tbservidor USING (idServidor)
                                           JOIN tbpessoa USING (idPessoa)
                        WHERE tbservidor.idPerfil <> 10";
            
            # nome
            if(!is_null($parametroNomeMat)){
                $select .= " AND tbpessoa.nome LIKE '%$parametroNomeMat%'";
            }
                    
            $select .= " ORDER BY resultado, dtProcesso desc";
            
            $resumo = $pessoal->select($select);
            
            # Monta a tabela
            $tabela = new Tabela();
            $tabela->set_conteudo($resumo);
            $tabela->set_label(array("Resultado","idFuncional","Nome","Data","Processo","Instituição","Cargo","Matrícula"));
            $tabela->set_align(array("center","center","left","center"));
            $tabela->set_funcao(array(NULL,NULL,NULL,"date_to_php"));
            
            $tabela->set_classe(array("Acumulacao"));
            $tabela->set_metodo(array("get_resultado"));
    
            $tabela->set_titulo("Área de Acumulação");
            
            $tabela->set_formatacaoCondicional(array( array('coluna' => 0,
                                                    'valor' => 'Em Aberto',
                                                    'operador' => '=',
                                                    'id' => 'emAberto'),  
                                              array('coluna' => 0,
                                                    'valor' => 'Ilícito',
                                                    'operador' => '=',
                                                    'id' => 'arquivado'),
                                              array('coluna' => 0,
                                                    'valor' => 'Lícito',
                                                    'operador' => '=',
                                                    'id' => 'vigenteReducao')   
                                                    ));
            
            $tabela->set_idCampo('idServidor');
            $tabela->set_editar('?fase=editaServidor');            
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


