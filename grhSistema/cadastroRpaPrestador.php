<?php

/**
 * Cadastro de RPA
 *  
 * By Alat
 */
# Reservado para o servidor logado
$idUsuario = null;

# Configuração
include ("_config.php");

# Permissão de Acesso
$acesso = Verifica::acesso($idUsuario, [1, 2, 12]);

if ($acesso) {
    # Conecta ao Banco de Dados
    $intra = new Intra();
    $pessoal = new Pessoal();

    # Verifica a fase do programa
    $fase = get('fase', 'listar');

    # Verifica se veio menu grh e registra o acesso no log
    $grh = get('grh', false);
    if ($grh) {
        # Grava no log a atividade
        $atividade = "Visualizou o cadastro de Prestadores";
        $data = date("Y-m-d H:i:s");
        $intra->registraLog($idUsuario, $data, $atividade, null, null, 7);
    }

    # pega o id (se tiver)
    $id = soNumeros(get('id'));

    $sessionCpfPrestador = get_session('sessionCpfPrestador');

    # Começa uma nova página
    $page = new Page();
    $page->iniciaPagina();

    # Cabeçalho da Página
    AreaServidor::cabecalho();

    # Abre um novo objeto Modelo
    $objeto = new Modelo();

    ################################################################
    # Nome do Modelo
    $objeto->set_nome('Cadastro de Prestadores de Serviço');

    # Botão de voltar da lista
    $objeto->set_voltarLista('cadastroRpa.php');

    # select da lista
    $objeto->set_selectLista("SELECT cpf,
                                     idPrestador,
                                     idPrestador,
                                     idPrestador,
                                     idPrestador,
                                     idPrestador
                                FROM tbrpa_prestador
                             ORDER BY prestador");

    # select do edita
    $objeto->set_selectEdita("SELECT cpf,
                                     prestador,
                                     dtNascimento,
                                     especialidade,
                                     inss,                                   
                                     identidade,
                                     orgaoId,
                                     dataId,
                                     endereco,
                                     bairro,
                                     idCidade,
                                     cep,
                                     telefone1,
                                     telefone2,
                                     email,
                                     obs
                                FROM tbrpa_prestador
                                WHERE idPrestador = {$id}");
                                
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
    $objeto->set_label(["Cpf", "Prestador", "Endereço", "Contatos", "Documentos"]);
    $objeto->set_align(["center", "left", "left", "left", "left"]);

    $objeto->set_classe([null, "RpaPrestador", "RpaPrestador", "RpaPrestador", "RpaPrestador"]);
    $objeto->set_metodo([null, "exibePrestador", "exibeEndereco", "exibeContatos", "exibeDocumentos"]);

    # Classe do banco de dados
    $objeto->set_classBd('Pessoal');

    # Nome da tabela
    $objeto->set_tabela('tbrpa_prestador');

    # Nome do campo id
    $objeto->set_idCampo('idPrestador');

    # Tipo de label do formulário
    $objeto->set_formlabelTipo(1);

    # Pega os dados da combo de cidade
    $cidade = $pessoal->select('SELECT idCidade,
                                       CONCAT(tbcidade.nome," (",tbestado.uf,")")
                                  FROM tbcidade JOIN tbestado USING (idEstado)
                              ORDER BY proximidade,tbestado.uf,tbcidade.nome');
    array_unshift($cidade, array(null, null));

    # Preenche o autofocus de acordo com o cpf
    if (empty($sessionCpfPrestador)) {
        $autoFocusCpf = true;
        $autoFocusPrestador = false;
    } else {
        $autoFocusCpf = false;
        $autoFocusPrestador = true;
    }

    # Campos para o formulario
    $objeto->set_campos(array(
        array('linha' => 1,
            'col' => 3,
            'nome' => 'cpf',
            'label' => 'CPF:',
            'tipo' => 'cpf',
            'required' => true,
            'title' => 'CPF do prestador',
            'unique' => true,
            'padrao' => $sessionCpfPrestador,
            'autofocus' => $autoFocusCpf,
            'size' => 20),
        array('linha' => 1,
            'nome' => 'prestador',
            'label' => 'Nome Completo do Prestador:',
            'tipo' => 'texto',
            'required' => true,
            'autofocus' => $autoFocusPrestador,
            'col' => 9,
            'size' => 200),
        array('linha' => 2,
            'nome' => 'dtNascimento',
            'label' => 'Data de Nascimento:',
            'tipo' => 'date',
            'col' => 3,
            'size' => 20),
        array('linha' => 2,
            'nome' => 'especialidade',
            'label' => 'Especialidade do Prestador:',
            'tipo' => 'texto',
            'required' => true,
            'col' => 9,
            'size' => 200),
        array('linha' => 3,
            'col' => 3,
            'nome' => 'inss',
            'label' => 'PIS / PASEP:',
            'tipo' => 'texto',
            'title' => 'Número da inscrição do INSS ou do PIS',
            'size' => 50),
        array('linha' => 3,
            'col' => 3,
            'nome' => 'identidade',
            'label' => 'Documento de Identidade:',
            'tipo' => 'texto',
            'title' => 'Identidade do servidor',
            'size' => 20),
        array('linha' => 3,
            'col' => 3,
            'nome' => 'orgaoId',
            'label' => 'Órgão da Identidade:',
            'tipo' => 'texto',
            'title' => 'Órgão da identidade',
            'size' => 10),
        array('linha' => 3,
            'col' => 3,
            'nome' => 'dataId',
            'label' => 'Data de Emissão da Identidade:',
            'tipo' => 'data',
            'size' => 15,
            'title' => 'Data de Emissão.'),
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
        array('linha' => 3,
            'fieldset' => 'Contatos',
            'nome' => 'telefone1',
            'label' => 'Telefone 1:',
            'tipo' => 'texto',
            'col' => 4,
            'size' => 100),
        array('linha' => 3,
            'nome' => 'telefone2',
            'label' => 'Telefone 2:',
            'tipo' => 'texto',
            'col' => 4,
            'size' => 100),
        array('linha' => 3,
            'nome' => 'email',
            'label' => 'E-mail:',
            'tipo' => 'email',
            'title' => 'E-mail',
            'col' => 4,
            'size' => 100),
        array('linha' => 5,
            'nome' => 'obs',
            'fieldset' => 'fecha',
            'label' => 'Observação:',
            'tipo' => 'textarea',
            'size' => array(80, 5))));

    # idUsuário para o Log
    $objeto->set_idUsuario($idUsuario);

    ################################################################
    switch ($fase) {
        case "" :
        case "listar" :
            $objeto->listar();
            break;

        case "editar" :
        case "excluir" :
            $objeto->$fase($id);
            break;

        case "gravar" :
            $objeto->gravar($id, null, "cadastroRpaPrestadorPosGravacao.php");
            break;
    }

    $page->terminaPagina();
} else {
    loadPage("../../areaServidor/sistema/login.php");
}