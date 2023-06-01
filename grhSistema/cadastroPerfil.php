<?php

/**
 * Cadastro de Perfil
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
    $subFase = get('subFase', 1);

    # Verifica se veio menu grh e registra o acesso no log
    $grh = get('grh', false);
    if ($grh) {
        # Grava no log a atividade
        $atividade = "Visualizou o cadastro de perfil";
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
    if ($fase <> "relatorio") {
        AreaServidor::cabecalho();
    }

    # Abre um novo objeto Modelo
    $objeto = new Modelo();

    ################################################################
    # Nome do Modelo (aparecerá nos fildset e no caption da tabela)
    $objeto->set_nome('Perfil');

    # bot?o de voltar da lista
    $objeto->set_voltarLista('grh.php');

    # controle de pesquisa
    $objeto->set_parametroLabel('Pesquisar');
    $objeto->set_parametroValue($parametro);

    # select da lista
    $objeto->set_selectLista('SELECT idPerfil,
                                      tipo,
                                      nome,
                                      idPerfil,
                                      idPerfil,
                                      idPerfil,
                                      idPerfil
                                 FROM tbperfil
                                WHERE nome LIKE "%' . $parametro . '%"
                                   OR idPerfil LIKE "%' . $parametro . '%" 
                             ORDER BY tipo,nome');

    # select do edita
    $objeto->set_selectEdita('SELECT nome,
                                     tipo,
                                     progressao,
                                     trienio,
                                     comissao,
                                     gratificacao,
                                     ferias,
                                     licenca,
                                     matIni,
                                     matFim,
                                     novoServidor,
                                    obs
                               FROM tbperfil
                              WHERE idPerfil = ' . $id);
    
    # Habilita o modo leitura para usuario de regra 12
    if (Verifica::acesso($idUsuario, 12)) {
        $objeto->set_modoLeitura(true);
    }

    # Excluir somente para Administradores
    if (Verifica::acesso($idUsuario, 1)) {
        $objeto->set_linkExcluir('?fase=excluir');  // Excluir somente para administradores
    }
    
    # Caminhos
    $objeto->set_linkEditar('?fase=editar');
    $objeto->set_linkGravar('?fase=gravar');
    $objeto->set_linkListar('?fase=listar');

    # Parametros da tabela
    $objeto->set_label(["id", "Tipo", "Perfil", "Servidores Ativos", "Ver", "Servidores Inativos", "Ver"]);
    $objeto->set_colspanLabel([null, null, null, 2, null, 2]);
    $objeto->set_width([5, 23, 30, 10, 5, 10, 5]);
    $objeto->set_align(["center", "center", "left"]);
    #$objeto->set_function(array (null,null,null,null,null,null,"get_nome"));

    $objeto->set_classe([null, null, null, "Pessoal", null, "Pessoal", null]);
    $objeto->set_metodo([null, null, null, "get_numServidoresAtivosPerfil", null, "get_numServidoresInativosPerfil", null]);

    $objeto->set_rowspan(1);
    $objeto->set_grupoCorColuna(1);

    $objeto->set_colunaSomatorio([3, 5]);

    # Ver servidores ativos
    $servAtivos = new Link(null, "?fase=aguardeAtivos&id={$id}");
    $servAtivos->set_imagem(PASTA_FIGURAS_GERAIS . 'olho.png', 20, 20);
    $servAtivos->set_title("Exibe os servidores ativos");

    # Ver servidores inativos
    $servInativos = new Link(null, '?fase=aguardeInativos&id=' . $id);
    $servInativos->set_imagem(PASTA_FIGURAS_GERAIS . 'olho.png', 20, 20);
    $servInativos->set_title("Exibe os servidores inativos");

    # Coloca o objeto link na tabela			
    $objeto->set_link(array(null, null, null, null, $servAtivos, null, $servInativos));

    # Classe do banco de dados
    $objeto->set_classBd('Pessoal');

    # Nome da tabela
    $objeto->set_tabela('tbperfil');

    # Nome do campo id
    $objeto->set_idCampo('idPerfil');

    # Tipo de label do formulário
    $objeto->set_formlabelTipo(1);

    # Foco do form
    $objeto->set_formFocus('nome');

    # Campos para o formulario
    $objeto->set_campos(array(
        array('linha' => 1,
            'nome' => 'nome',
            'title' => 'Nome do Perfil',
            'label' => 'Perfil:',
            'tipo' => 'texto',
            'required' => true,
            'autofocus' => true,
            'size' => 50),
        array('linha' => 1,
            'nome' => 'tipo',
            'title' => 'Tipo do Perfil',
            'label' => 'Tipo:',
            'tipo' => 'combo',
            'required' => true,
            'array' => array("Concursados", "Não Concursados", "Outros"),
            'size' => 20),
        array('linha' => 3,
            'nome' => 'progressao',
            'title' => 'informa se esse perfil tem direito a progressão',
            'label' => 'Progressão:',
            'required' => true,
            'tipo' => 'combo',
            'array' => array("Sim", "Não"),
            'size' => 20),
        array('linha' => 3,
            'nome' => 'trienio',
            'title' => 'informa se esse perfil tem direito ao triênio',
            'label' => 'Triênio:',
            'required' => true,
            'tipo' => 'combo',
            'array' => array("Sim", "Não"),
            'size' => 20),
        array('linha' => 3,
            'nome' => 'comissao',
            'title' => 'informa se esse perfil tem direito a ter cargo em comissão',
            'required' => true,
            'label' => 'Comissão:',
            'tipo' => 'combo',
            'array' => array("Sim", "Não"),
            'size' => 20),
        array('linha' => 3,
            'nome' => 'gratificacao',
            'title' => 'informa se esse perfil tem direito a receber gratificação especial',
            'label' => 'Gratificação:',
            'required' => true,
            'tipo' => 'combo',
            'array' => array("Sim", "Não"),
            'size' => 20),
        array('linha' => 3,
            'nome' => 'ferias',
            'required' => true,
            'title' => 'informa se esse perfil tem direito as férias',
            'label' => 'Férias:',
            'tipo' => 'combo',
            'array' => array("Sim", "Não"),
            'size' => 20),
        array('linha' => 3,
            'nome' => 'licenca',
            'title' => 'informa se esse perfil tem direito a licença',
            'required' => true,
            'label' => 'Licença:',
            'tipo' => 'combo',
            'array' => array("Sim", "Não"),
            'size' => 20),
        array('linha' => 4,
            'nome' => 'matIni',
            'title' => 'Início da faixa da matrícula reservada para esse perfil',
            'label' => 'Matrícula Inicial:',
            'tipo' => 'texto',
            'col' => 3,
            'size' => 10),
        array('linha' => 4,
            'nome' => 'matFim',
            'title' => 'Fim da faixa da matrícula reservada para esse perfil',
            'label' => 'Matrícula Final:',
            'tipo' => 'texto',
            'col' => 3,
            'size' => 10),
        array('linha' => 4,
            'nome' => 'novoServidor',
            'title' => 'Permite que novos servidores sejam cadastrados nesse perfil',
            'required' => true,
            'label' => 'Habilita Novo Servidor:',
            'tipo' => 'combo',
            'array' => array(array(1, "Sim"), array(0, "Não")),
            'col' => 2,
            'size' => 10),
        array('linha' => 5,
            'nome' => 'obs',
            'label' => 'Observação:',
            'tipo' => 'textarea',
            'size' => array(80, 5))));

    # idUsuário para o Log
    $objeto->set_idUsuario($idUsuario);

    # Gráfico
    $imagem = new Imagem(PASTA_FIGURAS . 'pie.png', null, 15, 15);
    $botaoGra = new Button();
    $botaoGra->set_title("Exibe gráfico da quantidade de servidores");
    $botaoGra->set_url("?fase=grafico");
    $botaoGra->set_imagem($imagem);

    # Relatório
    $imagem = new Imagem(PASTA_FIGURAS . 'print.png', null, 15, 15);
    $botaoRel = new Button();
    $botaoRel->set_imagem($imagem);
    $botaoRel->set_title("Imprimir");
    $botaoRel->set_target("_blank");
    $botaoRel->set_url('../grhRelatorios/perfil.php');

    $objeto->set_botaoListarExtra([$botaoGra, $botaoRel]);
    
    $objeto->set_rotinaExtraListar("callout");
    $objeto->set_rotinaExtraListarParametro("Os Perfis do tipo Outros NÃO APARECERÃO nas listagens e relatório de servidores.<br/>Estes perfis são para bolsistas e estagiários que só estão no cadastro para poderem acessar o sistema.");

    ################################################################

    switch ($fase) {
        case "" :
        case "listar" :
            $objeto->listar();
            break;

        ################################################################    

        case "excluir" :
            # Verifica se esse perfil tem servidor
            $sevAtivos = $pessoal->get_servidoresAtivosPerfil($id);
            $sevIntivos = $pessoal->get_servidoresInativosPerfil($id);
            $serTotal = $sevAtivos + $sevIntivos;

            if ($serTotal > 0) {
                alert('Este Perfil tem ' . $serTotal . ' servidores cadastrados:\n' . $sevAtivos . ' ativo(s) e ' . $sevIntivos . ' inativo(s).\nEle não poderá ser excluído.');
                back(1);
            } else {
                $objeto->excluir($id);
            }
            break;

        ################################################################

        case "editar" :
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
            set_session('origem', 'cadastroPerfil.php?fase=listaServidoresAtivos&id=' . $id);

            # Cria um menu
            $menu = new MenuBar();

            # Voltar
            $linkVoltar = new Button("Voltar", "?");
            $linkVoltar->set_title('Volta para a página anterior');
            $linkVoltar->set_accessKey('V');
            $menu->add_link($linkVoltar, "left");

            # Relatório
            $imagem = new Imagem(PASTA_FIGURAS . 'print.png', null, 15, 15);
            $botaoRel = new Button();
            $botaoRel->set_imagem($imagem);
            $botaoRel->set_title("Imprimir");
            $botaoRel->set_target("_blank");
            $botaoRel->set_url("?fase=relatorio&subFase=1&id=$id");
            $menu->add_link($botaoRel, "right");

            $menu->show();

            $servidor = new Pessoal();

            # Lista de Servidores Ativos
            $lista = new ListaServidores('Servidores Ativos - Perfil: ' . $pessoal->get_perfilNome($id));
            $lista->set_situacao(1);
            $lista->set_perfil($id);
            $lista->set_relatorio(true);
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
            set_session('origem', 'cadastroPerfil.php?fase=listaServidoresInativos&id=' . $id);

            # Cria um menu
            $menu = new MenuBar();

            # Voltar
            $linkVoltar = new Button("Voltar", "?");
            $linkVoltar->set_title('Volta para a página anterior');
            $linkVoltar->set_accessKey('V');
            $menu->add_link($linkVoltar, "left");

            # Relatório
            $imagem = new Imagem(PASTA_FIGURAS . 'print.png', null, 15, 15);
            $botaoRel = new Button();
            $botaoRel->set_imagem($imagem);
            $botaoRel->set_title("Imprimir");
            $botaoRel->set_target("_blank");
            $botaoRel->set_url("?fase=relatorio&subFase=2&id=$id");
            $menu->add_link($botaoRel, "right");

            $menu->show();

            $servidor = new Pessoal();

            # Lista de Servidores Inativos
            $lista = new ListaServidores('Servidores Inativos - Perfil: ' . $pessoal->get_perfilNome($id));
            $lista->set_situacao(1);
            $lista->set_situacaoSinal("<>");
            $lista->set_perfil($id);
            $lista->set_relatorio(true);
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
                $lista->set_perfil($id);
                $lista->showRelatorio();
            } else {
                # Lista de Servidores Inativos
                $lista = new ListaServidores('Servidores Inativos');
                $lista->set_situacao(1);
                $lista->set_situacaoSinal("<>");
                $lista->set_perfil($id);
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
            $selectGrafico = 'SELECT tbperfil.nome, count(tbservidor.idServidor) 
                                FROM tbservidor JOIN tbperfil USING (idPerfil)
                               WHERE tbservidor.situacao = 1
                            GROUP BY tbperfil.nome';

            $servidores = $pessoal->select($selectGrafico);

            titulo('Servidores por Perfil');

            $grid3 = new Grid();
            $grid3->abreColuna(3);
            br();

            # Tabela
            $tabela = new Tabela();
            $tabela->set_conteudo($servidores);
            $tabela->set_label(["Perfil", "Servidores"]);
            $tabela->set_align(["left", "center"]);
            $tabela->set_colunaSomatorio(1);
            $tabela->set_totalRegistro(false);
            $tabela->show();

            $grid3->fechaColuna();
            $grid3->abreColuna(9);

            $chart = new Chart("Pie", $servidores);
            $chart->show();

            $grid3->fechaColuna();
            $grid3->fechaGrid();

            $grid->fechaColuna();
            $grid->fechaGrid();
            break;
        
        ################################################################

        case "historico" :
            # Botão voltar
            botaoVoltar('?');

            # Exibe o Título
            $grid = new Grid();
            $grid->abreColuna(12);

            $historico = new ListaHistorico();
            $historico->set_tabela("tbperfil");
            $historico->set_id($id);
            $historico->show();

            $grid->fechaColuna();
            $grid->fechaGrid();
            break;
    }
    $page->terminaPagina();
} else {
    loadPage("../../areaServidor/sistema/login.php");
}