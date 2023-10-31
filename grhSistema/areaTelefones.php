<?php

/**
 * Cadastro de Telefones
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

    # Verifica se veio menu grh e registra o acesso no log
    $grh = get('grh', false);
    if ($grh) {
        # Grava no log a atividade
        $atividade = "Visualizou a area de telefones";
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

    # Começa uma nova página
    $page = new Page();
    #$page->set_jscript("<script>CKEDITOR.replace('ramais');</script>");
    $page->iniciaPagina();

    # Cabeçalho da Página
    if ($fase <> "relatorio") {
        AreaServidor::cabecalho();
    }

    # Abre um novo objeto Modelo
    $objeto = new Modelo();

    ################################################################
    $objeto->set_nome($pessoal->get_nomeCompletoLotacao($id));

    # botão de voltar da lista
    $objeto->set_voltarLista('grh.php');

    # controle de pesquisa
    $objeto->set_parametroLabel('Pesquisar');
    $objeto->set_parametroValue($parametro);

    # select da lista
    $objeto->set_selectLista("SELECT DIR,
                                      GER,
                                      nome,
                                      ramais,
                                      email,
                                      idLotacao,
                                      idLotacao
                                 FROM tblotacao
                                WHERE ativo
                                  AND (DIR LIKE '%$parametro%'
                                   OR GER LIKE '%$parametro%'
                                   OR nome LIKE '%$parametro%'
                                   OR ramais LIKE '%$parametro%')
                             ORDER BY DIR asc, GER asc, nome asc");

    # select do edita
    $objeto->set_selectEdita("SELECT ramais,
                                     email
                                FROM tblotacao
                               WHERE idLotacao = {$id}");

    # Habilita o modo leitura para usuario de regra 12
    if (Verifica::acesso($idUsuario, 12)) {
        $objeto->set_modoLeitura(true);
    }

    # Caminhos
    if (Verifica::acesso($idUsuario, [1, 2])) {
        $objeto->set_linkEditar('?fase=editar');
    }
    #$objeto->set_linkExcluir('?fase=excluir');     // Retirado para evidar exclusão acidental
    $objeto->set_linkGravar('?fase=gravar');
    $objeto->set_linkListar('?fase=listar');

    $objeto->set_botaoIncluir(false);

    # Parametros da tabela
    $objeto->set_titulo('Área de Telefones');
    $objeto->set_subtitulo('Para transferir clica em OK, digita o ramal desejado, espera começar a chamar e desligue.');
    $objeto->set_label(["Diretoria", "Gerência", "Nome", "Telefones / Ramais", "Email", "Servidores"]);
    $objeto->set_align(["center", "center", "left", "left", "left", "center"]);
    $objeto->set_funcao([null, null, null, "nl2br2", "espaco2br"]);

    # Ver servidores
    $servAtivos = new Link(null, '?fase=listaServidoresAtivos&id=' . $id);
    $servAtivos->set_imagem(PASTA_FIGURAS_GERAIS . 'olho.png', 20, 20);
    $servAtivos->set_title("Exibe os servidores desta lotação");

    # Coloca o objeto link na tabela			
    $objeto->set_link([null, null, null, null, null, $servAtivos]);

    $objeto->set_rowspan(0);
    $objeto->set_grupoCorColuna(0);

    # Classe do banco de dados
    $objeto->set_classBd('Pessoal');

    # Nome da tabela
    $objeto->set_tabela('tblotacao');

    # Nome do campo id
    $objeto->set_idCampo('idLotacao');

    # Tipo de label do formulário
    $objeto->set_formlabelTipo(1);

    # Campos para o formulario
    $objeto->set_campos(array(
        array('linha' => 1,
            'col' => 12,
            'nome' => 'ramais',
            'label' => 'Ramais:',
            'title' => 'Número dos telefones/ramais/faxes da lotação',
            'tipo' => 'textarea',
            'autofocus' => true,
            'size' => array(80, 8)),
        array('linha' => 2,
            'col' => 12,
            'nome' => 'email',
            'label' => 'Email:',
            'title' => 'Email do Setor',
            'tipo' => 'texto',
            'size' => 50)));

    # idUsuário para o Log
    $objeto->set_idUsuario($idUsuario);

    ################################################################

    switch ($fase) {
        case "" :
        case "listar" :
            $objeto->listar();
            break;

        case "editar" :
        case "excluir" :
        case "gravar" :
            $objeto->$fase($id);
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
            $botaoRel->set_url("?fase=relatorio&id=$id");
            $botaoRel->set_imagem($imagem2);
            $menu->add_link($botaoRel, "right");

            $menu->show();

            # Limita o tamanho da tela
            $grid = new Grid();
            $grid->abreColuna(12);

            # Lista de Servidores Ativos
            $lista = new ListaServidores('Servidores da Lotação: ' . $pessoal->get_nomeLotacao($id));
            $lista->set_situacao(1);
            $lista->set_lotacao($id);
            $lista->showTabela();

            $grid->fechaColuna();
            $grid->fechaGrid();
            break;

        ################################################################

        case "relatorio" :
            # Lista de Servidores Ativos
            $lista = new ListaServidores('Servidores Ativos');
            $lista->set_situacao(1);
            $lista->set_lotacao($id);
            $lista->showRelatorio();
            break;
    }

    $page->terminaPagina();
} else {
    loadPage("../../areaServidor/sistema/login.php");
}