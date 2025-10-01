<?php

/**
 * servidor afastamento
 *  
 * By Alat
 */
# Inicia as variáveis que receberão as sessions
$idUsuario = null;   # Servidor logado
$idServidorPesquisado = null;   # Servidor Editado na pesquisa do sistema do GRH
# Configuração
include ("_config.php");

# Permissão de Acesso
$acesso = Verifica::acesso($idUsuario, [1, 2, 12]);

if ($acesso) {
    # Conecta ao Banco de Dados
    $intra = new Intra();
    $pessoal = new Pessoal();
    $aposentadoria = new Aposentadoria();

    # Verifica se veio menu grh e registra o acesso no log
    $grh = get('grh', false);
    if ($grh) {
        # Grava no log a atividade
        $atividade = "Cadastro do servidor - Afastamentos";
        $data = date("Y-m-d H:i:s");
        $intra->registraLog($idUsuario, $data, $atividade, null, null, 7, $idServidorPesquisado);
    }

    # Verifica se tem ou não botão de voltar
    $volta = get("volta", true);

    # Verifica a fase do programa
    $fase = get('fase');

    # Começa uma nova página
    $page = new Page();
    $page->iniciaPagina();

    # Menu e Cabeçalho
    if ($fase <> "relatorio") {

        # Cabeçalho da Página
        AreaServidor::cabecalho();
    }

##############################################################################################################################################
    # Limita o tamanho da tela
    $grid = new Grid();
    $grid->abreColuna(12);

    #######################################

    switch ($fase) {
        case "":

            # Cria um menu
            if ($volta) {
                $menu = new MenuBar();

                # Botão voltar
                $linkBotaoVoltar = new Button('Voltar', 'servidorMenu.php');
                $linkBotaoVoltar->set_title('Volta para a página anterior');
                $linkBotaoVoltar->set_accessKey('V');
                $menu->add_link($linkBotaoVoltar, "left");

                # Relatórios
                $imagem = new Imagem(PASTA_FIGURAS . 'print.png', null, 15, 15);
                $botaoRel = new Button();
                $botaoRel->set_title("Relatório dessa pesquisa");
                $botaoRel->set_url("?fase=relatorio");
                $botaoRel->set_target("_blank");
                $botaoRel->set_imagem($imagem);
                $menu->add_link($botaoRel, "right");

                # Calendário
                $botaoCalendario = new Link("Calendário", "calendario.php");
                $botaoCalendario->set_class('button');
                $botaoCalendario->set_title('Exibe o calendário');
                $botaoCalendario->set_target("_calendario");
                $menu->add_link($botaoCalendario, "right");
                $menu->show();
            } else {
                br();
            }

            # Exibe os dados do servidor
            get_DadosServidor($idServidorPesquisado);

            # classe de lic médica
            $classeLicMedica = new LicencaMedica();

//    for ($x = 2002; $x <= 2022; $x++) {
//        $verificadias = new VerificaDiasAfastados($idServidorPesquisado);
//        $verificadias->setAno($x);
//        $verificadias->verifica();
//
//        if (anoBissexto($x)) {
//            br(2);
//            echo $x . " (Bissexto) -> " . $verificadias->getDiasAfastados();
//            echo " dias afastados | " . (366 - $verificadias->getDiasAfastados()) . " dias trabalhados";
//            echo " | Data Inicial Licença em Aberto: " . date_to_php($classeLicMedica->getDtIniciaLicencaAberto($idServidorPesquisado, $x));          
//        } else {
//            br(2);
//            echo $x . "            -> " . $verificadias->getDiasAfastados();
//            echo " dias afastados | " . (365 - $verificadias->getDiasAfastados()) . " dias trabalhados";
//            echo " | Data Inicial Licença em Aberto: " . date_to_php($classeLicMedica->getDtIniciaLicencaAberto($idServidorPesquisado, $x));
//            
//        }
//    }

            $afast = new ListaAfastamentosServidor($idServidorPesquisado);
            $afast->exibeTabela();
            break;

        case "relatorio":
            
            $afast = new ListaAfastamentosServidor($idServidorPesquisado);
            $afast->exibeRelatorio2();            
            break;
    }


    $grid->fechaColuna();
    $grid->fechaGrid();

    $page->terminaPagina();
} else {
    loadPage("../../areaServidor/sistema/login.php");
}