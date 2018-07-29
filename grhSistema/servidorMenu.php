<?php

/**
 * Menu de Servidores
 *  
 * By Alat
 */

# Inicia as variáveis que receberão as sessions
$idUsuario = NULL;              # Servidor logado
$idServidorPesquisado = NULL;	# Servidor Editado na pesquisa do sistema do GRH

# Configuração
include ("_config.php");

# Zera session usadas
set_session('sessionParametro');	# Zera a session do par�metro de pesquisa da classe modelo1
set_session('sessionPaginacao');	# Zera a session de pagina��o da classe modelo1

# Verifica se veio dos alertas
$alertas = get_session("alertas");

# Permissão de Acesso
$acesso = Verifica::acesso($idUsuario,2);

if($acesso){    
    # Conecta ao Banco de Dados
    $pessoal = new Pessoal();
	
    # Verifica a fase do programa
    $fase = get('fase','menu');
    
    # Registra no log
    $origem = get('origem',FALSE);
    if($origem){
        # Grava no log a atividade
        $atividade = "Visualizou o cadastro do servidor ".$pessoal->get_nome($idServidorPesquisado);
        $data = date("Y-m-d H:i:s");
        $intra->registraLog($idUsuario,$data,$atividade,NULL,NULL,7);
    }

    # Começa uma nova página
    $page = new Page();			
    $page->iniciaPagina();
    
    # Cabeçalho da Página
    AreaServidor::cabecalho();
    
    if($fase == "menu"){    
        # Limita o tamanho da tela
        $grid = new Grid();
        $grid->abreColuna(12);

        # Cria um menu
        $menu = new MenuBar();

        # Voltar
        if(is_null($alertas)){
            $caminhoVolta = 'servidor.php';
        }else{
            $caminhoVolta = 'grh.php?fase=alertas&alerta='.$alertas;
        }

        $linkBotao1 = new Link("Voltar",$caminhoVolta);
        $linkBotao1->set_class('button');
        $linkBotao1->set_title('Volta para a página anterior');
        $linkBotao1->set_accessKey('V');
        $menu->add_link($linkBotao1,"left");

        # Pasta Funcional
        $linkBotao3 = new Link("Pasta","?fase=pasta");
        $linkBotao3->set_class('button'); 
        $linkBotao3->set_title('Exibe a pasta funcional do servidor');
        $linkBotao3->set_accessKey('P');
        #$menu->add_link($linkBotao3,"right");

        if(Verifica::acesso($idUsuario,1)){
            # Histórico
            $linkBotao4 = new Link("Histórico","../../areaServidor/sistema/historico.php?idServidor=".$idServidorPesquisado);
            $linkBotao4->set_class('button');
            $linkBotao4->set_title('Exibe as alterações feita no cadastro desse servidor');        
            $linkBotao4->set_accessKey('H');
            $menu->add_link($linkBotao4,"right");

            # Excluir
            $linkBotao5 = new Link("Excluir","servidorExclusao.php");
            $linkBotao5->set_class('alert button');
            $linkBotao5->set_title('Excluir Servidor');
            $linkBotao5->set_accessKey('x');
            $menu->add_link($linkBotao5,"right");
        }

        $menu->show();
    }else{
        botaoVoltar("?");
    }
    
    $grid = new Grid();
    $grid->abreColuna(12);
        # Exibe os dados do Servidor
        Grh::listaDadosServidor($idServidorPesquisado);
    $grid->fechaColuna();
    $grid->fechaGrid();
    
    switch ($fase){	
        # Exibe o Menu Inicial
        case "menu" :
            # Ocorrencias do servidor
            Grh::exibeOcorênciaServidor($idServidorPesquisado);
            
            # monta o menu do servidor
            Grh::menuServidor($idServidorPesquisado);
            
            # Exibe o rodapé da página
            br();
            Grh::rodape($idUsuario,$idServidorPesquisado,$pessoal->get_idPessoa($idServidorPesquisado));
            break;
        
        ##################################################################	
        
        case "pasta" :
            # Pasta Funcional
            $grid = new Grid();
            $grid->abreColuna(4);
            
            # Título
            tituloTable('Pasta Funcional');
            
            br();
                        
            # Pega o idfuncional
            $idFuncional = $pessoal->get_idFuncional($idServidorPesquisado);
            
            # Define a pasta
            $pasta = "../../_arquivo/";
            
            $achei = NULL;
            
            # Encontra a pasta
            foreach (glob($pasta.$idFuncional."*") as $escolhido) {
                $achei = $escolhido;
            }
            
            # Verifica se tem pasta desse servidor
            if(file_exists($achei)){
                
                $grupoarquivo = NULL;
                $contador = 0;
                
                # Inicia o menu
                $tamanhoImage = 60;
                $menu = new MenuGrafico(1);
            
                # pasta
                $ponteiro  = opendir($achei."/");
                while ($arquivo = readdir($ponteiro)) {

                    # Desconsidera os diretorios 
                    if($arquivo == ".." || $arquivo == "."){
                        continue;
                    }
                    
                    # Verifica a codificação do nome do arquivo
                    if(codificacao($arquivo) == 'ISO-8859-1'){
                        $arquivo = utf8_encode($arquivo);
                    }

                    # Divide o nome do arquivos
                    $partesArquivo = explode('.',$arquivo);
                    
                    # VErifica se arquivo é da pasta
                    if(substr($arquivo, 0, 5) == "Pasta"){
                        $botao = new BotaoGrafico();
                        $botao->set_label($partesArquivo[0]);
                        $botao->set_url($achei.'/'.$arquivo);
                        $botao->set_target('_blank');
                        $botao->set_image(PASTA_FIGURAS.'pasta.png',$tamanhoImage,$tamanhoImage);
                        $menu->add_item($botao);
                        
                        $contador++;
                    }
                }
                if($contador >0){
                    $menu->show();
                }
            }else{                
                p("Nenhum arquivo encontrado.","center");
            }
            
            #$callout->fecha();
            $grid->fechaColuna();
            $grid->abreColuna(8);
            
            #############################################################
            
            tituloTable('Processos');
            br();
            
            # Verifica se tem pasta desse servidor
            if(file_exists($achei)){
                
                $grupoarquivo = NULL;
                 
                # Inicia o menu
                $tamanhoImage = 60;
                $menu = new MenuGrafico(4);
            
                # pasta
                $ponteiro  = opendir($achei."/");
                while ($arquivo = readdir($ponteiro)) {

                    # Desconsidera os diretorios 
                    if($arquivo == ".." || $arquivo == "."){
                        continue;
                    }

                    # Verifica a codificação do nome do arquivo
                    if(codificacao($arquivo) == 'ISO-8859-1'){
                        $arquivo = utf8_encode($arquivo);
                    }
                    
                    # Divide o nome do arquivos
                    $partesArquivo = explode('.',$arquivo);
                    
                    
                    # VErifica se arquivo é da pasta
                    if(substr($arquivo, 0, 5) <> "Pasta"){
                        $botao = new BotaoGrafico();
                        $botao->set_label($partesArquivo[0]);
                        $botao->set_url($achei.'/'.$arquivo);
                        $botao->set_target('_blank');
                        $botao->set_image(PASTA_FIGURAS.'processo.png',$tamanhoImage,$tamanhoImage);
                        $menu->add_item($botao);
                    }
                }
                $menu->show();
            }else{               
                p("Nenhum arquivo encontrado.","center");
            }
            
            #$callout->fecha();
            $grid->fechaColuna();
            $grid->abreColuna(8);
            break;
    }

    $grid->fechaColuna();
    $grid->fechaGrid();
    $page->terminaPagina();
}else{
    loadPage("../../areaServidor/sistema/login.php");
}
