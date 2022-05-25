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
    $fase = get('fase', 'ver');

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
    $objeto->set_nome('Contatos');

    # select do edita
    $objeto->set_selectEdita('SELECT telResidencialDDD,
                                     telResidencial,
                                     telCelularDDD,
                                     telCelular,
                                     telRecadosDDD,
                                     telRecados,
                                     emailUenf,
                                     emailPessoal,
                                     emailOutro
                                FROM tbpessoa
                               WHERE idPessoa = ' . $idPessoa);
    
    # Habilita o modo leitura para usuario de regra 12
    if (Verifica::acesso($idUsuario, 12)) {
        $objeto->set_modoLeitura(true);
    }

    # Caminhos
    $objeto->set_linkGravar('?fase=gravar');
    $objeto->set_linkListar('servidorMenu.php');
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

    # Campos para o formulario
    $objeto->set_campos(array(
        array('linha' => 1,
            'nome' => 'telResidencialDDD',
            'label' => 'DDD:',
            'tipo' => 'texto',
            'title' => 'DDD',
            'autofocus' => true,
            'col' => 1,
            'fieldset' => 'Telefones',
            'size' => 2),
        array('linha' => 1,
            'nome' => 'telResidencial',
            'label' => 'Telefone Residencial:',
            'tipo' => 'telefone',
            'title' => 'Telefone Residencial',
            'col' => 3,
            'fieldset' => 'Telefones',
            'size' => 30),
        array('linha' => 1,
            'nome' => 'telCelularDDD',
            'label' => 'DDD:',
            'tipo' => 'texto',
            'title' => 'DDD',
            'col' => 1,
            'size' => 2),
        array('linha' => 1,
            'nome' => 'telCelular',
            'label' => 'Telefone Celular:',
            'tipo' => 'celular',
            'title' => 'Telefone Celular',
            'col' => 3,
            'size' => 30),
        array('linha' => 1,
            'nome' => 'telRecadosDDD',
            'label' => 'DDD:',
            'tipo' => 'texto',
            'title' => 'DDD',
            'col' => 1,
            'size' => 2),
        array('linha' => 1,
            'nome' => 'telRecados',
            'label' => 'Outro telefone para recado:',
            'tipo' => 'texto',
            'title' => 'Telefone Recados',
            'col' => 3,
            'size' => 30),
        array('linha' => 2,
            'nome' => 'emailUenf',
            'label' => 'E-mail Uenf:',
            'tipo' => 'mail',
            'title' => 'E-mail institucional da Uenf',
            'col' => 4,
            'fieldset' => 'E-mails',
            'size' => 100),
        array('linha' => 2,
            'nome' => 'emailPessoal',
            'label' => 'E-mail Pessoal:',
            'tipo' => 'mail',
            'title' => 'E-mail Pessoal',
            'col' => 4,
            'size' => 100),
        array('linha' => 2,
            'nome' => 'emailOutro',
            'label' => 'Outro E-mail:',
            'tipo' => 'mail',
            'title' => 'Outro E-mail',
            'col' => 4,
            'size' => 100),
    ));

    # Log
    $objeto->set_idUsuario($idUsuario);
    $objeto->set_idServidorPesquisado($idServidorPesquisado);

    ################################################################
    switch ($fase) {
        case "ver" :
        case "editar" :
            $objeto->$fase($idPessoa);
            break;

        case "gravar" :
            $objeto->gravar($idPessoa, 'servidorContatosExtra.php');
            break;
    }

    $page->terminaPagina();
} else {
    loadPage("../../areaServidor/sistema/login.php");
}