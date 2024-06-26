<?php

/**
 * Cadastro de Banco
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
        $atividade = "Visualizou o cadastro de bancos";
        $data = date("Y-m-d H:i:s");
        $intra->registraLog($idUsuario, $data, $atividade, null, null, 7);
    }

    # pega o id (se tiver)
    $id = soNumeros(get('id'));

    # Começa uma nova página
    $page = new Page();
    $page->iniciaPagina();

    # Cabeçalho da Página
    AreaServidor::cabecalho();

    # Abre um novo objeto Modelo
    $objeto = new Modelo();

    ################################################################
    # Nome do Modelo
    $objeto->set_nome('Banco');

    # Botão de voltar da lista
    $objeto->set_voltarLista('grh.php');

    # select da lista
    $objeto->set_selectLista("SELECT idbanco,
                                      codigo,
                                      banco,
                                      obs,
                                      idbanco
                                 FROM tbbanco
                             ORDER BY codigo");

    # select do edita
    $objeto->set_selectEdita("SELECT codigo,
                                     banco,
                                     obs
                                FROM tbbanco
                               WHERE idbanco = {$id}");

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
    $objeto->set_label(["Id", "Código", "Banco", "Obs"]);
    $objeto->set_width([5, 10, 30, 45]);
    $objeto->set_align(["center", "center", "left", "left"]);

    # Classe do banco de dados
    $objeto->set_classBd('Pessoal');

    # Nome da tabela
    $objeto->set_tabela('tbbanco');

    # Nome do campo id
    $objeto->set_idCampo('idbanco');

    # Campos para o formulario
    $objeto->set_campos(array(
        array('linha' => 1,
            'nome' => 'codigo',
            'label' => 'Código:',
            'tipo' => 'texto',
            'required' => true,
            'autofocus' => true,
            'size' => 5,
            'col' => 3),
        array('linha' => 1,
            'nome' => 'banco',
            'label' => 'Banco:',
            'tipo' => 'texto',
            'required' => true,
            'col' => 6,
            'size' => 50),
        array('linha' => 2,
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