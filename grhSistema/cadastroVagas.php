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
    $vaga = new Vaga();
    $concurso = new Concurso();

    # Verifica a fase do programa
    $fase = get('fase', 'listar');

    # Verifica se veio menu grh e registra o acesso no log
    $grh = get('grh', false);
    if ($grh) {
        # Grava no log a atividade
        $atividade = "Visualizou o cadastro de vagas de docentes";
        $data = date("Y-m-d H:i:s");
        $intra->registraLog($idUsuario, $data, $atividade, null, null, 7);
    }

    # pega o id (se tiver)
    $id = soNumeros(get('id'));

    # Pega os parâmetros
    $parametroCentro = get('parametroCentro', get_session('parametroCentro'));
    $parametroCargo = post('parametroCargo', get_session('parametroCargo', 128));

    if ($parametroCentro == "Todos") {
        $parametroCentro = null;
    }

    # Começa uma nova página
    $page = new Page();
    $page->iniciaPagina();

    # Cabeçalho da Página
    AreaServidor::cabecalho();

    # Abre um novo objeto Modelo
    $objeto = new Modelo();

    ################################################################
    # Nome do Modelo

    if (empty($parametroCentro)) {
        $objeto->set_nome("Vagas de Docentes");
    } else {
        $objeto->set_nome("Vagas de Docentes do $parametroCentro");
    }

    # select do edita
    $objeto->set_selectEdita('SELECT centro,
                                     idCargo
                                FROM tbvaga
                               WHERE idVaga = ' . $id);

    # Caminhos
    $objeto->set_linkGravar('?fase=gravar');
    $objeto->set_linkListar('areaVagasDocentes.php');
    $objeto->set_voltarForm('areaVagasDocentes.php');

    # Classe do banco de dados
    $objeto->set_classBd('Pessoal');

    # Nome da tabela
    $objeto->set_tabela('tbvaga');

    # Nome do campo id
    $objeto->set_idCampo('idVaga');

    # Pega os dados da combo cargo
    $cargo = $pessoal->select('SELECT idcargo,nome
                                 FROM tbcargo LEFT JOIN tbtipocargo USING (idTipoCargo)
                                              LEFT JOIN tbarea USING (idarea)
                                WHERE idCargo = 128 OR idCargo = 129              
                             ORDER BY tbtipocargo.cargo,tbarea.area,nome');

    array_unshift($cargo, array(0, null));

    # Campos para o formulario
    $objeto->set_campos(array(
        array('linha' => 1,
            'col' => 2,
            'nome' => 'centro',
            'label' => 'Centro:',
            'tipo' => 'combo',
            'array' => array(null, "CCT", "CCTA", "CCH", "CBB"),
            'required' => true,
            'autofocus' => true,
            'padrao' => $parametroCentro,
            'size' => 30),
        array('linha' => 1,
            'col' => 4,
            'nome' => 'idCargo',
            'label' => 'Cargo:',
            'tipo' => 'combo',
            'array' => $cargo,
            'padrao' => $parametroCargo,
            'required' => true,
            'size' => 30)));

    # idUsuário para o Log
    $objeto->set_idUsuario($idUsuario);

    $objeto->set_botaoVoltarLista(false);
    $objeto->set_botaoIncluir(false);

    ################################################################

    switch ($fase) {
        case "" :
        case "listar" :
            $objeto->$fase();
            break;

        case "editar" :
            $objeto->$fase($id);
            break;

        case "excluir" :
            $objeto->$fase($id);
            break;

        case "gravar" :
            $objeto->$fase($id);
            break;
    }

    $page->terminaPagina();
} else {
    loadPage("../../areaServidor/sistema/login.php");
}