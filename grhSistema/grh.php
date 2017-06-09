<?php
/**
 * Sistema do GRH
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
    $pessoal = new Pessoal();
    $intra = new Intra();
	
    # Verifica a fase do programa
    $fase = get('fase','menu');
    $alerta = get('alerta');
		
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
    if(($fase <> 'alertas') AND ($fase <> 'resumoAlertas') AND ($fase <> 'sobre') AND ($fase <> 'atualizacoes')){       
        p(SISTEMA,'grhTitulo');
        p("Versão: ".VERSAO,"versao");
    
        # Limita o tamanho da tela
        $grid = new Grid();
        $grid->abreColuna(12);

        # Cria um menu
        $menu1 = new MenuBar();

        # Voltar
        $linkVoltar = new Link("Sair","../../areaServidor/sistema/login.php");
        $linkVoltar->set_class('button');
        $linkVoltar->set_title('Sair do Sistema');
        $linkVoltar->set_confirma('Tem certeza que deseja sair do sistema?');
        $linkVoltar->set_accessKey('i');
        $menu1->add_link($linkVoltar,"left");

        # Relatórios
        $imagem1 = new Imagem(PASTA_FIGURAS.'print.png',NULL,15,15);
        $botaoRel = new Button();
        $botaoRel->set_url("grhRelatorios.php");
        $botaoRel->set_title("Relatórios dos Sistema");
        $botaoRel->set_imagem($imagem1);
        $menu1->add_link($botaoRel,"right");
        
        # Área do Servidor
        $linkArea = new Link("Área do Servidor","../../areaServidor/sistema/areaServidor.php");
        $linkArea->set_class('button');
        $linkArea->set_title('Área do Servidor');
	$menu1->add_link($linkArea,"right");        
        
        # Sobre
        $linkSobre = new Link("Sobre","?fase=sobre");
        $linkSobre->set_class('success button');
        $linkSobre->set_title('Exibe informações do Sistema');
        $menu1->add_link($linkSobre,"right");

        $menu1->show();

        $grid->fechaColuna();
        $grid->fechaGrid();
    }
    
##################################################################
    
    # Menu
    switch ($fase)
    {	
        # Exibe o Menu Inicial
        case "menu" :
            # acessa a rotina de atualizar os status das férias
            $pessoal->mudaStatusFeriasConfirmadaFruida();

            # monta o menu principal
            Grh::menu($idUsuario);
    
            # Zera a session de alertas
            set_session('alertas');
            
            # Exibe o rodapé da página
            br();
            Grh::rodape($idUsuario);
            break;

##################################################################	

        case "resumoAlertas" :
            titulo('Alertas');
            br();                
            $checkup = New Checkup(FALSE);
            
            echo "<ul class='checkupResumo'>";
            $checkup->get_all();
            echo "</ul>";
            
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
            
            if(is_null($alerta)){
                $checkup->get_all();
            }else{
                $checkup->$alerta();
            }
            
            # Grava no log a atividade
            $data = date("Y-m-d H:i:s");
            $atividade = 'Visualizou os Alertas do Sistema';
            $intra->registraLog($idUsuario,$data,$atividade,NULL,NULL,4);
            
            $grid->fechaColuna();
            $grid->fechaGrid();
            break;
            
##################################################################
            
        case "sobre" :

            # Limita o tamanho da tela
            br(3);
            $grid = new Grid("center");
            $grid->abreColuna(6);
            
            # Cria um menu
            $menu2 = new MenuBar();
            
            $painel2 = new Callout();
            $painel2->set_title('Sobre o Sistema');
            #$painel2->set_botaoFechar(TRUE);
            $painel2->abre();
            
            br();
            p(SISTEMA,'grhTitulo');
            p('Versão: '.VERSAO.'<br/>Atualizado em: '.ATUALIZACAO,'versao');

            br();
            p('Desenvolvedor: '.AUTOR,'versao');
            p(EMAILAUTOR,'versao');
            
            # detalhes
            $linkFecha = new Link("Detalhes","?fase=atualizacoes");
            $linkFecha->set_class('button');
            $linkFecha->set_title('Exibe os detalhes das atualizações');
            $menu2->add_link($linkFecha,"left");
            
            # ok
            $linkFecha = new Link("Ok","?");
            $linkFecha->set_class('button');
            $linkFecha->set_title('fecha esta janela');
            $menu2->add_link($linkFecha,"right");
            $menu2->show();
            
            $painel2 ->fecha();

            $grid->fechaColuna();
            $grid->fechaGrid();

            break;
            
##################################################################
        
        case "atualizacoes" :
            
            # Limita a tela
            $grid = new Grid();
            $grid->abreColuna(12);
            
            # botão voltar
            botaoVoltar("?","Voltar","Volta ao Menu principal");
            
            # Título
            titulo("Detalhes das Atualizações");
            #p("Detalhes das Atualizações","center","f16");
            br();
            
            # Limita a tela
            $grid = new Grid("center");
            $grid->abreColuna(10);
            
            # Pega os dados 
            $atualizacoes = $intra->get_atualizacoes();
            
            # Percorre os dados
            foreach ($atualizacoes as $valor) {
                
                p("Versão:".$valor[0],"f16");
                p(date_to_php($valor[1]),"right","f10");
                
                $painel3 = new Callout();
                $painel3->set_title('Alterações');
                $painel3->abre();
                    p("<pre>".$valor[2]."</pre>");
                $painel3 ->fecha();
                hr();
            }
            $grid->fechaColuna();
            $grid->fechaGrid();
            
            $grid->fechaColuna();
            $grid->fechaGrid();
            break;
        
##################################################################
        

    }

    $page->terminaPagina();
}else{
    loadPage("../../areaServidor/sistema/login.php");
}
