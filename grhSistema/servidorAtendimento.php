<?php

/**
 * Histórico de atendimentos de um servidor
 *  
 * By Alat
 */
# Inicia as variáveis que receberão as sessions
$idUsuario = null;
$idServidorPesquisado = null;

# Configuração
include ("_config.php");

# Permissão de Acesso
$acesso = Verifica::acesso($idUsuario, [1, 2, 12]);

if ($acesso) {
    # Verifica a fase do programa
    $fase = get('fase', 'listar');

    # Conecta ao Banco de Dados
    $pessoal = new Pessoal();
    $intra = new Intra();
    
    # usuario
    $usuario = $intra->get_nickUsuario($idUsuario);

    # Verifica se veio menu grh e registra o acesso no log
    $grh = get('grh', false);
    if ($grh) {
        # Grava no log a atividade
        $atividade = "Cadastro do servidor - Atendimentos";
        $data = date("Y-m-d H:i:s");
        $intra->registraLog($idUsuario, $data, $atividade, null, null, 7, $idServidorPesquisado);
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
    # Exibe os dados do Servidor
    $objeto->set_rotinaExtra("get_DadosServidor");
    $objeto->set_rotinaExtraParametro($idServidorPesquisado);

    # Nome do Modelo (aparecerá nos fildset e no caption da tabela)
    $objeto->set_nome('Histórico de Atendimentos');

    # botão de voltar da lista
    $objeto->set_voltarLista('servidorMenu.php');

    # select da lista
    $objeto->set_selectLista("SELECT data,
                                     idUsuario,
                                     atendimento,
                                     idAtendimento
                                FROM tbatendimento
                               WHERE idServidor = {$idServidorPesquisado}
                            ORDER BY data desc, idAtendimento desc");

    # select do edita
    $objeto->set_selectEdita("SELECT data,
                                     atendimento,
                                     idServidor,
                                     idUsuario
                                FROM tbatendimento
                               WHERE idAtendimento = {$id}");

    # Habilita o modo leitura para usuario de regra 12
    if (Verifica::acesso($idUsuario, 12)) {
        $objeto->set_modoLeitura(true);
    }

    # Caminhos
    #$objeto->set_linkExcluir('?fase=excluir');
    $objeto->set_botaoEditar(false);

    $objeto->set_linkEditar('?fase=editar');
    $objeto->set_linkGravar('?fase=gravar');
    $objeto->set_linkListar('?fase=listar');

    # Editar e excluir condicional
    $objeto->set_editarCondicional('?fase=editar', $usuario, 1, "=");
    $objeto->set_excluirCondicional('?fase=excluir', $usuario, 1, "=");

    # Parametros da tabela
    $objeto->set_label(["Data", "Servidor GRH", "Atendimento"]);
    $objeto->set_width([10, 10, 70]);
    $objeto->set_align(["center", "center", "left"]);
    $objeto->set_funcao(["date_to_php"]);
    $objeto->set_classe([null, "Intra"]);
    $objeto->set_metodo([null, "get_nickUsuario"]);

    # Classe do banco de dados
    $objeto->set_classBd('pessoal');

    # Nome da tabela
    $objeto->set_tabela('tbatendimento');

    # Nome do campo id
    $objeto->set_idCampo('idAtendimento');

    # Campos para o formulario
    $objeto->set_campos(array(
        array('nome' => 'data',
            'label' => 'Data:',
            'tipo' => 'data',
            'size' => 20,
            'maxLength' => 20,
            'padrao' => date("Y-m-d"),
            'required' => true,
            'col' => 3,
            'linha' => 1),
        array('linha' => 2,
            'col' => 12,
            'nome' => 'atendimento',
            'label' => 'Atendimento:',
            'required' => true,            
            'autofocus' => true,
            'tipo' => 'textarea',
            'size' => array(80, 8)),
        array('nome' => 'idServidor',
            'label' => 'idServidor:',
            'tipo' => 'hidden',
            'padrao' => $idServidorPesquisado,
            'size' => 5,
            'title' => 'idServidor',
            'linha' => 4),
        array('nome' => 'idUsuario',
            'label' => 'idUsuario:',
            'tipo' => 'hidden',
            'padrao' => $idUsuario,
            'size' => 5,
            'title' => 'idServidor',
            'linha' => 4),
    ));

    # Log
    $objeto->set_idUsuario($idUsuario);
    $objeto->set_idServidorPesquisado($idServidorPesquisado);

    ################################################################

    switch ($fase) {
        case "" :
        case "listar" :
        case "editar" :
        case "excluir" :
            $objeto->$fase($id);
            break;

        case "gravar" :
            $objeto->gravar($id);
            break;
    }
    $page->terminaPagina();
} else {
    loadPage("../../areaServidor/sistema/login.php");
}