<?php
/**
 * Área de Frequência
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
    $fase = get('fase');

    # Verifica se veio menu grh e registra o acesso no log
    $grh = get('grh',FALSE);
    if($grh){
        # Grava no log a atividade
        $atividade = "Visualizou a área de Frequência";
        $data = date("Y-m-d H:i:s");
        $intra->registraLog($idUsuario,$data,$atividade,NULL,NULL,7);
    }

    # pega o id (se tiver)
    $id = soNumeros(get('id'));

    # Pega os parâmetros
    #$parametroAno = post('parametroAno',get_session('parametroAno',date('Y')));
    #$parametroMes = post('parametroMes',get_session('parametroMes',date('m')));
    #$parametroLotacao = post('parametroLotacao',get_session('parametroLotacao',66));

    # Joga os parâmetros par as sessions
    #set_session('parametroAno',$parametroAno);
    #set_session('parametroMes',$parametroMes);
    #set_session('parametroLotacao',$parametroLotacao);

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
            br(4);
            aguarde();
            br();

            # Limita a tela
            $grid1 = new Grid("center");
            $grid1->abreColuna(5);
                p("Aguarde...","center");
            $grid1->fechaColuna();
            $grid1->fechaGrid();

            loadPage('?fase=exibeLista');
            break;

################################################################

        case "exibeLista" :
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
            $botaoRel->set_url("?fase=relatorio");
            $botaoRel->set_target("_blank");
            $botaoRel->set_imagem($imagem);
            $menu1->add_link($botaoRel,"right");

            $menu1->show();

        ################################################################

            # Exibe a tabela de Servidores afastados
            $afast = new LicencaSemVencimentos();
            $afast->set_linkEditar('?fase=editaServidor');
            $afast->exibeTabela();

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
            set_session('origem','areaLicencaSemVencimentos.php');

            # Carrega a página específica
            loadPage('servidorMenu.php');
            break;

    ################################################################

        # Relatório
        case "relatorio" :
            $afast = new LicencaSemVencimentos();
            $afast->exibeRelatorio();
            break;
    }

    $page->terminaPagina();
}else{
    loadPage("../../areaServidor/sistema/login.php");
}