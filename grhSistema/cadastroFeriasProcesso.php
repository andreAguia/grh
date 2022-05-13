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
        $atividade = "Visualizou o cadastro de processo de férias";
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
    $objeto->set_nome('Processo de Férias');

    # Botão de voltar da lista
    $objeto->set_voltarLista('areaFeriasExercicio.php');

    # select da lista
    $objeto->set_selectLista('SELECT lotacao,
                                     processo,
                                     obs,
                                     idFeriasProcesso
                                FROM tbferiasprocesso
                            ORDER BY lotacao');

    # select do edita
    $objeto->set_selectEdita('SELECT lotacao,
                                     processo,
                                     obs
                                FROM tbferiasprocesso
                               WHERE idFeriasProcesso = ' . $id);

    # Caminhos
    $objeto->set_linkEditar('?fase=editar');
    $objeto->set_linkExcluir('?fase=excluir');
    $objeto->set_linkGravar('?fase=gravar');
    $objeto->set_linkListar('?fase=listar');

    # Parametros da tabela
    $objeto->set_label(["Lotação", "Processo", "Obs"]);
    $objeto->set_width([20, 30, 30]);
    $objeto->set_align(["left", "left", "left"]);

    # Classe do banco de dados
    $objeto->set_classBd('Pessoal');

    # Nome da tabela
    $objeto->set_tabela('tbferiasprocesso');

    # Nome do campo id
    $objeto->set_idCampo('idFeriasProcesso');

    # Tipo de label do formulário
    $objeto->set_formlabelTipo(1);

    # Lotação
    if (empty($id)) {
        $result = $pessoal->select('SELECT DISTINCT DIR, DIR
                                  FROM tblotacao
                                 WHERE ativo
                                   AND DIR NOT IN (SELECT lotacao FROM tbferiasprocesso)
                              ORDER BY DIR');
    } else {
        $result = $pessoal->select('SELECT DISTINCT DIR, DIR
                                  FROM tblotacao
                                 WHERE ativo
                              ORDER BY DIR');
    }
    array_unshift($result, array(null, null));

    # Campos para o formulario
    $objeto->set_campos(array(
        array('linha' => 1,
            'col' => 3,
            'nome' => 'lotacao',
            'label' => 'Diretpria / Centro:',
            'tipo' => 'combo',
            'array' => $result,
            'required' => true,
            'autofocus' => true,
            'size' => 50),
        array('linha' => 1,
            'col' => 4,
            'nome' => 'processo',
            'label' => 'N° do Processo:',
            'tipo' => 'texto',
            'required' => true,
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