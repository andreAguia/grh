<?php

/**
 * Dados de Ordenação de Despesas
 *  
 * By Alat
 */
# Servidor logado
$idUsuario = null;

# Servidor Editado na pesquisa do sistema do GRH
$idServidorPesquisado = null;

# Configuração
include ("_config.php");

# Permissão de Acesso
$acesso = Verifica::acesso($idUsuario, [1, 2, 12]);

if ($acesso) {
    # Conecta ao Banco de Dados    
    $pessoal = new Pessoal();
    $cargoComissao = new CargoComissao();
    $intra = new Intra();

    # Verifica se veio menu grh e registra o acesso no log
    $grh = get('grh', false);
    if ($grh) {
        # Grava no log a atividade
        $atividade = "Cadastro do servidor - Controle de ordenação de despesas";
        $data = date("Y-m-d H:i:s");
        $intra->registraLog($idUsuario, $data, $atividade, null, null, 7, $idServidorPesquisado);
    }

    # Verifica a fase do programa
    $fase = get('fase', 'listar');

    # pega o id (se tiver)
    $id = soNumeros(get('id'));

    # Pega o origem quando vier do cadastro de Cargo em comissão
    $origem = get_session('origem');

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
    # Exibe os dados do Servidor
    $objeto->set_rotinaExtra("get_DadosServidor");
    $objeto->set_rotinaExtraParametro($idServidorPesquisado);

    # Nome do Modelo (aparecerá nos fildset e no caption da tabela)
    $objeto->set_nome('Controle de Designação para Ordenador de Despesas');

    # botão de voltar da lista
    $objeto->set_voltarLista('servidorMenu.php');

    # select da lista
    $objeto->set_selectLista('SELECT descricao,
                                     idComissao,
                                     idOrdenador,
                                     idOrdenador,
                                     obs,                                     
                                     idOrdenador
                                FROM tbordenador
                               WHERE idServidor = ' . $idServidorPesquisado . '
                            ORDER BY dtDesignacao desc');

    # select do edita
    $objeto->set_selectEdita('SELECT descricao,
                                     idComissao,
                                     dtDesignacao,
                                     dtPublicDesignacao,
                                     pgPublicDesignacao,
                                     numProcDesignacao,
                                     dtAtoDesignacao,
                                     dtTermino,
                                     dtPublicTermino,
                                     pgPublicTermino,
                                     numProcTermino,
                                     dtAtoTermino,
                                     obs,
                                     idServidor
                                FROM tbordenador
                               WHERE idOrdenador = ' . $id);
    
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
    $objeto->set_label(array("Descrição", "Cargo em Comissão<br/>Vinculado","Designação<br/>da Competência", " Término",  "Obs"));
    $objeto->set_width(array(20, 20, 20, 20, 20));
    $objeto->set_align(array("left", "center", "center", "left", "left"));
    #$objeto->set_funcao(array("date_to_php"));

    $objeto->set_classe(array(null, "Ordenador", "Ordenador", "Ordenador"));
    $objeto->set_metodo(array(null, "exibeDadosCargoComissaoVinculado", "exibeDadosDesignacao", "exibeDadosTermino"));

    # Classe do banco de dados
    $objeto->set_classBd('pessoal');

    # Nome da tabela
    $objeto->set_tabela('tbordenador');

    # Nome do campo id
    $objeto->set_idCampo('idOrdenador');

    # Pega os dados da combo idComissao
    $cargo = $pessoal->select("SELECT idComissao,
                                      concat(tbtipocomissao.simbolo,' - ',tbtipocomissao.descricao)
                                 FROM tbcomissao LEFT JOIN tbtipocomissao USING (idTipoComissao)
                                 WHERE idServidor = {$idServidorPesquisado}
                             ORDER BY tbcomissao.dtNom");

    array_unshift($cargo, array(0, null));

    # Campos para o formulario
    $objeto->set_campos(array(
        array(
            'nome' => 'descricao',
            'label' => 'Descrição:',
            'tipo' => 'texto',
            'size' => 100,
            'col' => 8,
            'required' => true,
            'autofocus' => true,
            'title' => 'Descrição da ordenação de despesa específica',
            'linha' => 1),
        array(
            'linha' => 1,
            'nome' => 'idComissao',
            'label' => 'Vinculado ao cargo:',
            'tipo' => 'combo',
            'array' => $cargo,
            'title' => 'Cargo',
            'col' => 4,
            'size' => 30),
        array(
            'nome' => 'dtDesignacao',
            'label' => 'Data da Designação:',
            'fieldset' => 'Designação da Competência',
            'tipo' => 'data',
            'size' => 20,
            'required' => true,
            'title' => 'Data da Designação.',
            'col' => 3,
            'linha' => 2),
        array(
            'nome' => 'dtAtoDesignacao',
            'label' => 'Data do Ato do Reitor:',
            'title' => 'Data do Ato do Reitor da Designacao',
            'tipo' => 'data',
            'size' => 20,
            'col' => 3,
            'linha' => 2),
        array(
            'nome' => 'numProcDesignacao',
            'label' => 'Processo:',
            'tipo' => 'processo',
            'size' => 30,
            'title' => 'Número do Processo',
            'col' => 3,
            'linha' => 2),
        array(
            'nome' => 'dtPublicDesignacao',
            'label' => 'Data da Publicação:',
            'tipo' => 'data',
            'size' => 20,
            'col' => 3,
            'title' => 'Data da Publicação no DOERJ.',
            'linha' => 3),
        array(
            'nome' => 'pgPublicDesignacao',
            'label' => 'Página da Publicação:',
            'tipo' => 'texto',
            'size' => 20,
            'col' => 3,
            'title' => 'Página da Publicação no DOERJ.',
            'linha' => 3),
        array(
            'nome' => 'dtTermino',
            'label' => 'Data do Término:',
            'fieldset' => 'Término',
            'tipo' => 'data',
            'size' => 20,
            'title' => 'Data do Término.',
            'col' => 3,
            'linha' => 4),
        array(
            'nome' => 'dtAtoTermino',
            'label' => 'Data do Ato do Reitor:',
            'title' => 'Data do ato do reitor do término da designacao',
            'tipo' => 'data',
            'size' => 20,
            'col' => 3,
            'linha' => 4),
        array(
            'nome' => 'numProcTermino',
            'label' => 'Processo:',
            'tipo' => 'processo',
            'size' => 30,
            'title' => 'Número do Processo',
            'col' => 3,
            'linha' => 4),
        array(
            'nome' => 'dtPublicTermino',
            'label' => 'Data da Publicação:',
            'tipo' => 'data',
            'size' => 20,
            'col' => 3,
            'title' => 'Data da Publicação no DOERJ.',
            'linha' => 5),
        array(
            'nome' => 'pgPublicTermino',
            'label' => 'Página da Publicação:',
            'tipo' => 'texto',
            'size' => 20,
            'col' => 3,
            'title' => 'Página da Publicação no DOERJ.',
            'linha' => 5),
        array(
            'linha' => 6,
            'nome' => 'obs',
            'col' => 12,
            'label' => 'Observação:',
            'tipo' => 'textarea',
            'fieldset' => 'fecha',
            'size' => array(80, 4)),
        array(
            'nome' => 'idServidor',
            'label' => 'idServidor:',
            'tipo' => 'hidden',
            'padrao' => $idServidorPesquisado,
            'size' => 5,
            'title' => 'Matrícula',
            'linha' => 5)));

    # Log
    $objeto->set_idUsuario($idUsuario);
    $objeto->set_idServidorPesquisado($idServidorPesquisado);

    # Constroi o link de voltar de acordo com a origem
    if (!vazio($origem)) {
        $objeto->set_linkListar($origem);
        $objeto->set_voltarForm($origem);
    }

    ################################################################

    switch ($fase) {

        case "" :
        case "listar" :
            $objeto->listar();
            break;

        ######################################   

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