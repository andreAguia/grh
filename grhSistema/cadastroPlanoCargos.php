<?php

/**
 * Cadastro de Plano de Cargos e Salários
 *
 * By Alat
 */
# Reservado para o servidor logado
$idUsuario = NULL;

# Configuração
include ("_config.php");

# Permissão de Acesso
$acesso = Verifica::acesso($idUsuario, 2);

if ($acesso) {

    # Conecta ao Banco de Dados
    $intra = new Intra();
    $pessoal = new Pessoal();

    # Verifica a fase do programa
    $fase = get('fase', 'listar');

    # Verifica se veio menu grh e registra o acesso no log
    $grh = get('grh', FALSE);
    if ($grh) {
        # Grava no log a atividade
        $atividade = "Visualizou o cadastro de plano de cargos e vencimentos";
        $data = date("Y-m-d H:i:s");
        $intra->registraLog($idUsuario, $data, $atividade, NULL, NULL, 7);
    }

    # pega o id (se tiver)
    $id = soNumeros(get('id'));

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
    if ($fase <> "exibeTabela") {
        AreaServidor::cabecalho();
    }

    # Abre um novo objeto Modelo
    $objeto = new Modelo();

    ################################################################
    # Nome do Modelo (aparecerá nos fildset e no caption da tabela)
    $objeto->set_nome('Plano de Cargos & Vencimentos');

    # bot?o de voltar da lista
    $objeto->set_voltarLista('grh.php');

    # controle de pesquisa
    $objeto->set_parametroLabel('Pesquisar');
    $objeto->set_parametroValue($parametro);

    # select da lista
    $objeto->set_selectLista('SELECT idPlano,
                                      numDecreto,
                                      servidores,
                                      dtDecreto,
                                      dtPublicacao,
                                      dtVigencia,
                                      CASE planoAtual
                                            WHEN 1 THEN "Vigente"
                                            ELSE "Antigo"
                                       end,
                                      idPlano,
                                      idPlano
                                 FROM tbplano
                                WHERE numDecreto LIKE "%' . $parametro . '%"
                                   OR idPlano LIKE "%' . $parametro . '%"
                             ORDER BY planoAtual desc, dtPublicacao desc, numDecreto desc');

    # select do edita
    $objeto->set_selectEdita('SELECT numDecreto,
                                     servidores,
                                     planoAtual,
                                     dtDecreto,
                                     dtPublicacao,
                                     dtVigencia,
                                     link,
                                     obs
                                FROM tbplano
                               WHERE idPlano = ' . $id);

    # Caminhos
    $objeto->set_linkEditar('?fase=editar');
    $objeto->set_linkGravar('?fase=gravar');
    $objeto->set_linkListar('?fase=listar');

    # Dá acesso a exclusão somente ao administrador
    if (Verifica::acesso($idUsuario, 1)) {
        #$objeto->set_linkExcluir('?fase=excluir');
    }

    # Parametros da tabela
    $objeto->set_label(array("id", "Decreto / Lei", "Servidores", "Data do Decreto / Lei", "Publicação no DOERJ", "Data da Vigência", "Plano Atual", "DO", "Tabela"));
    #$objeto->set_width(array(5,20,20,20,10,10));
    $objeto->set_align(array("center", "left"));
    $objeto->set_funcao(array(NULL, NULL, NULL, "date_to_php", "date_to_php", "date_to_php"));

    $objeto->set_classe([NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'PlanoCargos', 'PlanoCargos']);
    $objeto->set_metodo([NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'exibeLei', 'exibeBotaoTabela']);

    $objeto->set_formatacaoCondicional(array(
        array('coluna' => 6,
            'valor' => "Antigo",
            'operador' => '=',
            'id' => 'inativo')));

    # Classe do banco de dados
    $objeto->set_classBd('Pessoal');

    # Nome da tabela
    $objeto->set_tabela('tbplano');

    # Nome do campo id
    $objeto->set_idCampo('idPlano');

    # Tipo de label do formulário
    $objeto->set_formlabelTipo(1);

    # Campos para o formulario
    $objeto->set_campos(array(
        array('linha' => 1,
            'col' => 6,
            'nome' => 'numDecreto',
            'label' => 'Decreto ou Lei:',
            'title' => 'Número do Decreto',
            'tipo' => 'texto',
            'required' => TRUE,
            'autofocus' => TRUE,
            'size' => 30),
        array('linha' => 1,
            'nome' => 'servidores',
            'col' => 3,
            'label' => 'Servidores:',
            'title' => 'O plano se refere a qual tipo de servidor.',
            'tipo' => 'combo',
            'array' => array(NULL, "Todos", "Adm/Tec", "Professor"),
            'padrao' => 'Sim',
            'size' => 10),
        array('linha' => 1,
            'nome' => 'planoAtual',
            'col' => 3,
            'label' => 'Plano atual:',
            'title' => 'Se é o Plano de Cargos atualmente ativo',
            'tipo' => 'combo',
            'array' => array(array('1', 'Sim'), array(NULL, 'Não')),
            'padrao' => 'Sim',
            'size' => 10),
        array('linha' => 2,
            'col' => 4,
            'nome' => 'dtDecreto',
            'label' => 'Data do Decreto:',
            'title' => 'Data do decreto',
            'tipo' => 'data',
            'required' => TRUE,
            'size' => 15),
        array('linha' => 2,
            'nome' => 'dtPublicacao',
            'col' => 4,
            'label' => 'Data da Publicação:',
            'title' => 'Data da Publicação no DOERJ',
            'tipo' => 'data',
            'required' => TRUE,
            'size' => 15),
        array('linha' => 2,
            'nome' => 'dtVigencia',
            'col' => 4,
            'label' => 'Data da Vigência:',
            'title' => 'Data em que o plano passou a vigorar',
            'tipo' => 'data',
            'required' => TRUE,
            'size' => 15),
        array('linha' => 3,
            'col' => 12,
            'nome' => 'link',
            'label' => 'Nome do arquivo da Lei:',
            'title' => 'texto do Decreto',
            'tipo' => 'texto',
            'bloqueadoEsconde' => TRUE,
            'size' => 250),
        array('linha' => 4,
            'col' => 12,
            'nome' => 'obs',
            'label' => 'Observação:',
            'tipo' => 'textarea',
            'size' => array(80, 5))));

    # idUsuário para o Log
    $objeto->set_idUsuario($idUsuario);

    $objeto->set_voltarForm('?fase=editar&id=' . $id);

    ################################################################

    switch ($fase) {
        case "" :
        case "listar" :
            $objeto->listar();
            break;

        ################################################################

        case "editar" :
            # Limita o tamanho da tela
            $grid = new Grid();
            $grid->abreColuna(12);

            # Pega os dados do plano
            $plano = new PlanoCargos();
            $dados = $plano->get_dadosPlano($id);

            # Cria um menu
            $menu = new MenuBar();

            # Voltar
            $linkVoltar = new Button("Voltar", "?");
            $linkVoltar->set_title('Volta para a página anterior');
            $linkVoltar->set_accessKey('V');
            $menu->add_link($linkVoltar, "left");

            # Texto da Lei
            if (!vazio($dados[4])) {
                $botaoRel = new Button("Diário Oficial", "../_legislacao/" . $dados[4]);
                $botaoRel->set_title("Publicação da Lei");
                $botaoRel->set_target("_blank");
                $menu->add_link($botaoRel, "right");
            }

            # Tabela
            $botaoRel = new Button("Tabela", "?fase=tabela&id=$id");
            $botaoRel->set_title("Tabela");
            $menu->add_link($botaoRel, "right");

            # Editar
            $botaoEditar = new Button("Editar", "?fase=editar2&id=$id");
            $botaoEditar->set_title("Editar");
            $menu->add_link($botaoEditar, "right");

            $menu->show();

            $objeto->set_botaoVoltarForm(FALSE);
            $objeto->set_botaoHistorico(FALSE);

            $objeto->editar($id, TRUE);

            $grid->fechaColuna();
            $grid->fechaGrid();
            break;

        ################################################################

        case "editar2" :
            $objeto->editar($id);
            break;

        ################################################################

        case "exibeTabela" :
            # Limita o tamanho da tela
            $grid = new Grid();
            $grid->abreColuna(12);

            $painel = new Callout();
            $painel->abre();

            $plano = new PlanoCargos();
            $plano->exibeTabela($id, FALSE);

            $painel->fecha();

            $grid->fechaColuna();
            $grid->fechaGrid();
            break;

        ################################################################

        case "tabela" :
            # Limita o tamanho da tela
            $grid = new Grid();
            $grid->abreColuna(12);

            # Pega os dados do plano
            $plano = new PlanoCargos();
            $dados = $plano->get_dadosPlano($id);

            # Cria um menu
            $menu = new MenuBar();

            # Voltar
            $linkVoltar = new Button("Voltar", "?fase=editar&id=$id");
            $linkVoltar->set_title('Volta para a página anterior');
            $linkVoltar->set_accessKey('V');
            $menu->add_link($linkVoltar, "left");

            # Verifica se permite edição dos valores
            if (Verifica::acesso($idUsuario, 1)) {   // Somente Administradores
                $linkVoltar = new Button("Incluir Valor", "cadastroTabelaSalarial.php?fase=editar&pcv=.$id");
                $linkVoltar->set_title('Inclui novo valor de vencimento na tabela salarial');
                $linkVoltar->set_accessKey('I');
                $menu->add_link($linkVoltar, "right");
            }

            $menu->show();

            # Verifica se permite edição dos valores
            if (Verifica::acesso($idUsuario, 1)) {   // Somente Administradores
                $plano->exibeTabela($id, TRUE);
            } else {
                $plano->exibeTabela($id, FALSE);
            }

            $grid->fechaColuna();
            $grid->fechaGrid();
            break;

        ################################################################

        case "excluir" :
        case "gravar" :
            $objeto->$fase($id);
            break;

        ################################################################
    }

    $page->terminaPagina();
} else {
    loadPage("../../areaServidor/sistema/login.php");
}
