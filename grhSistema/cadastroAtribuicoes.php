<?php

/**
 * Cadastro de Atricuições
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
        $atividade = "Visualizou o cadastro de atribuiçoes";
        $data = date("Y-m-d H:i:s");
        $intra->registraLog($idUsuario, $data, $atividade, null, null, 7);
    }

    # pega o id (se tiver)
    $id = soNumeros(get('id'));

    # Pega o parametro de pesquisa (se tiver)
    if (is_null(post('parametro'))) {     # Se o parametro n?o vier por post (for nulo)
        $parametro = retiraAspas(get_session('sessionParametro'));
    } else {  # passa o parametro da session para a variavel parametro retirando as aspas
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
    $objeto->set_nome('Atribuições dos Servidores');

    # Botão de voltar da lista
    $objeto->set_voltarLista('grh.php');

    # controle de pesquisa
    $objeto->set_parametroLabel('Pesquisar');
    $objeto->set_parametroValue($parametro);

    # ordenaç?o
    if (is_null($orderCampo)) {
        $orderCampo = "1";
    }

    if (is_null($orderTipo)) {
        $orderTipo = 'asc';
    }

    # select da lista
    $objeto->set_selectLista('SELECT tarefa,
                                      encarregado1,
                                      encarregado2,
                                      encarregado3,
                                      idAtribuicao
                                 FROM tbatribuicao
                                WHERE tarefa LIKE "%' . $parametro . '%"
                                   OR encarregado1 LIKE "%' . $parametro . '%"
                                   OR encarregado2 LIKE "%' . $parametro . '%"
                                   OR encarregado3 LIKE "%' . $parametro . '%"    
                             ORDER BY ' . $orderCampo . ' ' . $orderTipo);

    # select do edita
    $objeto->set_selectEdita('SELECT tarefa,
                                     encarregado1,
                                     encarregado2,
                                     encarregado3
                                FROM tbatribuicao
                               WHERE idAtribuicao = ' . $id);

    # ordem da lista
    $objeto->set_orderCampo($orderCampo);
    $objeto->set_orderTipo($orderTipo);
    $objeto->set_orderChamador('?fase=listar');

    # Caminhos
    if (Verifica::acesso($idUsuario, [1, 7])) {
        $objeto->set_linkEditar('?fase=editar');
        $objeto->set_linkExcluir('?fase=excluir');
    } else {
        $objeto->set_botaoIncluir(false);
    }
    $objeto->set_linkGravar('?fase=gravar');
    $objeto->set_linkListar('?fase=listar');

    # Parametros da tabela
    $objeto->set_label(array("Tarefa", "Encarregado Principal", "Vice-Encarregado", "Sub-Vice-Encarregado"));
    $objeto->set_width(array(60, 10, 10, 10));
    $objeto->set_align(array("left"));

    # Classe do banco de dados
    $objeto->set_classBd('Pessoal');

    # Nome da tabela
    $objeto->set_tabela('tbatribuicao');

    # Nome do campo id
    $objeto->set_idCampo('idAtribuicao');

    # Tipo de label do formulário
    $objeto->set_formlabelTipo(1);

    # Cria combo de servidores da GRH
    $select = "SELECT tbpessoa.nome 
                 FROM tbpessoa JOIN tbservidor USING (idPessoa) 
                               JOIN tbhistlot USING (idServidor)
                               JOIN tblotacao ON (tbhistlot.lotacao = tblotacao.idLotacao)
                WHERE situacao = 1
                  AND tbhistlot.data = (select max(data) from tbhistlot where tbhistlot.idServidor = tbservidor.idServidor)
                  AND tbhistlot.lotacao = 66
                 ORDER BY tbpessoa.nome";

    $servGrh = $pessoal->select($select);

    # Inicia o array da combo
    $comboServidores[] = null;
    $comboServidores[] = "Todos";
    $comboServidores[] = "Bolsista";
    $comboServidores[] = "Gerente";

    # Adapta o array a combo
    foreach ($servGrh as $item) {
        $comboServidores[] = get_nomeSimples($item["nome"]);
    }

    # Campos para o formulario
    $objeto->set_campos(array(
        array('nome' => 'tarefa',
            'label' => 'Tarefa:',
            'tipo' => 'texto',
            'size' => 200,
            'required' => true,
            'autofocus' => true,
            'title' => 'Descriçao da Tarefa',
            'col' => 12,
            'linha' => 1),
        array('linha' => 2,
            'nome' => 'encarregado1',
            'label' => 'Encarregado Principal:',
            'tipo' => 'combo',
            'array' => $comboServidores,
            'required' => true,
            'col' => 4,
            'size' => 50),
        array('linha' => 2,
            'nome' => 'encarregado2',
            'label' => 'Vice-Encarregado:',
            'tipo' => 'combo',
            'array' => $comboServidores,
            'col' => 4,
            'size' => 50),
        array('linha' => 2,
            'nome' => 'encarregado3',
            'label' => 'Sub-Vice-Encarregado:',
            'tipo' => 'combo',
            'array' => $comboServidores,
            'col' => 4,
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
    }

    $page->terminaPagina();
} else {
    loadPage("../../areaServidor/sistema/login.php");
}