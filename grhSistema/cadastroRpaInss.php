<?php

/**
 * Cadastro de RPA
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
    $inss = new RpaInss();

    # Verifica a fase do programa
    $fase = get('fase', 'listar');

    # Verifica se veio menu grh e registra o acesso no log
    $grh = get('grh', false);
    if ($grh) {
        # Grava no log a atividade
        $atividade = "Visualizou o cadastro de RPAs";
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
    $objeto->set_nome('Tabela do INSS');

    # Botão de voltar da lista
    $objeto->set_voltarLista('cadastroRpa.php?fase=tabelas');

    # select da lista
    $objeto->set_selectLista("SELECT dtInicial,
                                     valorInicial,
                                     valorFinal,
                                     CONCAT(aliquota,' %'),
                                     idInss
                                FROM tbrpa_inss
                             ORDER BY dtInicial desc, valorInicial");

    # select do edita
    $objeto->set_selectEdita("SELECT dtInicial,
                                     valorInicial,
                                     valorFinal,
                                     aliquota,
                                     obs
                                FROM tbrpa_inss
                                WHERE idInss = {$id}");
                                
    # Habilita o modo leitura para usuario de regra 12
    if (Verifica::acesso($idUsuario, 12)) {
        $objeto->set_modoLeitura(true);
    }

    # Caminhos
    $objeto->set_linkEditar('?fase=editar');
    $objeto->set_linkExcluir('?fase=excluir');
    $objeto->set_linkGravar('?fase=gravar');
    $objeto->set_linkListar('?fase=listar');
    
    $objeto->set_rowspan(0);
    $objeto->set_grupoCorColuna(0);

    # Parametros da tabela
    $objeto->set_label(["Data Inicial", "Valor Inicial", "Valor Final", "Aliquota"]);
    $objeto->set_width([22, 22, 22, 22]);
    $objeto->set_align(["center","right","right"]);
    $objeto->set_funcao(["date_to_php", "formataMoeda2", "formataMoeda2"]);

    # Classe do banco de dados
    $objeto->set_classBd('Pessoal');

    # Nome da tabela
    $objeto->set_tabela('tbrpa_inss');

    # Nome do campo id
    $objeto->set_idCampo('idInss');

    # Campos para o formulario
    $objeto->set_campos(array(
        array('linha' => 1,
            'nome' => 'dtInicial',
            'label' => 'Data:',
            'tipo' => 'date',
            'padrao' => $inss->getUltimaDataDigitada(),
            'required' => true,
            'autofocus' => true,
            'col' => 4,
            'size' => 10),
        array('linha' => 2,
            'nome' => 'valorInicial',
            'label' => 'Valor Inicial:',
            'tipo' => 'moeda',
            'padrao' => 0,
            'col' => 4,
            'size' => 10),
        array('linha' => 2,
            'nome' => 'valorFinal',
            'label' => 'Valor Final:',
            'tipo' => 'moeda',
            'required' => true,
            'col' => 4,
            'size' => 10),
        array('linha' => 2,
            'nome' => 'aliquota',
            'label' => 'Aliquota:',
            'tipo' => 'porcentagem',
            'required' => true,
            'col' => 4,
            'size' => 5),
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
            $objeto->$fase($id);
            break;
        
        case "gravar" :
            $objeto->gravar($id, 'cadastroRpaInssExtra.php');
            break;
    }

    $page->terminaPagina();
} else {
    loadPage("../../areaServidor/sistema/login.php");
}