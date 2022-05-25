<?php

/**
 * Cadastro do tipo de vacina
 *  
 * By Alat
 */
# Inicia as variáveis que receberão as sessions
$idUsuario = null;

# Configuração
include ("_config.php");

# Permissão de Acesso
$acesso = Verifica::acesso($idUsuario, [1, 2, 12]);

if ($acesso) {
    # Verifica a fase do programa
    $fase = get('fase', 'listar');

    # pega o id (se tiver)
    $id = soNumeros(get('id'));

    $intra = new Intra();

    # Verifica se veio menu grh e registra o acesso no log
    $grh = get('grh', false);
    if ($grh) {
        # Grava no log a atividade
        $atividade = "Cadastro do tipo de vacina";
        $data = date("Y-m-d H:i:s");
        $intra->registraLog($idUsuario, $data, $atividade, null, null, 7);
    }

    # Começa uma nova página
    $page = new Page();
    $page->iniciaPagina();

    # Cabeçalho da Página
    AreaServidor::cabecalho();

    # Abre um novo objeto Modelo
    $objeto = new Modelo();

    ################################################################
    # Nome do Modelo (aparecerá nos fildset e no caption da tabela)
    $objeto->set_nome('Cadastro do Tipo de Vacina');

    # botão de voltar da lista
    $objeto->set_voltarLista('areaVacina.php');

    # select da lista
    $objeto->set_selectLista("SELECT idTipoVacina,
                                     doenca,
                                     nome,
                                     idTipoVacina,
                                     idTipoVacina,
                                     idTipoVacina,
                                     obs
                                FROM tbtipovacina
                       ORDER BY nome");

    # select do edita
    $objeto->set_selectEdita("SELECT doenca,
                                     nome,
                                     obs,
                                     idTipoVacina
                                FROM tbtipovacina
                               WHERE idTipoVacina = {$id}");
                               
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
    $objeto->set_label(array("#", "Doença", "Nome", "Servidores Ativos", "Servidores Inativos", "Total", "Obs"));
    $objeto->set_width(array(5, 20, 20, 10, 10, 10, 20));
    $objeto->set_align(array("center", "center", "left"));
    $objeto->set_classe([null, null, null, "Vacina", "Vacina", "Vacina"]);
    $objeto->set_metodo([null, null, null, "getNumServidoresAtivosVacinadosVacina", "getNumServidoresInativosVacinadosVacina", "getNumServidoresGeralVacinadosVacina"]);

    # Classe do banco de dados
    $objeto->set_classBd('Pessoal');

    # Nome da tabela
    $objeto->set_tabela('tbtipovacina');

    # Nome do campo id
    $objeto->set_idCampo('idTipoVacina');

    # Tipo de label do formulário
    $objeto->set_formLabelTipo(1);

    # Campos para o formulario
    $objeto->set_campos(array(
        array('nome' => 'doenca',
            'label' => 'Combate qual Doença:',
            'tipo' => 'texto',
            'size' => 250,
            'required' => true,
            'autofocus' => true,
            'col' => 6,
            'title' => 'Nome da Vacina',
            'linha' => 1),
        array('nome' => 'nome',
            'label' => 'Nome:',
            'tipo' => 'texto',
            'size' => 250,
            'required' => true,
            'autofocus' => true,
            'col' => 6,
            'title' => 'Nome da Vacina',
            'linha' => 1),
        array('nome' => 'obs',
            'label' => 'Observações:',
            'tipo' => 'textarea',
            'size' => array(80, 5),
            'col' => 12,
            'linha' => 2)));

    # Log
    $objeto->set_idUsuario($idUsuario);

    ################################################################

    switch ($fase) {
        case "" :
        case "listar" :
        case "editar" :
            $objeto->$fase($id);
            break;

        ################################################################    

        case "excluir" :
            # Verifica se esse tipo de vacina tem servidor vacinado
            $vacina = new Vacina();
            $sevidores = $vacina->getNumServidoresAtivosVacinadosVacina($id);

            if ($sevidores > 0) {
                alert('Este tipo de vacina tem ' . $sevidores . ' servidores vacinados cadastrados.\nEle não poderá ser excluído.');
                back(1);
            } else {
                $objeto->excluir($id);
            }
            break;

        ################################################################

        case "gravar" :
            $objeto->gravar($id);
            break;
    }

    $page->terminaPagina();
} else {
    loadPage("../../areaServidor/sistema/login.php");
}