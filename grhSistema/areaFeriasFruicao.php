<?php

/**
 * Área de Férias
 * 
 * Por data de fruição
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
    $intra = new Intra();
    $pessoal = new Pessoal();

    # Verifica a fase do programa
    $fase = get('fase');

    # Verifica se veio menu grh e registra o acesso no log
    $grh = get('grh', false);
    if ($grh) {
        # Grava no log a atividade
        $atividade = "Visualizou a área de férias";
        $data = date("Y-m-d H:i:s");
        $intra->registraLog($idUsuario, $data, $atividade, null, null, 7);
    }

    # pega o id (se tiver)
    $id = soNumeros(get('id'));
    set_session('areaFerias', false);

    # Pega os parâmetros
    $parametroAno = post('parametroAno', get_session('parametroAno', date("Y")));
    $parametroLotacao = post('parametroLotacao', get_session('parametroLotacao'));
    $parametroStatus = post('parametroStatus', get_session('parametroStatus'));

    # Joga os parâmetros par as sessions    
    set_session('parametroAno', $parametroAno);
    set_session('parametroLotacao', $parametroLotacao);
    set_session('parametroStatus', $parametroStatus);

    # Começa uma nova página
    $page = new Page();
    $page->iniciaPagina();

    # Cabeçalho da Página
    AreaServidor::cabecalho();

    $grid = new Grid();
    $grid->abreColuna(12);

    # Cria um menu
    $menu1 = new MenuBar();

    # Voltar
    $botaoVoltar = new Link("Voltar", "grh.php");
    $botaoVoltar->set_class('button');
    $botaoVoltar->set_title('Voltar a página anterior');
    $botaoVoltar->set_accessKey('V');
    $menu1->add_link($botaoVoltar, "left");

    # Ano Exercício
    $botaoExercicio = new Link("por Ano de Exercício", "areaFeriasExercicio.php");
    $botaoExercicio->set_class('button');
    $botaoExercicio->set_title('Férias por Ano Exercício');
    #$menu1->add_link($botaoExercicio,"right");
    # Ano por Fruíção
    $botaoFruicao = new Link("Ano de Fruição");
    $botaoFruicao->set_class('button');
    $botaoFruicao->set_title('Férias por Ano em que foi realmente fruído');
    #$menu1->add_link($botaoFruicao,"right");

    $menu1->show();

    # Título
    titulo("Área de Férias - Por Ano de Fruíção");

    ################################################################
    # Formulário de Pesquisa
    $form = new Form('areaFeriasFruicao.php');

    # Cria um array com os anos possíveis
    $anoInicial = 1999;
    $anoAtual = date('Y');
    $anos = arrayPreenche($anoAtual + 2, $anoInicial, "d");

    $controle = new Input('parametroAno', 'combo', 'Ano de Fruição:', 1);
    $controle->set_size(8);
    $controle->set_title('Filtra por Ano em que as férias foi/será fruída');
    $controle->set_array($anos);
    $controle->set_valor($parametroAno);
    $controle->set_onChange('formPadrao.submit();');
    $controle->set_linha(1);
    $controle->set_col(2);
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

    # Status    
    $controle = new Input('parametroStatus', 'combo', 'Status:', 1);
    $controle->set_size(10);
    $controle->set_title('Filtra por Status');
    $controle->set_array(array("Todos", "solicitada", "fruída"));
    $controle->set_valor($parametroStatus);
    $controle->set_onChange('formPadrao.submit();');
    $controle->set_linha(1);
    $controle->set_col(2);
    $form->add_item($controle);

    $form->show();

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

            $grid2 = new Grid();

            # Área Lateral
            $grid2->abreColuna(3);

            #######################################
            # Menu
            tituloTable("Menu");

            $menu = new Menu("menuProcedimentos");
            $menu->add_item('titulo', 'Tipo');
            $menu->add_item('link', 'por Ano de Exercício', 'areaFeriasExercicio.php');
            $menu->add_item('link', '<b>por Ano de Fruíção</b>', '#');
            #$menu->add_item('link', 'pendentes', 'areaFeriasPendentes.php');

            $menu->add_item('titulo', 'Relatórios');
            $menu->add_item('linkWindow', 'Anual Agrupado por Mês', '../grhRelatorios/ferias.fruicao.anual.porMes.php');
            $menu->add_item('linkWindow', 'Anual Agrupado por Lotação', '../grhRelatorios/ferias.fruicao.anual.porLotacao.php');
            $menu->add_item('linkWindow', 'Mensal Geral', '../grhRelatorios/ferias.fruicao.mensal.geral.php');
            $menu->add_item('linkWindow', 'Mensal Agrupado por Lotação', '../grhRelatorios/ferias.fruicao.mensal.porLotacao.php');
            $menu->show();

            #######################################
            # Resumo por Ano Exercício
            # Conecta com o banco de dados
            $servidor = new Pessoal();

            # Pega os dados
            $select = "SELECT anoExercicio,
                              count(*) as tot                          
                         FROM tbferias JOIN tbservidor ON (tbservidor.idServidor = tbferias.idServidor)
                                       JOIN tbhistlot ON (tbservidor.idServidor = tbhistlot.idServidor)
                                       JOIN tblotacao ON (tbhistlot.lotacao=tblotacao.idLotacao)
                        WHERE (YEAR(tbferias.dtInicial) = $parametroAno)
                          AND tbhistlot.data =(select max(data) from tbhistlot where tbhistlot.idServidor = tbservidor.idServidor)";

            # Lotação
            if (($parametroLotacao <> "*") AND ($parametroLotacao <> "")) {
                # Verifica se o que veio é numérico
                if (is_numeric($parametroLotacao)) {
                    $select .= ' AND (tblotacao.idlotacao = "' . $parametroLotacao . '")';
                } else { # senão é uma diretoria genérica
                    $select .= ' AND (tblotacao.DIR = "' . $parametroLotacao . '")';
                }
            }

            # Status
            if (($parametroStatus <> "Todos") AND ($parametroStatus <> "")) {
                $select .= ' AND (tbferias.status = "' . $parametroStatus . '")';
            }

            $select .= " GROUP BY anoExercicio ORDER BY anoExercicio";

            $resumo = $servidor->select($select);

            # Pega a soma dos campos
            $soma = 0;
            foreach ($resumo as $value) {
                $soma += $value['tot'];
            }

            # Monta a tabela
            $tabela = new Tabela();
            $tabela->set_conteudo($resumo);
            $tabela->set_label(array("Exercício", "Solicitações"));
            $tabela->set_totalRegistro(false);
            $tabela->set_rodape("Total de Solicitações: " . $soma);
            $tabela->set_align(array("center"));
            #$tabela->set_funcao(array("exibeDescricaoStatus"));
            $tabela->set_titulo("Ano Exercício");
            $tabela->show();

            #######################################
            # Resumo por Mês
            # Conecta com o banco de dados
            $servidor = new Pessoal();

            # Pega os dados
            $select = "SELECT month(dtInicial),
                              count(*) as tot                          
                         FROM tbferias JOIN tbservidor ON (tbservidor.idServidor = tbferias.idServidor)
                                       JOIN tbhistlot ON (tbservidor.idServidor = tbhistlot.idServidor)
                                       JOIN tblotacao ON (tbhistlot.lotacao=tblotacao.idLotacao)
                         WHERE (YEAR(tbferias.dtInicial) = $parametroAno)
                          AND tbhistlot.data =(select max(data) from tbhistlot where tbhistlot.idServidor = tbservidor.idServidor)";

            # Lotação
            if (($parametroLotacao <> "*") AND ($parametroLotacao <> "")) {
                # Verifica se o que veio é numérico
                if (is_numeric($parametroLotacao)) {
                    $select .= ' AND (tblotacao.idlotacao = "' . $parametroLotacao . '")';
                } else { # senão é uma diretoria genérica
                    $select .= ' AND (tblotacao.DIR = "' . $parametroLotacao . '")';
                }
            }

            if (($parametroStatus <> "Todos") AND ($parametroStatus <> "")) {
                $select .= ' AND (tbferias.status = "' . $parametroStatus . '")';
            }

            $select .= " GROUP BY year(dtInicial),month(dtInicial) ORDER BY year(dtInicial),month(dtInicial)";

            $resumo = $servidor->select($select);

            # Pega a soma dos campos
            $soma = 0;
            foreach ($resumo as $value) {
                $soma += $value['tot'];
            }

            # Monta a tabela
            $tabela = new Tabela();
            $tabela->set_conteudo($resumo);
            $tabela->set_label(array("Mês", "Solicitações"));
            $tabela->set_totalRegistro(false);
            $tabela->set_rodape("Total de Solicitações: " . $soma);
            $tabela->set_align(array("center"));
            $tabela->set_funcao(array("get_nomeMes"));
            $tabela->set_titulo("Mensal (Data Inicial)");
            $tabela->show();

            #######################################
            # Resumo por status
            # Conecta com o banco de dados
            $servidor = new Pessoal();

            # Pega os dados
            $select = "SELECT status,
                              count(*) as tot                          
                         FROM tbferias JOIN tbservidor ON (tbservidor.idServidor = tbferias.idServidor)
                                       JOIN tbhistlot ON (tbservidor.idServidor = tbhistlot.idServidor)
                                       JOIN tblotacao ON (tbhistlot.lotacao=tblotacao.idLotacao)
                         WHERE (YEAR(tbferias.dtInicial) = $parametroAno)
                          AND tbhistlot.data =(select max(data) from tbhistlot where tbhistlot.idServidor = tbservidor.idServidor)";

            # Lotação
            if (($parametroLotacao <> "*") AND ($parametroLotacao <> "")) {
                # Verifica se o que veio é numérico
                if (is_numeric($parametroLotacao)) {
                    $select .= ' AND (tblotacao.idlotacao = "' . $parametroLotacao . '")';
                } else { # senão é uma diretoria genérica
                    $select .= ' AND (tblotacao.DIR = "' . $parametroLotacao . '")';
                }
            }

            $select .= " GROUP BY status ORDER BY status";

            $resumo = $servidor->select($select);

            # Pega a soma dos campos
            $soma = 0;
            foreach ($resumo as $value) {
                $soma += $value['tot'];
            }

            # Monta a tabela
            $tabela = new Tabela();
            $tabela->set_conteudo($resumo);
            $tabela->set_label(array("Status", "Solicitações"));
            $tabela->set_totalRegistro(false);
            $tabela->set_rodape("Total de Solicitações: " . $soma);
            $tabela->set_align(array("center"));
            $tabela->set_funcao(array("exibeDescricaoStatus"));
            $tabela->set_titulo("Status");
            $tabela->show();

            #######################################
            # Área Principal            
            $grid2->fechaColuna();
            $grid2->abreColuna(9);

            # Conecta com o banco de dados
            $servidor = new Pessoal();

            $select = "SELECT tbservidor.idServidor,
                             tbferias.anoExercicio,
                             tbferias.dtInicial,
                             tbferias.numDias,
                             date_format(ADDDATE(tbferias.dtInicial,tbferias.numDias-1),'%d/%m/%Y') as dtf,
                             idFerias,
                             tbferias.status,
                             tbsituacao.situacao
                        FROM tbservidor LEFT JOIN tbpessoa ON (tbservidor.idPessoa = tbpessoa.idPessoa)
                                             JOIN tbhistlot ON (tbservidor.idServidor = tbhistlot.idServidor)
                                             JOIN tblotacao ON (tbhistlot.lotacao=tblotacao.idLotacao)
                                             JOIN tbferias ON (tbservidor.idServidor = tbferias.idServidor)
                                             JOIN tbsituacao ON (tbservidor.situacao = tbsituacao.idSituacao)
                       WHERE tbhistlot.data =(select max(data) from tbhistlot where tbhistlot.idServidor = tbservidor.idServidor)
                         AND YEAR(tbferias.dtInicial) = $parametroAno";

            # Lotação
            if (($parametroLotacao <> "*") AND ($parametroLotacao <> "")) {
                # Verifica se o que veio é numérico
                if (is_numeric($parametroLotacao)) {
                    $select .= ' AND (tblotacao.idlotacao = "' . $parametroLotacao . '")';
                } else { # senão é uma diretoria genérica
                    $select .= ' AND (tblotacao.DIR = "' . $parametroLotacao . '")';
                }
            }

            if (($parametroStatus <> "Todos") AND ($parametroStatus <> "")) {
                $select .= ' AND (tbferias.status = "' . $parametroStatus . '")';
            }


            $select .= " ORDER BY tbpessoa.nome, tbferias.anoExercicio, dtInicial";

            $result = $servidor->select($select);

            $tabela = new Tabela();
            $tabela->set_titulo("Ano de Fruição: " . $parametroAno . " (Data Inicial)");
            $tabela->set_label(array('Nome', 'Exercício', 'Inicio', 'Dias', 'Fim', 'Período', 'Status', 'Situação'));
            $tabela->set_align(array("left"));
            $tabela->set_funcao(array(null, null, "date_to_php", null, null, null, null));
            $tabela->set_classe(array("pessoal", null, null, null, null, "pessoal"));
            $tabela->set_metodo(array("get_nomeECargoELotacao", null, null, null, null, "get_feriasPeriodo"));
            $tabela->set_conteudo($result);
            $tabela->set_rowspan(0);
            $tabela->set_grupoCorColuna(0);

            $tabela->set_editar('?fase=editaServidorFerias&id=');
            $tabela->set_nomeColunaEditar("Acessar");
            $tabela->set_editarBotao("ver.png");
            $tabela->set_idCampo('idServidor');
            $tabela->show();

            $grid2->fechaColuna();
            $grid2->fechaGrid();
            break;

################################################################
        # Chama o menu do Servidor que se quer editar
        case "editaServidorFerias" :
            set_session('idServidorPesquisado', $id);
            set_session('areaFerias', "fruicao");
            loadPage('servidorFerias.php');
            break;

################################################################
    }
    $grid->fechaColuna();
    $grid->fechaGrid();

    $page->terminaPagina();
} else {
    loadPage("../../areaServidor/sistema/login.php");
}
