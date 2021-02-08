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
$acesso = Verifica::acesso($idUsuario, 2);

if ($acesso) {
    # Conecta ao Banco de Dados
    $intra = new Intra();
    $pessoal = new Pessoal();
    $concurso = new Concurso();

    # Verifica a fase do programa
    $fase = get('fase', 'listar');
    $idConcurso = get('idConcurso', get_session('idConcurso'));
    set_session('idConcurso', $idConcurso);

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
    $objeto->set_nome('Vagas do Concurso');
    
    # select da lista
    $objeto->set_selectLista("SELECT cargo,
                                     vagasNovas,
                                     vagasReposicao,
                                     idConcursoVaga
                                 FROM tbconcursovaga JOIN tbtipocargo USING (idTipoCargo)
                                WHERE idConcurso = {$idConcurso}
                             ORDER BY 1");

    # select do edita
    $objeto->set_selectEdita("SELECT idTipoCargo,
                                     vagasNovas,
                                     vagasReposicao,
                                     idConcurso
                                FROM tbconcursovaga
                              WHERE idConcursoVaga = {$id}");

    # Caminhos
    $objeto->set_linkEditar('?fase=editar');
    $objeto->set_linkExcluir('?fase=excluir');
    $objeto->set_linkGravar('?fase=gravar');
    $objeto->set_linkListar('cadastroConcurso.php?fase=concursoVagas&id=' . $idConcurso);
    $objeto->set_voltarForm('cadastroConcurso.php?fase=concursoVagas&id=' . $idConcurso);

    # Parametros da tabela
    $objeto->set_label(array("Cargo", "Vagas Novas", "Vagas de Reposição"));
    $objeto->set_width(array(50, 20, 20));
    $objeto->set_align(array("left"));

    # Classe do banco de dados
    $objeto->set_classBd('Pessoal');

    # Nome da tabela
    $objeto->set_tabela('tbconcursovaga');

    # Nome do campo id
    $objeto->set_idCampo('idConcursoVaga');

    # Tipo de label do formulário
    $objeto->set_formlabelTipo(1);

    # Pega os dados da combo de Estado
    $result3 = $pessoal->select('SELECT idTipoCargo,
                                        cargo
                                  FROM tbtipocargo
                              ORDER BY cargo');
    array_push($result3, array(null, null));

    # Campos para o formulario
    $objeto->set_campos(array(
        array('linha' => 1,
            'nome' => 'idTipoCargo',
            'title' => 'Cargo',
            'label' => 'Cargo:',
            'tipo' => 'combo',
            'autofocus' => true,
            'required' => true,
            'array' => $result3,
            'col' => 4,
            'size' => 5),
        array('linha' => 1,
            'nome' => 'vagasNovas',
            'label' => 'Vagas Novas:',
            'tipo' => 'numero',
            'col' => 3,
            'size' => 5),
        array('linha' => 1,
            'nome' => 'vagasReposicao',
            'label' => 'Vagas Reposição:',
            'tipo' => 'numero',
            'col' => 3,
            'size' => 5),
        array('linha' => 3,
            'nome' => 'idConcurso',
            'label' => 'Concurso:',
            'tipo' => 'hidden',
            'col' => 3,
            'padrao' => $idConcurso,
            'size' => 5),
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