<?php

/**
 * Cadastro de Estado Civil
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
        $atividade = "Visualizou o cadastro de categoria de habilitação";
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
    $page->iniciaPagina();

    # Cabeçalho da Página
    AreaServidor::cabecalho();

    # Abre um novo objeto Modelo
    $objeto = new Modelo();

    ################################################################
    # Nome do Modelo (aparecerá nos fildset e no caption da tabela)
    $objeto->set_nome('Categoria de Habilitação');

    # botão de voltar da lista
    $objeto->set_voltarLista('grh.php');

    # controle de pesquisa
    $objeto->set_parametroLabel('Pesquisar');
    $objeto->set_parametroValue($parametro);

    # select da lista
    $objeto->set_selectLista("SELECT idHabilitacaoCategoria,
                                     categoria,
                                     tipo,
                                     exemplo
                                 FROM tbhabilitacaocategoria
                                 ORDER BY idHabilitacaoCategoria");
    # select do edita
    $objeto->set_selectEdita("SELECT categoria,
                                     tipo,
                                     exemplo
                                 FROM tbhabilitacaocategoria
                               WHERE idHabilitacaoCategoria = {$id}");

    # Habilita o modo leitura para usuario de regra 12
    if (Verifica::acesso($idUsuario, 12)) {
        $objeto->set_modoLeitura(true);
    }

    # Caminhos
    $objeto->set_linkEditar('?fase=editar');
    $objeto->set_linkExcluir('?fase=excluir');
    $objeto->set_linkGravar('?fase=gravar');
    $objeto->set_linkListar('?fase=listar');

    # Parametros da tabela
    $objeto->set_label(["Id", "Categoria", "Tipo de Veículo", "Exemplo"]);
    $objeto->set_width([5, 10, 38, 38]);
    $objeto->set_align(["center", "center", "left", "left"]);

    # Classe do banco de dados
    $objeto->set_classBd('Pessoal');

    # Nome da tabela
    $objeto->set_tabela('tbhabilitacaocategoria');

    # Nome do campo id
    $objeto->set_idCampo('idHabilitacaoCategoria');

    # Campos para o formulario
    $objeto->set_campos(array(
        array('linha' => 1,
            'nome' => 'categoria',
            'label' => 'Categoria:',
            'tipo' => 'texto',
            'required' => true,
            'autofocus' => true,
            'col' => 2,
            'size' => 5),
        array('linha' => 2,
            'nome' => 'tipo',
            'label' => 'Tipo de veículo:',
            'tipo' => 'texto',
            'required' => true,
            'col' => 12,
            'size' => 250),
        array('linha' => 3,
            'nome' => 'exemplo',
            'label' => 'Exemplo:',
            'tipo' => 'texto',
            'col' => 12,
            'size' => 250)
    ));

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
    }

    $page->terminaPagina();
} else {
    loadPage("../../areaServidor/sistema/login.php");
}