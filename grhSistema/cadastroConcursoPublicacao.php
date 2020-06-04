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
    $idConcurso = get('idConcurso');

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
    $objeto->set_nome('Publicações');

    # Botão de voltar da lista
    $objeto->set_voltarForm('cadastroConcurso.php?fase=editar&id=' . $idConcurso);

    # select da lista
    $objeto->set_selectLista('SELECT idConcurso,
                                      data,
                                      pag,
                                      descricao,
                                      obs,
                                      idConcursoPublicacao
                                 FROM tbconcursopublicacao
                             ORDER BY data');

    # select do edita
    $objeto->set_selectEdita('SELECT idConcurso,
                                     descricao,
                                     data,
                                     pag,
                                     obs
                                FROM tbconcursopublicacao
                               WHERE idConcursoPublicacao = ' . $id);

    # Caminhos
    $objeto->set_linkEditar('?fase=editar&idConcurso=' . $idConcurso);
    $objeto->set_linkExcluir('?fase=excluir&idConcurso=' . $idConcurso);
    $objeto->set_linkGravar('?fase=gravar&idConcurso=' . $idConcurso);
    $objeto->set_linkListar('cadastroConcurso.php?fase=editar&id=' . $idConcurso);

    # Parametros da tabela
    $objeto->set_label(array("Data", "Pag", "Descrição", "Obs"));
    #$objeto->set_width(array(5,40,45));
    #$objeto->set_align(array("center","center","left"));
    # Classe do banco de dados
    $objeto->set_classBd('Pessoal');

    # Nome da tabela
    $objeto->set_tabela('tbconcursopublicacao');

    # Nome do campo id
    $objeto->set_idCampo('idConcursoPublicacao');

    # Tipo de label do formulário
    $objeto->set_formlabelTipo(1);

    # Pega os dados para combo concurso 
    $concurso = $pessoal->select('SELECT idconcurso,
                                         concat(anoBase," - Edital: ",DATE_FORMAT(dtPublicacaoEdital,"%d/%m/%Y")) as concurso
                                    FROM tbconcurso
                                    WHERE tipo = 2
                                ORDER BY dtPublicacaoEdital desc');

    # Campos para o formulario
    $objeto->set_campos(array(
        array('linha' => 1,
            'nome' => 'idConcurso',
            'label' => 'Concurso:',
            'tipo' => 'hidden',
            'array' => $concurso,
            'padrao' => $idConcurso,
            'col' => 3,
            'size' => 30),
        array('linha' => 1,
            'nome' => 'descricao',
            'label' => 'Descrição:',
            'tipo' => 'texto',
            'required' => true,
            'col' => 12,
            'size' => 250),
        array('linha' => 2,
            'nome' => 'data',
            'label' => 'Data:',
            'tipo' => 'data',
            'title' => 'Data da Publicação',
            'required' => true,
            'col' => 3,
            'size' => 20),
        array('linha' => 2,
            'nome' => 'pag',
            'label' => 'Página:',
            'tipo' => 'texto',
            'col' => 2,
            'size' => 10),
        array('linha' => 3,
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