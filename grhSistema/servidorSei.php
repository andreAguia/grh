<?php

/**
 * Cadastro de documentos no Sei
 *  
 * By Alat
 */
# Inicia as variáveis que receberão as sessions
$idUsuario = null;
$idServidorPesquisado = null;

# Configuração
include ("_config.php");

# Permissão de Acesso
$acesso = Verifica::acesso($idUsuario, [1, 2, 12]);

if ($acesso) {
    # Verifica a fase do programa
    $fase = get('fase', 'listar');

    # pega o id (se tiver)
    $id = soNumeros(get('id'));

    # Pega o parametro de pesquisa (se tiver)
    if (is_null(post('parametro'))) {     # Se o parametro não vier por post (for nulo)
        $parametro = retiraAspas(get_session('sessionParametro'));
    } else {
        $parametro = post('parametro');                # Se vier por post, retira as aspas e passa para a variavel parametro
        set_session('sessionParametro', $parametro);    # transfere para a session para poder recuperá-lo depois
    }

    # Conecta ao Banco de Dados
    $pessoal = new Pessoal();
    $intra = new Intra();

    # Verifica se veio menu grh e registra o acesso no log
    $grh = get('grh', false);
    if ($grh) {
        # Grava no log a atividade
        $atividade = "Cadastro do servidor - Documentos no Sei";
        $data = date("Y-m-d H:i:s");
        $intra->registraLog($idUsuario, $data, $atividade, null, null, 7, $idServidorPesquisado);
    }

    # Começa uma nova página
    $page = new Page();
    $page->iniciaPagina();

    # Cabeçalho da Página
    AreaServidor::cabecalho();

    # Abre um novo objeto Modelo
    $objeto = new Modelo();

    ################################################################
    # Exibe os dados do Servidor
    $objeto->set_rotinaExtra("get_DadosServidor");
    $objeto->set_rotinaExtraParametro($idServidorPesquisado);
    
    $objeto->set_rotinaExtraEditar("callout");
    $objeto->set_rotinaExtraEditarParametro("O processo deverá ser no formato: SEI-260009/XXXXXX/XXXX");

    # Nome do Modelo (aparecerá nos fildset e no caption da tabela)
    $objeto->set_nome('Cadastro de Documentos no Sei');

    # botão de voltar da lista
    $objeto->set_voltarLista('servidorMenu.php');

    # controle de pesquisa
    $objeto->set_parametroLabel('Pesquisar');
    $objeto->set_parametroValue($parametro);

    # select da lista
    $objeto->set_selectLista('SELECT tbseicategoria.categoria,
                                     descricao,
                                     CONCAT("SEI-",numero),
                                     idSei
                                FROM tbsei LEFT JOIN tbseicategoria USING (idSeiCategoria)
                          WHERE idServidor=' . $idServidorPesquisado . '
                            AND (tbseicategoria.categoria LIKE "%' . $parametro . '%"
                                 OR descricao LIKE "%' . $parametro . '%" 
                                 OR CONCAT("SEI-",numero) LIKE "%' . $parametro . '%")
                       ORDER BY tbseicategoria.categoria desc');

    # select do edita
    $objeto->set_selectEdita('SELECT idSeiCategoria,
                                     numero,
                                     descricao,
                                     idServidor
                                FROM tbsei
                               WHERE idSei = ' . $id);

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
    $objeto->set_label(array("Categoria", "Descrição", "Número"));
    $objeto->set_width(array(25, 45, 20));
    $objeto->set_align(array("center", "left", "left"));

    $objeto->set_rowspan(0);
    $objeto->set_grupoCorColuna(0);

    # Classe do banco de dados
    $objeto->set_classBd('Pessoal');

    # Nome da tabela
    $objeto->set_tabela('tbsei');

    # Nome do campo id
    $objeto->set_idCampo('idSei');

    # Tipo de label do formulário
    $objeto->set_formLabelTipo(1);

    # Pega os dados da datalist curso
    $categorias = $pessoal->select('SELECT idSeiCategoria, categoria
                                       FROM tbseicategoria
                                   ORDER BY categoria');
    array_unshift($categorias, array(null, null));

    # Campos para o formulario
    $objeto->set_campos(array(
        array('nome' => 'idSeiCategoria',
            'label' => 'Categoria:',
            'tipo' => 'combo',
            'array' => $categorias,
            'size' => 100,
            'col' => 3,
            'required' => true,
            'title' => 'Descrição do Elogio ou Advertência.',
            'linha' => 2),
        array('nome' => 'numero',
            'label' => 'Número:',
            'tipo' => 'sei',
            'size' => 50,
            'col' => 6,
            'required' => true,
            'title' => 'Descrição do Elogio ou Advertência.',
            'linha' => 2),        
        array('nome' => 'descricao',
            'label' => 'Descrição:',
            'tipo' => 'texto',
            'size' => 200,
            'col' => 12,
            'required' => true,
            'title' => 'Descrição do Elogio ou Advertência.',
            'linha' => 2),
        array('nome' => 'idServidor',
            'label' => 'idServidor:',
            'tipo' => 'hidden',
            'padrao' => $idServidorPesquisado,
            'size' => 5,
            'title' => 'Matrícula',
            'linha' => 4)));

    # Log
    $objeto->set_idUsuario($idUsuario);
    $objeto->set_idServidorPesquisado($idServidorPesquisado);

    # Cadastro de Categoria
    if (Verifica::acesso($idUsuario, 1)) {
        $botaoCat = new Button("Categorias");
        $botaoCat->set_title("Acessa o Cadastro de Categorias");
        $botaoCat->set_url('cadastroSeiCategoria.php');

        $objeto->set_botaoListarExtra([$botaoCat]);
    }

    ################################################################

    switch ($fase) {
        case "" :
        case "listar" :
        case "editar" :
        case "excluir" :
            $objeto->$fase($id);
            break;

        case "gravar" :
            $objeto->gravar($id);
            break;
    }

    $page->terminaPagina();
} else {
    loadPage("../../areaServidor/sistema/login.php");
}