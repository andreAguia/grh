<?php
/**
 * Cadastro de Tempo de Serviço
 *  
 * By Alat
 */

# Inicia as variáveis que receberão as sessions
$idUsuario = NULL;              # Servidor logado
$idServidorPesquisado = NULL;	# Servidor Editado na pesquisa do sistema do GRH

# Configuração
include ("_config.php");

# Permissão de Acesso
$acesso = Verifica::acesso($idUsuario,2);

if($acesso){    
    # Conecta ao Banco de Dados
    $intra = new Intra();
    $pessoal = new Pessoal();
    $aposentadoria = new Aposentadoria();

    # Começa uma nova página
    $page = new Page();			
    $page->iniciaPagina();

    # Cabeçalho da Página
    AreaServidor::cabecalho();
    
##############################################################################################################################################
    
    # Limita o tamanho da tela
    $grid = new Grid();
    $grid->abreColuna(12);

    # Verifica a data de saída
    $dtSaida = $pessoal->get_dtSaida($idServidorPesquisado);      # Data de Saída de servidor inativo
    $dtHoje = date("Y-m-d");                                      # Data de hoje
    $dtFinal = NULL;

    # Analisa a data
    if(!vazio($dtSaida)){           // Se tem saída é a saída
        $dtFinal = date_to_bd($dtSaida);
        $disabled = TRUE;
        $autofocus = FALSE;
    }else{                          // Não tem saída então é hoje
        $dtFinal = $dtHoje;         
    }

    # Finalmente define o valor
    $parametro = $dtFinal;

    # Cria um menu
    $menu = new MenuBar();

    # Botão voltar
    $linkBotaoVoltar = new Button('Voltar','servidorMenu.php');
    $linkBotaoVoltar->set_title('Volta para a página anterior');
    $linkBotaoVoltar->set_accessKey('V');
    $menu->add_link($linkBotaoVoltar,"left");

    $imagem = new Imagem(PASTA_FIGURAS.'ajuda.png',NULL,15,15);
    $botaoHelp = new Button();
    $botaoHelp->set_imagem($imagem);
    $botaoHelp->set_title("Ajuda");
    $botaoHelp->set_url("https://docs.google.com/document/d/e/2PACX-1vSH4_OkFekLul3KY6AlTHP0WjDblvsQXdX1uA319UV4REs3d9YklhQJqSFoL_yrHfYEaSmX94RtQ47Q/pub");
    $botaoHelp->set_target("_blank");            
    #$menu->add_link($botaoHelp,"right");

    # Relatório
    $imagem = new Imagem(PASTA_FIGURAS.'print.png',NULL,15,15);
    $botaoRel = new Button();
    $botaoRel->set_imagem($imagem);
    $botaoRel->set_title("Imprimir Relatório de Histórico de Tempo de Serviço Averbado");
    $botaoRel->set_url("../grhRelatorios/servidorAverbacao.php?data=$parametro");
    $botaoRel->set_target("_blank");
    #$menu->add_link($botaoRel,"right");
    
    $linkBotaoHistorico = new Button("Tempo de Serviço");
    $linkBotaoHistorico->set_title('Exibe o tempo de Serviço desse Servidor');    
    $linkBotaoHistorico->set_onClick("abreFechaDivId('divTempoServicoAposentadoria');");
    $linkBotaoHistorico->set_class('success button');
    $menu->add_link($linkBotaoHistorico,"right");
    
    $linkRegras = new Button("Regras");
    $linkRegras->set_title('Exibe as regras da aposentadoria');
    $linkRegras->set_onClick("abreFechaDivId('divRegrasAposentadoria');");
    $linkRegras->set_class('success button');
    $menu->add_link($linkRegras,"right");

    $menu->show();

    # Exibe os dados do servidor
    get_DadosServidor($idServidorPesquisado);
    
##############################################################################################################################################
#   Regras
##############################################################################################################################################
    
    echo '<div id="divRegrasAposentadoria">';   
    $aposentadoria->exibeRegras();
    echo '</div>';
    
##############################################################################################################################################
#   Tempo de Serviço
##############################################################################################################################################
    
    echo '<div id="divTempoServicoAposentadoria">';   
    $aposentadoria->exibeTempo($idServidorPesquisado);
    echo '</div>';
    
##############################################################################################################################################
#   Previsão de Aposentadoria
##############################################################################################################################################
    
    $grid1 = new Grid();
    $grid1->abreColuna(4);
    
    # Aposentadoria Integral
    $aposentadoria->exibePrevisaoIntegral($idServidorPesquisado);
    
    $grid1->fechaColuna();
    
    #############################################
       
    $grid1->abreColuna(4);
    
    # Aposentadoria Proporcional
    $aposentadoria->exibePrevisaoProporcional($idServidorPesquisado);
        
    $grid1->fechaColuna();
    
    #############################################
    
    $grid1->abreColuna(4);
    
    # Aposentadoria Compulsória
    $aposentadoria->exibePrevisaoCompulsoria($idServidorPesquisado);
    
    $grid1->fechaColuna();
    $grid1->fechaGrid();
    
    #############################################

    $page->terminaPagina();
}else{
    loadPage("../../areaServidor/sistema/login.php");
}