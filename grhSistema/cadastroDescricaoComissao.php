<?php

/**
 * Cadastro de Estado Civil
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
        $atividade = "Visualizou o cadastro de estado";
        $data = date("Y-m-d H:i:s");
        $intra->registraLog($idUsuario, $data, $atividade, NULL, NULL, 7);
    }

    # pega o id (se tiver)
    $id = soNumeros(get('id'));

    # Pega o parametro de pesquisa (se tiver)
    if (is_null(post('parametro'))) {            # Se o parametro n?o vier por post (for nulo)
        $parametro = retiraAspas(get_session('sessionParametro')); # passa o parametro da session para a variavel parametro retirando as aspas
    } else {
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
    AreaServidor::cabecalho();

    # Abre um novo objeto Modelo
    $objeto = new Modelo();

    ################################################################
    # Nome do Modelo (aparecerá nos fildset e no caption da tabela)
    $objeto->set_nome('Cadastro de Descrição do Cargo em Comissão');

    # botão de voltar da lista
    $objeto->set_voltarLista('areaCargoComissao.php');

    # controle de pesquisa
    $objeto->set_parametroLabel('Pesquisar');
    $objeto->set_parametroValue($parametro);

    # select da lista
    $objeto->set_selectLista('SELECT idDescricaoComissao,
                                      CONCAT(tbtipocomissao.simbolo," - (",tbtipocomissao.descricao,")") as comissao,
                                      tbdescricaocomissao.descricao,
                                      if(tbdescricaocomissao.ativo = 0,"Não","Sim")
                                 FROM tbdescricaocomissao JOIN tbtipocomissao USING (idTipoComissao)
                                WHERE tbdescricaocomissao.descricao LIKE "%' . $parametro . '%"
                                   OR tbtipocomissao.descricao LIKE "%' . $parametro . '%"
                                   OR tbtipocomissao.simbolo LIKE "%' . $parametro . '%"
                             ORDER BY tbtipocomissao.simbolo, tbtipocomissao.descricao,  tbdescricaocomissao.descricao');

    # select do edita
    $objeto->set_selectEdita('SELECT idTipoComissao,
                                     ativo,
                                     descricao,
                                     obs
                                FROM tbdescricaocomissao
                               WHERE idDescricaoComissao = ' . $id);

    # Caminhos
    $objeto->set_linkEditar('?fase=editar');
    $objeto->set_linkExcluir('?fase=excluir');
    $objeto->set_linkGravar('?fase=gravar');
    $objeto->set_linkListar('?fase=listar');

    # Parametros da tabela
    $objeto->set_label(array("Id", "Cargo em Comissão", "Descrição", "Ativo"));
    #$objeto->set_width(array(5,70,10));
    $objeto->set_align(array("center", "left", "left", "center"));

    $objeto->set_rowspan(1);
    $objeto->set_grupoCorColuna(1);

    # Classe do banco de dados
    $objeto->set_classBd('Pessoal');

    # Nome da tabela
    $objeto->set_tabela('tbdescricaocomissao');

    # Nome do campo id
    $objeto->set_idCampo('idDescricaoComissao');

    # Tipo de label do formulário
    $objeto->set_formlabelTipo(1);

    # Pega os dados da combo tipo de Comissão
    $comissao = $pessoal->select('SELECT idTipoComissao,
                                         CONCAT(tbtipocomissao.simbolo," - (",tbtipocomissao.descricao,")") as comissao
                                    FROM tbtipocomissao
                                ORDER BY ativo desc, simbolo');

    array_unshift($comissao, array(NULL, NULL));

    # Campos para o formulario
    $objeto->set_campos(array(
        array('nome' => 'idTipoComissao',
            'label' => 'Tipo da Cargo em Comissão:',
            'tipo' => 'combo',
            'required' => TRUE,
            'autofocus' => TRUE,
            'array' => $comissao,
            'size' => 20,
            'col' => 9,
            'title' => 'Tipo dp Cargo em Comissão',
            'linha' => 1),
        array('linha' => 1,
            'col' => 3,
            'nome' => 'ativo',
            'required' => TRUE,
            'label' => 'Ativo:',
            'title' => 'Se o cargo está ativo e permite movimentações',
            'tipo' => 'combo',
            'array' => array(array(1, 'Sim'), array(0, 'Não')),
            'padrao' => 1,
            'size' => 5),
        array('linha' => 2,
            'nome' => 'descricao',
            'label' => 'Descrição:',
            'tipo' => 'texto',
            'required' => TRUE,
            'col' => 12,
            'size' => 250),
        array('linha' => 3,
            'nome' => 'obs',
            'label' => 'Observação:',
            'tipo' => 'textarea',
            'col' => 12,
            'size' => array(80, 5))));

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