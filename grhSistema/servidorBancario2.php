<?php

/**
 * Dados Bancários
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
    # Conecta ao Banco de Dados
    $pessoal = new Pessoal();
    $intra = new Intra();

    # Verifica se veio menu grh e registra o acesso no log
    $grh = get('grh', false);
    if ($grh) {
        # Grava no log a atividade
        $atividade = "Cadastro do servidor - Dados Bancários";
        $data = date("Y-m-d H:i:s");
        $intra->registraLog($idUsuario, $data, $atividade, null, null, 7, $idServidorPesquisado);
    }

    # Verifica a fase do programa
    $fase = get('fase', 'listar');

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
    $objeto->set_nome('Cadastro de Dados Bancários');

    # botão de voltar da lista
    $objeto->set_voltarLista('servidorMenu.php');

    # select da lista
    $objeto->set_selectLista("SELECT padrao,
                                     CONCAT(codigo,' (', banco,')'),
                                     agencia,
                                     conta,
                                     tbhistbanco.obs,
                                     idHistBanco
                                FROM tbhistbanco JOIN tbbanco USING (idBanco)
                               WHERE idServidor = {$idServidorPesquisado}
                            ORDER BY padrao DESC");

    # select do edita
    $objeto->set_selectEdita("SELECT idBanco,
                                     agencia,
                                     conta,
                                     padrao,
                                     obs,
                                     idServidor
                                FROM tbhistbanco
                               WHERE idHistBanco = {$id}");

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
    $objeto->set_label(["Padrão", "Banco", "Agência", "Conta", "Obs"]);
    $objeto->set_width([10, 20, 15, 15, 30]);
    $objeto->set_align(["center"]);
    $objeto->set_funcao(["ressaltaSimNao"]);

    # Classe do banco de dados
    $objeto->set_classBd('pessoal');

    # Nome da tabela
    $objeto->set_tabela('tbhistbanco');

    # Nome do campo id
    $objeto->set_idCampo('idHistBanco');

    # Pega os dados da combo dos bancos
    $banco = $pessoal->select('SELECT idBanco,
                                       CONCAT(codigo," (", banco,")")
                                  FROM tbbanco
                              ORDER BY codigo');
    array_unshift($banco, array(null, null));

    # Campos para o formulario
    $objeto->set_campos(array(
        array('linha' => 1,
            'nome' => 'padrao',
            'label' => 'Conta Padrão:',
            'tipo' => 'simnao3',
            'required' => true,
            'col' => 2,
            'size' => 15),
        array('linha' => 1,
            'nome' => 'idBanco',
            'label' => 'Banco:',
            'tipo' => 'combo',
            'array' => $banco,
            'required' => true,
            'autofocus' => true,
            'col' => 4,
            'title' => 'Nome do Banco do Servidor',
            'size' => 20),
        array('linha' => 1,
            'nome' => 'agencia',
            'label' => 'Agência:',
            'tipo' => 'texto',
            'required' => true,
            'col' => 3,
            'title' => 'Número da Agência',
            'size' => 10),
        array('linha' => 1,
            'nome' => 'conta',
            'label' => 'Conta Corrente:',
            'tipo' => 'texto',
            'col' => 3,
            'required' => true,
            'title' => 'Número da conta corrente do servidor',
            'size' => 20),
        array('linha' => 2,
            'nome' => 'obs',
            'label' => 'Observação:',
            'tipo' => 'textarea',
            'col' => 12,
            'size' => array(80, 5)),
        array('nome' => 'idServidor',
            'label' => 'idServidor:',
            'tipo' => 'hidden',
            'padrao' => $idServidorPesquisado,
            'size' => 5,
            'title' => 'idServidor',
            'linha' => 4)));

    # Log
    $objeto->set_idUsuario($idUsuario);
    $objeto->set_idServidorPesquisado($idServidorPesquisado);
    
    $mensagem = "Atenção!<br/> Todo servidor poderá ter apenas uma conta padrão."
            . "<br/>Quando uma conta padrão é incluída as outras contas, caso existam, serão automaticamente alteradas para NÃO padrão.";


    ################################################################

    switch ($fase) {
        case "" :
        case "listar" :
        $objeto->set_rotinaExtraListar("callout");
            $objeto->set_rotinaExtraListarParametro($mensagem);
            $objeto->listar();
            break;

        case "editar" :            
            $objeto->set_rotinaExtraEditar("callout");
            $objeto->set_rotinaExtraEditarParametro($mensagem);
            $objeto->editar($id);
            break;
        
        case "excluir" :
            $objeto->$fase($id);
            break;

        case "gravar" :
            $objeto->$fase($id, "servidorBancarioExtra.php");
            break;
    }
    $page->terminaPagina();
} else {
    loadPage("../../areaServidor/sistema/login.php");
}