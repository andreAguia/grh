<?php

/**
 * Dados Gerais do servidor
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
    $objeto->set_nome("Justificativa para a Isenção");

    # select do edita
    $selectEdita = 'SELECT justificativaVacina
                      FROM tbservidor
                     WHERE idServidor = ' . $idServidorPesquisado;

    $objeto->set_selectEdita($selectEdita);

    # Caminhos
    $objeto->set_linkGravar('?fase=gravar');
    #$objeto->set_linkListar('?');
    $objeto->set_linkListar('servidorVacina.php');

    # botão voltar
    $objeto->set_voltarForm('servidorVacina.php');

    # retira o botão incluir
    $objeto->set_botaoIncluir(false);

    # Classe do banco de dados
    $objeto->set_classBd('Pessoal');

    # Nome da tabela
    $objeto->set_tabela('tbservidor');

    # Nome do campo id
    $objeto->set_idCampo('idServidor');

    # Campos para o formulario
    $campos = array(array('linha' => 1,
            'nome' => 'justificativaVacina',
            'label' => 'Justificativa para a Isenção:',
            'tipo' => 'textarea',
            'size' => array(80, 4),
            'autofocus' => true,
            'title' => 'Justificativa para a isenção de enviar o comprovante de vacinação.'));

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
            $objeto->gravar($idServidorPesquisado);
            break;
    }
    $page->terminaPagina();
} else {
    loadPage("../../areaServidor/sistema/login.php");
}