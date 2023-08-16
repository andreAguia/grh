<?php

/**
 * Dados Bancários do servidor
 *  
 * By Alat
 */
# Inicia as variáveis que receberão as sessions
$idUsuario = null;              # Servidor logado
$idServidorPesquisado = null; # Servidor Editado na pesquisa do sistema do GRH
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
        $atividade = "Cadastro do servidor - Dados bancários";
        $data = date("Y-m-d H:i:s");
        $intra->registraLog($idUsuario, $data, $atividade, null, null, 7, $idServidorPesquisado);
    }

    # Verifica a fase do programa
    $fase = get('fase', 'editar');

    # Pega o idPessoa
    $idPessoa = $pessoal->get_idPessoa($idServidorPesquisado);

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
    $objeto->set_nome('Dados Bancários');

    # select do edita
    $objeto->set_selectEdita('SELECT banco,
                                     agencia,
                                     conta,
                                     obsFinanceiro
                                FROM tbpessoa
                               WHERE idPessoa = ' . $idPessoa);
    
    # Habilita o modo leitura para usuario de regra 12
    if (Verifica::acesso($idUsuario, 12)) {
        $objeto->set_modoLeitura(true);
    }

    # Caminhos
    $objeto->set_linkGravar('?fase=gravar');
    #$objeto->set_linkListar('?');
    $objeto->set_linkListar('servidorMenu.php');

    # botão voltar
    $objeto->set_voltarForm('servidorMenu.php');

    # retira o botão incluir
    $objeto->set_botaoIncluir(false);

    # Classe do banco de dados
    $objeto->set_classBd('Pessoal');

    # Nome da tabela
    $objeto->set_tabela('tbpessoa');

    # Nome do campo id
    $objeto->set_idCampo('idPessoa');

    # Tipo de label do formulário
    $objeto->set_formlabelTipo(1);
    
    # Pega os dados da combo dos bancos
    $banco = $pessoal->select('SELECT idBanco,
                                       CONCAT(codigo," (", banco,")")
                                  FROM tbbanco
                              ORDER BY codigo');
    array_unshift($banco, array(null, null));

    # Campos para o formulario
    $objeto->set_campos(array(
        array('linha' => 1,
            'nome' => 'banco',
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
        array('linha' => 4,
            'nome' => 'obsFinanceiro',
            'label' => 'Observação:',
            'tipo' => 'textarea',
            'col' => 12,
            'size' => array(80, 5))));

    # Log
    $objeto->set_idUsuario($idUsuario);
    $objeto->set_idServidorPesquisado($idServidorPesquisado);

    ################################################################
    switch ($fase) {
        case "editar" :
        case "excluir" :
        case "gravar" :
            $objeto->$fase($idPessoa);
            break;
    }

    $page->terminaPagina();
} else {
    loadPage("../../areaServidor/sistema/login.php");
}