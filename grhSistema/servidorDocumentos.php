<?php

/**
 * Dados da Documentação do servidor
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
        $atividade = "Cadastro do servidor - Documentos";
        $data = date("Y-m-d H:i:s");
        $intra->registraLog($idUsuario, $data, $atividade, null, null, 7, $idServidorPesquisado);
    }

    # Verifica a fase do programa
    if (Verifica::acesso($idUsuario, 12)) {
        $fase = get('fase', 'editar');
    } else {
        $fase = get('fase', 'ver');
    }

    # Pega dados dessa matrícula
    $idPessoa = $pessoal->get_idPessoa($idServidorPesquisado);
    $idCargo = $pessoal->get_idCargo($idServidorPesquisado);

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
    $objeto->set_nome('Documentação');

    $selectEdita = 'SELECT cpf,
                         pisPasep,
                         reservista,
                         reservistaCateg,
                         identidade,
                         orgaoId,
                         dtId,
                         titulo,
                         zona,
                         secao,
                         tituloUf,
                         motorista,
                         dtVencMotorista,
                         conselhoClasse,
                        registroClasse,
                        passaporte,
                        passaporteExpedicao,
                        passaporteValidade,
                        passaporteAutoridade,
                        cp,
                        serieCp,
                        ufCp
                   FROM tbdocumentacao
                  WHERE idPessoa = ' . $idPessoa;
    
    # Habilita o modo leitura para usuario de regra 12
    if (Verifica::acesso($idUsuario, 12)) {
        $objeto->set_modoLeitura(true);
    }

    # select do edita
    $objeto->set_selectEdita($selectEdita);


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
    $objeto->set_tabela('tbdocumentacao');

    # Nome do campo id
    $objeto->set_idCampo('idPessoa');

    # Pega os dados da combo de cidade
    $estado = $pessoal->select('SELECT uf,
                                       uf
                                  FROM tbestado
                              ORDER BY 2');
    array_unshift($estado, array(null, null)); # Adiciona o valor de nulo
    # Campos para o formulario
    $campos = array(
        array('linha' => 1,
            'col' => 3,
            'nome' => 'cpf',
            'label' => 'CPF:',
            'tipo' => 'cpf',
            'required' => true,
            'autofocus' => true,
            'title' => 'CPF do servidor',
            'size' => 20),
        array('linha' => 1,
            'col' => 3,
            'nome' => 'pisPasep',
            'label' => 'Pis/Pasep:',
            'tipo' => 'texto',
            'title' => 'Pis Pasep do Servidor',
            'size' => 20),
        array('linha' => 1,
            'col' => 3,
            'nome' => 'reservista',
            'label' => 'Reservista:',
            'tipo' => 'texto',
            'title' => 'Reservista',
            'size' => 15),
        array('linha' => 1,
            'col' => 3,
            'nome' => 'reservistaCateg',
            'label' => 'Categoria:',
            'tipo' => 'texto',
            'title' => 'Categoria de reservista',
            'size' => 5),
        array('linha' => 2,
            'col' => 3,
            'nome' => 'identidade',
            'label' => 'Número:',
            'tipo' => 'texto',
            'fieldset' => 'Identidade',
            'title' => 'Identidade do servidor',
            'size' => 20),
        array('linha' => 2,
            'col' => 3,
            'nome' => 'orgaoId',
            'label' => 'Órgão:',
            'tipo' => 'texto',
            'title' => 'Órgão da identidade',
            'size' => 10),
        array('linha' => 2,
            'col' => 3,
            'nome' => 'dtId',
            'label' => 'Data de Emissão:',
            'tipo' => 'data',
            'size' => 15,
            'title' => 'Data de Emissão.'),
        array('linha' => 3,
            'col' => 3,
            'nome' => 'titulo',
            'label' => 'Título:',
            'tipo' => 'texto',
            'title' => 'Número do Título Eleitoral',
            'fieldset' => 'Título Eleitoral',
            'size' => 15),
        array('linha' => 3,
            'col' => 2,
            'nome' => 'zona',
            'label' => 'Zona:',
            'tipo' => 'texto',
            'title' => 'Zona Eleitoral',
            'size' => 5),
        array('linha' => 3,
            'col' => 2,
            'nome' => 'secao',
            'label' => 'Seção:',
            'tipo' => 'texto',
            'title' => 'Seção Eleitoral',
            'size' => 5),
        array('linha' => 3,
            'col' => 2,
            'nome' => 'tituloUf',
            'label' => 'UF:',
            'tipo' => 'combo',
            'array' => $estado,
            'title' => 'Unidade de Federaçao do Titulo Eleitoral',
            'size' => 3),
        array('linha' => 4,
            'col' => 4,
            'nome' => 'motorista',
            'label' => 'Número:',
            'tipo' => 'texto',
            'title' => 'Carteira de Habilitação',
            'fieldset' => 'Carteira de Habilitação',
            'size' => 10),
        array('linha' => 4,
            'col' => 3,
            'nome' => 'dtVencMotorista',
            'label' => 'Data de Vencimento:',
            'tipo' => 'data',
            'size' => 15,
            'title' => 'Data de Vencimento da Carteira de Habilitação.'),
        array('linha' => 5,
            'col' => 4,
            'nome' => 'conselhoClasse',
            'label' => 'Conselho de Classe:',
            'tipo' => 'texto',
            'title' => 'Nome do Conselho de Classe',
            'fieldset' => 'Conselho de Classe',
            'size' => 50),
        array('linha' => 5,
            'nome' => 'registroClasse',
            'col' => 3,
            'label' => 'Número:',
            'tipo' => 'texto',
            'title' => 'Número do registro',
            'size' => 20),
        array('linha' => 6,
            'col' => 3,
            'nome' => 'passaporte',
            'label' => 'Nº do Passaporte:',
            'tipo' => 'texto',
            'title' => 'Número do Passaporte',
            'fieldset' => 'Passaporte',
            'size' => 50),
        array('linha' => 6,
            'col' => 3,
            'nome' => 'passaporteExpedicao',
            'label' => 'Data de Expedição:',
            'tipo' => 'data',
            'size' => 15,
            'title' => 'Data em que o passaporte foi expedido.'),
        array('linha' => 6,
            'col' => 3,
            'nome' => 'passaporteValidade',
            'label' => 'Data de Validade:',
            'tipo' => 'data',
            'size' => 15,
            'title' => 'Data de validade do passaporte.'),
        array('linha' => 6,
            'col' => 3,
            'nome' => 'passaporteAutoridade',
            'label' => 'Autoridade:',
            'tipo' => 'texto',
            'title' => 'Autoridade emissora do passaporte',
            'size' => 50),        
        array('linha' => 7,
            'col' => 3,
            'nome' => 'cp',
            'label' => 'Numero:',
            'tipo' => 'texto',
            'title' => 'Numero da Carteira Profissional CLT',
            'fieldset' => 'Carteira Profissional CLT',
            'size' => 20),
        array('linha' => 7,
            'nome' => 'serieCp',
            'col' => 2,
            'label' => 'Serie:',
            'tipo' => 'texto',
            'title' => 'Serie',
            'size' => 10),
        array('linha' => 7,
            'nome' => 'ufCp',
            'col' => 2,
            'label' => 'UF:',
            'tipo' => 'combo',
            'array' => $estado,
            'title' => 'Unidade da Federaçao',
            'size' => 10)
    );

    $objeto->set_campos($campos);

    # Log
    $objeto->set_idUsuario($idUsuario);
    $objeto->set_idServidorPesquisado($idServidorPesquisado);

    ################################################################
    switch ($fase) {
        case "editar" :
        case "ver" :
        case "gravar" :
            $objeto->$fase($idPessoa);
            break;
    }
    $page->terminaPagina();
} else {
    loadPage("../../areaServidor/sistema/login.php");
}