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
        $atividade = "Visualizou o cadastro de Tipos de Nomeação";
        $data = date("Y-m-d H:i:s");
        $intra->registraLog($idUsuario, $data, $atividade, null, null, 7);
    }

    # Pega os tipos de nomeação
    $tipoNom = new TipoNomeacao();

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
    $objeto->set_nome('Tipos de Nomeação');

    # Botão de voltar da lista
    $objeto->set_voltarLista('grh.php');

    $select = "SELECT idTipoNomeacao,
                      nome,
                      descricao,
                      IF(remunerado = 1,'Sim','Não'),
                      CASE";

    foreach ($tipoNom->tiposVisibilidade as $tt) {
        $select .= " WHEN visibilidade = {$tt[0]} THEN '{$tt[1]}'";
    }

    $select .= "          ELSE '---'
                      END,                            
                      idTipoNomeacao,
                      idTipoNomeacao
                 FROM tbtiponomeacao as tt
             ORDER BY idTipoNomeacao";
    
    # select da lista
    $objeto->set_selectLista($select);

    # select do edita
    $objeto->set_selectEdita("SELECT nome,
                                     descricao,
                                     remunerado,
                                     visibilidade,
                                     idTipoNomeacao
                               FROM tbtiponomeacao
                              WHERE idTipoNomeacao = {$id}");

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
    $objeto->set_label(["Id", "Tipo de Nomeação", "Descrição", "Remunerado?", "Visibilidade","Nomeações<br/>Cadastradas"]);
    $objeto->set_align(["center", "left", "left"]);
    $objeto->set_width([5, 20, 30, 10, 25]);
    
    $objeto->set_classe([null, null, null, null, null, "TipoNomeacao"]);
    $objeto->set_metodo([null, null, null, null, null, "get_numNomeacoesPorTipo"]);


    # Classe do banco de dados
    $objeto->set_classBd('Pessoal');

    # Nome da tabela
    $objeto->set_tabela('tbtiponomeacao');

    # Nome do campo id
    $objeto->set_idCampo('idTipoNomeacao');

    # Tipo de label do formulário
    $objeto->set_formlabelTipo(1);

    # Campos para o formulario
    $objeto->set_campos(array(
        array('linha' => 1,
            'col' => 6,
            'nome' => 'nome',
            'label' => 'Tipo de Nomeação:',
            'tipo' => 'texto',
            'required' => true,
            'autofocus' => true,
            'size' => 100),
        array('linha' => 2,
            'col' => 12,
            'nome' => 'descricao',
            'label' => 'Descrição:',
            'tipo' => 'texto',
            'required' => true,
            'size' => 250),
        array('linha' => 3,
            'col' => 2,
            'nome' => 'remunerado',
            'label' => 'Remunerado?:',
            'tipo' => 'simnao2',
            'required' => true,
            'size' => 1),
        array('linha' => 3,
            'col' => 4,
            'nome' => 'visibilidade',
            'tipo' => 'combo',
            'label' => 'Descrição:',
            'array' => [
                [1, "Em todas as listagens"],
                [2, "Somente na cadastro do servidor"]
            ],
            'required' => true,
            'size' => 20)));

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