<?php

/**
 * Cadastro de Plano de Cargos e Salários
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
        $atividade = "Visualizou o cadastro de plano de cargos e vencimentos";
        $data = date("Y-m-d H:i:s");
        $intra->registraLog($idUsuario, $data, $atividade, null, null, 7);
    }

    # pega o id (se tiver)
    $id = soNumeros(get('id'));

    # Pega o parametro de pesquisa (se tiver)
    if (is_null(post('parametro'))) {
        $parametro = retiraAspas(get_session('sessionParametro'));
    } else {
        $parametro = post('parametro');
        set_session('sessionParametro', $parametro);
    }

    # Começa uma nova página
    $page = new Page();
    $page->iniciaPagina();

    # Cabeçalho da Página
    AreaServidor::cabecalho();

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

    # Habilita o modo leitura para usuario de regra 12
    if (Verifica::acesso($idUsuario, 12)) {
        $objeto->set_modoLeitura(true);
    }

    # Caminhos
    $objeto->set_linkEditar('?fase=editar');
    $objeto->set_linkGravar('?fase=gravar');
    $objeto->set_linkListar('?fase=listar');

    # Dá acesso a exclusão somente ao administrador
    if (Verifica::acesso($idUsuario, 1)) {
        $objeto->set_linkExcluir('?fase=excluir');
    }

    # Parametros da tabela
    $objeto->set_label(array("id", "Decreto / Lei", "Servidores", "Data do Decreto / Lei", "Publicação no DOERJ", "Data da Vigência", "Plano Atual", "DO", "Tabela"));
    #$objeto->set_width(array(5,20,20,20,10,10));
    $objeto->set_align(array("center", "left"));
    $objeto->set_funcao(array(null, null, null, "date_to_php", "date_to_php", "date_to_php"));

    $objeto->set_classe([null, null, null, null, null, null, null, 'PlanoCargos', 'PlanoCargos']);
    $objeto->set_metodo([null, null, null, null, null, null, null, 'exibeLei', 'exibeBotaoTabela']);

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
            'required' => true,
            'autofocus' => true,
            'size' => 30),
        array('linha' => 1,
            'nome' => 'servidores',
            'col' => 3,
            'label' => 'Servidores:',
            'title' => 'O plano se refere a qual tipo de servidor.',
            'tipo' => 'combo',
            'array' => array(null, "Todos", "Adm/Tec", "Professor"),
            'padrao' => 'Sim',
            'size' => 10),
        array('linha' => 1,
            'nome' => 'planoAtual',
            'col' => 3,
            'label' => 'Plano atual:',
            'title' => 'Se é o Plano de Cargos atualmente ativo',
            'tipo' => 'combo',
            'array' => array(array('1', 'Sim'), array(null, 'Não')),
            'padrao' => 'Sim',
            'size' => 10),
        array('linha' => 2,
            'col' => 4,
            'nome' => 'dtDecreto',
            'label' => 'Data do Decreto:',
            'title' => 'Data do decreto',
            'tipo' => 'data',
            'required' => true,
            'size' => 15),
        array('linha' => 2,
            'nome' => 'dtPublicacao',
            'col' => 4,
            'label' => 'Data da Publicação:',
            'title' => 'Data da Publicação no DOERJ',
            'tipo' => 'data',
            'required' => true,
            'size' => 15),
        array('linha' => 2,
            'nome' => 'dtVigencia',
            'col' => 4,
            'label' => 'Data da Vigência:',
            'title' => 'Data em que o plano passou a vigorar',
            'tipo' => 'data',
            'required' => true,
            'size' => 15),
        array('linha' => 3,
            'col' => 12,
            'nome' => 'link',
            'label' => 'Nome do arquivo da Lei:',
            'title' => 'texto do Decreto',
            'tipo' => 'texto',
            'bloqueadoEsconde' => true,
            'size' => 250),
        array('linha' => 4,
            'col' => 12,
            'nome' => 'obs',
            'label' => 'Observação:',
            'tipo' => 'textarea',
            'size' => array(80, 5))));

    # idUsuário para o Log
    $objeto->set_idUsuario($idUsuario);

    ################################################################

    switch ($fase) {
        case "" :
        case "listar" :
            $objeto->listar();
            break;

        ################################################################

        case "editar" :
            $objeto->editar($id);
            break;

        ################################################################

        case "exibeTabela" :
            # Limita o tamanho da tela
            $grid = new Grid();
            $grid->abreColuna(12);

            # Somente para admin
            if (Verifica::acesso($idUsuario, 1)) {

                # Cria um menu
                $menu1 = new MenuBar();

//                # Relatório
//                $botaoVoltar = new Link("Imprimir", "?fase=imprimir");
//                $botaoVoltar->set_class('button');
//                $botaoVoltar->set_title('Imprimir a tabela do plano');
//                $menu1->add_link($botaoVoltar, "right");
                # Editar
                $botaoVoltar = new Link("Editar", "cadastroTabelaSalarial.php");
                $botaoVoltar->set_class('button');
                $botaoVoltar->set_title('Edita os valores da tabela');
                $menu1->add_link($botaoVoltar, "right");

                $menu1->show();
            } else {
                br();
            }

            $plano = new PlanoCargos();
            $plano->exibeTabela($id, true);

            # guarda na sessio o plano caso deseje editar
            set_session('parametroPlano', $id);

            $grid->fechaColuna();
            $grid->fechaGrid();
            break;

        ################################################################

        case "gravar" :

            $objeto->$fase($id);
            break;

        ################################################################

        case "excluir" :
            # Verifica se este plano tem algum salário cadastrado nele
            $classe = new Classe();
            if ($classe->get_numSalarios($id) == 0) {
                $objeto->excluir($id);
            } else {
                alert('Este pĺano de cargos tem salário cadastrados.\nNão é possível excluí-lo');
                back(1);
            }
            break;

        ################################################################
    }

    $page->terminaPagina();
} else {
    loadPage("../../areaServidor/sistema/login.php");
}
