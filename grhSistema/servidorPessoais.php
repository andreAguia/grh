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
    $intra = new Intra();

    # Verifica se veio menu grh e registra o acesso no log
    $grh = get('grh', false);
    if ($grh) {
        # Grava no log a atividade
        $atividade = "Cadastro do servidor - Dados pessoais";
        $data = date("Y-m-d H:i:s");
        $intra->registraLog($idUsuario, $data, $atividade, null, null, 7, $idServidorPesquisado);
    }

    # Verifica a fase do programa
    if (Verifica::acesso($idUsuario, 12)) {
        $fase = get('fase', 'editar');
    }else{
        $fase = get('fase', 'ver');
    }

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
    $objeto->set_nome('Dados Pessoais');

    # select do edita
    $objeto->set_selectEdita("SELECT dtNasc,
                                     dtFalecimento,
                                     sexo,
                                     estCiv,
                                     conjuge,
                                     naturalidade,
                                     nacionalidade,
                                     paisOrigem,
                                     anoChegada,
                                     nomePai,
                                     nomeMae
                                FROM tbpessoa
                               WHERE idPessoa = {$idPessoa}");

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

    # Pega os dados da combo de estado civil
    $estadoCivil = $pessoal->select('SELECT idestCiv,
                                       estciv
                                  FROM tbestciv
                              ORDER BY estciv');
    array_unshift($estadoCivil, array(null, null)); # Adiciona o valor de nulo
    # Pega os dados da combo de nacionalidade
    $nacionalidade = $pessoal->select('SELECT idNacionalidade,
                                       nacionalidade
                                  FROM tbnacionalidade
                              ORDER BY nacionalidade');
    array_unshift($nacionalidade, array(null, null)); # Adiciona o valor de nulo
    # Pega os dados da combo de país de origem
    $paisOrigem = $pessoal->select('SELECT idPais,
                                       pais
                                  FROM tbpais
                              ORDER BY pais');
    array_unshift($paisOrigem, array(null, null)); # Adiciona o valor de nulo
    # Campos para o formulario
    $objeto->set_campos(array(
        array('linha' => 1,
            'nome' => 'dtNasc',
            'label' => 'Data de Nascimento:',
            'tipo' => 'data',
            'size' => 20,
            'col' => 3,
            'title' => 'Data de Nascimento.'),
        array('linha' => 1,
            'nome' => 'dtFalecimento',
            'label' => 'Data de Falecimento:',
            'tipo' => 'data',
            'size' => 20,
            'col' => 3,
            'title' => 'Data de Falecimento.'),
        array('linha' => 1,
            'nome' => 'sexo',
            'label' => 'Sexo:',
            'tipo' => 'combo',
            'array' => array(null, "Masculino", "Feminino"),
            'title' => 'Sexo do Servidor',
            'required' => true,
            'col' => 3,
            'size' => 15),
        array('linha' => 1,
            'nome' => 'estCiv',
            'label' => 'Estado Civil:',
            'tipo' => 'combo',
            'array' => $estadoCivil,
            'title' => 'Estado Civil do Servidor',
            'col' => 3,
            'size' => 15),
        array('linha' => 2,
            'nome' => 'conjuge',
            'label' => 'Conjuge:',
            'tipo' => 'texto',
            'title' => 'Nome do conjuge',
            'plm' => true,
            'col' => 6,
            'size' => 50),
        array('linha' => 2,
            'nome' => 'naturalidade',
            'label' => 'Naturalidade: (Cidade)',
            'tipo' => 'texto',
            'col' => 3,
            'title' => 'Servidor natural da cidade de',
            'size' => 30),
        array('linha' => 2,
            'nome' => 'nacionalidade',
            'label' => 'Nacionalidade: (País)',
            'tipo' => 'combo',
            'array' => $nacionalidade,
            'title' => 'Nacionalidade do Servido',
            'col' => 3,
            'size' => 30),
        array('linha' => 3,
            'nome' => 'paisOrigem',
            'label' => 'País de Origem:',
            'tipo' => 'combo',
            'array' => $paisOrigem,
            'title' => 'País de Origem do Servidor',
            'col' => 3,
            'size' => 15),
        array('linha' => 3,
            'nome' => 'anoChegada',
            'label' => 'Ano de Chegada:',
            'tipo' => 'texto',
            'title' => 'Ano de chegada ao Brasil',
            'col' => 3,
            'size' => 5),
        array('linha' => 4,
            'nome' => 'nomePai',
            'label' => 'Nome do Pai:',
            'tipo' => 'texto',
            'title' => 'Nome do Pai do Servidor',
            'plm' => true,
            'fieldset' => 'Filiação',
            'col' => 6,
            'size' => 50),
        array('linha' => 4,
            'nome' => 'nomeMae',
            'label' => 'Nome da Mãe:',
            'tipo' => 'texto',
            'title' => 'Nome da Mãe do Servidor',
            'plm' => true,
            'col' => 6,
            'size' => 50)
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
            $objeto->$fase($idPessoa, "servidorPessoaisExtra.php");
            break;
    }

    $page->terminaPagina();
} else {
    loadPage("../../areaServidor/sistema/login.php");
}