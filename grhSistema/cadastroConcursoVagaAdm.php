<?php

/**
 * Cadastro de Concurso ADM
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
    set_session('origem', basename(__FILE__) . "?fase={$fase}");
    $idConcurso = get_session('idConcurso');

    # Pega o tipo do concurso
    $concurso = new Concurso($idConcurso);
    $tipo = $concurso->get_tipo($idConcurso);

    # pega o id (se tiver)
    $id = soNumeros(get('id'));

    # Começa uma nova página
    $page = new Page();
    $page->iniciaPagina();

    if ($fase == "listar") {
        # Grava no log a atividade
        $atividade = "Visualizou as vagas do concurso " . $concurso->get_nomeConcurso($idConcurso);
        $data = date("Y-m-d H:i:s");
        $intra->registraLog($idUsuario, $data, $atividade, null, null, 7);
    }

    # Cabeçalho da Página
    AreaServidor::cabecalho();

    # Abre um novo objeto Modelo
    $objeto = new Modelo();

    ################################################################
    # Nome do Modelo (aparecerá nos fildset e no caption da tabela)
    $objeto->set_nome('Vagas');

    # Botão de voltar da lista
    $objeto->set_voltarLista('areaConcursoAdm.php');

    # select da lista
    $objeto->set_selectLista("SELECT cargo,
                                     vagasNovas,
                                     vagasReposicao,
                                     idConcursoVaga,
                                     CONCAT(idConcurso,'&',idTipoCargo),
                                     CONCAT(idConcurso,'&',idTipoCargo),
                                     idConcurso
                                 FROM tbconcursovaga JOIN tbtipocargo USING (idTipoCargo)
                                WHERE idConcurso = {$idConcurso}
                             ORDER BY 1");

    # select do edita
    $objeto->set_selectEdita("SELECT idTipoCargo,
                                     vagasNovas,
                                     vagasReposicao,
                                     obs,
                                     idConcurso,
                                     idConcurso
                                FROM tbconcursovaga
                              WHERE idConcursoVaga = {$id}");

    # Caminhos
    $objeto->set_linkEditar('?fase=editar');
    $objeto->set_linkExcluir('?fase=excluir');
    $objeto->set_linkGravar('?fase=gravar');
    $objeto->set_linkListar('?fase=listar');

    # Parametros da tabela
    $objeto->set_label(array("Cargo", "Vagas Novas", "Vagas de Reposição", "Total", "Servidores Ativos"));
    $objeto->set_width(array(30, 14, 14, 14, 14));
    $objeto->set_align(array("left"));
    $objeto->set_classe(array(null, null, null, "Concurso", "Concurso"));
    $objeto->set_metodo(array(null, null, null, "get_totalVagasConcurso", "get_numServidoresAtivosConcursoCargo"));
    $objeto->set_colunaSomatorio([1, 2, 3, 4]);
    $objeto->set_totalRegistro(false);

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
                                  WHERE tipo = "Adm/Tec"
                              ORDER BY idTipoCargo');
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
        array('linha' => 2,
            'nome' => 'obs',
            'label' => 'Observação:',
            'tipo' => 'textarea',
            'col' => 12,
            'size' => array(80, 5)),
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
            # Cria uma rotina extra

            function rotinaLateral($idConcurso) {
                $grid = new Grid();
                $grid->abreColuna(3);

                # Exibe os dados do Concurso
                $concurso = new Concurso($idConcurso);
                $concurso->exibeDadosConcurso($idConcurso, true);

                # menu
                $concurso->exibeMenu($idConcurso, "Vagas");

                $grid->fechaColuna();
                $grid->abreColuna(9);
            }

            $objeto->set_rotinaExtraListar("rotinaLateral");
            $objeto->set_rotinaExtraListarParametro($idConcurso);

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