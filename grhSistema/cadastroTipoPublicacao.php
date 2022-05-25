<?php

/**
 * Cadastro de Perfil
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
        $atividade = "Visualizou o cadastro de tipo de publicação";
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
    # Nome do Modelo (aparecerá nos fildset e no caption da tabela)
    $objeto->set_nome("Cadastro de tipos de Publicações");

    # bot?o de voltar da lista
    $objeto->set_voltarLista('areaPublicacao.php');

    # select da lista
    $objeto->set_selectLista('SELECT idTipoPublicacao,
                                     nome,
                                     obs
                                 FROM tbtipopublicacao
                             ORDER BY nome');

    # select do edita
    $objeto->set_selectEdita("SELECT nome,
                                     obs
                               FROM tbtipopublicacao
                              WHERE idTipoPublicacao = {$id}");

    # Caminhos
    $objeto->set_linkEditar('?fase=editar');
    $objeto->set_linkExcluir('?fase=excluir');
    $objeto->set_linkGravar('?fase=gravar');
    $objeto->set_linkListar('?fase=listar');

    # Parametros da tabela
    $objeto->set_label(array("id", "Tipo", "Obs"));
    #$objeto->set_colspanLabel(array(null, null, null, 2, null, 2));
    $objeto->set_width(array(5, 30, 55));
    #$objeto->set_align(array("center", "center", "left"));
    #$objeto->set_funcao(array (null,null,null,null,null,null,"get_nome"));
    #$objeto->set_classe(array(null, null, null, "Pessoal", null, "Pessoal", null));
    #$objeto->set_metodo(array(null, null, null, "get_numServidoresAtivosPerfil", null, "get_numServidoresInativosPerfil", null));
    # Classe do banco de dados
    $objeto->set_classBd('Pessoal');

    # Nome da tabela
    $objeto->set_tabela('tbtipopublicacao');

    # Nome do campo id
    $objeto->set_idCampo('idTipoPublicacao');

    # Tipo de label do formulário
    $objeto->set_formlabelTipo(1);

    # Campos para o formulario
    $objeto->set_campos(array(
        array('linha' => 1,
            'nome' => 'nome',
            'title' => 'Nome do tipo',
            'label' => 'Nome:',
            'tipo' => 'texto',
            'required' => true,
            'autofocus' => true,
            'col' => 6,
            'size' => 50),
        array('nome' => 'obs',
            'label' => 'Observações:',
            'tipo' => 'textarea',
            'size' => array(80, 5),
            'col' => 12,
            'linha' => 2)));

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