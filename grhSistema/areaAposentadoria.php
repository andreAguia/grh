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

    # Pega os parâmetros
    $parametroAno = post('parametroAno', get_session('parametroAno', $aposentadoria->get_ultimoAnoAposentadoria()));
    $parametroMotivo = post('parametroMotivo', get_session('parametroMotivo', 3));
    $parametroNome = post('parametroNome', get_session('parametroNome'));
    $parametroSexo = get('parametroSexo', get_session('parametroSexo', "Feminino"));

    # Joga os parâmetros par as sessions
    set_session('parametroAno', $parametroAno);
    set_session('parametroMotivo', $parametroMotivo);
    set_session('parametroSexo', $parametroSexo);
    set_session('parametroNome', $parametroNome);

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

            $aposentadoria->exibeMenu(8);

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

            $aposentadoria->exibeMenu(4);

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

            $aposentadoria->exibeMenu(4);

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
            $controle->set_col(12);
            $form->add_item($controle);

            $form->show();

            # Exibe a lista
            $aposentadoria->exibeAtivosPrevisao("Masculino", $parametroNome);

            $grid2->fechaColuna();
            $grid2->fechaGrid();
            break;

################################################################

        case "previsaoF" :

            $grid2 = new Grid();
            $grid2->abreColuna(12, 3);

            $painel = new Callout();
            $painel->abre();

            $aposentadoria->exibeMenu(5);

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

            $aposentadoria->exibeMenu(5);

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
            $controle->set_col(12);
            $form->add_item($controle);

            $form->show();

            # Exibe a lista
            $aposentadoria->exibeAtivosPrevisao("Feminino", $parametroNome);

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

            # Número de Servidores
            $painel = new Callout("success");
            $painel->abre();
            $numServidores = $aposentadoria->get_numServidoresAposentados();
            p($numServidores, "estatisticaNumero");
            p("Servidores Aposentados<br/>(Estatutários e Celetistas)", "estatisticaTexto");
            $painel->fecha();

            $grid->fechaColuna();

            #################################################################

            $grid->abreColuna(12, 9);
            # Abre um callout
            $panel = new Callout();
            $panel->abre();

            tituloTable("por Tipo de Aposentadoria");
            br();

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
            $chart->set_tamanho($largura = 300, $altura = 300);
            $chart->show();

            $grid->fechaColuna();

            #################################################################

            $grid->abreColuna(6);

            # Tabela
            $tabela = new Tabela();
            #$tabela->set_titulo("por Tipo de Aposentadoria");
            $tabela->set_conteudo($servidores);
            $tabela->set_label(array("Aposentadoria", "Servidores"));
            $tabela->set_width(array(80, 20));
            $tabela->set_align(array("left", "center"));
            $tabela->set_rodape("Total de Servidores: " . $total);
            $tabela->show();

            $grid->fechaColuna();
            $grid->fechaGrid();

            $panel->fecha();

            $grid->fechaColuna();
            $grid->fechaGrid();

            #################################################################
            # Abre um callout
            $panel = new Callout();
            $panel->abre();

            # Título
            tituloTable("por Ano da Aposentadoria");

            # Geral - Por Perfil
            $selectGrafico = 'SELECT YEAR(tbservidor.dtDemissao), count(tbservidor.idServidor) as jj
                                FROM tbservidor LEFT JOIN tbmotivo on (tbservidor.motivo = tbmotivo.idMotivo)
                               WHERE tbservidor.situacao = 2
                            GROUP BY YEAR(tbservidor.dtDemissao)
                            ORDER BY 1 asc ';

            $servidores = $pessoal->select($selectGrafico);

            # Soma a coluna do count
            $total = array_sum(array_column($servidores, "jj"));

            # Tabela
            $tabela = new Tabela();
            #$tabela->set_titulo("por Perfil");
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
            #$chart->set_tamanho($largura = 1000,$altura = 500);
            $chart->show();

            $panel->fecha();

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

            $aposentadoria->exibeMenu(7);

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

            $aposentadoria->exibeMenu(7);

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

            $aposentadoria->exibeMenu(6);

            $painel->fecha();

            $grid2->fechaColuna();
            $grid2->abreColuna(12, 9);

            # Formulário de Pesquisa
            $form = new Form('?fase=compulsoria');

            # Cria um array com os anos possíveis
            $anos = arrayPreenche(date("Y"), date("Y") + 20);

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
            $titulo = 'Servidor(es) estatutário(s) que faz(em) 75 anos em '.$parametroAno;

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
    }
    $page->terminaPagina();
} else {
    loadPage("../../areaServidor/sistema/login.php");
}
