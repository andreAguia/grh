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
    $intra = new Intra();

    # Verifica se veio menu grh e registra o acesso no log
    $grh = get('grh', false);
    if ($grh) {
        # Grava no log a atividade
        $atividade = "Cadastro do servidor - Endereços e contatos";
        $data = date("Y-m-d H:i:s");
        $intra->registraLog($idUsuario, $data, $atividade, null, null, 7, $idServidorPesquisado);
    }

    # Verifica a fase do programa
    if (Verifica::acesso($idUsuario, 12)) {
        $fase = get('fase', 'editar');
    } else {
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
    $objeto->set_nome('Endereço & Contatos');

    # select do edita
    $objeto->set_selectEdita('SELECT endereco,
                                     bairro,
                                     idCidade,
                                     cep,
                                     telResidencialDDD,
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
    array_unshift($cidade, array(null, null)); # Adiciona o valor de nulo
    # Campos para o formulario
    $objeto->set_campos(array(
        array('linha' => 5,
            'nome' => 'endereco',
            'label' => 'Endereço:',
            'tipo' => 'texto',
            'autofocus' => true,
            'fieldset' => 'Endereço',
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
            'size' => 10),
        array('linha' => 7,
            'nome' => 'telResidencialDDD',
            'label' => 'DDD:',
            'tipo' => 'texto',
            'title' => 'DDD',
            'autofocus' => true,
            'col' => 1,
            'fieldset' => 'Telefones',
            'size' => 2),
        array('linha' => 7,
            'nome' => 'telResidencial',
            'label' => 'Telefone Residencial:',
            'tipo' => 'texto',
            'title' => 'Telefone Residencial',
            'col' => 3,
            'fieldset' => 'Telefones',
            'size' => 30),
        array('linha' => 7,
            'nome' => 'telCelularDDD',
            'label' => 'DDD:',
            'tipo' => 'texto',
            'title' => 'DDD',
            'col' => 1,
            'size' => 2),
        array('linha' => 7,
            'nome' => 'telCelular',
            'label' => 'Telefone Celular:',
            'tipo' => 'texto',
            'title' => 'Telefone Celular',
            'col' => 3,
            'size' => 30),
        array('linha' => 7,
            'nome' => 'telRecadosDDD',
            'label' => 'DDD:',
            'tipo' => 'texto',
            'title' => 'DDD',
            'col' => 1,
            'size' => 2),
        array('linha' => 7,
            'nome' => 'telRecados',
            'label' => 'Outro telefone para recado:',
            'tipo' => 'texto',
            'title' => 'Telefone Recados',
            'col' => 3,
            'size' => 30),
        array('linha' => 8,
            'nome' => 'emailUenf',
            'label' => 'E-mail Uenf:',
            'tipo' => 'email',
            'title' => 'E-mail institucional da Uenf',
            'col' => 4,
            'fieldset' => 'E-mails',
            'size' => 100),
        array('linha' => 8,
            'nome' => 'emailPessoal',
            'label' => 'E-mail Pessoal:',
            'tipo' => 'email',
            'title' => 'E-mail Pessoal',
            'col' => 4,
            'size' => 100),
        array('linha' => 8,
            'nome' => 'emailOutro',
            'label' => 'E-mail Outro:',
            'tipo' => 'email',
            'title' => 'Outro e-mail',
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
        case "excluir" :
            $objeto->$fase($idPessoa);
            break;

        case "gravar" :
            $objeto->gravar($idPessoa, "servidorEnderecoContatosExtra.php");
            break;
    }

    $page->terminaPagina();
} else {
    loadPage("../../areaServidor/sistema/login.php");
}