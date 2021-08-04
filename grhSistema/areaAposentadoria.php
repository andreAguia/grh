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
$acesso = Verifica::acesso($idUsuario, 2);

if ($acesso) {

    # Conecta ao Banco de Dados
    $pessoal = new Pessoal();
    $intra = new Intra();
    $aposentadoria = new Aposentadoria();

    # Verifica a fase do programa
    $fase = get('fase');

    # pega o id (se tiver)
    $id = soNumeros(get('id'));

    # Verifica se veio menu grh e registra o acesso no log
    $grh = get('grh', false);
    if ($grh) {
        # Grava no log a atividade
        $atividade = "Visualizou a área de aposentadoria";
        $data = date("Y-m-d H:i:s");
        $intra->registraLog($idUsuario, $data, $atividade, null, null, 7);
    }

    # Começa uma nova página
    $page = new Page();
    $page->iniciaPagina();

    # Cabeçalho da Página
    AreaServidor::cabecalho();

    if ($fase <> "configuracaoIntegral"
            AND $fase <> "validaConfiguracaoIntegral"
            AND $fase <> "configuracaoProporcional"
            AND $fase <> "validaConfiguracaoProporcional"
            AND $fase <> "configuracaoCompulsoria"
            AND $fase <> "validaConfiguracaoCompulsoria"
    ) {

        # Pega os parâmetros
        $parametroAno = post('parametroAno', get_session('parametroAno', $aposentadoria->get_ultimoAnoAposentadoria()));
        $parametroMotivo = post('parametroMotivo', get_session('parametroMotivo', 3));
        $parametroNome = post('parametroNome', get_session('parametroNome'));
        $parametroSexo = post('parametroSexo', get_session('parametroSexo', "Feminino"));
        $parametroLotacao = post('parametroLotacao', get_session('parametroLotacao'));
        $parametroCargo = post('parametroCargo', get_session('parametroCargo'));

        # Joga os parâmetros par as sessions
        set_session('parametroAno', $parametroAno);
        set_session('parametroMotivo', $parametroMotivo);
        set_session('parametroNome', $parametroNome);
        set_session('parametroSexo', $parametroSexo);
        set_session('parametroLotacao', $parametroLotacao);
        set_session('parametroCargo', $parametroCargo);
    }

    $grid = new Grid();
    $grid->abreColuna(12);

    # Cria um menu
    $menu = new MenuBar();

    # Voltar
    $botaoVoltar = new Link("Voltar", "grh.php");
    $botaoVoltar->set_class('button');
    $botaoVoltar->set_title('Voltar a página anterior');
    $botaoVoltar->set_accessKey('V');
    $menu->add_link($botaoVoltar, "left");

    if (($fase == "previsaoM") OR ($fase == "previsaoM1")) {

        $imagem2 = new Imagem(PASTA_FIGURAS . 'print.png', null, 15, 15);
        $botaoRel = new Button();
        $botaoRel->set_title("Relatório");
        $botaoRel->set_url("../grhRelatorios/aposentados.previsao.php");
        $botaoRel->set_target("_blank");
        $botaoRel->set_imagem($imagem2);
        $menu->add_link($botaoRel, "right");

        set_session('parametroSexo', "Masculino");
    } elseif (($fase == "previsaoF") OR ($fase == "previsaoF1")) {

        $imagem2 = new Imagem(PASTA_FIGURAS . 'print.png', null, 15, 15);
        $botaoRel = new Button();
        $botaoRel->set_title("Relatório");
        $botaoRel->set_url("../grhRelatorios/aposentados.previsao.php");
        $botaoRel->set_target("_blank");
        $botaoRel->set_imagem($imagem2);
        $menu->add_link($botaoRel, "right");

        set_session('parametroSexo', "Feminino");
    } elseif (($fase == "") OR ($fase == "porAno")) {

        $imagem2 = new Imagem(PASTA_FIGURAS . 'print.png', null, 15, 15);
        $botaoRel = new Button();
        $botaoRel->set_title("Relatório de Aposentados por Ano de Saída");
        $botaoRel->set_url("../grhRelatorios/aposentados.porAno.php");
        $botaoRel->set_target("_blank");
        $botaoRel->set_imagem($imagem2);
        $menu->add_link($botaoRel, "right");
    } elseif ($fase == "motivo") {

        $imagem2 = new Imagem(PASTA_FIGURAS . 'print.png', null, 15, 15);
        $botaoRel = new Button();
        $botaoRel->set_title("Relatório de Aposentados por Tipo");
        $botaoRel->set_url("../grhRelatorios/aposentados.porTipo.php");
        $botaoRel->set_target("_blank");
        $botaoRel->set_imagem($imagem2);
        $menu->add_link($botaoRel, "right");
    } elseif ($fase == "porIdadeMasculino") {

        $imagem2 = new Imagem(PASTA_FIGURAS . 'print.png', null, 15, 15);
        $botaoRel = new Button();
        $botaoRel->set_title("Relatório");
        $botaoRel->set_url("../grhRelatorios/estatutario.masculino.acima60.php");
        $botaoRel->set_target("_blank");
        $botaoRel->set_imagem($imagem2);
        $menu->add_link($botaoRel, "right");
    } elseif ($fase == "porIdadeFeminino") {

        $imagem2 = new Imagem(PASTA_FIGURAS . 'print.png', null, 15, 15);
        $botaoRel = new Button();
        $botaoRel->set_title("Relatório");
        $botaoRel->set_url("../grhRelatorios/estatutario.feminino.acima55.php");
        $botaoRel->set_target("_blank");
        $botaoRel->set_imagem($imagem2);
        $menu->add_link($botaoRel, "right");
    }

    $menu->show();

    # Título
    titulo("Área de Aposentadoria");
    br();

    switch ($fase) {

####################################################################################################################
        # Aposentados por ano
        case "" :
        case "porAno" :

            $grid2 = new Grid();
            $grid2->abreColuna(12, 3);

            $painel = new Callout();
            $painel->abre();

            $aposentadoria->exibeMenu(1);

            $painel->fecha();

            $grid2->fechaColuna();
            $grid2->abreColuna(12, 9);

            # Formulário de Pesquisa
            $form = new Form('?fase=porAno');

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
            $controle->set_col(2);
            $form->add_item($controle);

            $form->show();

            # Exibe a lista
            $aposentadoria->exibeAposentadosPorAno($parametroAno);

            $grid2->fechaColuna();
            $grid2->fechaGrid();
            break;

####################################################################################################################
        # Aposentadoria por Motivo / Tipo
        case "motivo" :

            $grid2 = new Grid();
            $grid2->abreColuna(12, 3);

            $painel = new Callout();
            $painel->abre();

            $aposentadoria->exibeMenu(2);

            $painel->fecha();

            $grid2->fechaColuna();
            $grid2->abreColuna(12, 9);

            # Formulário de Pesquisa
            $form = new Form('?fase=motivo');

            # Cria um array com os tipo possíveis
            $selectMotivo = "SELECT DISTINCT idMotivo,
                                    tbmotivo.motivo
                               FROM tbmotivo JOIN tbservidor ON (tbservidor.motivo = tbmotivo.idMotivo)
                              WHERE situacao = 2
                                AND (tbservidor.idPerfil = 1 OR tbservidor.idPerfil = 4)
                           ORDER BY 2";

            $motivosPossiveis = $pessoal->select($selectMotivo);

            $controle = new Input('parametroMotivo', 'combo', 'Motivo:', 1);
            $controle->set_size(8);
            $controle->set_title('Filtra por Motivo');
            $controle->set_array($motivosPossiveis);
            $controle->set_valor(date("Y"));
            $controle->set_valor($parametroMotivo);
            $controle->set_onChange('formPadrao.submit();');
            $controle->set_autofocus(true);
            $controle->set_linha(1);
            $controle->set_col(6);
            $form->add_item($controle);

            $form->show();

            # Exibe a lista
            $aposentadoria->exibeAposentadosPorTipo($parametroMotivo);

            $grid2->fechaColuna();
            $grid2->fechaGrid();
            break;

####################################################################################################################

        case "regras" :

            $grid2 = new Grid();
            $grid2->abreColuna(12, 3);

            $painel = new Callout();
            $painel->abre();

            $aposentadoria->exibeMenu(15);

            $painel->fecha();

            $grid2->fechaColuna();
            $grid2->abreColuna(12, 9);

            $aposentadoria->exibeRegras();

            $grid2->fechaColuna();
            $grid2->fechaGrid();
            break;

################################################################

        case "previsaoM" :

            $grid2 = new Grid();
            $grid2->abreColuna(12, 3);

            $painel = new Callout();
            $painel->abre();

            $aposentadoria->exibeMenu(6);

            $painel->fecha();

            $grid2->fechaColuna();
            $grid2->abreColuna(12, 9);

            br(5);
            aguarde("Calculando ...");
            br();

            $grid2->fechaColuna();
            $grid2->fechaGrid();

            loadPage('?fase=previsaoM1');
            break;

################################################################
        # Listagem de servidores ativos com previsão para posentadoria
        case "previsaoM1" :

            $grid2 = new Grid();
            $grid2->abreColuna(12, 3);

            $painel = new Callout();
            $painel->abre();

            $aposentadoria->exibeMenu(6);

            $painel->fecha();

            $grid2->fechaColuna();
            $grid2->abreColuna(12, 9);

            # Formulário de Pesquisa
            $form = new Form('?fase=previsaoM');

            # Nome
            $controle = new Input('parametroNome', 'texto', 'Nome do Servidor', 1);
            $controle->set_size(100);
            $controle->set_title('Filtra por Nome');
            $controle->set_valor($parametroNome);
            $controle->set_autofocus(true);
            $controle->set_onChange('formPadrao.submit();');
            $controle->set_linha(1);
            $controle->set_col(4);
            $form->add_item($controle);

            # Lotação
            $result = $pessoal->select('(SELECT idlotacao, concat(IFnull(tblotacao.DIR,"")," - ",IFnull(tblotacao.GER,"")," - ",IFnull(tblotacao.nome,"")) lotacao
                                              FROM tblotacao
                                             WHERE ativo) UNION (SELECT distinct DIR, DIR
                                              FROM tblotacao
                                             WHERE ativo)
                                          ORDER BY 2');
            array_unshift($result, array("*", 'Todas'));

            $controle = new Input('parametroLotacao', 'combo', 'Lotação:', 1);
            $controle->set_size(30);
            $controle->set_title('Filtra por Lotação');
            $controle->set_array($result);
            $controle->set_valor($parametroLotacao);
            $controle->set_onChange('formPadrao.submit();');
            $controle->set_linha(1);
            $controle->set_col(8);
            $form->add_item($controle);

            $form->show();

            # Exibe a lista
            $aposentadoria->exibeAtivosPrevisao("Masculino", $parametroNome, $parametroLotacao);

            $grid2->fechaColuna();
            $grid2->fechaGrid();
            break;

################################################################

        case "previsaoF" :

            $grid2 = new Grid();
            $grid2->abreColuna(12, 3);

            $painel = new Callout();
            $painel->abre();

            $aposentadoria->exibeMenu(7);

            $painel->fecha();

            $grid2->fechaColuna();
            $grid2->abreColuna(12, 9);

            br(5);
            aguarde("Calculando ...");
            br();

            $grid2->fechaColuna();
            $grid2->fechaGrid();

            loadPage('?fase=previsaoF1');
            break;

################################################################
        # Listagem de servidores ativos com previsão para posentadoria
        case "previsaoF1" :

            $grid2 = new Grid();
            $grid2->abreColuna(12, 3);

            $painel = new Callout();
            $painel->abre();

            $aposentadoria->exibeMenu(7);

            $painel->fecha();

            $grid2->fechaColuna();
            $grid2->abreColuna(12, 9);

            # Formulário de Pesquisa
            $form = new Form('?fase=previsaoF');

            # Nome
            $controle = new Input('parametroNome', 'texto', 'Nome do Servidor', 1);
            $controle->set_size(100);
            $controle->set_title('Filtra por Nome');
            $controle->set_valor($parametroNome);
            $controle->set_autofocus(true);
            $controle->set_onChange('formPadrao.submit();');
            $controle->set_linha(1);
            $controle->set_col(4);
            $form->add_item($controle);

            # Lotação
            $result = $pessoal->select('(SELECT idlotacao, concat(IFnull(tblotacao.DIR,"")," - ",IFnull(tblotacao.GER,"")," - ",IFnull(tblotacao.nome,"")) lotacao
                                              FROM tblotacao
                                             WHERE ativo) UNION (SELECT distinct DIR, DIR
                                              FROM tblotacao
                                             WHERE ativo)
                                          ORDER BY 2');
            array_unshift($result, array("*", 'Todas'));

            $controle = new Input('parametroLotacao', 'combo', 'Lotação:', 1);
            $controle->set_size(30);
            $controle->set_title('Filtra por Lotação');
            $controle->set_array($result);
            $controle->set_valor($parametroLotacao);
            $controle->set_onChange('formPadrao.submit();');
            $controle->set_linha(1);
            $controle->set_col(8);
            $form->add_item($controle);

            $form->show();

            # Exibe a lista
            $aposentadoria->exibeAtivosPrevisao("Feminino", $parametroNome, $parametroLotacao);

            $grid2->fechaColuna();
            $grid2->fechaGrid();
            break;

####################################################################################################################
        # Estatística
        case "anoEstatistica" :

            $grid = new Grid();
            $grid->abreColuna(12, 3);

            $painel = new Callout();
            $painel->abre();

            $aposentadoria->exibeMenu(3);

            $painel->fecha();

            $grid->fechaColuna();

            #################################################################

            $grid->abreColuna(12, 9);

            $grid = new Grid();
            $grid->abreColuna(6);

            # Monta o select
            $selectGrafico = 'SELECT tbmotivo.motivo, count(tbservidor.idServidor) as jj
                                FROM tbservidor LEFT JOIN tbmotivo on (tbservidor.motivo = tbmotivo.idMotivo)
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

            $grid->fechaColuna();

            #################################################################

            $grid->abreColuna(6);

            # Tabela
            $tabela = new Tabela();
            $tabela->set_titulo("por Tipo de Aposentadoria");
            $tabela->set_conteudo($servidores);
            $tabela->set_label(array("Aposentadoria", "Servidores"));
            $tabela->set_width(array(80, 20));
            $tabela->set_align(array("left", "center"));
            $tabela->set_rodape("Total de Servidores: " . $total);
            $tabela->show();

            $grid->fechaColuna();
            $grid->fechaGrid();

            ###            
            # Select
            $selectGrafico = 'SELECT YEAR(tbservidor.dtDemissao), count(tbservidor.idServidor) as jj
                                FROM tbservidor LEFT JOIN tbmotivo on (tbservidor.motivo = tbmotivo.idMotivo)
                               WHERE tbservidor.situacao = 2
                            GROUP BY YEAR(tbservidor.dtDemissao)
                            ORDER BY 1 asc ';

            $servidores = $pessoal->select($selectGrafico);

            # Soma a coluna do count
            $total = array_sum(array_column($servidores, "jj"));

            hr();

            tituloTable("por Ano da Aposentadoria");

            # Tabela
            $tabela = new Tabela();
            $tabela->set_titulo("por Ano da Aposentadoria");
            $tabela->set_conteudo($servidores);
            $tabela->set_label(array("Ano", "Servidores"));
            $tabela->set_width(array(80, 20));
            $tabela->set_align(array("left", "center"));
            $tabela->set_rodape("Total de Servidores: " . $total);
            #$tabela->show();
            # Gráfico
            $chart = new Chart("ColumnChart", $servidores);
            $chart->set_idDiv("perfil");
            $chart->set_legend(false);
            $chart->set_label(array("Ano", "Nº de Servidores"));
            $chart->set_tamanho($largura = 900, $altura = 500);
            $chart->show();

            $grid->fechaColuna();
            $grid->fechaGrid();
            break;

####################################################################################################################

        case "editarAno" :
            br(8);
            aguarde();

            # Informa o $id Servidor
            set_session('idServidorPesquisado', $id);

            # Informa a origem
            set_session('origem', 'areaAposentadoria.php?');

            # Carrega a página específica
            loadPage('servidorMenu.php');
            break;

        ################################################################

        case "editarMotivo" :
            br(8);
            aguarde();

            # Informa o $id Servidor
            set_session('idServidorPesquisado', $id);

            # Informa a origem
            set_session('origem', 'areaAposentadoria.php?fase=motivo');

            # Carrega a página específica
            loadPage('servidorMenu.php');
            break;

        ################################################################

        case "editarPrevisaoF" :
            br(8);
            aguarde();

            # Informa o $id Servidor
            set_session('idServidorPesquisado', $id);

            # Informa a origem
            set_session('origem', 'areaAposentadoria.php?fase=previsaoF');

            # Carrega a página específica
            loadPage('servidorMenu.php');
            break;

        ################################################################

        case "editarPrevisaoM" :
            br(8);
            aguarde();

            # Informa o $id Servidor
            set_session('idServidorPesquisado', $id);

            # Informa a origem
            set_session('origem', 'areaAposentadoria.php?fase=previsaoM');

            # Carrega a página específica
            loadPage('servidorMenu.php');
            break;

        ################################################################

        case "somatorio" :
            $grid2 = new Grid();
            $grid2->abreColuna(12, 3);

            $painel = new Callout();
            $painel->abre();

            $aposentadoria->exibeMenu(13);

            $painel->fecha();

            $grid2->fechaColuna();
            $grid2->abreColuna(12, 9);

            br(5);
            aguarde("Calculando ...");
            br();

            $grid2->fechaColuna();
            $grid2->fechaGrid();

            loadPage('?fase=previsaoSomatorio');
            break;

        ################################################################

        case "previsaoSomatorio" :

            $grid2 = new Grid();
            $grid2->abreColuna(12, 3);

            $painel = new Callout();
            $painel->abre();

            $aposentadoria->exibeMenu(13);

            $painel->fecha();

            $grid2->fechaColuna();
            $grid2->abreColuna(12, 9);

            $painel = new Callout();
            $painel->abre();

            $aposentadoria->exibeSomatorio();

            $painel->fecha();

            $grid2->fechaColuna();
            $grid2->fechaGrid();
            break;

        ################################################################

        case "compulsoria" :

            $grid2 = new Grid();
            $grid2->abreColuna(12, 3);

            $painel = new Callout();
            $painel->abre();

            $aposentadoria->exibeMenu(8);

            $painel->fecha();

            $grid2->fechaColuna();
            $grid2->abreColuna(12, 9);

            # Formulário de Pesquisa
            $form = new Form('?fase=compulsoria');

            # Cria um array com os anos possíveis
            $anos = arrayPreenche(date("Y"), date("Y") + 20);
            if ($parametroAno < date("Y")) {
                $parametroAno = date("Y");
            }

            $controle = new Input('parametroAno', 'combo');
            $controle->set_size(6);
            $controle->set_title('Filtra por Ano exercício');
            $controle->set_array($anos);
            $controle->set_valor($parametroAno);
            $controle->set_autofocus(true);
            $controle->set_onChange('formPadrao.submit();');
            $controle->set_linha(1);
            $controle->set_col(2);
            $form->add_item($controle);

            $form->show();

            # Exibe a lista

            $select = 'SELECT month(dtNasc),  
                          tbservidor.idServidor,
                          dtNasc,
                          TIMESTAMPDIFF(YEAR,tbpessoa.dtNasc,CURDATE()),
                          ADDDATE(dtNasc, INTERVAL 75 YEAR),
                          tbservidor.idServidor
                    FROM tbservidor LEFT JOIN tbpessoa USING (idPessoa)
                    WHERE tbservidor.situacao = 1
                    AND idPerfil = 1
                    AND (' . $parametroAno . ' - YEAR(tbpessoa.dtNasc) = 75)                    
                    ORDER BY dtNasc';

            $result = $pessoal->select($select);
            $count = $pessoal->count($select);
            $titulo = 'Servidor(es) estatutário(s) que faz(em) 75 anos em ' . $parametroAno;

            # Exibe a tabela
            $tabela = new Tabela();
            $tabela->set_conteudo($result);
            $tabela->set_label(['Mês', 'Servidor', 'Nascimento', 'Idade', 'Fará 75', 'Lotação']);
            $tabela->set_align(['center', 'left', 'center', 'center', 'center', 'left']);
            $tabela->set_titulo($titulo);
            $tabela->set_classe([null, "Pessoal", null, null, null, "Pessoal"]);
            $tabela->set_metodo([null, "get_nomeECargo", null, null, null, "get_lotacao"]);
            $tabela->set_funcao(["get_nomeMes", null, "date_to_php", null, "date_to_php"]);
            #$tabela->set_editar($this->linkEditar);
            $tabela->set_rowspan(0);
            $tabela->set_grupoCorColuna(0);
            $tabela->set_idCampo('idServidor');

            if ($count > 0) {
                $tabela->show();
            } else {
                br();
                tituloTable($titulo);
                $callout = new Callout();
                $callout->abre();
                p('Nenhum item encontrado !!', 'center');
                $callout->fecha();
            }

            $grid2->fechaColuna();
            $grid2->fechaGrid();
            break;

        ################################################################   

        /*
         * Integral
         */

        case "aguardaIntegral" :
            $grid2 = new Grid();
            $grid2->abreColuna(12, 4, 3);

            $painel = new Callout();
            $painel->abre();

            $aposentadoria->exibeMenu(10);

            $painel->fecha();

            $grid2->fechaColuna();
            $grid2->abreColuna(12, 8, 9);

            br(5);
            aguarde("Calculando ...");
            br();

            $grid2->fechaColuna();
            $grid2->fechaGrid();

            loadPage('?fase=listaIntegral');
            break;

        ################################################################

        case "listaIntegral" :
            # Limita o tamanho da tela
            $grid2 = new Grid();
            $grid2->abreColuna(12, 4, 3);

            $painel = new Callout();
            $painel->abre();

            $aposentadoria->exibeMenu(10);

            $painel->fecha();

            $grid2->fechaColuna();
            $grid2->abreColuna(12, 8, 9);

            $grid3 = new Grid();
            $grid3->abreColuna(6);

            # Formulário de Pesquisa
            $form = new Form('?fase=aguardaIntegral');

            /*
             *  Lotação
             */
            $result = $pessoal->select('(SELECT idlotacao, concat(IFnull(tblotacao.DIR,"")," - ",IFnull(tblotacao.GER,"")," - ",IFnull(tblotacao.nome,"")) lotacao
                                              FROM tblotacao
                                             WHERE ativo) UNION (SELECT distinct DIR, DIR
                                              FROM tblotacao
                                             WHERE ativo)
                                          ORDER BY 2');
            array_unshift($result, array("*", 'Todas'));

            $controle = new Input('parametroLotacao', 'combo', 'Lotação:', 1);
            $controle->set_autofocus(true);
            $controle->set_size(30);
            $controle->set_title('Filtra por Lotação');
            $controle->set_array($result);
            $controle->set_valor($parametroLotacao);
            $controle->set_onChange('formPadrao.submit();');
            $controle->set_linha(1);
            $controle->set_col(12);
            $form->add_item($controle);
            /*
             *  Cargos
             */
            $result1 = $pessoal->select('SELECT tbcargo.idCargo, 
                                                    concat(tbtipocargo.cargo," - ",tbarea.area," - ",tbcargo.nome) as cargo
                                              FROM tbcargo LEFT JOIN tbtipocargo USING (idTipoCargo)
                                                           LEFT JOIN tbarea USING (idArea)    
                                      ORDER BY 2');

            # cargos por nivel
            $result2 = $pessoal->select('SELECT cargo,cargo FROM tbtipocargo WHERE cargo <> "Professor Associado" AND cargo <> "Professor Titular" ORDER BY 2');

            # junta os dois
            $result = array_merge($result2, $result1);

            # acrescenta Professor
            array_unshift($result, array('Professor', 'Professores'));

            # acrescenta todos
            array_unshift($result, array('*', '-- Todos --'));

            $controle = new Input('parametroCargo', 'combo', 'Cargo - Área - Função:', 1);
            $controle->set_size(30);
            $controle->set_title('Filtra por Cargo');
            $controle->set_array($result);
            $controle->set_valor($parametroCargo);
            $controle->set_onChange('formPadrao.submit();');
            $controle->set_linha(2);
            $controle->set_col(12);
            $form->add_item($controle);

            /*
             *  Sexo
             */

            $controle = new Input('parametroSexo', 'combo', 'Sexo:', 1);
            $controle->set_size(30);
            $controle->set_title('Filtra por Sexo');
            $controle->set_array([
                ["Feminino", "Feminino"],
                ["Masculino", "Masculino"]
            ]);
            $controle->set_valor($parametroSexo);
            $controle->set_onChange('formPadrao.submit();');
            $controle->set_linha(1);
            $controle->set_col(6);
            $form->add_item($controle);

            $form->show();

            $grid3->fechaColuna();
            $grid3->abreColuna(12, 12, 6);

            $aposentadoria->exibeRegrasIntegral();

            $grid3->fechaColuna();
            $grid3->fechaGrid();

            if ($parametroLotacao == "*") {
                $parametroLotacao = null;
            }

            if ($parametroCargo == "*") {
                $parametroCargo = null;
            }

            # Lista de Servidores Ativos
            $aposentadoria->exibeServidoresAtivosPodemAposentarIntegral($parametroSexo, $parametroLotacao, $parametroCargo);

            $grid2->fechaColuna();
            $grid2->fechaGrid();
            break;

        ################################################################

        case "editaIntegral" :
            br(8);
            aguarde();

            # Informa o $id Servidor
            set_session('idServidorPesquisado', $id);

            # Informa a origem
            set_session('origem', 'areaAposentadoria.php?fase=aguardaIntegral');

            # Carrega a página específica
            loadPage('servidorMenu.php');
            break;

        ################################################################

        /*
         * Proporcional
         */

        case "aguardaProporcional" :
            $grid2 = new Grid();
            $grid2->abreColuna(12, 4, 3);

            $painel = new Callout();
            $painel->abre();

            $aposentadoria->exibeMenu(11);

            $painel->fecha();

            $grid2->fechaColuna();
            $grid2->abreColuna(12, 8, 9);

            br(5);
            aguarde("Calculando ...");
            br();

            $grid2->fechaColuna();
            $grid2->fechaGrid();

            loadPage('?fase=listaProporcional');
            break;

        ################################################################

        case "listaProporcional" :
            # Limita o tamanho da tela
            $grid2 = new Grid();
            $grid2->abreColuna(12, 4, 3);

            $painel = new Callout();
            $painel->abre();

            $aposentadoria->exibeMenu(11);

            $painel->fecha();

            $grid2->fechaColuna();
            $grid2->abreColuna(12, 8, 9);

            $grid3 = new Grid();
            $grid3->abreColuna(12, 6);

            # Formulário de Pesquisa
            $form = new Form('?fase=aguardaProporcional');

            /*
             *  Lotação
             */
            $result = $pessoal->select('(SELECT idlotacao, concat(IFnull(tblotacao.DIR,"")," - ",IFnull(tblotacao.GER,"")," - ",IFnull(tblotacao.nome,"")) lotacao
                                              FROM tblotacao
                                             WHERE ativo) UNION (SELECT distinct DIR, DIR
                                              FROM tblotacao
                                             WHERE ativo)
                                          ORDER BY 2');
            array_unshift($result, array("*", 'Todas'));

            $controle = new Input('parametroLotacao', 'combo', 'Lotação:', 1);
            $controle->set_autofocus(true);
            $controle->set_size(30);
            $controle->set_title('Filtra por Lotação');
            $controle->set_array($result);
            $controle->set_valor($parametroLotacao);
            $controle->set_onChange('formPadrao.submit();');
            $controle->set_linha(1);
            $controle->set_col(12);
            $form->add_item($controle);

            /*
             *  Cargos
             */
            $result1 = $pessoal->select('SELECT tbcargo.idCargo, 
                                                    concat(tbtipocargo.cargo," - ",tbarea.area," - ",tbcargo.nome) as cargo
                                              FROM tbcargo LEFT JOIN tbtipocargo USING (idTipoCargo)
                                                           LEFT JOIN tbarea USING (idArea)    
                                      ORDER BY 2');

            # cargos por nivel
            $result2 = $pessoal->select('SELECT cargo,cargo FROM tbtipocargo WHERE cargo <> "Professor Associado" AND cargo <> "Professor Titular" ORDER BY 2');

            # junta os dois
            $result = array_merge($result2, $result1);

            # acrescenta Professor
            array_unshift($result, array('Professor', 'Professores'));

            # acrescenta todos
            array_unshift($result, array('*', '-- Todos --'));

            $controle = new Input('parametroCargo', 'combo', 'Cargo - Área - Função:', 1);
            $controle->set_size(30);
            $controle->set_title('Filtra por Cargo');
            $controle->set_array($result);
            $controle->set_valor($parametroCargo);
            $controle->set_onChange('formPadrao.submit();');
            $controle->set_linha(1);
            $controle->set_col(12);
            $form->add_item($controle);

            /*
             *  Sexo
             */

            $controle = new Input('parametroSexo', 'combo', 'Sexo:', 1);
            $controle->set_size(30);
            $controle->set_title('Filtra por Sexo');
            $controle->set_array([
                ["Feminino", "Feminino"],
                ["Masculino", "Masculino"]
            ]);
            $controle->set_valor($parametroSexo);
            $controle->set_onChange('formPadrao.submit();');
            $controle->set_linha(1);
            $controle->set_col(6);
            $form->add_item($controle);

            $form->show();

            $grid3->fechaColuna();
            $grid3->abreColuna(12, 12, 6);

            $aposentadoria->exibeRegrasProporcional();

            $grid3->fechaColuna();
            $grid3->fechaGrid();

            if ($parametroLotacao == "*") {
                $parametroLotacao = null;
            }

            if ($parametroCargo == "*") {
                $parametroCargo = null;
            }

            # Lista de Servidores Ativos
            $aposentadoria->exibeServidoresAtivosPodemAposentarProporcional($parametroSexo, $parametroLotacao, $parametroCargo);

            $grid->fechaColuna();
            $grid->fechaGrid();
            break;

        ################################################################

        case "editaProporcional" :
            br(8);
            aguarde();

            # Informa o $id Servidor
            set_session('idServidorPesquisado', $id);

            # Informa a origem
            set_session('origem', 'areaAposentadoria.php?fase=aguardaProporcional');

            # Carrega a página específica
            loadPage('servidorMenu.php');
            break;

        ################################################################

        /*
         * Compulsória
         */

        case "aguardaCompulsoria" :
            $grid2 = new Grid();
            $grid2->abreColuna(12, 4, 3);

            $painel = new Callout();
            $painel->abre();

            $aposentadoria->exibeMenu(12);

            $painel->fecha();

            $grid2->fechaColuna();
            $grid2->abreColuna(12, 8, 9);

            br(5);
            aguarde("Calculando ...");
            br();

            $grid2->fechaColuna();
            $grid2->fechaGrid();

            loadPage('?fase=listaCompulsoria');
            break;

        ################################################################

        case "listaCompulsoria" :
            # Limita o tamanho da tela
            $grid2 = new Grid();
            $grid2->abreColuna(12, 4, 3);

            $painel = new Callout();
            $painel->abre();

            $aposentadoria->exibeMenu(12);

            $painel->fecha();

            $grid2->fechaColuna();
            $grid2->abreColuna(12, 8, 9);

            # Formulário de Pesquisa
            $form = new Form('?fase=aguardaCompulsoria');

            $grid3 = new Grid();
            $grid3->abreColuna(12, 7);

            /*
             *  Lotação
             */
            $result = $pessoal->select('(SELECT idlotacao, concat(IFnull(tblotacao.DIR,"")," - ",IFnull(tblotacao.GER,"")," - ",IFnull(tblotacao.nome,"")) lotacao
                                              FROM tblotacao
                                             WHERE ativo) UNION (SELECT distinct DIR, DIR
                                              FROM tblotacao
                                             WHERE ativo)
                                          ORDER BY 2');
            array_unshift($result, array("*", 'Todas'));

            $controle = new Input('parametroLotacao', 'combo', 'Lotação:', 1);
            $controle->set_autofocus(true);
            $controle->set_size(30);
            $controle->set_title('Filtra por Lotação');
            $controle->set_array($result);
            $controle->set_valor($parametroLotacao);
            $controle->set_onChange('formPadrao.submit();');
            $controle->set_linha(1);
            $controle->set_col(12);
            $form->add_item($controle);

            /*
             *  Cargos
             */
            $result1 = $pessoal->select('SELECT tbcargo.idCargo, 
                                                    concat(tbtipocargo.cargo," - ",tbarea.area," - ",tbcargo.nome) as cargo
                                              FROM tbcargo LEFT JOIN tbtipocargo USING (idTipoCargo)
                                                           LEFT JOIN tbarea USING (idArea)    
                                      ORDER BY 2');

            # cargos por nivel
            $result2 = $pessoal->select('SELECT cargo,cargo FROM tbtipocargo WHERE cargo <> "Professor Associado" AND cargo <> "Professor Titular" ORDER BY 2');

            # junta os dois
            $result = array_merge($result2, $result1);

            # acrescenta Professor
            array_unshift($result, array('Professor', 'Professores'));

            # acrescenta todos
            array_unshift($result, array('*', '-- Todos --'));

            $controle = new Input('parametroCargo', 'combo', 'Cargo - Área - Função:', 1);
            $controle->set_size(30);
            $controle->set_title('Filtra por Cargo');
            $controle->set_array($result);
            $controle->set_valor($parametroCargo);
            $controle->set_onChange('formPadrao.submit();');
            $controle->set_linha(1);
            $controle->set_col(12);
            $form->add_item($controle);

            /*
             *  Sexo
             */

            $controle = new Input('parametroSexo', 'combo', 'Sexo:', 1);
            $controle->set_size(30);
            $controle->set_title('Filtra por Sexo');
            $controle->set_array([
                ["Feminino", "Feminino"],
                ["Masculino", "Masculino"]
            ]);
            $controle->set_valor($parametroSexo);
            $controle->set_onChange('formPadrao.submit();');
            $controle->set_linha(1);
            $controle->set_col(6);
            $form->add_item($controle);

            $form->show();

            $grid3->fechaColuna();
            $grid3->abreColuna(12, 12, 5);

            $aposentadoria->exibeRegrasCompulsoria();

            $grid3->fechaColuna();
            $grid3->fechaGrid();

            if ($parametroLotacao == "*") {
                $parametroLotacao = null;
            }

            if ($parametroCargo == "*") {
                $parametroCargo = null;
            }

            # Lista de Servidores Ativos
            $aposentadoria->exibeServidoresAtivosPodemAposentarCompulsoria($parametroSexo, $parametroLotacao, $parametroCargo);

            $grid->fechaColuna();
            $grid->fechaGrid();
            break;

        ################################################################

        case "editaCompulsoria" :
            br(8);
            aguarde();

            # Informa o $id Servidor
            set_session('idServidorPesquisado', $id);

            # Informa a origem
            set_session('origem', 'areaAposentadoria.php?fase=aguardaCompulsoria');

            # Carrega a página específica
            loadPage('servidorMenu.php');
            break;

        ################################################################

        case "configuracaoIntegral" :
            $grid2 = new Grid();
            $grid2->abreColuna(12, 3);

            $painel = new Callout();
            $painel->abre();

            $aposentadoria->exibeMenu(16);

            $painel->fecha();

            $grid2->fechaColuna();
            $grid2->abreColuna(12, 9);

            titulotable("Configuração - Aposentadoria Integral");

            # Verifica se está bloqueado
            $bloqueado = get("b", true);

            # Pega os valores
            $diasAposentMasculino = $intra->get_variavel("aposentadoria.integral.tempo.masculino");
            $diasAposentFeminino = $intra->get_variavel("aposentadoria.integral.tempo.feminino");
            $idadeAposentMasculino = $intra->get_variavel("aposentadoria.integral.idade.masculino");
            $idadeAposentFeminino = $intra->get_variavel("aposentadoria.integral.idade.feminino");
            $tempoCargoAposentMasculino = $intra->get_variavel("aposentadoria.integral.tempo.cargo.masculino");
            $tempoCargoAposentFeminino = $intra->get_variavel("aposentadoria.integral.tempo.cargo.feminino");

            # Pega os comentarios
            $diasAposentMasculinoComentario = $intra->get_variavelComentario("aposentadoria.integral.tempo.masculino");
            $diasAposentFemininoComentario = $intra->get_variavelComentario("aposentadoria.integral.tempo.feminino");
            $idadeAposentMasculinoComentario = $intra->get_variavelComentario("aposentadoria.integral.idade.masculino");
            $idadeAposentFemininoComentario = $intra->get_variavelComentario("aposentadoria.integral.idade.feminino");
            $tempoCargoAposentMasculinoComentario = $intra->get_variavelComentario("aposentadoria.integral.tempo.cargo.masculino");
            $tempoCargoAposentFemininoComentario = $intra->get_variavelComentario("aposentadoria.integral.tempo.cargo.feminino");

            # Formulário
            if ($bloqueado) {
                $form = new Form('?fase=configuracaoIntegral&b=0', 'login');
            } else {
                br();
                callout("Atenção ! Configurar as variáveis de aposentadoria de acordo com a legislação vigente.<br/>"
                        . "Qualquer alteração destes valores irá afetar as previsões de aposentadoria do sistema.");
                $form = new Form('?fase=validaConfiguracaoIntegral', 'login');
            }

            # Idade Feminino
            $controle = new Input('idadeFeminino', 'numero', 'Idade:', 1);
            $controle->set_size(10);
            $controle->set_linha(1);
            $controle->set_col(2);
            $controle->set_valor($idadeAposentFeminino);
            $controle->set_autofocus(true);
            #$controle->set_helptext($idadeAposentFemininoComentario);
            $controle->set_title($idadeAposentFemininoComentario);
            $controle->set_fieldset("Feminino");
            if ($bloqueado) {
                $controle->set_readonly(true);
                $controle->set_disabled(true);
            }
            $form->add_item($controle);

            # dias Feminino
            $controle = new Input('diasFeminino', 'numero', 'Tempo de Serviço:', 1);
            $controle->set_size(10);
            $controle->set_linha(1);
            $controle->set_col(3);
            $controle->set_valor($diasAposentFeminino);
            #$controle->set_helptext($diasAposentFemininoComentario);
            $controle->set_title($diasAposentFemininoComentario);
            if ($bloqueado) {
                $controle->set_readonly(true);
                $controle->set_disabled(true);
            }
            $form->add_item($controle);

            # tempo Feminino
            $controle = new Input('tempoFeminino', 'numero', 'Tempo no Cargo (anos):', 1);
            $controle->set_size(10);
            $controle->set_linha(1);
            $controle->set_col(3);
            $controle->set_valor($tempoCargoAposentFeminino);
            #$controle->set_helptext($diasAposentFemininoComentario);
            $controle->set_title($tempoCargoAposentFemininoComentario);
            if ($bloqueado) {
                $controle->set_readonly(true);
                $controle->set_disabled(true);
            }
            $form->add_item($controle);

            # Idade Masculino
            $controle = new Input('idadeMasculino', 'numero', 'Idade:', 1);
            $controle->set_size(10);
            $controle->set_linha(2);
            $controle->set_col(2);
            $controle->set_valor($idadeAposentMasculino);
            $controle->set_autofocus(true);
            #$controle->set_helptext($idadeAposentMasculinoComentario);
            $controle->set_title($idadeAposentMasculinoComentario);
            $controle->set_fieldset("Masculino");
            if ($bloqueado) {
                $controle->set_readonly(true);
                $controle->set_disabled(true);
            }
            $form->add_item($controle);

            # dias Masculino
            $controle = new Input('diasMasculino', 'numero', 'Tempo de Serviço:', 1);
            $controle->set_size(10);
            $controle->set_linha(2);
            $controle->set_col(3);
            $controle->set_valor($diasAposentMasculino);
            #$controle->set_helptext($diasAposentMasculinoComentario);
            if ($bloqueado) {
                $controle->set_readonly(true);
                $controle->set_disabled(true);
            }
            $form->add_item($controle);

            # tempo Masculino
            $controle = new Input('tempoMasculino', 'numero', 'Tempo no Cargo (anos):', 1);
            $controle->set_size(10);
            $controle->set_linha(2);
            $controle->set_col(3);
            $controle->set_valor($tempoCargoAposentMasculino);
            #$controle->set_helptext($diasAposentFemininoComentario);
            $controle->set_title($tempoCargoAposentMasculino);
            if ($bloqueado) {
                $controle->set_readonly(true);
                $controle->set_disabled(true);
            }
            $form->add_item($controle);

            # submit
            $controle = new Input('submit', 'submit');
            $controle->set_fieldset("fecha");
            if ($bloqueado) {
                $controle->set_valor('Editar');
            } else {
                $controle->set_valor('Salvar');
            }
            $controle->set_linha(3);
            $form->add_item($controle);

            $form->show();

            $grid2->fechaColuna();
            $grid2->fechaGrid();
            break;

        ################################################################

        case "validaConfiguracaoIntegral" :

            # Recebe os valores digitados
            $idadeFeminino = post("idadeFeminino");
            $diasFeminino = post("diasFeminino");
            $tempoFeminino = post("tempoFeminino");

            $idadeMasculino = post("idadeMasculino");
            $diasMasculino = post("diasMasculino");
            $tempoMasculino = post("tempoMasculino");

            # Recebe os valores atuais
            $diasAposentMasculino = $intra->get_variavel("aposentadoria.integral.tempo.masculino");
            $diasAposentFeminino = $intra->get_variavel("aposentadoria.integral.tempo.feminino");
            $idadeAposentMasculino = $intra->get_variavel("aposentadoria.integral.idade.masculino");
            $idadeAposentFeminino = $intra->get_variavel("aposentadoria.integral.idade.feminino");
            $tempoCargoAposentMasculino = $intra->get_variavel("aposentadoria.integral.tempo.cargo.masculino");
            $tempoCargoAposentFeminino = $intra->get_variavel("aposentadoria.integral.tempo.cargo.feminino");

            # Inicia as variáveis do erro
            $msgErro = null;
            $erro = 0;

            # Verifica se foi preenchido
            if (empty($idadeFeminino)) {
                $msgErro .= 'O campo idade para aposentadoria do sexo feminino é obrigatório!\n';
                $erro = 1;
            }

            if (empty($diasFeminino)) {
                $msgErro .= 'O campo tempo de serviço para aposentadoria do sexo feminino é obrigatório!\n';
                $erro = 1;
            }

            if (empty($tempoFeminino)) {
                $msgErro .= 'O campo de tempo no cargo para aposentadoria do sexo feminino é obrigatório!\n';
                $erro = 1;
            }

            if (empty($idadeMasculino)) {
                $msgErro .= 'O campo idade para aposentadoria do sexo masculino é obrigatório!\n';
                $erro = 1;
            }

            if (empty($diasMasculino)) {
                $msgErro .= 'O campo de tempo de serviço para aposentadoria do sexo masculino é obrigatório!\n';
                $erro = 1;
            }

            if (empty($tempoMasculino)) {
                $msgErro .= 'O campo de tempo no cargo para aposentadoria do sexo masculino é obrigatório!\n';
                $erro = 1;
            }

            # Verifica se tem erro
            if ($erro == 0) {
                if ($diasAposentMasculino <> $diasMasculino) {
                    $intra->set_variavel("aposentadoria.integral.tempo.masculino", $diasMasculino);
                    $intra->registraLog($idUsuario, date("Y-m-d H:i:s"), "Alterou de {$diasAposentMasculino} para {$diasMasculino} os dias da aposentadoria integral dos servidores masculinos", "tbvariaveis", null, 2);
                }

                if ($diasAposentFeminino <> $diasFeminino) {
                    $intra->set_variavel("aposentadoria.integral.tempo.feminino", $diasFeminino);
                    $intra->registraLog($idUsuario, date("Y-m-d H:i:s"), "Alterou de {$diasAposentFeminino} para {$diasFeminino} os dias da aposentadoria integral dos servidores femininos", "tbvariaveis", null, 2);
                }

                if ($idadeAposentMasculino <> $idadeMasculino) {
                    $intra->set_variavel("aposentadoria.integral.idade.masculino", $idadeMasculino);
                    $intra->registraLog($idUsuario, date("Y-m-d H:i:s"), "Alterou de {$idadeAposentMasculino} para {$idadeMasculino} a idade da aposentadoria integral dos servidores masculinos", "tbvariaveis", null, 2);
                }

                if ($idadeAposentFeminino <> $idadeFeminino) {
                    $intra->set_variavel("aposentadoria.integral.idade.feminino", $idadeFeminino);
                    $intra->registraLog($idUsuario, date("Y-m-d H:i:s"), "Alterou de {$idadeAposentFeminino} para {$idadeFeminino} a idade da aposentadiria integral dos servidores femininos", "tbvariaveis", null, 2);
                }

                if ($tempoCargoAposentMasculino <> $tempoMasculino) {
                    $intra->set_variavel("aposentadoria.integral.tempo.cargo.masculino", $tempoMasculino);
                    $intra->registraLog($idUsuario, date("Y-m-d H:i:s"), "Alterou de {$tempoCargoAposentMasculino} para {$tempoMasculino} o tempo no cargo para aposentadoria integral dos servidores masculinos", "tbvariaveis", null, 2);
                }

                if ($tempoCargoAposentFeminino <> $tempoFeminino) {
                    $intra->set_variavel("aposentadoria.integral.tempo.cargo.feminino", $tempoFeminino);
                    $intra->registraLog($idUsuario, date("Y-m-d H:i:s"), "Alterou de {$tempoCargoAposentFeminino} para {$tempoFeminino} o tempo no cargo para aposentadoria integral dos servidores femininos", "tbvariaveis", null, 2);
                }

                loadPage("?fase=configuracaoIntegral");
            } else {
                alert($msgErro);
                back(1);
            }

            break;

        ################################################################

        case "configuracaoProporcional" :
            $grid2 = new Grid();
            $grid2->abreColuna(12, 3);

            $painel = new Callout();
            $painel->abre();

            $aposentadoria->exibeMenu(17);

            $painel->fecha();

            $grid2->fechaColuna();
            $grid2->abreColuna(12, 9);

            titulotable("Configuração - Aposentadoria Proporcional");

            # Verifica se está bloqueado
            $bloqueado = get("b", true);

            # Pega os valores
            $diasAposentMasculino = $intra->get_variavel("aposentadoria.proporcional.tempo.masculino");
            $diasAposentFeminino = $intra->get_variavel("aposentadoria.proporcional.tempo.feminino");
            $idadeAposentMasculino = $intra->get_variavel("aposentadoria.proporcional.idade.masculino");
            $idadeAposentFeminino = $intra->get_variavel("aposentadoria.proporcional.idade.feminino");
            $tempoCargoAposentMasculino = $intra->get_variavel("aposentadoria.proporcional.tempo.cargo.masculino");
            $tempoCargoAposentFeminino = $intra->get_variavel("aposentadoria.proporcional.tempo.cargo.feminino");

            # Pega os comentarios
            $diasAposentMasculinoComentario = $intra->get_variavelComentario("aposentadoria.proporcional.tempo.masculino");
            $diasAposentFemininoComentario = $intra->get_variavelComentario("aposentadoria.proporcional.tempo.feminino");
            $idadeAposentMasculinoComentario = $intra->get_variavelComentario("aposentadoria.proporcional.idade.masculino");
            $idadeAposentFemininoComentario = $intra->get_variavelComentario("aposentadoria.proporcional.idade.feminino");
            $tempoCargoAposentMasculinoComentario = $intra->get_variavelComentario("aposentadoria.proporcional.tempo.cargo.masculino");
            $tempoCargoAposentFemininoComentario = $intra->get_variavelComentario("aposentadoria.proporcional.tempo.cargo.feminino");

            # Formulário
            if ($bloqueado) {
                $form = new Form('?fase=configuracaoProporcional&b=0', 'login');
            } else {
                br();
                callout("Atenção ! Configurar as variáveis de aposentadoria de acordo com a legislação vigente.<br/>"
                        . "Qualquer alteração destes valores irá afetar as previsões de aposentadoria do sistema.");
                $form = new Form('?fase=validaConfiguracaoProporcional', 'login');
            }

            # Idade Feminino
            $controle = new Input('idadeFeminino', 'numero', 'Idade:', 1);
            $controle->set_size(10);
            $controle->set_linha(1);
            $controle->set_col(2);
            $controle->set_valor($idadeAposentFeminino);
            $controle->set_autofocus(true);
            #$controle->set_helptext($idadeAposentFemininoComentario);
            $controle->set_title($idadeAposentFemininoComentario);
            $controle->set_fieldset("Feminino");
            if ($bloqueado) {
                $controle->set_readonly(true);
                $controle->set_disabled(true);
            }
            $form->add_item($controle);

            # dias Feminino
            $controle = new Input('diasFeminino', 'numero', 'Tempo de Serviço:', 1);
            $controle->set_size(10);
            $controle->set_linha(1);
            $controle->set_col(3);
            $controle->set_valor($diasAposentFeminino);
            #$controle->set_helptext($diasAposentFemininoComentario);
            $controle->set_title($diasAposentFemininoComentario);
            if ($bloqueado) {
                $controle->set_readonly(true);
                $controle->set_disabled(true);
            }
            $form->add_item($controle);

            # tempo Feminino
            $controle = new Input('tempoFeminino', 'numero', 'Tempo no Cargo (anos):', 1);
            $controle->set_size(10);
            $controle->set_linha(1);
            $controle->set_col(3);
            $controle->set_valor($tempoCargoAposentFeminino);
            #$controle->set_helptext($diasAposentFemininoComentario);
            $controle->set_title($tempoCargoAposentFemininoComentario);
            if ($bloqueado) {
                $controle->set_readonly(true);
                $controle->set_disabled(true);
            }
            $form->add_item($controle);

            # Idade Masculino
            $controle = new Input('idadeMasculino', 'numero', 'Idade:', 1);
            $controle->set_size(10);
            $controle->set_linha(2);
            $controle->set_col(2);
            $controle->set_valor($idadeAposentMasculino);
            #$controle->set_helptext($idadeAposentMasculinoComentario);
            $controle->set_title($idadeAposentMasculinoComentario);
            $controle->set_fieldset("Masculino");
            if ($bloqueado) {
                $controle->set_readonly(true);
                $controle->set_disabled(true);
            }
            $form->add_item($controle);

            # dias Masculino
            $controle = new Input('diasMasculino', 'numero', 'Tempo de Serviço:', 1);
            $controle->set_size(10);
            $controle->set_linha(2);
            $controle->set_col(3);
            $controle->set_valor($diasAposentMasculino);
            #$controle->set_helptext($diasAposentMasculinoComentario);
            $controle->set_title($diasAposentMasculinoComentario);
            if ($bloqueado) {
                $controle->set_readonly(true);
                $controle->set_disabled(true);
            }
            $form->add_item($controle);

            # tempo Masculino
            $controle = new Input('tempoMasculino', 'numero', 'Tempo no Cargo (anos):', 1);
            $controle->set_size(10);
            $controle->set_linha(2);
            $controle->set_col(3);
            $controle->set_valor($tempoCargoAposentMasculino);
            #$controle->set_helptext($diasAposentFemininoComentario);
            $controle->set_title($tempoCargoAposentMasculino);
            if ($bloqueado) {
                $controle->set_readonly(true);
                $controle->set_disabled(true);
            }
            $form->add_item($controle);

            # submit
            $controle = new Input('submit', 'submit');
            $controle->set_fieldset("fecha");
            if ($bloqueado) {
                $controle->set_valor('Editar');
            } else {
                $controle->set_valor('Salvar');
            }
            $controle->set_linha(3);
            $form->add_item($controle);

            $form->show();

            $grid2->fechaColuna();
            $grid2->fechaGrid();
            break;

        ################################################################

        case "validaConfiguracaoProporcional" :

            # Recebe os valores digitados
            $idadeFeminino = post("idadeFeminino");
            $diasFeminino = post("diasFeminino");
            $tempoFeminino = post("tempoFeminino");

            $idadeMasculino = post("idadeMasculino");
            $diasMasculino = post("diasMasculino");
            $tempoMasculino = post("tempoMasculino");

            # Recebe os valores atuais
            $diasAposentMasculino = $intra->get_variavel("aposentadoria.proporcional.tempo.masculino");
            $diasAposentFeminino = $intra->get_variavel("aposentadoria.proporcional.tempo.feminino");
            $idadeAposentMasculino = $intra->get_variavel("aposentadoria.proporcional.idade.masculino");
            $idadeAposentFeminino = $intra->get_variavel("aposentadoria.proporcional.idade.feminino");
            $tempoCargoAposentMasculino = $intra->get_variavel("aposentadoria.proporcional.tempo.cargo.masculino");
            $tempoCargoAposentFeminino = $intra->get_variavel("aposentadoria.proporcional.tempo.cargo.feminino");

            # Inicia as variáveis do erro
            $msgErro = null;
            $erro = 0;

            # Verifica se foi preenchido
            if (empty($idadeFeminino)) {
                $msgErro .= 'O campo idade para aposentadoria do sexo feminino é obrigatório!\n';
                $erro = 1;
            }

            if (empty($diasFeminino)) {
                $msgErro .= 'O campo tempo de serviço para aposentadoria do sexo feminino é obrigatório!\n';
                $erro = 1;
            }

            if (empty($tempoFeminino)) {
                $msgErro .= 'O campo de tempo no cargo para aposentadoria do sexo feminino é obrigatório!\n';
                $erro = 1;
            }

            if (empty($idadeMasculino)) {
                $msgErro .= 'O campo idade para aposentadoria do sexo masculino é obrigatório!\n';
                $erro = 1;
            }

            if (empty($diasMasculino)) {
                $msgErro .= 'O campo de tempo de serviço para aposentadoria do sexo masculino é obrigatório!\n';
                $erro = 1;
            }

            if (empty($tempoMasculino)) {
                $msgErro .= 'O campo de tempo no cargo para aposentadoria do sexo masculino é obrigatório!\n';
                $erro = 1;
            }

            # Verifica se tem erro
            if ($erro == 0) {
                if ($diasAposentMasculino <> $diasMasculino) {
                    $intra->set_variavel("aposentadoria.proporcional.tempo.masculino", $diasMasculino);
                    $intra->registraLog($idUsuario, date("Y-m-d H:i:s"), "Alterou de {$diasAposentMasculino} para {$diasMasculino} os dias da aposentadoria proporcional dos servidores masculinos", "tbvariaveis", null, 2);
                }

                if ($diasAposentFeminino <> $diasFeminino) {
                    $intra->set_variavel("aposentadoria.proporcional.tempo.feminino", $diasFeminino);
                    $intra->registraLog($idUsuario, date("Y-m-d H:i:s"), "Alterou de {$diasAposentFeminino} para {$diasFeminino} os dias da aposentadoria proporcional dos servidores femininos", "tbvariaveis", null, 2);
                }

                if ($idadeAposentMasculino <> $idadeMasculino) {
                    $intra->set_variavel("aposentadoria.proporcional.idade.masculino", $idadeMasculino);
                    $intra->registraLog($idUsuario, date("Y-m-d H:i:s"), "Alterou de {$idadeAposentMasculino} para {$idadeMasculino} a idade da aposentadoria proporcional dos servidores masculinos", "tbvariaveis", null, 2);
                }

                if ($idadeAposentFeminino <> $idadeFeminino) {
                    $intra->set_variavel("aposentadoria.proporcional.idade.feminino", $idadeFeminino);
                    $intra->registraLog($idUsuario, date("Y-m-d H:i:s"), "Alterou de {$idadeAposentFeminino} para {$idadeFeminino} a idade da aposentadiria proporcional dos servidores femininos", "tbvariaveis", null, 2);
                }

                if ($tempoCargoAposentMasculino <> $tempoMasculino) {
                    $intra->set_variavel("aposentadoria.proporcional.tempo.cargo.masculino", $tempoMasculino);
                    $intra->registraLog($idUsuario, date("Y-m-d H:i:s"), "Alterou de {$tempoCargoAposentMasculino} para {$tempoMasculino} o tempo no cargo para aposentadoria proporcional dos servidores masculinos", "tbvariaveis", null, 2);
                }

                if ($tempoCargoAposentFeminino <> $tempoFeminino) {
                    $intra->set_variavel("aposentadoria.proporcional.tempo.cargo.feminino", $tempoFeminino);
                    $intra->registraLog($idUsuario, date("Y-m-d H:i:s"), "Alterou de {$tempoCargoAposentFeminino} para {$tempoFeminino} o tempo no cargo para aposentadoria proporcional dos servidores femininos", "tbvariaveis", null, 2);
                }

                loadPage("?fase=configuracaoProporcional");
            } else {
                alert($msgErro);
                back(1);
            }

            break;

        ################################################################

        case "configuracaoCompulsoria" :
            $grid2 = new Grid();
            $grid2->abreColuna(12, 3);

            $painel = new Callout();
            $painel->abre();

            $aposentadoria->exibeMenu(18);

            $painel->fecha();

            $grid2->fechaColuna();
            $grid2->abreColuna(12, 9);

            titulotable("Configuração - Aposentadoria Compulsória");

            # Verifica se está bloqueado
            $bloqueado = get("b", true);

            # Pega os valores
            $idadeAposent = $intra->get_variavel("aposentadoria.compulsoria.idade");

            # Pega os comentarios
            $idadeAposentComentario = $intra->get_variavelComentario("aposentadoria.compulsoria.idade");

            # Formuário exemplo de login
            if ($bloqueado) {
                $form = new Form('?fase=configuracaoCompulsoria&b=0', 'login');
            } else {
                br();
                callout("Atenção ! Configurar as variáveis de aposentadoria de acordo com a legislação vigente.<br/>"
                        . "Qualquer alteração destes valores irá afetar as previsões de aposentadoria do sistema.");
                $form = new Form('?fase=validaConfiguracaoCompulsoria', 'login');
            }

            # Idade Feminino
            $controle = new Input('idade', 'numero', 'Idade:', 1);
            $controle->set_size(10);
            $controle->set_linha(1);
            $controle->set_col(2);
            $controle->set_valor($idadeAposent);
            $controle->set_autofocus(true);
            #$controle->set_helptext($idadeAposentFemininoComentario);
            $controle->set_title($idadeAposentComentario);
            if ($bloqueado) {
                $controle->set_readonly(true);
                $controle->set_disabled(true);
            }
            $form->add_item($controle);

            # submit
            $controle = new Input('submit', 'submit');
            $controle->set_fieldset("fecha");
            if ($bloqueado) {
                $controle->set_valor('Editar');
            } else {
                $controle->set_valor('Salvar');
            }
            $controle->set_linha(3);
            $form->add_item($controle);

            $form->show();

            $grid2->fechaColuna();
            $grid2->fechaGrid();
            break;

        ################################################################

        case "validaConfiguracaoCompulsoria" :

            # Recebe os valores digitados
            $idade = post("idade");

            # Recebe os valores atuais
            $idadeAposent = $intra->get_variavel("aposentadoria.compulsoria.idade");

            # Inicia as variáveis do erro
            $msgErro = null;
            $erro = 0;

            # Verifica se foi preenchido
            if (empty($idade)) {
                $msgErro .= 'O campo idade para aposentadoria compulsória é obrigatório!\n';
                $erro = 1;
            }

            # Verifica se tem erro
            if ($erro == 0) {
                if ($idadeAposent <> $idade) {
                    $intra->set_variavel("aposentadoria.compulsoria.idade", $idade);
                    $intra->registraLog($idUsuario, date("Y-m-d H:i:s"), "Alterou de {$idadeAposent} para {$idade} a idade da aposentadiria compulsória dos servidores", "tbvariaveis", null, 2);
                }

                loadPage("?fase=configuracaoCompulsoria");
            } else {
                alert($msgErro);
                back(1);
            }

            break;

        ################################################################
    }
    $page->terminaPagina();
} else {
    loadPage("../../areaServidor/sistema/login.php");
}
    