<?php

/**
 * Cadastro de Lotação
 *  
 * By Alat
 */
# Reservado para o servidor logado
$idUsuario = null;

# Configuração
include("_config.php");

# Permissão de Acesso
$acesso = Verifica::acesso($idUsuario, [1, 2, 12]);

if ($acesso) {
    # Conecta ao Banco de Dados
    $intra = new Intra();
    $pessoal = new Pessoal();

    # Verifica a fase do programa
    $fase = get('fase', 'listar');
    $subFase = get('subFase', 1);

    # Verifica se veio menu grh e registra o acesso no log
    $grh = get('grh', false);
    if ($grh) {
        # Grava no log a atividade
        $atividade = "Visualizou o cadastro de lotação";
        $data = date("Y-m-d H:i:s");
        $intra->registraLog($idUsuario, $data, $atividade, null, null, 7);
    }

    # pega o id (se tiver)
    $id = soNumeros(get('id'));

    # Pega os parâmetros
    $parametroLotacao = post('parametroLotacao', get_session('parametroLotacao', 'DGA'));
    $parametroSituacao = post('parametroSituacao', get_session('parametroSituacao'));
    $parametroTipo = get('parametroTipo', get_session('parametroTipo', 1));

    # Joga os parâmetros para as sessions
    set_session('parametroSituacao', $parametroSituacao);
    set_session('parametroLotacao', $parametroLotacao);
    set_session('parametroTipo', $parametroTipo);

    # Pega o parametro de pesquisa (se tiver)
    if (is_null(post('parametro'))) {     # Se o parametro n?o vier por post (for nulo)
        $parametro = retiraAspas(get_session('sessionParametro')); # passa o parametro da session para a variavel parametro retirando as aspas
    } else {
        $parametro = post('parametro');                # Se vier por post, retira as aspas e passa para a variavel parametro
        set_session('sessionParametro', $parametro);    # transfere para a session para poder recuperá-lo depois
    }

    # Começa uma nova página
    $page = new Page();
    $page->iniciaPagina();

    # Cabeçalho da Página
    if ($fase <> "relatorio") {
        AreaServidor::cabecalho();
    }

    # Abre um novo objeto Modelo
    $objeto = new Modelo();

    ################################################################
    # Nome do Modelo (aparecerá nos fildset e no caption da tabela)
    if ($parametroTipo) {
        $complemento = " Ativas";
    } else {
        $complemento = " Inativas";
    }
    $objeto->set_nome('Lotações ' . $complemento);

    # botão de voltar da lista
    $objeto->set_voltarLista('grh.php');

    # controle de pesquisa
    $objeto->set_parametroLabel('Pesquisar');
    $objeto->set_parametroValue($parametro);

    # select da lista
    $objeto->set_selectLista('SELECT idLotacao,
                                      IF(ativo = 1,DIR,CONCAT(DIR,"<br/>","(",UADM,")")),
                                      codigo,
                                      campus,
                                      GER,
                                      idLotacao,
                                      idLotacao,
                                      idLotacao,
                                      idLotacao,
                                      idLotacao,
                                      idLotacao,
                                      if(ativo = 0,"Não","Sim"),
                                      idLotacao
                                 FROM tblotacao LEFT JOIN tbcampus USING (idCampus)
                                WHERE ativo = ' . $parametroTipo . '  
                                AND (DIR LIKE "%' . $parametro . '%"
                                   OR GER LIKE "%' . $parametro . '%"
                                   OR nome LIKE "%' . $parametro . '%"
                                   OR campus LIKE "%' . $parametro . '%"    
                                   OR idLotacao LIKE "%' . $parametro . '%") 
                             ORDER BY ativo desc, UADM asc, DIR asc, campus, GER asc, nome asc');

    # select do edita
    $objeto->set_selectEdita('SELECT codigo,
                                     UADM,
                                     DIR,
                                     idCampus,
                                     GER,
                                     nome,
                                     email,
                                     ativo,
                                     ramais,
                                     obs
                                FROM tblotacao
                               WHERE idLotacao = ' . $id);

    # Habilita o modo leitura para usuario de regra 12
    if (Verifica::acesso($idUsuario, 12)) {
        $objeto->set_modoLeitura(true);
    }

    # Caminhos
    $objeto->set_linkEditar('?fase=editar');
    #$objeto->set_linkExcluir('?fase=excluir');     // Retirado para evidar exclusão acidental
    $objeto->set_linkGravar('?fase=gravar');
    $objeto->set_linkListar('?fase=listar');

    # Parametros da tabela
    $objeto->set_label(["id", "Diretoria<br/>Centro", "Código", "Campus<br/>Universitário", "Sigla", "Nome", "Servidores Ativos", "Ver", "Servidores Inativos", "Ver", "Histórico de<br/>Servidores", "Lotação<br/>Ativa?"]);
    $objeto->set_colspanLabel([null, null, null, null, null, null, 2, null, 2]);
    #$objeto->set_width(array(5,8,8,8,8,43,5,5,5));
    $objeto->set_align(["center", "center", "center", "center", "center", "left"]);

    $objeto->set_classe([null, null, null, null, null, "Lotacao", "Pessoal", null, "Pessoal"]);
    $objeto->set_metodo([null, null, null, null, null, "exibeNomeEmail", "get_numServidoresAtivosLotacao", null, "get_numServidoresInativosLotacao"]);

    $objeto->set_rowspan(1);
    $objeto->set_grupoCorColuna(1);

    $objeto->set_colunaSomatorio([6, 8]);

    # Ver servidores ativos
    $servAtivos = new Link(null, "?fase=aguardeAtivos&id={$id}");
    $servAtivos->set_imagem(PASTA_FIGURAS_GERAIS . 'olho.png', 20, 20);
    $servAtivos->set_title("Exibe os servidores ativos");

    # Ver servidores inativos
    $servInativos = new Link(null, "?fase=aguardeInativos&id={$id}");
    $servInativos->set_imagem(PASTA_FIGURAS_GERAIS . 'olho.png', 20, 20);
    $servInativos->set_title("Exibe os servidores inativos");

    # Ver histórico de servidores
    $historicoServidores = new Link(null, "?fase=aguardeHistorico&id={$id}");
    $historicoServidores->set_imagem(PASTA_FIGURAS_GERAIS . 'olho.png', 20, 20);
    $historicoServidores->set_title("Exibe o histórico dos servidores nesta lotação");

    # Coloca o objeto link na tabela			
    $objeto->set_link([null, null, null, null, null, null, null, $servAtivos, null, $servInativos, $historicoServidores]);

    # Classe do banco de dados
    $objeto->set_classBd('Pessoal');

    # Nome da tabela
    $objeto->set_tabela('tblotacao');

    # Nome do campo id
    $objeto->set_idCampo('idLotacao');

    # Pega os dados da combo do Campus
    $result1 = $pessoal->select('SELECT idCampus,
                                        campus
                                  FROM tbcampus
                              ORDER BY campus');
    array_unshift($result1, array(null, null));

    # Campos para o formulario
    $objeto->set_campos(array(
        array('linha' => 1,
            'col' => 2,
            'nome' => 'codigo',
            'label' => 'Código:',
            'tipo' => 'texto',
            'autofocus' => true,
            'size' => 15),
        array('linha' => 1,
            'col' => 2,
            'nome' => 'UADM',
            'label' => 'Unidade Administrativa:',
            'tipo' => 'combo',
            'required' => true,
            'array' => array('UENF', 'FENORTE'),
            'size' => 15),
        array('linha' => 1,
            'col' => 3,
            'nome' => 'DIR',
            'label' => 'Sigla da Diretoria/Centro:',
            'title' => 'Sigla da Diretoria',
            'tipo' => 'texto',
            'required' => true,
            'size' => 30),
        array('linha' => 1,
            'col' => 3,
            'nome' => 'idCampus',
            'label' => 'Campus:',
            'tipo' => 'combo',
            'required' => true,
            'array' => $result1,
            'size' => 15),
        array('linha' => 1,
            'col' => 2,
            'nome' => 'GER',
            'label' => 'Sigla da Gerência/Laboratório:',
            'title' => 'Sigla da Gerência',
            'tipo' => 'texto',
            'size' => 30),
        array('linha' => 2,
            'col' => 6,
            'nome' => 'nome',
            'label' => 'Nome completo da lotação:',
            'title' => 'Nome completo da lotação sem siglas',
            'tipo' => 'texto',
            'required' => true,
            'size' => 100),
        array('linha' => 2,
            'col' => 4,
            'nome' => 'email',
            'label' => 'E-mail:',
            'title' => 'E-mail oficiaL da lotação',
            'tipo' => 'texto',
            'size' => 100),
        array('linha' => 2,
            'col' => 2,
            'nome' => 'ativo',
            'required' => true,
            'label' => 'Lotação Ativa?',
            'title' => 'Se a lotação está ativa e permite movimentações',
            'tipo' => 'combo',
            'array' => array(array(1, 'Sim'), array(0, 'Não')),
            'padrao' => 'Sim',
            'size' => 5),
        array('linha' => 5,
            'nome' => 'ramais',
            'label' => 'Ramais:',
            'tipo' => 'textarea',
            'size' => array(80, 6)),
        array('linha' => 6,
            'nome' => 'obs',
            'label' => 'Observação:',
            'tipo' => 'textarea',
            'size' => array(80, 6))));

    # idUsuário para o Log
    $objeto->set_idUsuario($idUsuario);

    # Grafico
    $imagem1 = new Imagem(PASTA_FIGURAS . 'pie.png', null, 15, 15);
    $botaoGra = new Button();
    $botaoGra->set_title("Exibe gráfico da quantidade de servidores");
    $botaoGra->set_url("?fase=grafico");
    $botaoGra->set_imagem($imagem1);

    # Relatório 
    $imagem = new Imagem(PASTA_FIGURAS . 'print.png', null, 15, 15);
    $botaoRel = new Button();
    $botaoRel->set_imagem($imagem);
    $botaoRel->set_title("Imprimir");
    $botaoRel->set_target("_blank");
    $botaoRel->set_url('../grhRelatorios/lotacao.ativa.php');

    # Organograma Geral
    $imagem3 = new Imagem(PASTA_FIGURAS . 'organograma2.png', null, 15, 15);
    $botaoOrg = new Button();
    $botaoOrg->set_title("Exibe o Organograma da UENF");
    $botaoOrg->set_imagem($imagem3);
    $botaoOrg->set_target("_blank");
    $botaoOrg->set_url('../../_arquivos/documentos/25.pdf');

    # Estrutura da Uenf
    $botaoEstrtutura = new Button("Estrutura", "?fase=estrutura");
    $botaoEstrtutura->set_title("Exibe a estrutura da UENF");
    $botaoEstrtutura->set_target("_blank");

    if ($parametroTipo == 1) {
        # Lotações Ativas
        $botaoAtivo = new Button("Lotações Ativas", "#");
        $botaoAtivo->set_title("Exibe os Cargos Ativos");
        $botaoAtivo->set_class("hollow button");

        # Lotações Inativas
        $botaoInativo = new Button("Lotações Inativas", "?parametroTipo=0");
        $botaoInativo->set_title("Exibe os Cargos Inativos");

        $arrayBotoes = array($botaoGra, $botaoRel, $botaoOrg, $botaoEstrtutura, $botaoAtivo, $botaoInativo);
    } else {
        # Lotações Ativas
        $botaoAtivo = new Button("Lotações Ativas", "?parametroTipo=1");
        $botaoAtivo->set_title("Exibe os Cargos Ativos");

        # Lotações Inativas
        $botaoInativo = new Button("Lotações Inativas", "#");
        $botaoInativo->set_title("Exibe os Cargos Inativos");
        $botaoInativo->set_class("hollow button");

        $arrayBotoes = array($botaoAtivo, $botaoInativo);
    }

    # array de botões    
    $objeto->set_botaoListarExtra($arrayBotoes);

    ################################################################

    switch ($fase) {
        case "" :
        case "listar" :
            $objeto->listar();
            break;

        ################################################################

        case "editar" :
        case "excluir" :
        case "gravar" :
            $objeto->$fase($id);
            break;

        ################################################################

        case "aguardeAtivos" :
            br(10);
            aguarde("Montando a Listagem");
            br();
            loadPage('?fase=listaServidoresAtivos&id=' . $id);
            break;

        ################################################################

        case "aguardeInativos" :
            br(10);
            aguarde("Montando a Listagem");
            br();
            loadPage('?fase=listaServidoresInativos&id=' . $id);
            break;

        ################################################################

        case "listaServidoresAtivos" :
            # Limita o tamanho da tela
            $grid = new Grid();
            $grid->abreColuna(12);

            # Informa a origem
            set_session('origem', 'cadastroLotacao.php?fase=listaServidoresAtivos&id=' . $id);

            # Cria um menu
            $menu = new MenuBar();

            # Voltar
            $linkVoltar = new Link("Voltar", "?");
            $linkVoltar->set_class('button');
            $linkVoltar->set_title('Volta para a página anterior');
            $linkVoltar->set_accessKey('V');
            $menu->add_link($linkVoltar, "left");

            # Relatório
            $imagem2 = new Imagem(PASTA_FIGURAS . 'print.png', null, 15, 15);
            $botaoRel = new Button();
            $botaoRel->set_title("Relatório dos Servidores");
            $botaoRel->set_target("_blank");
            $botaoRel->set_url("?fase=relatorio&subFase=1&id=$id");
            $botaoRel->set_imagem($imagem2);
            $menu->add_link($botaoRel, "right");
            $menu->show();

            # Titulo
            titulo('Servidores da Lotação: ' . $pessoal->get_nomeLotacao($id));
            br();

            # Lista de Servidores Ativos
            $lista = new ListaServidores('Servidores Ativos');
            $lista->set_situacao(1);
            $lista->set_lotacao($id);
            $lista->set_escondeTipoPerfil("Outros");
            $lista->showTabela();

            $grid->fechaColuna();
            $grid->fechaGrid();
            break;

        ################################################################

        case "listaServidoresInativos" :
            # Limita o tamanho da tela
            $grid = new Grid();
            $grid->abreColuna(12);

            # Informa a origem
            set_session('origem', 'cadastroLotacao.php?fase=listaServidoresInativos&id=' . $id);

            # Cria um menu
            $menu = new MenuBar();

            # Voltar
            $linkVoltar = new Link("Voltar", "?");
            $linkVoltar->set_class('button');
            $linkVoltar->set_title('Volta para a página anterior');
            $linkVoltar->set_accessKey('V');
            $menu->add_link($linkVoltar, "left");

            # Relatório
            $imagem2 = new Imagem(PASTA_FIGURAS . 'print.png', null, 15, 15);
            $botaoRel = new Button();
            $botaoRel->set_title("Relatório dos Servidores");
            $botaoRel->set_target("_blank");
            $botaoRel->set_url("?fase=relatorio&subFase=2&id=$id");
            $botaoRel->set_imagem($imagem2);
            $menu->add_link($botaoRel, "right");

            $menu->show();

            # Titulo
            titulo('Servidores da Lotação: ' . $pessoal->get_nomeLotacao($id));
            br();

            # Lista de Servidores Inativos
            $lista = new ListaServidores('Servidores Inativos');
            $lista->set_situacao(1);
            $lista->set_escondeTipoPerfil("Outros");
            $lista->set_situacaoSinal("<>");
            $lista->set_lotacao($id);
            $lista->showTabela();

            $grid->fechaColuna();
            $grid->fechaGrid();
            break;

        ################################################################

        case "relatorio" :
            if ($subFase == 1) {
                # Lista de Servidores Ativos
                $lista = new ListaServidores('Servidores Ativos');
                $lista->set_situacao(1);
                $lista->set_lotacao($id);
                $lista->set_escondeTipoPerfil("Outros");
                $lista->showRelatorio();
            } else {
                # Lista de Servidores Inativos
                $lista = new ListaServidores('Servidores Inativos');
                $lista->set_situacao(1);
                $lista->set_situacaoSinal("<>");
                $lista->set_lotacao($id);
                $lista->set_escondeTipoPerfil("Outros");
                $lista->showRelatorio();
            }
            break;

        ################################################################

        case "grafico" :
            # Botão voltar
            botaoVoltar('?');

            # Exibe o Título
            $grid = new Grid();
            $grid->abreColuna(12);

            # Pega os dados
            $selectGrafico = 'SELECT tblotacao.dir, count(tbservidor.matricula) 
                                FROM tbservidor LEFT  JOIN tbhistlot ON (tbservidor.idServidor = tbhistlot.idServidor)
                                                      JOIN tblotacao ON (tbhistlot.lotacao=tblotacao.idLotacao)
                               WHERE tbhistlot.data = (select max(data) from tbhistlot where tbhistlot.idServidor = tbservidor.idServidor)
                                 AND situacao = 1
                                 AND ativo                           
                            GROUP BY tblotacao.dir
                            ORDER BY tblotacao.dir';

            $servidores = $pessoal->select($selectGrafico);

            titulo('Servidores por Lotação');

            $grid3 = new Grid();
            $grid3->abreColuna(12, 4);
            br();

            # Tabela
            $tabela = new Tabela();
            $tabela->set_conteudo($servidores);
            $tabela->set_label(array("Diretoria / Centro", "Servidores"));
            #$tabela->set_width(array(80, 20));
            $tabela->set_align(array("left", "center"));
            $tabela->set_colunaSomatorio(1);
            $tabela->set_totalRegistro(false);
            $tabela->show();

            $grid3->fechaColuna();
            $grid3->abreColuna(12, 8);

            $chart = new Chart("Pie", $servidores);
            $chart->set_tamanho(700, 500);
            $chart->show();

            $grid3->fechaColuna();
            $grid3->fechaGrid();

            $grid->fechaColuna();
            $grid->fechaGrid();
            break;

        ################################################################

        case "organograma" :

            # Limita a Tela
            $grid = new Grid();
            $grid->abreColuna(12);

            $lotacaoClasse = new Lotacao();

            # Menu 
            $menu = new MenuBar();

            # Voltar
            $botaoVoltar = new Link("Voltar", "?");
            $botaoVoltar->set_class('button');
            $botaoVoltar->set_title('Voltar a página anterior');
            $botaoVoltar->set_accessKey('V');
            $menu->add_link($botaoVoltar, "left");

            # Relatórios
            $imagem = new Imagem(PASTA_FIGURAS . 'print.png', null, 15, 15);
            $botaoRel = new Button();
            $botaoRel->set_title("Relatório dessa pesquisa");
            $botaoRel->set_url("?fase=relatorio");
            $botaoRel->set_target("_blank");
            $botaoRel->set_imagem($imagem);
            #$menu->add_link($botaoRel, "right");

            $menu->show();

            # Formulário de Pesquisa
            $form = new Form('?fase=organograma');

            # Lotação
            $result = $pessoal->select('SELECT DISTINCT DIR, DIR
                                          FROM tblotacao
                                         WHERE ativo
                                      ORDER BY DIR');

            array_unshift($result, array("Pró Reitorias", "Pró Reitorias"));
            array_unshift($result, array("Centros", "Centros"));
            array_unshift($result, array("Administrativo", "Administrativo"));

            $controle = new Input('parametroLotacao', 'combo', 'Lotação:', 1);
            $controle->set_size(30);
            $controle->set_autofocus(true);
            $controle->set_title('Filtra por Lotação');
            $controle->set_array($result);
            $controle->set_valor($parametroLotacao);
            $controle->set_onChange('formPadrao.submit();');
            $controle->set_linha(1);
            $controle->set_col(4);
            $form->add_item($controle);

            $form->show();

            # Título
            tituloTable("Organograma - {$parametroLotacao}");
            br();

            $org = new Organograma($parametroLotacao);
            $org->show();

            $grid->fechaColuna();
            $grid->fechaGrid();
            break;

        ################################################################

        case "estrutura" :

            # Limita a Tela
            $grid = new Grid();
            $grid->abreColuna(12);

            $lotacaoClasse = new Lotacao();

//            # Menu 
//            $menu = new MenuBar();
//
//            # Voltar
//            $botaoVoltar = new Link("Voltar", "?");
//            $botaoVoltar->set_class('button');
//            $botaoVoltar->set_title('Voltar a página anterior');
//            $botaoVoltar->set_accessKey('V');
//            #$menu->add_link($botaoVoltar, "left");
//            # Relatórios
//            $imagem = new Imagem(PASTA_FIGURAS . 'print.png', null, 15, 15);
//            $botaoRel = new Button();
//            $botaoRel->set_title("Relatório dessa pesquisa");
//            $botaoRel->set_url("?fase=relatorio");
//            $botaoRel->set_target("_blank");
//            $botaoRel->set_imagem($imagem);
//            $menu->add_link($botaoRel, "right");
//
//            $menu->show();
            # Título
            br();
            tituloTable("Estrutura da UENF");

            # Conecta ao Banco de Dados
            $pessoal = new Pessoal();

            $lotacao1 = $pessoal->select("SELECT idLotacao, DIR, GER, nome
                                           FROM tblotacao
                                          WHERE ativo
                                            AND DIR = 'Reitoria'
                                       ORDER BY DIR, GER");

            $lotacao2 = $pessoal->select("SELECT idLotacao, DIR, GER, nome
                                           FROM tblotacao
                                          WHERE ativo
                                            AND SUBSTRING(DIR, 1, 3) = 'PRO'
                                       ORDER BY DIR, GER");

            $lotacao3 = $pessoal->select("SELECT idLotacao, DIR, GER, nome
                                           FROM tblotacao
                                          WHERE ativo
                                            AND DIR <> 'Outros'
                                            AND DIR <> 'Reitoria'
                                            AND SUBSTRING(DIR, 1, 3) <> 'PRO'
                                       ORDER BY DIR, GER");

            $estrutura = array_merge($lotacao1, $lotacao2, $lotacao3);

            $primeiraDiretoria = "";
            $primeiraGerencia = "";

            # Começa a lista
            echo "<dl>";
            foreach ($estrutura as $item) {
                if ($item['DIR'] <> $primeiraDiretoria) {
                    br();
                    echo "<dt>{$item['DIR']} - " . $lotacaoClasse->get_nomeDiretoria($item['idLotacao']) . "</dt>";
                    $primeiraDiretoria = $item['DIR'];
                }

                if (!empty($item['GER'])) {
                    echo "<dd>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp; - {$item['GER']} - {$item['nome']}</dd>";
                    $primeiraGerencia = $item['GER'];
                }
            }


            echo "</dl>";

            $grid->fechaColuna();
            $grid->fechaGrid();
            break;

        ################################################################

        case "aguardeHistorico" :
            br(10);
            aguarde("Montando a Listagem");
            br();
            loadPage('?fase=listaHistorico&id=' . $id);
            break;

        ################################################################

        case "listaHistorico" :
            # Limita o tamanho da tela
            $grid = new Grid();
            $grid->abreColuna(12);

            # Informa a origem
            set_session('origem', 'cadastroLotacao.php?fase=listaHistorico&id=' . $id);

            # Cria um menu
            $menu = new MenuBar();

            # Voltar
            $linkVoltar = new Link("Voltar", "?");
            $linkVoltar->set_class('button');
            $linkVoltar->set_title('Volta para a página anterior');
            $linkVoltar->set_accessKey('V');
            $menu->add_link($linkVoltar, "left");

            # Relatório
            $imagem2 = new Imagem(PASTA_FIGURAS . 'print.png', null, 15, 15);
            $botaoRel = new Button();
            $botaoRel->set_title("Relatório dos Servidores");
            $botaoRel->set_target("_blank");
            $botaoRel->set_url("?fase=relatorio&subFase=1&id=$id");
            $botaoRel->set_imagem($imagem2);
            #$menu->add_link($botaoRel, "right");
            $menu->show();

            # Formulário de Pesquisa
            $form = new Form("?fase=listaHistorico&id={$id}");

            # Situação
            $result = $pessoal->select('SELECT idsituacao, situacao
                                              FROM tbsituacao                                
                                          ORDER BY 1');
            array_unshift($result, array('*', '-- Todos --'));

            $controle = new Input('parametroSituacao', 'combo', 'Situação:', 1);
            $controle->set_size(30);
            $controle->set_title('Filtra por Situação');
            $controle->set_array($result);
            $controle->set_valor($parametroSituacao);
            $controle->set_onChange('formPadrao.submit();');
            $controle->set_linha(1);
            $controle->set_col(4);
            $form->add_item($controle);

            $form->show();

            $select = "SELECT tbservidor.idFuncional,
                              tbpessoa.nome,
                              tbservidor.idServidor,                     
                              tbperfil.nome,
                              tbservidor.idServidor,
                              tbhistlot.idHistLot,
                              tbhistlot.idHistLot,
                              tbservidor.idServidor
                         FROM tbservidor LEFT JOIN tbpessoa USING (idPessoa)
                                              JOIN tbhistlot USING (idServidor)
                                              JOIN tblotacao ON (tbhistlot.lotacao=tblotacao.idLotacao)
                                         LEFT JOIN tbperfil ON (tbservidor.idPerfil = tbperfil.idPerfil)
               WHERE idLotacao = {$id}";

            if ($parametroSituacao <> null AND $parametroSituacao <> "*") {
                $select .= " AND tbservidor.situacao = {$parametroSituacao}";
            }

            $select .= " ORDER BY tbpessoa.nome, tbhistlot.data DESC";

            $result = $pessoal->select($select);

            $tabela = new Tabela();
            $tabela->set_titulo('Histórico de Servidores');
            $tabela->set_subtitulo($pessoal->get_nomeLotacao2($id));
            $tabela->set_label(['IdFuncional', 'Nome', 'Cargo', 'Perfil', 'Situação', 'Chegada ao Setor', 'Saída do Setor', 'Editar']);
            $tabela->set_align(["center", "left", "left"]);
            #$tabela->set_funcao([null, null, null, null, null, "date_to_php"]);

            $tabela->set_classe([null, null, "pessoal", null, "pessoal", "Lotacao", "Lotacao"]);
            $tabela->set_metodo([null, null, "get_Cargo", null, "get_Situacao", "getDataChegadaDeOnde", "getDataSaidaPraOnde"]);

            $tabela->set_rowspan(1);
            $tabela->set_grupoCorColuna(1);

            # Botão Editar
            $botao = new Link(null, '?fase=editaServidor&idServidor=', 'Acessa o servidor');
            $botao->set_imagem(PASTA_FIGURAS . 'bullet_edit.png', 20, 20);

            # Coloca o objeto link na tabela			
            $tabela->set_link([null, null, null, null, null, null, null, $botao]);

            $tabela->set_conteudo($result);
            $tabela->show();

            $grid->fechaColuna();
            $grid->fechaGrid();
            break;

        ################################################################

        case "editaServidor" :
            br(8);
            aguarde();

            # pega o idServidor
            $idServidor = soNumeros(get('idServidor'));

            # Informa o $id Servidor
            set_session('idServidorPesquisado', $idServidor);

            # Carrega a página específica
            loadPage('servidorMenu.php');
            break;
    }

    ################################################################

    if ($fase <> "organograma") {
        $page->terminaPagina();
    }
} else {
    loadPage("../../areaServidor/sistema/login.php");
}    