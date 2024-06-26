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
    $objeto->set_nome('Endereço');

    # select do edita
    $objeto->set_selectEdita('SELECT endereco,
                                     bairro,
                                     idCidade,
                                     cep
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

    # Pega os dados da combo de cidade
    $cidade = $pessoal->select('SELECT idCidade,
                                       CONCAT(tbcidade.nome," (",tbestado.uf,")")
                                  FROM tbcidade JOIN tbestado USING (idEstado)
                              ORDER BY proximidade,tbestado.uf,tbcidade.nome');
    array_unshift($cidade, array(null, null));
    
    # Campos para o formulario
    $objeto->set_campos(array(
        array('linha' => 5,
            'nome' => 'endereco',
            'label' => 'Endereço:',
            'tipo' => 'texto',
            'autofocus' => true,
            'plm' => true,
            'title' => 'Endereço do Servidor',
            'col' => 12,
            'size' => 150),
        array('linha' => 6,
            'nome' => 'bairro',
            'label' => 'Bairro:',
            'tipo' => 'texto',
            'title' => 'Bairro',
            'plm' => true,
            'col' => 4,
            'size' => 50),
        array('linha' => 6,
            'nome' => 'idCidade',
            'label' => 'Cidade:',
            'tipo' => 'combo',
            'array' => $cidade,
            'title' => 'Cidade de Moradia do Servidor',
            'col' => 5,
            'size' => 30),
        array('linha' => 6,
            'nome' => 'cep',
            'label' => 'Cep:',
            'tipo' => 'cep',
            'title' => 'Cep',
            'col' => 3,
            'size' => 10)
    ));

    # Log
    $objeto->set_idUsuario($idUsuario);
    $objeto->set_idServidorPesquisado($idServidorPesquisado);

    ################################################################
    switch ($fase) {
        case "ver" :
        case "editar" :
        case "excluir" :
            $objeto->$fase($idPessoa);
            break;

        case "gravar" :
            $objeto->$fase($idPessoa);
            break;
    }

    $page->terminaPagina();
} else {
    loadPage("../../areaServidor/sistema/login.php");
}