<?php
/**
 * Sistema do GRH
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
    $pessoal = new Pessoal();
    $intra = new Intra();
	
    # Verifica a fase do programa
    $fase = get('fase','menu');
		
    # Define a senha padrão de acordo com o que está nas variáveis
    #define("SENHA_PADRAO",$config->get_variavel('senha_padrao'));

    # Começa uma nova página
    $page = new Page();
    $page->set_bodyOnLoad("ajaxLoadPage('grh.php?fase=resumoAlertas','divAlertas',null);");
    $page->iniciaPagina();

    # Cabeçalho da Página
    if($fase <> 'resumoAlertas'){  
        AreaServidor::cabecalho();
    }
     
    # Menu
    if(($fase <> 'alertas') AND ($fase <> 'resumoAlertas')){       
        p(SISTEMA,'grhTitulo');
    
        # Limita o tamanho da tela
        $grid = new Grid();
        $grid->abreColuna(12);

        # Cria um menu
        $menu1 = new MenuBar();

        # Voltar
        $linkBotao1 = new Link("Sair","../../areaServidor/sistema/login.php");
        $linkBotao1->set_class('button');
        $linkBotao1->set_title('Sair do Sistema');
        $linkBotao1->set_confirma('Tem certeza que deseja sair do sistema?');
        $linkBotao1->set_accessKey('i');
        $menu1->add_link($linkBotao1,"left");

        # Relatórios
        $linkBotao3 = new Link("Relatorios","grhRelatorios.php");
        $linkBotao3->set_class('button');
        $linkBotao3->set_title('Relatórios dos Sistema');
        $linkBotao3->set_accessKey('R');
        $menu1->add_link($linkBotao3,"right");
        
        # Área do Servidor
        $linkBotao3 = new Link("Área do Servidor","../../areaServidor/sistema/areaServidor.php");
        $linkBotao3->set_class('button');
        $linkBotao3->set_title('Área do Servidor');
        $menu1->add_link($linkBotao3,"right");

        $menu1->show();

        $grid->fechaColuna();
        $grid->fechaGrid();
    }
    switch ($fase)
    {	
        # Exibe o Menu Inicial
        case "menu" :
            # acessa a rotina de atualizar os status das férias
            $pessoal->mudaStatusFeriasConfirmadaFruida();

            # monta o menu principal
            Grh::menu($idUsuario);
            break;

##################################################################	

        case "resumoAlertas" :
            titulo('Alertas');
            br();                
            $checkup = New Checkup(false);                
            $checkup->get_all();
            break;

##################################################################

        case "alertas" :
            # Botão voltar
            botaoVoltar('?');
            
            # Limita o tamanho da tela
            $grid = new Grid();
            $grid->abreColuna(12);

            # Exibe o título
            titulo('Alertas do Sistema');
            br();
            
            # executa o checkup
            $checkup = New Checkup();
            $checkup->get_all();
            
            # Grava no log a atividade
            $data = date("Y-m-d H:i:s");
            $atividade = 'Visualizou os Alertas do Sistema';
            $intra->registraLog($idUsuario,$data,$atividade,null,null,4);
            
            $grid->fechaColuna();
            $grid->fechaGrid();
            break;
    }

    $page->terminaPagina();
}else{
    loadPage("../../areaServidor/sistema/login.php");
}
