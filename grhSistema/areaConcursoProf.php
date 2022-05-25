<?php

/**
 * Área concurso Professores
 *  
 * By Alat
 */
# Reservado para o servidor logado
$idUsuario = null;

# Configuração
include ("_config.php");

# Limpa as sessões
set_session('idConcurso');
set_session('parametroCargo');

# Permissão de Acesso
$acesso = Verifica::acesso($idUsuario, [1, 2, 12]);

if ($acesso) {
    # Conecta ao Banco de Dados
    $intra = new Intra();
    $pessoal = new Pessoal();
    $concurso = new Concurso();

    # Verifica a fase do programa
    $fase = get('fase', 'listar');
    $idConcurso = get('idConcurso');
    $origem = get('origem');

    # Limpa os parâmetros quando vem das vagas
    if ($origem == "vagas") {
        set_session('parametroCentro');
    }

    # Verifica se veio menu grh e registra o acesso no log
    $grh = get('grh', false);
    if ($grh) {
        # Grava no log a atividade
        $atividade = "Visualizou a área de concurso de professores";
        $data = date("Y-m-d H:i:s");
        $intra->registraLog($idUsuario, $data, $atividade, null, null, 7);
    }

    # pega o id (se tiver)
    $id = soNumeros(get('id'));

    # Pega os parâmetros
    $parametroAno = post('parametroAno', get_session('parametroAno', "*"));
    $parametroCentro = post('parametroCentro', get_session('parametroCentro', "*"));

    # Joga os parâmetros para as sessions
    set_session('parametroAno', $parametroAno);
    set_session('parametroCentro', $parametroCentro);

    # Coloca o tipo do concurso na session
    set_session('concursoTipo', 2);

    # Começa uma nova página
    $page = new Page();
    $page->iniciaPagina();

    # Cabeçalho da Página
    if ($fase <> "relatorioInativos" AND $fase <> "relatorioAtivos" AND $fase <> "relatorio") {
        AreaServidor::cabecalho();
    }

    $grid = new Grid();
    $grid->abreColuna(12);

################################################################

    switch ($fase) {
        case "listar" :

            # Cria um menu
            $menu1 = new MenuBar();

            # Voltar
            $botaoVoltar = new Link("Voltar", "grh.php");
            $botaoVoltar->set_class('button');
            $botaoVoltar->set_title('Voltar a página anterior');
            $botaoVoltar->set_accessKey('V');
            $menu1->add_link($botaoVoltar, "left");

            # Relatórios
            $imagem = new Imagem(PASTA_FIGURAS . 'print.png', null, 15, 15);
            $botaoRel = new Button();
            $botaoRel->set_title("Relatório dessa pesquisa");
            $botaoRel->set_url("?fase=relatorio");
            $botaoRel->set_target("_blank");
            $botaoRel->set_imagem($imagem);
            $menu1->add_link($botaoRel, "right");

            # Vagas
            $botaoVoltar = new Link("Vagas", "areaVagasDocentes.php");
            $botaoVoltar->set_class('button');
            $botaoVoltar->set_title('Exibe as vagas dos concursos');
            $menu1->add_link($botaoVoltar, "right");

            # Novo Concurso
            $botaoVoltar = new Link("Novo Concurso", "cadastroConcurso.php?fase=editar");
            $botaoVoltar->set_class('button');
            $menu1->add_link($botaoVoltar, "right");

            $menu1->show();

            $form = new Form('?');

            # Ano
            $anoBase = $pessoal->select('SELECT DISTINCT anoBase, anoBase
                                            FROM tbconcurso
                                           WHERE tipo = 2
                                          ORDER BY anoBase DESC');

            array_unshift($anoBase, array("*", 'Todos'));

            $controle = new Input('parametroAno', 'combo', 'Ano:', 1);
            $controle->set_size(8);
            $controle->set_title('Filtra por Ano');
            $controle->set_array($anoBase);
            $controle->set_valor($parametroAno);
            $controle->set_onChange('formPadrao.submit();');
            $controle->set_autofocus(true);
            $controle->set_linha(1);
            $controle->set_col(2);
            $form->add_item($controle);

            # Centro
            $centros = [
                ["*", "Todos"],
                ["CCH", "CCH"],
                ["CBB", "CBB"],
                ["CCT", "CCT"],
                ["CCTA", "CCTA"],
            ];

            $controle = new Input('parametroCentro', 'combo', 'Centro:', 1);
            $controle->set_size(8);
            $controle->set_title('Filtra por Centro');
            $controle->set_array($centros);
            $controle->set_valor($parametroCentro);
            $controle->set_onChange('formPadrao.submit();');
            $controle->set_linha(1);
            $controle->set_col(3);
            $form->add_item($controle);

            $form->show();

            # Monta a tabala
            $select = "SELECT idConcurso,
                      anobase,
                      dtPublicacaoEdital,
                      regime,
                      CASE tipo
                        WHEN 1 THEN 'Adm & Tec'
                        WHEN 2 THEN 'Professor'
                        ELSE '--'
                      END,
                      orgExecutor,                      
                      tbplano.numDecreto,
                      idConcurso,
                      idConcurso,
                      idConcurso,
                      idConcurso,
                      idConcurso,
                      idConcurso,
                      idConcurso
                 FROM tbconcurso as TT LEFT JOIN tbplano USING (idPlano)
                WHERE tipo = 2 ";

            # Ano Base
            if ($parametroAno <> "*") {
                $select .= "AND anoBase = '{$parametroAno}'";
            }

            # Centro
            if ($parametroCentro <> "*") {
                $select .= "AND '{$parametroCentro}' IN (SELECT DISTINCT centro FROM tbvaga JOIN tbvagahistorico USING (idVaga) WHERE TT.idConcurso = tbvagahistorico.idConcurso)";
            }

            $select .= " ORDER BY anobase desc, dtPublicacaoEdital desc";

            $resumo = $pessoal->select($select);

            # Monta a tabela
            $tabela = new Tabela();
            $tabela->set_conteudo($resumo);
            $tabela->set_titulo("Concursos para Servidores Professores");
            $tabela->set_label(["id", "Ano Base", "Publicação <br/>do Edital", "Regime", "Tipo", "Executor", "Plano de Cargos", "Centros", "Servidores Ativos", "Ver", "Servidores Inativos", "Ver", "Total", "Acessar"]);
            $tabela->set_colspanLabel([null, null, null, null, null, null, null, null, 2, null, 2]);
            $tabela->set_align(["center"]);
            $tabela->set_width([5, 6, 8, 10, 7, 7, 17, 10, 5, 5, 5, 5, 5]);
            $tabela->set_funcao([null, null, 'date_to_php']);
            $tabela->set_classe([null, null, null, null, null, null, null, "Concurso", "Concurso", null, "Concurso", null, "Concurso"]);
            $tabela->set_excluirCondicional('cadastroConcurso.php?fase=excluir', 0, 12, "==");
            $tabela->set_metodo([null, null, null, null, null, null, null, "get_centroVagas", "get_numServidoresAtivosConcurso", null, "get_numServidoresInativosConcurso", null, "get_numServidoresConcurso"]);
            $tabela->set_rowspan(1);
            $tabela->set_grupoCorColuna(1);

            # Ver servidores ativos
            $servAtivos = new Link(null, "?fase=aguardeAtivos&id=");
            $servAtivos->set_imagem(PASTA_FIGURAS_GERAIS . 'olho.png', 20, 20);
            $servAtivos->set_title("Exibe os servidores ativos");

            # Ver servidores inativos
            $servInativos = new Link(null, '?fase=aguardeInativos&id=');
            $servInativos->set_imagem(PASTA_FIGURAS_GERAIS . 'olho.png', 20, 20);
            $servInativos->set_title("Exibe os servidores inativos");

            # Botão Editar
            $botao = new Link(null, '?fase=acessaConcurso&idConcurso=', 'Acessa a página do concurso');
            $botao->set_imagem(PASTA_FIGURAS . 'bullet_edit.png', 20, 20);

            # Coloca o objeto link na tabela			
            $tabela->set_link([null, null, null, null, null, null, null, null, null, $servAtivos, null, $servInativos, null, $botao]);

            $tabela->show();
            break;

        ################################################################
        # Chama o menu do Servidor que se quer editar
        case "acessaConcurso" :
            set_session('idConcurso', $idConcurso);
            loadPage('cadastroConcursoPublicacao.php');
            break;

        ################################################################
        # Relatório
        case "relatorio" :

            # Inicia a variável do título
            $titulo = null;

            # Monta a tabala
            $select = "SELECT anobase,
                      dtPublicacaoEdital,
                      regime,
                      CASE tipo
                        WHEN 1 THEN 'Adm & Tec'
                        WHEN 2 THEN 'Professor'
                        ELSE '--'
                      END,
                      orgExecutor,                      
                      tbplano.numDecreto,
                      idConcurso,
                      idConcurso,
                      idConcurso,
                      idConcurso
                 FROM tbconcurso as TT LEFT JOIN tbplano USING (idPlano)
                WHERE tipo = 2 ";

            # Ano Base
            if ($parametroAno <> "*") {
                $select .= "AND anoBase = '{$parametroAno}'";
                $titulo = "Ano: {$parametroAno}";
            }

            # Centro
            if ($parametroCentro <> "*") {
                $select .= "AND '{$parametroCentro}' IN (SELECT DISTINCT centro FROM tbvaga JOIN tbvagahistorico USING (idVaga) WHERE TT.idConcurso = tbvagahistorico.idConcurso)";
                $titulo .= " Centro: {$parametroCentro}";
            }

            $select .= " ORDER BY anobase desc, dtPublicacaoEdital desc";

            $resumo = $pessoal->select($select);

            # Monta a tabela
            $relatorio = new Relatorio();
            $relatorio->set_conteudo($resumo);
            $relatorio->set_titulo("Concursos para Servidores Professores");
            $relatorio->set_subtitulo($titulo);
            $relatorio->set_label(["Ano Base", "Publicação <br/>do Edital", "Regime", "Tipo", "Executor", "Plano de Cargos", "Centros", "Ativos", "Inativos", "Total"]);
            $relatorio->set_align(["center"]);
            $relatorio->set_width([10, 10, 10, 10, 10, 20, 10, 5, 5, 5]);
            $relatorio->set_funcao([null, 'date_to_php']);
            $relatorio->set_classe([null, null, null, null, null, null, "Concurso", "Concurso", "Concurso", "Concurso"]);
            $relatorio->set_metodo([null, null, null, null, null, null, "get_centroVagas", "get_numServidoresAtivosConcurso", "get_numServidoresInativosConcurso", "get_numServidoresConcurso"]);
            $relatorio->set_bordaInterna(true);
            $relatorio->show();
            break;

        ################################################################

        case "aguardeAtivos" :
            br(10);
            aguarde("Montando a Listagem");
            br();
            loadPage('?fase=exibeServidoresAtivos&id=' . $id);
            break;

        ################################################################

        case "aguardeInativos" :
            br(10);
            aguarde("Montando a Listagem");
            br();
            loadPage('?fase=exibeServidoresInativos&id=' . $id);
            break;

        ################################################################

        case "exibeServidoresAtivos" :
            # Limita o tamanho da tela
            $grid = new Grid();
            $grid->abreColuna(12);

            # Informa a origem
            set_session('origem', 'areaConcursoProf.php?fase=exibeServidoresAtivos&id=' . $id);

            # Cria um menu
            $menu = new MenuBar();

            # Botão voltar
            $btnVoltar = new Button("Voltar", "?");
            $btnVoltar->set_title('Volta para a página anterior');
            $btnVoltar->set_accessKey('V');
            $menu->add_link($btnVoltar, "left");

            # Relatório
            $imagem2 = new Imagem(PASTA_FIGURAS . 'print.png', null, 15, 15);
            $botaoRel = new Button();
            $botaoRel->set_title("Relatório dos Servidores");
            $botaoRel->set_target("_blank");
            $botaoRel->set_url("?fase=relatorioAtivos&id=$id");
            $botaoRel->set_imagem($imagem2);
            $menu->add_link($botaoRel, "right");

            $menu->show();

            # Lista de Servidores Ativos
            $lista = new ListaServidores('Servidores Ativos - Concurso: ' . $concurso->get_nomeConcurso($id));
            $lista->set_situacao(1);
            $lista->set_concurso($id);
            $lista->showTabela();

            $grid->fechaColuna();
            $grid->fechaGrid();
            break;

        ################################################################

        case "exibeServidoresInativos" :
            # Limita o tamanho da tela
            $grid = new Grid();
            $grid->abreColuna(12);

            # Informa a origem
            set_session('origem', 'areaConcursoProf.php?fase=exibeServidoresInativos&id=' . $id);

            # Cria um menu
            $menu = new MenuBar();

            # Botão voltar
            $btnVoltar = new Button("Voltar", "?");
            $btnVoltar->set_title('Volta para a página anterior');
            $btnVoltar->set_accessKey('V');
            $menu->add_link($btnVoltar, "left");

            # Relatório
            $imagem2 = new Imagem(PASTA_FIGURAS . 'print.png', null, 15, 15);
            $botaoRel = new Button();
            $botaoRel->set_title("Relatório dos Servidores");
            $botaoRel->set_target("_blank");
            $botaoRel->set_url("?fase=relatorioInativos&id=$id");
            $botaoRel->set_imagem($imagem2);
            $menu->add_link($botaoRel, "right");

            $menu->show();

            # Lista de Servidores Inativos
            $lista = new ListaServidores('Servidores Inativos - Concurso: ' . $concurso->get_nomeConcurso($id));
            $lista->set_situacao(1);
            $lista->set_situacaoSinal("<>");
            $lista->set_concurso($id);
            $lista->showTabela();

            $grid->fechaColuna();
            $grid->fechaGrid();
            break;

        ################################################################

        case "relatorioAtivos" :
            # Lista de Servidores Ativos
            $lista = new ListaServidores('Servidores Ativos - Concurso: ' . $concurso->get_nomeConcurso($id));
            $lista->set_situacao(1);
            $lista->set_concurso($id);
            $lista->showRelatorio();
            break;

        ################################################################

        case "relatorioInativos" :
            # Lista de Servidores Inativos
            $lista = new ListaServidores('Servidores Inativos - Concurso: ' . $concurso->get_nomeConcurso($id));
            $lista->set_situacao(1);
            $lista->set_situacaoSinal("<>");
            $lista->set_concurso($id);
            $lista->showRelatorio();
            break;

        ################################################################
    }

    $grid->fechaColuna();
    $grid->fechaGrid();

    $page->terminaPagina();
} else {
    loadPage("../../areaServidor/sistema/login.php");
}
