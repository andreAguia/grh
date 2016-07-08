<?php

/**
 * Menu de Servidores
 *  
 * By Alat
 */

# Inicia as variáveis que receberão as sessions
$idUsuario = null;              # Servidor logado
$idServidorPesquisado = null;	# Servidor Editado na pesquisa do sistema do GRH

# Configuração
include ("_config.php");

# Zera session usadas
set_session('sessionParametro');	# Zera a session do par�metro de pesquisa da classe modelo1
set_session('sessionPaginacao');	# Zera a session de pagina��o da classe modelo1

# Permissão de Acesso
$acesso = Verifica::acesso($idUsuario,2);

if($acesso)
{    
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

    # Verifica qual botões ficará inativo
    switch ($fase)
    {
      case "menu" :
          $classBotao2 = 'disabled button';
          $classBotao3 = 'button';
          break;

      case "relatorios" :
          $classBotao2 = 'button';
          $classBotao3 = 'disabled button';
          break;
    }
    
    # Voltar
    $linkBotao1 = new Link("Voltar",'servidor.php');
    $linkBotao1->set_class('button');
    $linkBotao1->set_title('Volta para a página anterior');
    $linkBotao1->set_accessKey('V');
    $menu->add_link($linkBotao1,"left");

    # Cadastros
    $linkBotao2 = new Link("Cadastros","servidorMenu.php");
    $linkBotao2->set_class($classBotao2);
    $linkBotao2->set_title('Cadastro dos Servidores');
    $linkBotao2->set_accessKey('C');
    $menu->add_link($linkBotao2,"right");

    # Relatórios
    $linkBotao3 = new Link("Relatorios","servidorMenu.php?fase=relatorios");
    $linkBotao3->set_class($classBotao3);
    $linkBotao3->set_title('Relatórios desse servidor');
    $linkBotao3->set_accessKey('R');
    $menu->add_link($linkBotao3,"right");
    
    if(Verifica::acesso($idUsuario,1)){
        # Histórico
        $linkBotao4 = new Link("Histórico","../../admin/adminSistema/historico.php?idServidor=".$idServidorPesquisado);
        $linkBotao4->set_class('button');
        $linkBotao4->set_title('Exibe as alterações feita no cadastro desse servidor');
        $linkBotao4->set_accessKey('H');
        $menu->add_link($linkBotao4,"right");
        
        # Excluir
        $linkBotao5 = new Link("Excluir","servidorExclusao.php");
        $linkBotao5->set_class('alert button');
        $linkBotao5->set_title('Excluir Servidor');
        $linkBotao5->set_accessKey('E');
        $menu->add_link($linkBotao5,"right");
    }

    $menu->show();
    
    $grid->fechaColuna();
    $grid->fechaGrid();
    
    # Exibe os dados do Servidor
    Grh::listaDadosServidor($idServidorPesquisado);
    
    switch ($fase)
    {	
        # Exibe o Menu Inicial
        case "menu" :
            # monta o menu do servidor
            Grh::menuServidor($idServidorPesquisado);
            break;
        
   ##################################################################	

        case "relatorios" :
            # Limita o tamanho da tela
            $grid = new Grid();
            $grid->abreColuna(12);
                titulo('Relatórios');
                $div = new Div("button");
                $div->abre();
                echo '<ul class="menuVertical">';
                echo '<li><a href="#">Ficha Cadastral</a></li>';
                echo '<li><a href="#">Capa da Pasta</a></li>';
                echo '<li><a href="#">Folhas de Processos Arquivadas na Pasta Funcional</a></li>';
                echo '<li><a href="#">FAF - Formulário de Avaliação Funcional</a></li>';
                echo '<li><a href="#">Folha de Presença</a></li>';
                echo '</ul>';
                $div->fecha();
            $grid->fechaColuna();
            $grid->fechaGrid();
            break;

    }

    $page->terminaPagina();
}
?>

