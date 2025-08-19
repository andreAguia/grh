<?php

/**
 * Área de Licença Sem Vencimentos
 *
 * By Alat
 */
# Reservado para o servidor logado
$idUsuario = null;

# Configuração
include ("_config.php");

# Permissão de Acesso
$acesso = Verifica::acesso($idUsuario, [1, 2, 12]);

if ($acesso) {
    # Conecta ao Banco de Dados
    $intra = new Intra();
    $pessoal = new Pessoal();

    # Verifica a fase do programa
    $fase = get('fase');

    # Verifica se veio menu grh e registra o acesso no log
    $grh = get('grh', false);
    if ($grh) {
        # Grava no log a atividade
        $atividade = "Visualizou a área de licença sem vencimentos";
        $data = date("Y-m-d H:i:s");
        $intra->registraLog($idUsuario, $data, $atividade, null, null, 7);
    }

    # pega o id (se tiver)
    $id = soNumeros(get('id'));

    # pega o idTpLicenca (se tiver)
    $idTpLicenca = soNumeros(get('idTpLicenca'));

    # Pega os parâmetros
    $parametroNome = post('parametroNome', get_session('parametroNome'));

    # Joga os parâmetros par as sessions
    set_session('parametroNome', $parametroNome);

    # Começa uma nova página
    $page = new Page();
    $page->iniciaPagina();

    # Cabeçalho da Página
    if ($fase <> "relatorio") {
        AreaServidor::cabecalho();
    }

################################################################

    switch ($fase) {
        case "" :
            br(4);
            aguarde();
            br();

            # Limita a tela
            $grid1 = new Grid("center");
            $grid1->abreColuna(5);
            p("Aguarde...", "center");
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
            $botaoVoltar = new Link("Voltar", "grh.php");
            $botaoVoltar->set_class('button');
            $botaoVoltar->set_title('Voltar a página anterior');
            $botaoVoltar->set_accessKey('V');
            $menu1->add_link($botaoVoltar, "left");

            # Calendário
            $botaoCalendario = new Link("Calendário", "calendario.php");
            $botaoCalendario->set_class('button');
            $botaoCalendario->set_title('Exibe o calendário');
            $botaoCalendario->set_target("_calendario");
            $menu1->add_link($botaoCalendario, "right");

            # Status
            $botao2 = new Button("Status");
            $botao2->set_title("Exibe as regras de mudança automática do status");
            $botao2->set_onClick("abreFechaDivId('divRegrasLsv');");
            $menu1->add_link($botao2, "right");

            # Relatórios
            $imagem = new Imagem(PASTA_FIGURAS . 'print.png', null, 15, 15);
            $botaoRel = new Button();
            $botaoRel->set_title("Relatório dessa pesquisa");
            $botaoRel->set_url("?fase=relatorio");
            $botaoRel->set_target("_blank");
            $botaoRel->set_imagem($imagem);
            $menu1->add_link($botaoRel, "right");

            $menu1->show();

            # Formulário de Pesquisa
            $form = new Form('?');

            $controle = new Input('parametroNome', 'texto', 'Pesquisa por Nome:', 1);
            $controle->set_size(100);
            $controle->set_title('Nome do servidor');
            $controle->set_valor($parametroNome);
            $controle->set_autofocus(true);
            $controle->set_onChange('formPadrao.submit();');
            $controle->set_linha(1);
            $controle->set_col(4);
            $form->add_item($controle);

            $form->show();

            exibeRegraStatusLSV();

            ################################################################
            # Exibe a tabela de Servidores
            $lsv = new LicencaSemVencimentos();
            
            $lsv->set_linkEditar('?fase=editaServidor');
            $lsv->exibeLista($parametroNome);           

            $grid->fechaColuna();
            $grid->fechaGrid();
            break;

        ################################################################

        case "editaServidor" :
            br(8);
            aguarde();

            # Informa o $id Servidor
            set_session('idServidorPesquisado', $id);

            # Informa a origem
            set_session('origem', 'areaLicencaSemVencimentos.php');

            # Carrega a página específica
            loadPage('servidorLicencaSemVencimentos.php');
            break;

        ################################################################
        # Relatório
        case "relatorio" :
            $lsv = new LicencaSemVencimentos();

            if (empty($parametroNome)) {
                $lsv->exibeRelatorio();
            } else {
                $lsv->exibeRelatorio($parametroNome);
            }
            break;

        ################################################################  

        case "documentacao" :
            $grid = new Grid();
            $grid->abreColuna(12);

            botaoVoltar("?");
            exibeDocumentacaoLicenca($idTpLicenca);

            $grid->fechaColuna();
            $grid->fechaGrid();
            break;
    }

    $page->terminaPagina();
} else {
    loadPage("../../areaServidor/sistema/login.php");
}
