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

    # Começa uma nova página
    $page = new Page();			
    $page->iniciaPagina();
    
    # Cabeçalho da Página
    AreaServidor::cabecalho();
    
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

    # Relatórios
    $linkBotao3 = new Link("Relatorios");
    $linkBotao3->set_class('button');    
    $linkBotao3->set_onClick("abreFechaDivId('RelServidor');");
    $linkBotao3->set_title('Relatórios desse servidor');
    $linkBotao3->set_accessKey('R');
    $menu->add_link($linkBotao3,"right");
    
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
    
    $grid->fechaColuna();
    $grid->fechaGrid();
    
    # Menu Relatório    
    $div = new Div("RelServidor");
    $div->abre();
    
    $grid = new Grid("right");
    $grid->abreColuna(6);
    
    echo '<nav aria-label="You are here:" role="navigation">';
    echo '<ul class="breadcrumbs">';
    
    # Ficha Cadastral
    echo '<li>';
    $link = new Link("Ficha Cadastral","../grhRelatorios/fichaCadastral.php");
    $link->set_title("Exibe a ficha cadastral do servidor");
    $link->set_janela(TRUE);    
    $link->show();
    echo '</li>';
    
    # Capa da Pasta
    echo '<li>';
    $link = new Link("Capa da Pasta","../grhRelatorios/capaPasta.php");
    $link->set_title("Exibe a Capa da pasta");
    #$link->set_class("disabled");
    $link->set_janela(TRUE);    
    $link->show();
    echo '</li>';
    
    # Ficha de Avaliação Funcional
    echo '<li>';
    $link = new Link("FAF","#");
    $link->set_title("Exibe a ficha de avaliação funcional");
    $link->set_class("disabled");
    #$link->set_janela(TRUE);    
    $link->show();
    echo '</li>';
    
    # Folha de Presença
    echo '<li>';
    $link = new Link("Folha de Presença","../grhRelatorios/folhaPresenca.php");
    $link->set_title("Exibe a folha de presença do Servidor");
    #$link->set_class("disabled");
    $link->set_janela(TRUE);    
    $link->show();
    echo '</li>';
    
    echo '</ul>';
    echo '</nav>';
    
    $grid->fechaColuna();
    $grid->fechaGrid();
    $div->fecha();
    
    # Exibe o arquivo e gaveta físicos da pasta do servidor
    $arquivoGaveta = $pessoal->get_arquivoGaveta($idServidorPesquisado);
    if(!is_null($arquivoGaveta)){
        $divGaveta = new Div("divGaveta");
        $divGaveta->abre();
        p($arquivoGaveta,"f18");
        $divGaveta->fecha();
    }
    
    # Exibe os dados do Servidor
    Grh::listaDadosServidor($idServidorPesquisado);
    
    switch ($fase)
    {	
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
    }

    $page->terminaPagina();
}else{
    loadPage("../../areaServidor/sistema/login.php");
}
