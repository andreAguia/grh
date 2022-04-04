<?php

/**
 * Cadastro de Cargos
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
    $fase = get('fase', 'listar');

    # Verifica se veio menu grh e registra o acesso no log
    $grh = get('grh', false);
    if ($grh) {
        # Grava no log a atividade
        $atividade = "Visualizou o cadastro de cargo efetivo";
        $data = date("Y-m-d H:i:s");
        $intra->registraLog($idUsuario, $data, $atividade, null, null, 7);
    }

    # pega o id (se tiver)
    $id = soNumeros(get('id'));
    
    # Pega o parametro de pesquisa (se tiver)
    if (is_null(post('parametro')))     # Se o parametro n?o vier por post (for nulo)
        $parametro = retiraAspas(get_session('sessionParametro'));# passa o parametro da session para a variavel parametro retirando as aspas
    else {
        $parametro = post('parametro');                # Se vier por post, retira as aspas e passa para a variavel parametro
        set_session('sessionParametro', $parametro);    # transfere para a session para poder recuperá-lo depois
    }

    # Ordem da tabela
    $orderCampo = get('orderCampo');
    $orderTipo = get('orderTipo');

    # Começa uma nova página
    $page = new Page();
    $page->iniciaPagina();

    # Cabeçalho da Página
    if ($fase <> "relatorio" AND $fase <> "mapa" AND $fase <> "relatorioInativo") {
        AreaServidor::cabecalho();
    }

    # Abre um novo objeto Modelo
    $objeto = new Modelo();

    ################################################################
    # Nome do Modelo (aparecerá nos fildset e no caption da tabela)
    $objeto->set_nome('Cadastro de Funções');

    # bot?o de voltar da lista
    $objeto->set_voltarLista('areaCargoEfetivo.php');

    # controle de pesquisa
    $objeto->set_parametroLabel('Pesquisar');
    $objeto->set_parametroValue($parametro);

    # select da lista
    $objeto->set_selectLista('SELECT idCargo,
                                      tbtipocargo.cargo,
                                      tbarea.area,
                                      nome,                              
                                      idCargo,
                                      idCargo,
                                      idCargo,
                                      idCargo,
                                      idCargo,
                                      idCargo,
                                      idCargo
                                 FROM tbcargo LEFT JOIN tbplano USING (idPlano)
                                              LEFT JOIN tbtipocargo USING (idTipoCargo)
                                              LEFT JOIN tbarea USING (idarea)
                                WHERE nome LIKE "%' . $parametro . '%"
                                   OR idCargo LIKE "%' . $parametro . '%" 
                                   OR tbarea.area LIKE "%' . $parametro . '%" 
                                   OR nome LIKE "%' . $parametro . '%"     
                                   OR tbtipocargo.cargo LIKE "%' . $parametro . '%"
                             ORDER BY 2 asc, 3 asc, 4 asc, 5 asc, 6 asc');

    # select do edita
    $objeto->set_selectEdita('SELECT idtipocargo,
                                     idarea,
                                     nome,
                                     idPlano,
                                     atribuicoes,
                                     obs
                                FROM tbcargo
                               WHERE idCargo = ' . $id);

    # Caminhos
    if (Verifica::acesso($idUsuario, 1)) {      // Excluir somente admin
        $objeto->set_linkExcluir('?fase=excluir');
    }
    $objeto->set_linkEditar('?fase=editar');
    $objeto->set_linkGravar('?fase=gravar');
    $objeto->set_linkListar('?fase=listar');

    # Parametros da tabela
    $objeto->set_label(["id", "Cargo", "Área", "Função", "Servidores<br/>Ativos", "Ver", "Servidores<br/>Inativos", "Ver"]);
    #$objeto->set_width(array(5,20,25,25,10,5,5));
    $objeto->set_align(["center", "left", "left", "left"]);

    $objeto->set_rowspan(1);
    $objeto->set_grupoCorColuna(1);

    $objeto->set_colunaSomatorio(4);

    $objeto->set_classe([null, null, null, null, "Pessoal", null, "Pessoal"]);
    $objeto->set_metodo([null, null, null, null, "get_numServidoresAtivosCargo", null, "get_numServidoresInativosCargo"]);

    # Ver servidores ativos
    $servAtivos = new Link(null, "?fase=aguardeAtivos&id={$id}");
    $servAtivos->set_imagem(PASTA_FIGURAS_GERAIS . 'olho.png', 20, 20);
    $servAtivos->set_title("Exibe os servidores ativos");

    # Ver servidores inativos
    $servInativos = new Link(null, "?fase=aguardeInativos&id={$id}");
    $servInativos->set_imagem(PASTA_FIGURAS_GERAIS . 'olho.png', 20, 20);
    $servInativos->set_title("Exibe os servidores inativos");

    # Coloca o objeto link na tabela			
    $objeto->set_link([null, null, null, null, null, $servAtivos, null, $servInativos]);

    # Classe do banco de dados
    $objeto->set_classBd('Pessoal');

    # Nome da tabela
    $objeto->set_tabela('tbcargo');

    # Nome do campo id
    $objeto->set_idCampo('idCargo');

    # Tipo de label do formulário
    $objeto->set_formlabelTipo(1);

    # Pega os dados da combo de Plano e Cargos
    $result1 = $pessoal->select('SELECT idPlano, 
                                      numDecreto
                                  FROM tbplano
                              ORDER BY numDecreto');

    # Pega os dados da combo de Tipos de Cargos
    $result2 = $pessoal->select('SELECT idTipoCargo, 
                                        cargo
                                   FROM tbtipocargo
                               ORDER BY idTipoCargo desc');
    array_push($result2, array(null, null));

    # Pega os dados da combo de Área
    $result3 = $pessoal->select('SELECT idArea,
                                        CONCAT(tbtipocargo.cargo," - ",area)
                                  FROM tbarea JOIN tbtipocargo USING (idTipoCargo)
                              ORDER BY idarea desc');
    array_push($result3, array(null, null));

    # Campos para o formulario
    $objeto->set_campos(array(
        array('linha' => 1,
            'col' => 6,
            'nome' => 'idtipocargo',
            'label' => 'Cargo:',
            'tipo' => 'combo',
            'required' => true,
            'array' => $result2,
            'size' => 30),
        array('linha' => 1,
            'col' => 6,
            'nome' => 'idarea',
            'label' => 'Área:',
            'tipo' => 'combo',
            'required' => true,
            'array' => $result3,
            'size' => 50),
        array('linha' => 2,
            'col' => 8,
            'nome' => 'nome',
            'label' => 'Função:',
            'tipo' => 'texto',
            'required' => true,
            'size' => 50),
        array('linha' => 2,
            'col' => 4,
            'nome' => 'idPlano',
            'label' => 'Plano de Cargos:',
            'tipo' => 'combo',
            'required' => true,
            'array' => $result1,
            'size' => 30),
        array('linha' => 4,
            'col' => 8,
            'nome' => 'atribuicoes',
            'label' => 'Atribuições do Cargo:',
            'tipo' => 'textarea',
            'size' => array(40, 15)),
        array('linha' => 4,
            'col' => 4,
            'nome' => 'obs',
            'label' => 'Observação:',
            'tipo' => 'textarea',
            'size' => array(40, 15))));

    # idUsuário para o Log
    $objeto->set_idUsuario($idUsuario);

    ################################################################

    switch ($fase) {
        case "" :
        case "listar" :
            $objeto->listar();

            # Div da listagem de servidores
            $divServidores = new div('divServidores');
            $divServidores->abre();
            $divServidores->fecha();
            break;

        ################################################################    

        case "editar" :
            $objeto->$fase($id);
            break;

        ################################################################    

        case "excluir" :
            # Verifica se tem servidores nesse cargo            
            if ($pessoal->get_numServidoresAtivosCargo($id) > 0 OR $pessoal->get_numServidoresInativosCargo($id) > 0) {
                alert("Não é possível excluir um cargo com servidores cadastrados !!");
                back(1);
            } else {
                # Se não tiver exclui o cargo
                $objeto->excluir($id);
            }
            break;

        ################################################################

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

        case "exibeServidoresAtivos" :
            # Limita o tamanho da tela
            $grid = new Grid();
            $grid->abreColuna(12);

            # Informa a origem
            set_session('origem', 'cadastroFuncao.php?fase=exibeServidoresAtivos&id=' . $id);

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
            $botaoRel->set_url("?fase=relatorio&id=$id");
            $botaoRel->set_imagem($imagem2);
            $menu->add_link($botaoRel, "right");

            $menu->show();

            # Lista de Servidores Ativos
            $lista = new ListaServidores('Servidores Ativos - Cargo: ' . $pessoal->get_nomeCargo($id));
            $lista->set_situacao(1);
            $lista->set_cargo($id);
            $lista->showTabela();

            $grid->fechaColuna();
            $grid->fechaGrid();
            break;

        ################################################################

        case "relatorio" :
            # Lista de Servidores Ativos
            $lista = new ListaServidores('Servidores Ativos');
            $lista->set_situacao(1);
            $lista->set_cargo($id);
            $lista->showRelatorio();
            break;

        ################################################################

        case "aguardeInativos" :
            br(10);
            aguarde("Montando a Listagem");
            br();
            loadPage('?fase=exibeServidoresInativos&id=' . $id);
            break;

        ################################################################

        case "exibeServidoresInativos" :
            # Limita o tamanho da tela
            $grid = new Grid();
            $grid->abreColuna(12);

            # Informa a origem
            set_session('origem', 'cadastroFuncao.php?fase=exibeServidoresInativos&id=' . $id);

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

            # Lista de Servidores Ativos
            $lista = new ListaServidores('Servidores Inativos - Cargo: ' . $pessoal->get_nomeCargo($id));
            $lista->set_situacao(1);
            $lista->set_situacaoSinal("<>");
            $lista->set_cargo($id);
            $lista->showTabela();

            $grid->fechaColuna();
            $grid->fechaGrid();
            break;

        ################################################################

        case "relatorioInativo" :
            # Lista de Servidores Inativos
            $lista = new ListaServidores('Servidores Inativos');
            $lista->set_situacao(1);
            $lista->set_situacaoSinal("<>");
            $lista->set_cargo($id);
            $lista->showRelatorio();
            break;
    }
    $page->terminaPagina();
} else {
    loadPage("../../areaServidor/sistema/login.php");
}