<?php

/**
 * Dados Gerais do servidor
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

    # Verifica a fase do programa
    $fase = get('fase', 'editar');

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

    # Pega o perfil do Servidor    
    $perfilServidor = $pessoal->get_idPerfil($idServidorPesquisado);

    # Nome do Modelo (aparecerá nos fildset e no caption da tabela)
    $objeto->set_nome('Processo de Avaliação');

    # select do edita
    $selectEdita = 'SELECT processoAvaliacaoSei,
                           processoAvaliacaoFisico
                      FROM tbservidor
                     WHERE idServidor = ' . $idServidorPesquisado;

    $objeto->set_selectEdita($selectEdita);

    # Caminhos
    $objeto->set_linkGravar('?fase=gravar');
    #$objeto->set_linkListar('?');
    $objeto->set_linkListar('servidorAvaliacao.php');

    # botão voltar
    $objeto->set_voltarForm('servidorAvaliacao.php');

    # retira o botão incluir
    $objeto->set_botaoIncluir(false);

    # Classe do banco de dados
    $objeto->set_classBd('Pessoal');

    # Nome da tabela
    $objeto->set_tabela('tbservidor');

    # Nome do campo id
    $objeto->set_idCampo('idServidor');

    # Tipo de label do formulário
    $objeto->set_formlabelTipo(1);

    # Campos para o formulario
    $campos = array(
        array('linha' => 1,
            'nome' => 'processoAvaliacaoSei',
            'label' => 'Processo Sei:',
            'tipo' => 'texto',
            'autofocus' => true,
            'size' => 30,
            'col' => 5,
            'title' => 'Número do processo.'),
        array('linha' => 1,
            'nome' => 'processoAvaliacaoFisico',
            'label' => 'Processo Físico (Quando houver):',
            'tipo' => 'processo',
            'autofocus' => true,
            'size' => 30,
            'col' => 5,
            'title' => 'Número do processo.'),
        );

    $objeto->set_campos($campos);

    # Log
    $objeto->set_idUsuario($idUsuario);
    $objeto->set_idServidorPesquisado($idServidorPesquisado);

    ################################################################

    switch ($fase) {
        case "editar" :
            $objeto->$fase($idServidorPesquisado);
            break;

        case "gravar" :
            $objeto->gravar($idServidorPesquisado,"servidorProcessoAvaliacaoExtra.php");
            break;
    }
    $page->terminaPagina();
} else {
    loadPage("../../areaServidor/sistema/login.php");
}