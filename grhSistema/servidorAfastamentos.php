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

    # Começa uma nova página
    $page = new Page();
    $page->iniciaPagina();

    # Cabeçalho da Página
    AreaServidor::cabecalho();

##############################################################################################################################################
    # Limita o tamanho da tela
    $grid = new Grid();
    $grid->abreColuna(12);

    # Cria um menu
    if ($volta) {
        $menu = new MenuBar();

        # Botão voltar
        $linkBotaoVoltar = new Button('Voltar', 'servidorMenu.php');
        $linkBotaoVoltar->set_title('Volta para a página anterior');
        $linkBotaoVoltar->set_accessKey('V');
        $menu->add_link($linkBotaoVoltar, "left");
        $menu->show();
    } else {
        br();
    }

    # Exibe os dados do servidor
    get_DadosServidor($idServidorPesquisado);

//    for ($x = 2004; $x <= 2022; $x++) {
//        $verificadias = new VerificaDiasAfastados($idServidorPesquisado);
//        $verificadias->setAno($x);
//        $verificadias->verifica();
//
//        if (anoBissexto($x)) {
//            echo $x . " (Bissexto) -> " . $verificadias->getDiasAfastados();
//            echo " dias afastados | " . (366 - $verificadias->getDiasAfastados()) . " dias trabalhados<br/>";
//            hr();
//        } else {
//            echo $x . "            -> " . $verificadias->getDiasAfastados();
//            echo " dias afastados | " .(365 - $verificadias->getDiasAfastados()) . " dias trabalhados<br/>";
//        }
//    }

    $afast = new ListaAfastamentosServidor($idServidorPesquisado);
    $afast->exibeTabela();

    $grid->fechaColuna();
    $grid->fechaGrid();

    $page->terminaPagina();
} else {
    loadPage("../../areaServidor/sistema/login.php");
}