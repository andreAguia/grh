<?php

/**
 * Área de Aposentadoria
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
    $pessoal = new Pessoal();
    $intra = new Intra();
    $aposentadoria = new Aposentadoria();

    # Verifica a fase do programa
    $fase = get('fase', 'aguardePorAno');

    # pega o id (se tiver)
    $id = soNumeros(get('id'));

    # Verifica se veio menu grh e registra o acesso no log
    $grh = get('grh', false);
    if ($grh) {
        # Grava no log a atividade
        $atividade = "Visualizou a área de servidores aposentados";
        $data = date("Y-m-d H:i:s");
        $intra->registraLog($idUsuario, $data, $atividade, null, null, 7);
    }

    # Começa uma nova página
    $page = new Page();
    $page->iniciaPagina();

    # Cabeçalho da Página
    if ($fase <> "porAnoRelatorio" AND $fase <> "porTipoRelatorio") {
        AreaServidor::cabecalho();
    }

    # Pega os parâmetros
    $parametroAno = post('parametroAno', get_session('parametroAno', $aposentadoria->get_ultimoAnoAposentadoria()));
    $parametroMotivo = post('parametroMotivo', get_session('parametroMotivo', 3));
    $parametroFundamentacao = post('parametroFundamentacao', get_session('parametroFundamentacao'));
    $parametroLotacao = post('parametroLotacao', get_session('parametroLotacao', $pessoal->get_idLotacao($intra->get_idServidor($idUsuario))));
    $parametroTipo = post('parametroTipo', get_session('parametroTipo', "Todos"));

    # Joga os parâmetros par as sessions
    set_session('parametroAno', $parametroAno);
    set_session('parametroMotivo', $parametroMotivo);
    set_session('parametroFundamentacao', $parametroFundamentacao);
    set_session('parametroLotacao', $parametroLotacao);
    set_session('parametroTipo', $parametroTipo);

    # Limita a página
    $grid = new Grid();
    $grid->abreColuna(12);

    # Cria um menu
    if ($fase <> "porAnoRelatorio" AND $fase <> "porTipoRelatorio") {
        $menu = new MenuBar();

        # Voltar
        $botaoVoltar = new Link("Voltar", "areaPrevisao.php");
        $botaoVoltar->set_class('button');
        $botaoVoltar->set_title('Voltar a página anterior');
        $botaoVoltar->set_accessKey('V');
        $menu->add_link($botaoVoltar, "left");

        # Servidores Aposentados por Ano
        if ($fase == "porAno" OR $fase == "aguardePorAno") {
            $botao1 = new Link("Por Ano", "#");
            $botao1->set_class('hollow button');
        } else {
            $botao1 = new Link("Por Ano", "?fase=aguardePorAno");
            $botao1->set_class('button');
        }
        $menu->add_link($botao1, "right");

        # Servidores Aposentados por Tipo de Aposentadoria    
        if ($fase == "porTipo" OR $fase == "aguardePorTipo") {
            $botao1 = new Link("Por Tipo", "#");
            $botao1->set_class('hollow button');
        } else {
            $botao1 = new Link("Por Tipo", "?fase=aguardePorTipo");
            $botao1->set_class('button');
        }
        $menu->add_link($botao1, "right");
        
        # Servidores Aposentados por Fundamentação Legal  
        if ($fase == "porFundamentacao" OR $fase == "aguardePorFundamentacao") {
            $botao1 = new Link("Por Fundamentação Legal", "#");
            $botao1->set_class('hollow button');
        } else {
            $botao1 = new Link("Por Fundamentação Legal", "?fase=aguardePorFundamentacao");
            $botao1->set_class('button');
        }
        $menu->add_link($botao1, "right");

        # Estatística dos Servidores Aposentados
        if ($fase == "estatistica" OR $fase == "aguardeEstatistica") {
            $botao1 = new Link("Estatística", "#");
            $botao1->set_class('hollow button');
        } else {
            $botao1 = new Link("Estatística", "?fase=aguardeEstatistica");
            $botao1->set_class('button');
        }
        $menu->add_link($botao1, "right");
        $menu->show();
    }

    $grid->fechaColuna();
    $grid->abreColuna(12);

    #######################################
    #######################################

    switch ($fase) {
        case "":
        case "aguardePorAno":

            br(4);
            aguarde();
            br();

            # Limita a tela
            $grid1 = new Grid("center");
            $grid1->abreColuna(5);
            p("Aguarde...", "center");
            $grid1->fechaColuna();
            $grid1->fechaGrid();

            loadPage('?fase=porAno');
            break;

        #######################################

        case "porAno" :

            # Coloca 2 colunas
            $grid1 = new Grid();
            $grid1->abreColuna(8);

            # Formulário de Pesquisa
            $form = new Form('?fase=aguardePorAno');

            # Cria um array com os anos possíveis
            $select = 'SELECT DISTINCT YEAR(tbservidor.dtDemissao), YEAR(tbservidor.dtDemissao)
                         FROM tbservidor 
                        WHERE situacao = 2
                          AND (tbservidor.idPerfil = 1 OR tbservidor.idPerfil = 4)
                     ORDER BY 1 desc';

            $anos = $pessoal->select($select);

            $controle = new Input('parametroAno', 'combo');
            $controle->set_size(6);
            $controle->set_title('Filtra por Ano exercício');
            $controle->set_array($anos);
            $controle->set_valor($parametroAno);
            $controle->set_autofocus(true);
            $controle->set_onChange('formPadrao.submit();');
            $controle->set_linha(1);
            $controle->set_col(4);
            $form->add_item($controle);

            $form->show();

            $grid1->fechaColuna();

            #####

            $grid1->abreColuna(4);

            # Botão de Relatório
            $menu1 = new MenuBar();

            # Relatórios
            $imagem = new Imagem(PASTA_FIGURAS . 'print.png', null, 15, 15);
            $botaoRel = new Button();
            $botaoRel->set_title("Relatório dessa pesquisa");
            $botaoRel->set_url("?fase=porAnoRelatorio");
            $botaoRel->set_target("_blank");
            $botaoRel->set_imagem($imagem);
            $menu1->add_link($botaoRel, "right");

            $menu1->show();

            $grid1->fechaColuna();
            $grid1->fechaGrid();

            # Exibe a lista
            $aposentadoria->exibeAposentadosPorAno($parametroAno, "editarPorAno");
            break;

        #######################################

        case "porAnoRelatorio" :

            # Exibe a lista
            $aposentadoria->exibeAposentadosPorAno($parametroAno, null, true);
            break;

        #######################################        

        case "editarPorAno" :

            br(8);
            aguarde();

            # Informa o $id Servidor
            set_session('idServidorPesquisado', $id);

            # Informa a origem
            set_session('origem', 'areaAposentadoria.php?fase=aguardePorAno');

            # Carrega a página específica
            loadPage('servidorMenu.php');
            break;

        #######################################
        #######################################

        case "aguardePorTipo":

            br(4);
            aguarde();
            br();

            # Limita a tela
            $grid1 = new Grid("center");
            $grid1->abreColuna(5);
            p("Aguarde...", "center");
            $grid1->fechaColuna();
            $grid1->fechaGrid();

            loadPage('?fase=porTipo');
            break;

        #######################################

        case "porTipo" :

            # Coloca 2 colunas
            $grid1 = new Grid();
            $grid1->abreColuna(8);

            # Formulário de Pesquisa
            $form = new Form('?fase=aguardePorTipo');

            # Cria um array com os tipo possíveis
            $selectMotivo = "SELECT DISTINCT idMotivo,
                                    tbmotivo.motivo
                               FROM tbmotivo JOIN tbservidor USING (idMotivo)
                              WHERE situacao = 2
                                AND (tbservidor.idPerfil = 1 OR tbservidor.idPerfil = 4)
                           ORDER BY 2";

            $motivosPossiveis = $pessoal->select($selectMotivo);

            $controle = new Input('parametroMotivo', 'combo', null, 1);
            $controle->set_size(8);
            $controle->set_title('Filtra por Motivo');
            $controle->set_array($motivosPossiveis);
            $controle->set_valor(date("Y"));
            $controle->set_valor($parametroMotivo);
            $controle->set_onChange('formPadrao.submit();');
            $controle->set_autofocus(true);
            $controle->set_linha(1);
            $controle->set_col(12);
            $form->add_item($controle);

            $form->show();

            $grid1->fechaColuna();

            #####

            $grid1->abreColuna(4);

            # Botão de Relatório
            $menu1 = new MenuBar();

            # Relatórios
            $imagem = new Imagem(PASTA_FIGURAS . 'print.png', null, 15, 15);
            $botaoRel = new Button();
            $botaoRel->set_title("Relatório dessa pesquisa");
            $botaoRel->set_url("?fase=porTipoRelatorio");
            $botaoRel->set_target("_blank");
            $botaoRel->set_imagem($imagem);
            $menu1->add_link($botaoRel, "right");

            $menu1->show();

            $grid1->fechaColuna();
            $grid1->fechaGrid();

            # Exibe a lista
            $aposentadoria->exibeAposentadosPorTipo($parametroMotivo, "editarPorTipo");
            break;

        #######################################

        case "porTipoRelatorio" :

            # Exibe a lista
            $aposentadoria->exibeAposentadosPorTipo($parametroMotivo, null, true);
            break;

        #######################################            

        case "editarPorTipo" :

            br(8);
            aguarde();

            # Informa o $id Servidor
            set_session('idServidorPesquisado', $id);

            # Informa a origem
            set_session('origem', 'areaAposentadoria.php?fase=aguardePorTipo');

            # Carrega a página específica
            loadPage('servidorMenu.php');
            break;

        #######################################
        #######################################

        case "aguardePorFundamentacao":

            br(4);
            aguarde();
            br();

            # Limita a tela
            $grid1 = new Grid("center");
            $grid1->abreColuna(5);
            p("Aguarde...", "center");
            $grid1->fechaColuna();
            $grid1->fechaGrid();

            loadPage('?fase=porFundamentacao');
            break;

        #######################################

        case "porFundamentacao" :

            # Coloca 2 colunas
            $grid1 = new Grid();
            $grid1->abreColuna(8);

            # Formulário de Pesquisa
            $form = new Form('?fase=aguardePorFundamentacao');

            # Cria um array com os tipo possíveis
            $selectMotivo = "SELECT DISTINCT motivoDetalhe,
                                    motivoDetalhe
                               FROM tbmotivo JOIN tbservidor USING (idMotivo)
                              WHERE situacao = 2
                                AND (tbservidor.idPerfil = 1 OR tbservidor.idPerfil = 4)
                           ORDER BY 2";

            $motivosPossiveis = $pessoal->select($selectMotivo);

            $controle = new Input('parametroFundamentacao', 'combo', null, 1);
            $controle->set_size(8);
            $controle->set_title('Filtra por Fundamentação Legal');
            $controle->set_array($motivosPossiveis);
            $controle->set_valor(date("Y"));
            $controle->set_valor($parametroFundamentacao);
            $controle->set_onChange('formPadrao.submit();');
            $controle->set_autofocus(true);
            $controle->set_linha(1);
            $controle->set_col(12);
            $form->add_item($controle);

            $form->show();

            $grid1->fechaColuna();

            #####

            $grid1->abreColuna(4);

            # Botão de Relatório
            $menu1 = new MenuBar();

            # Relatórios
            $imagem = new Imagem(PASTA_FIGURAS . 'print.png', null, 15, 15);
            $botaoRel = new Button();
            $botaoRel->set_title("Relatório dessa pesquisa");
            $botaoRel->set_url("?fase=porFundamentacaoRelatorio");
            $botaoRel->set_target("_blank");
            $botaoRel->set_imagem($imagem);
            $menu1->add_link($botaoRel, "right");

            $menu1->show();

            $grid1->fechaColuna();
            $grid1->fechaGrid();

            # Exibe a lista
            $aposentadoria->exibeAposentadosPorFundamentacaoLegal($parametroFundamentacao, "editarPorFundamentacao");
            break;

        #######################################

        case "porFundamentacaoRelatorio" :

            # Exibe a lista
            $aposentadoria->exibeAposentadosPorFundamentacaoLegal($parametroFundamentacao, null, true);
            break;

        #######################################            

        case "editarPorFundamentacao" :

            br(8);
            aguarde();

            # Informa o $id Servidor
            set_session('idServidorPesquisado', $id);

            # Informa a origem
            set_session('origem', 'areaAposentadoria.php?fase=aguardePorFundamentacao');

            # Carrega a página específica
            loadPage('servidorMenu.php');
            break;

        #######################################
        #######################################

        case "aguardeEstatistica":

            br(4);
            aguarde();
            br();

            # Limita a tela
            $grid1 = new Grid("center");
            $grid1->abreColuna(5);
            p("Aguarde...", "center");
            $grid1->fechaColuna();
            $grid1->fechaGrid();

            loadPage('?fase=estatistica');
            break;

        #######################################

        case "estatistica" :

            #tituloTable("por Tipo de Aposentadoria");
            #br();

            $grid1 = new Grid();
            $grid1->abreColuna(6);

            # Monta o select
            $selectGrafico = 'SELECT tbmotivo.motivo, count(tbservidor.idServidor) as jj
                                FROM tbservidor LEFT JOIN tbmotivo USING (idMotivo)
                               WHERE tbservidor.situacao = 2
                               AND (tbservidor.idPerfil = 1 OR tbservidor.idPerfil = 4)
                            GROUP BY tbmotivo.motivo
                            ORDER BY 2 DESC ';

            $servidores = $pessoal->select($selectGrafico);

            # Soma a coluna do count
            $total = array_sum(array_column($servidores, "jj"));

            $chart = new Chart("Pie", $servidores);
            $chart->set_idDiv("cargo");
            $chart->set_legend(false);
            $chart->set_tamanho($largura = 400, $altura = 300);
            $chart->show();

            $grid1->fechaColuna();

            #####

            $grid1->abreColuna(6);

            # Tabela
            $tabela = new Tabela();
            $tabela->set_titulo("por Tipo de Aposentadoria");
            $tabela->set_conteudo($servidores);
            $tabela->set_label(["Aposentadoria", "Servidores"]);
            $tabela->set_width([80, 20]);
            $tabela->set_align(["left", "center"]);
            $tabela->set_totalRegistro(false);
            $tabela->set_colunaSomatorio(1);
            $tabela->show();

            $grid1->fechaColuna();
            $grid1->fechaGrid();

            # Select
            $selectGrafico = 'SELECT YEAR(tbservidor.dtDemissao), count(tbservidor.idServidor) as jj
                                FROM tbservidor LEFT JOIN tbmotivo USING (idMotivo)
                               WHERE tbservidor.situacao = 2
                            GROUP BY YEAR(tbservidor.dtDemissao)
                            ORDER BY 1 asc ';

            $servidores = $pessoal->select($selectGrafico);

            # Soma a coluna do count
            $total = array_sum(array_column($servidores, "jj"));

            tituloTable("por Ano da Aposentadoria");

            # Gráfico
            $chart = new Chart("ColumnChart", $servidores);
            $chart->set_idDiv("perfil");
            $chart->set_legend(false);
            $chart->set_label(array("Ano", "Nº de Servidores"));
            $chart->set_tamanho($largura = 900, $altura = 500);
            $chart->show();

            # Tabela
            $tabela = new Tabela();
            $tabela->set_titulo("por Ano da Aposentadoria");
            $tabela->set_conteudo($servidores);
            $tabela->set_label(["Ano", "Servidores"]);
            $tabela->set_align(["left", "center"]);
            $tabela->set_rodape("Total de Servidores: " . $total);
            #$tabela->show();
            break;

        #######################################       
    }

    $grid->fechaColuna();
    $grid->fechaGrid();

    $page->terminaPagina();
} else {
    loadPage("../../areaServidor/sistema/login.php");
}