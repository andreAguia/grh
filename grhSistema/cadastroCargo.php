<?php

/**
 * Cadastro de Tipos de Cargos
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
    $fase = get('fase', 'listar');

    # Verifica se veio menu grh e registra o acesso no log
    $grh = get('grh', false);
    if ($grh) {
        # Grava no log a atividade
        $atividade = "Visualizou o cadastro de cargos efetivos";
        $data = date("Y-m-d H:i:s");
        $intra->registraLog($idUsuario, $data, $atividade, null, null, 7);
    }

    # pega o id (se tiver)
    $id = soNumeros(get('id'));

    # Pega o parametro de pesquisa (se tiver)
    if (is_null(post('parametro'))) {     # Se o parametro não vier por post (for nulo)
        $parametro = retiraAspas(get_session('sessionParametro')); # passa o parametro da session para a variavel parametro retirando as aspas
    } else {
        $parametro = post('parametro');                # Se vier por post, retira as aspas e passa para a variavel parametro
        set_session('sessionParametro', $parametro);    # transfere para a session para poder recuperá-lo depois
    }

    # Começa uma nova página
    $page = new Page();
    $page->iniciaPagina();

    # Cabeçalho da Página
    if ($fase <> "relatorioInativo" AND $fase <> "relatorioAtivo")
        AreaServidor::cabecalho();

    # Abre um novo objeto Modelo
    $objeto = new Modelo();

    ################################################################
    # Nome do Modelo (aparecerá nos fildset e no caption da tabela)
    $objeto->set_nome('Cadastro de Cargos');

    # Botão de voltar da lista
    $objeto->set_voltarLista('cadastroFuncao.php');

    # controle de pesquisa
    $objeto->set_parametroLabel('Pesquisar');
    $objeto->set_parametroValue($parametro);

    # select da lista
    $objeto->set_selectLista('SELECT idTipoCargo,
                                      tipo,
                                      cargo,
                                      sigla,
                                      nivel,
                                      numDecreto,
                                      idTipoCargo,
                                      idTipoCargo,
                                      idTipoCargo,
                                      idTipoCargo
                                 FROM tbtipocargo LEFT JOIN tbplano USING (idPlano)
                                WHERE cargo LIKE "%' . $parametro . '%"
                             ORDER BY 2 asc');

    # select do edita
    $objeto->set_selectEdita('SELECT tipo,
                                     cargo,                                     
                                     sigla,
                                     idPlano,
                                     nivel,
                                     vagas,
                                     obs
                                FROM tbtipocargo
                               WHERE idTipoCargo = ' . $id);

    # Habilita o modo leitura para usuario de regra 12
    if (Verifica::acesso($idUsuario, 12)) {
        $objeto->set_modoLeitura(true);
    }

    # Caminhos
    if (Verifica::acesso($idUsuario, 1)) {      // Excluir somente admin
        $objeto->set_linkExcluir('?fase=excluir');
    }
    $objeto->set_linkEditar('?fase=editar');
    $objeto->set_linkGravar('?fase=gravar');
    $objeto->set_linkListar('?fase=listar');

    # Parametros da tabela
    $objeto->set_label(["Id", "Tipo", "Cargo", "Sigla", "Nível", "Plano", "Servidores<br/>Ativos", "Ver", "Servidores<br/>Inativos", "Ver"]);
    $objeto->set_align(["center", "center", "left"]);

    $objeto->set_classe([null, null, null, null, null, null, 'Pessoal', null, 'Pessoal']);
    $objeto->set_metodo([null, null, null, null, null, null, 'get_numServidoresAtivosTipoCargo', null, 'get_numServidoresInativosTipoCargo']);

    $objeto->set_rowspan(1);
    $objeto->set_grupoCorColuna(1);

    $objeto->set_colunaSomatorio([6, 8]);

    # Ver servidores ativos
    $servAtivos = new Link(null, "?fase=aguardeAtivos&id={$id}");
    $servAtivos->set_imagem(PASTA_FIGURAS_GERAIS . 'olho.png', 20, 20);
    $servAtivos->set_title("Exibe os servidores ativos");

    # Ver servidores inativos
    $servInativos = new Link(null, '?fase=aguardeInativos&id=' . $id);
    $servInativos->set_imagem(PASTA_FIGURAS_GERAIS . 'olho.png', 20, 20);
    $servInativos->set_title("Exibe os servidores inativos");

    # Coloca o objeto link na tabela			
    $objeto->set_link(array(null, null, null, null, null, null, null, $servAtivos, null, $servInativos));

    # Classe do banco de dados
    $objeto->set_classBd('Pessoal');

    # Nome da tabela
    $objeto->set_tabela('tbtipocargo');

    # Nome do campo id
    $objeto->set_idCampo('idTipoCargo');

    # Pega os dados da combo de Plano e Cargos
    $result1 = $pessoal->select('SELECT idPlano, 
                                      numDecreto
                                  FROM tbplano
                              ORDER BY numDecreto');

    array_push($result1, array(null, null));

    # Campos para o formulario
    $objeto->set_campos(array(
        array('linha' => 1,
            'nome' => 'tipo',
            'label' => 'Tipo:',
            'tipo' => 'combo',
            'required' => true,
            'autofocus' => true,
            'array' => array(null, "Adm/Tec", "Professor"),
            'col' => 2,
            'size' => 50),
        array('linha' => 1,
            'nome' => 'cargo',
            'label' => 'Cargo:',
            'tipo' => 'texto',
            'required' => true,
            'col' => 4,
            'size' => 50),
        array('linha' => 1,
            'nome' => 'sigla',
            'label' => 'Sigla:',
            'tipo' => 'texto',
            'col' => 2,
            'size' => 50),
        array('linha' => 2,
            'nome' => 'nivel',
            'label' => 'Nível do Cargo:',
            'tipo' => 'combo',
            'required' => true,
            'array' => array(null, "Doutorado", "Superior", "Médio", "Fundamental", "Elementar"),
            'col' => 2,
            'size' => 30),
        array('linha' => 2,
            'col' => 4,
            'nome' => 'idPlano',
            'label' => 'Plano de Cargos:',
            'tipo' => 'combo',
            'required' => true,
            'array' => $result1,
            'size' => 30),
        array('linha' => 2,
            'col' => 2,
            'nome' => 'vagas',
            'label' => 'Vagas Publicadas:',
            'tipo' => 'numero',
            'size' => 10),
        array('linha' => 2,
            'nome' => 'obs',
            'label' => 'Observação:',
            'tipo' => 'textarea',
            'col' => 12,
            'size' => array(80, 5))));

    # idUsuário para o Log
    $objeto->set_idUsuario($idUsuario);

    # Gráfico
    $imagem = new Imagem(PASTA_FIGURAS . 'pie.png', null, 15, 15);
    $botaoGra = new Button();
    $botaoGra->set_title("Exibe gráfico da quantidade de servidores");
    #$botaoGra->set_onClick("abreFechaDivId('divGrafico');");
    $botaoGra->set_url("?fase=grafico");
    $botaoGra->set_imagem($imagem);

    $objeto->set_botaoListarExtra([$botaoGra]);

    # Rotina extra editar
    $objeto->set_rotinaExtraEditar("callout");
    $objeto->set_rotinaExtraEditarParametro("O campo vagas publicadas se refere as vagas "
            . "que foram publicadas no plano de cargos, entretanto este campo nada significa"
            . " na prática, pois o número de vagas que é considerado como válido para este cargo é o que foi"
            . " publicado nos editais dos concursos. Essa informação está no cadastro de concurso. ");

    ################################################################

    switch ($fase) {
        case "" :
        case "listar" :
            $objeto->listar();
            break;

        case "excluir" :
            # Verifica se tem servidores nesse cargo            
            if ($pessoal->get_numServidoresAtivosTipoCargo($id) > 0 OR $pessoal->get_numServidoresInativosTipoCargo($id) > 0) {
                alert("Não é possível excluir um cargo com servidores cadastrados !!");
                back(1);
            } else {
                # Se não tiver exclui o cargo
                $objeto->excluir($id);
            }
            break;
        case "editar" :
        case "gravar" :
            $objeto->$fase($id);
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
            set_session('origem', 'cadastroCargo.php?fase=exibeServidoresAtivos&id=' . $id);

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
            $botaoRel->set_url("?fase=relatorioAtivo&id=$id");
            $botaoRel->set_imagem($imagem2);
            $menu->add_link($botaoRel, "right");

            $menu->show();

            # Pega o nome do tipo de cargo
            $nomeTipo = $pessoal->get_nomeTipoCargo($id);

            # Lista de Servidores Ativos
            $lista = new ListaServidores('Servidores Ativos - Cargo: ' . $nomeTipo);
            $lista->set_situacao(1);
            $lista->set_tipoCargo($id);
            $lista->showTabela();

            $grid->fechaColuna();
            $grid->fechaGrid();
            break;

        ################################################################

        case "relatorioAtivo" :
            # Pega o nome do tipo de cargo
            $nomeTipo = $pessoal->get_nomeTipoCargo($id);

            # Lista de Servidores Ativos
            $lista = new ListaServidores("Cadastro de Servidores Ativos por Cargo");
            $lista->set_situacao(1);
            $lista->set_tipoCargo($id);
            $lista->showRelatorio();
            break;

        ################################################################

        case "exibeServidoresInativos" :
            # Limita o tamanho da tela
            $grid = new Grid();
            $grid->abreColuna(12);

            # Informa a origem
            set_session('origem', 'cadastroCargo.php?fase=exibeServidoresInativos&id=' . $id);

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
            $botaoRel->set_url("?fase=relatorioInativo&id=$id");
            $botaoRel->set_imagem($imagem2);
            $menu->add_link($botaoRel, "right");

            $menu->show();

            # Pega o nome do tipo de cargo
            $nomeTipo = $pessoal->get_nomeTipoCargo($id);

            # Lista de Servidores Inativos
            $lista = new ListaServidores('Servidores Inativos - Cargo: ' . $nomeTipo);
            $lista->set_situacao(1);
            $lista->set_situacaoSinal("<>");
            $lista->set_tipoCargo($id);
            $lista->showTabela();

            $grid->fechaColuna();
            $grid->fechaGrid();
            break;

        ################################################################

        case "relatorioInativo" :
            # Lista de Servidores Inativos
            $lista = new ListaServidores("Cadastro de Servidores Inativos por Cargo");
            $lista->set_situacao(1);
            $lista->set_situacaoSinal("<>");
            $lista->set_tipoCargo($id);
            $lista->showRelatorio();
            break;

        ################################################################

        case "grafico" :
            # Gráfico Estatístico
            $pessoal = new Pessoal();

            # Pega os dados
            $selectGrafico = 'SELECT tbtipocargo.cargo, count(tbservidor.matricula) 
                                FROM tbservidor JOIN tbcargo USING (idCargo)
                                                JOIN tbtipocargo USING (idTipoCargo)
                               WHERE tbservidor.situacao = 1
                            GROUP BY tbtipocargo.cargo';

            $servidores = $pessoal->select($selectGrafico);

            $grid2 = new Grid();
            $grid2->abreColuna(12);

            botaoVoltar("?");
            titulo('Servidores por Cargo');

            $grid3 = new Grid();
            $grid3->abreColuna(4);
            br();

            # Tabela
            $tabela = new Tabela();
            $tabela->set_conteudo($servidores);
            $tabela->set_label(array("Cargo", "Servidores"));
            $tabela->set_width(array(80, 20));
            $tabela->set_align(array("left", "center"));
            $tabela->set_colunaSomatorio(1);
            $tabela->set_totalRegistro(false);
            $tabela->show();

            $grid3->fechaColuna();
            $grid3->abreColuna(8);

            $chart = new Chart("Pie", $servidores);
            $chart->show();

            $grid3->fechaColuna();
            $grid3->fechaGrid();

            $grid2->fechaColuna();
            $grid2->fechaGrid();
            break;

        ################################################################
    }

    $page->terminaPagina();
} else {
    loadPage("../../areaServidor/sistema/login.php");
}