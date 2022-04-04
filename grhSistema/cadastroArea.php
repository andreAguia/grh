<?php

/**
 * Cadastro de Área
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

    # pega o id (se tiver)
    $id = soNumeros(get('id'));

    # Verifica a paginacão
    #$paginacao = get('paginacao',get_session('sessionPaginacao',0));	// Verifica se a paginação vem por get, senão pega a session
    #set_session('sessionPaginacao',$paginacao);                         // Grava a paginação na session
    # Pega o parametro de pesquisa (se tiver)
    if (is_null(post('parametro')))     # Se o parametro n?o vier por post (for nulo)
        $parametro = retiraAspas(get_session('sessionParametro'));# passa o parametro da session para a variavel parametro retirando as aspas
    else {
        $parametro = post('parametro');                # Se vier por post, retira as aspas e passa para a variavel parametro
        set_session('sessionParametro', $parametro);    # transfere para a session para poder recuperá-lo depois
    }

    # Começa uma nova página
    $page = new Page();
    $page->iniciaPagina();

    # Cabeçalho da Página
    if ($fase <> "relatorioAtivo" AND $fase <> "relatorioInativo") {
        AreaServidor::cabecalho();
    }

    # Abre um novo objeto Modelo
    $objeto = new Modelo();

    ################################################################
    # Nome do Modelo (aparecerá nos fildset e no caption da tabela)
    $objeto->set_nome('Área');

    # botão de voltar da lista
    $objeto->set_voltarLista('areaCargoEfetivo.php');

    # controle de pesquisa
    $objeto->set_parametroLabel('Pesquisar');
    $objeto->set_parametroValue($parametro);

    # select da lista
    $select = "SELECT idarea,
                       tbtipocargo.sigla,
                       area,
                       descricao,
                       requisitos,
                       idarea,
                       idarea,
                        idarea,
                       idarea
                  FROM tbarea LEFT JOIN tbtipocargo USING (idTipoCargo)
                 WHERE area LIKE '%$parametro%'
              ORDER BY 1 asc";

    $objeto->set_selectLista($select);

    # select do edita
    $objeto->set_selectEdita('SELECT area,
                                     idTipoCargo,
                                     descricao,
                                     requisitos,
                                     obs
                                FROM tbarea
                               WHERE idarea = ' . $id);

    # Caminhos
    if (Verifica::acesso($idUsuario, 1)) {      // Excluir somente admin
        $objeto->set_linkExcluir('?fase=excluir');
    }
    $objeto->set_linkEditar('?fase=editar');
    $objeto->set_linkGravar('?fase=gravar');
    $objeto->set_linkListar('?fase=listar');

    # Parametros da tabela
    $objeto->set_label(["id", "Cargo", "Area", "Descrição Sintética da Área", "Requisitos para Provimento", "Servidores<br/>Ativos", "Ver", "Servidores<br/>Inativos", "Ver"]);
    $objeto->set_width([5, 5, 15, 30, 20, 5, 5, 5, 5]);
    $objeto->set_align(["center", "center", "left", "left", "left"]);

    $objeto->set_rowspan(1);
    $objeto->set_grupoCorColuna(1);

    $objeto->set_colunaSomatorio([5, 7]);
    $objeto->set_totalRegistro(false);

    $objeto->set_classe([null, null, null, null, null, "CargoArea", null, "CargoArea"]);
    $objeto->set_metodo([null, null, null, null, null, "get_numServidoresAtivos", null, "get_numServidoresInativos"]);

    # Ver servidores ativos
    $servAtivos = new Link(null, "?fase=aguardeAtivos&id={$id}");
    $servAtivos->set_imagem(PASTA_FIGURAS_GERAIS . 'olho.png', 20, 20);
    $servAtivos->set_title("Exibe os servidores ativos");

    # Ver servidores inativos
    $servInativos = new Link(null, "?fase=aguardeInativos&id={$id}");
    $servInativos->set_imagem(PASTA_FIGURAS_GERAIS . 'olho.png', 20, 20);
    $servInativos->set_title("Exibe os servidores inativos");

    # Coloca o objeto link na tabela			
    $objeto->set_link([null, null, null, null, null, null, $servAtivos, null, $servInativos]);

    # Classe do banco de dados
    $objeto->set_classBd('Pessoal');

    # Nome da tabela
    $objeto->set_tabela('tbarea');

    # Nome do campo id
    $objeto->set_idCampo('idarea');

    # Tipo de label do formulário
    $objeto->set_formlabelTipo(1);

    # Pega os dados da combo de Tipos de Cargos
    $result2 = $pessoal->select('SELECT idTipoCargo, 
                                      cargo
                                  FROM tbtipocargo
                              ORDER BY idTipoCargo desc');
    array_unshift($result2, array(null, null));

    # Campos para o formulario
    $objeto->set_campos(array(
        array('linha' => 1,
            'col' => 6,
            'nome' => 'area',
            'label' => 'Área:',
            'tipo' => 'texto',
            'autofocus' => true,
            'required' => true,
            'size' => 50),
        array('linha' => 1,
            'col' => 6,
            'nome' => 'idTipoCargo',
            'label' => 'Cargo:',
            'tipo' => 'combo',
            'required' => true,
            'array' => $result2,
            'size' => 30),
        array('linha' => 2,
            'col' => 6,
            'nome' => 'descricao',
            'label' => 'Descrição Sintética da Área:',
            'tipo' => 'textarea',
            'size' => array(40, 7)),
        array('linha' => 2,
            'col' => 6,
            'nome' => 'requisitos',
            'label' => 'Requisitos para Provimento:',
            'tipo' => 'textarea',
            'size' => array(40, 7)),
        array('linha' => 2,
            'col' => 12,
            'nome' => 'obs',
            'label' => 'Observação:',
            'tipo' => 'textarea',
            'size' => array(40, 5))));

    # idUsuário para o Log
    $objeto->set_idUsuario($idUsuario);

    ################################################################

    switch ($fase) {
        case "" :
        case "listar" :
            $objeto->listar();
            break;

        case "excluir" :
            # Verifica se tem servidores cargos com essa área
            $cargoarea = new CargoArea();
            if ($cargoarea->get_numCargoArea($id) > 0) {
                alert("Existem cargos cadastrados nessa área. Não é possível excluí-la!!");
                back(1);
            } else {
                # Se não tiver exclui
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


        case "exibeServidoresAtivos" :
            # Limita o tamanho da tela
            $grid = new Grid();
            $grid->abreColuna(12);

            # Informa a origem
            set_session('origem', 'cadastroArea.php?fase=exibeServidoresAtivos&id=' . $id);

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

            # Lista de Servidores Ativos
            $lista = new ListaServidores("Servidores Ativos - Area: {$pessoal->get_area($id)}");
            $lista->set_situacao(1);
            $lista->set_area($id);
            $lista->showTabela();
            $grid->fechaColuna();
            $grid->fechaGrid();
            break;

        ################################################################

        case "relatorioAtivo" :
            # Pega o nome do tipo de cargo
            $nomeTipo = $pessoal->get_nomeTipoCargo($id);

            # Lista de Servidores Ativos
            $lista = new ListaServidores("Servidores Ativos - Area: {$pessoal->get_area($id)}");
            $lista->set_situacao(1);
            $lista->set_area($id);
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
            set_session('origem', 'cadastroArea.php?fase=exibeServidoresInativos&id=' . $id);

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
            $lista = new ListaServidores("Servidores Ativos - Area: {$pessoal->get_area($id)}");
            $lista->set_situacao(1);
            $lista->set_situacaoSinal("<>");
            $lista->set_area($id);
            $lista->showTabela();
            $grid->fechaColuna();
            $grid->fechaGrid();
            break;

        ################################################################

        case "relatorioInativo" :
            # Pega o nome do tipo de cargo
            $nomeTipo = $pessoal->get_nomeTipoCargo($id);

            # Lista de Servidores Ativos
            $lista = new ListaServidores("Servidores Inativos - Area: {$pessoal->get_area($id)}");
            $lista->set_situacao(1);
            $lista->set_situacaoSinal("<>");
            $lista->set_area($id);
            $lista->showRelatorio();
            break;

        ################################################################
    }

    $page->terminaPagina();
} else {
    loadPage("../../areaServidor/sistema/login.php");
}