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
    AreaServidor::cabecalho();

    # Abre um novo objeto Modelo
    $objeto = new Modelo();

    ################################################################
    # Nome do Modelo (aparecerá nos fildset e no caption da tabela)
    $objeto->set_nome('Área');

    # botão de voltar da lista
    $objeto->set_voltarLista('cadastroCargo.php');

    # controle de pesquisa
    $objeto->set_parametroLabel('Pesquisar');
    $objeto->set_parametroValue($parametro);

    # select da lista
    $select = "SELECT idarea,
                       tbtipocargo.cargo,
                       area,
                       descricao,
                       requisitos,
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
    $objeto->set_label(array("id", "Cargo", "Area", "Descrição Sintética da Área", "Requisitos para Provimento", "Servidores<br/>Ativos"));
    $objeto->set_width(array(5, 12, 18, 30, 21, 5));
    $objeto->set_align(array("center", "left", "left", "left", "left"));

    $objeto->set_rowspan(1);
    $objeto->set_grupoCorColuna(1);

    $objeto->set_classe(array(null, null, null, null, null, "Pessoal"));
    $objeto->set_metodo(array(null, null, null, null, null, "get_servidoresArea"));

    # Botão de exibição dos servidores
    $botao = new BotaoGrafico();
    $botao->set_label('');
    $botao->set_url('?fase=listaServidores&id=');
    $botao->set_imagem(PASTA_FIGURAS_GERAIS . 'ver.png', 20, 20);

    # Coloca o objeto link na tabela			
    $objeto->set_link(array("", "", "", "", "", "", "", "", $botao));

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

    # Paginação
    #$objeto->set_paginacao(true);
    #$objeto->set_paginacaoInicial($paginacao);
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