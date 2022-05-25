<?php

/**
 * Cadastro de Estado Civil
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
        $atividade = "Visualizou o cadastro de cidade";
        $data = date("Y-m-d H:i:s");
        $intra->registraLog($idUsuario, $data, $atividade, null, null, 7);
    }

    # pega o id (se tiver)
    $id = soNumeros(get('id'));

    # Pega o parametro de pesquisa (se tiver)
    if (is_null(post('parametro'))) {     # Se o parametro n?o vier por post (for nulo)
        $parametro = retiraAspas(get_session('sessionParametro'));  # passa o parametro da session para a variavel parametro retirando as aspas
    } else {
        $parametro = post('parametro');                # Se vier por post, retira as aspas e passa para a variavel parametro
        set_session('sessionParametro', $parametro);    # transfere para a session para poder recuperá-lo depois
    }

    # Ordem da tabela
    $orderCampo = get('orderCampo');
    $orderTipo = get('orderTipo');

    # Começa uma nova página
    $page = new Page();
    $page->iniciaPagina();

    # Cabeçalho da Página
    AreaServidor::cabecalho();

    # Abre um novo objeto Modelo
    $objeto = new Modelo();

    ################################################################
    # Nome do Modelo (aparecerá nos fildset e no caption da tabela)
    $objeto->set_nome('Professor Visitante');

    # botão de voltar da lista
    $objeto->set_voltarLista('grh.php');

    # controle de pesquisa
    $objeto->set_parametroLabel('Pesquisar');
    $objeto->set_parametroValue($parametro);

    # ordenaç?o
    if (is_null($orderCampo)) {
        $orderCampo = "2";
    }

    if (is_null($orderTipo)) {
        $orderTipo = 'asc';
    }

    # select da lista
    $objeto->set_selectLista("SELECT cpf,
                                     tbvisitante.nome,
                                     CONCAT(DIR,' - ',GER,' - ',tblotacao.nome),
                                     idVisitante
                                FROM tbvisitante JOIN tblotacao USING (idLotacao)
                               WHERE tbvisitante.nome LIKE '%{$parametro}%'
                             ORDER BY nome");

    # select do edita
    $objeto->set_selectEdita("SELECT nome,
                                     cpf,
                                     idLotacao,
                                     endereco,
                                     bairro,
                                     idCidade,
                                     cep,
                                     email,
                                     telefone1DDD,
                                     telefone1,
                                     telefone2DDD,
                                     telefone2
                                FROM tbvisitante
                               WHERE idVisitante = {$id}");

    # Habilita o modo leitura para usuario de regra 12
    if (Verifica::acesso($idUsuario, 12)) {
        $objeto->set_modoLeitura(true);
    }

    # ordem da lista
    $objeto->set_orderCampo($orderCampo);
    $objeto->set_orderTipo($orderTipo);
    $objeto->set_orderChamador('?fase=listar');

    # Caminhos
    $objeto->set_linkEditar('?fase=editar');
    $objeto->set_linkExcluir('?fase=excluir');
    $objeto->set_linkGravar('?fase=gravar');
    $objeto->set_linkListar('?fase=listar');

    # Parametros da tabela
    $objeto->set_label(array("Cpf", "Professor Visitante", "Lotação"));
    $objeto->set_width(array(20, 30, 40));
    $objeto->set_align(array("center", "left", "left"));

    # Classe do banco de dados
    $objeto->set_classBd('Pessoal');

    # Nome da tabela
    $objeto->set_tabela('tbvisitante');

    # Nome do campo id
    $objeto->set_idCampo('idVisitante');

    # Tipo de label do formulário
    $objeto->set_formlabelTipo(1);

    # Pega os dados da combo idLotacao
    $selectLotacao = 'SELECT idlotacao, 
                             concat(IFnull(tblotacao.UADM,"")," - ",IFnull(tblotacao.DIR,"")," - ",IFnull(tblotacao.GER,"")," - ",IFnull(tblotacao.nome,"")) as lotacao
                        FROM tblotacao ORDER BY ativo desc, lotacao';

    $result = $pessoal->select($selectLotacao);
    array_unshift($result, array(null, null)); # Adiciona o valor de nulo
    # Pega os dados da combo de cidade
    $cidade = $pessoal->select('SELECT idCidade,
                                       CONCAT(tbcidade.nome," (",tbestado.uf,")")
                                  FROM tbcidade JOIN tbestado USING (idEstado)
                              ORDER BY proximidade,tbestado.uf,tbcidade.nome');
    array_unshift($cidade, array(null, null)); # Adiciona o valor de nulo
    # Campos para o formulario
    $objeto->set_campos(array(
        array('linha' => 1,
            'nome' => 'nome',
            'label' => 'Nome do Professor Visitante:',
            'tipo' => 'texto',
            'required' => true,
            'autofocus' => true,
            'col' => 9,
            'size' => 150),
        array('linha' => 1,
            'nome' => 'cpf',
            'title' => 'Cpf',
            'label' => 'Cpf:',
            'tipo' => 'cpf',
            'col' => 3,
            'size' => 15),
        array('nome' => 'idLotacao',
            'label' => 'Lotacão:',
            'tipo' => 'combo',
            'array' => $result,
            'size' => 20,
            'col' => 12,
            'title' => 'Em qual setor o professor visitante está lotado',
            'linha' => 1),
        array('linha' => 3,
            'nome' => 'endereco',
            'label' => 'Endereço:',
            'tipo' => 'texto',
            'fieldset' => 'Endereço',
            'plm' => true,
            'title' => 'Endereço do Professor Visitante',
            'col' => 12,
            'size' => 150),
        array('linha' => 4,
            'nome' => 'bairro',
            'label' => 'Bairro:',
            'tipo' => 'texto',
            'title' => 'Bairro',
            'plm' => true,
            'col' => 4,
            'size' => 50),
        array('linha' => 4,
            'nome' => 'idCidade',
            'label' => 'Cidade:',
            'tipo' => 'combo',
            'array' => $cidade,
            'title' => 'Cidade de Moradia do Professor Visitante',
            'col' => 5,
            'size' => 30),
        array('linha' => 4,
            'nome' => 'cep',
            'label' => 'Cep:',
            'tipo' => 'cep',
            'title' => 'Cep',
            'col' => 3,
            'size' => 10),
        array('linha' => 5,
            'nome' => 'telefone1DDD',
            'label' => 'DDD:',
            'tipo' => 'texto',
            'title' => 'DDD',
            'col' => 1,
            'fieldset' => 'Contatos',
            'size' => 2),
        array('linha' => 5,
            'nome' => 'telefone1',
            'label' => 'Telefone:',
            'tipo' => 'telefone',
            'title' => 'Telefone',
            'col' => 3,
            'size' => 30),
        array('linha' => 5,
            'nome' => 'telefone2DDD',
            'label' => 'DDD:',
            'tipo' => 'texto',
            'title' => 'DDD',
            'col' => 1,
            'fieldset' => 'Telefones',
            'size' => 2),
        array('linha' => 5,
            'nome' => 'telefone2',
            'label' => 'Celular:',
            'tipo' => 'celular',
            'title' => 'Telefone',
            'col' => 3,
            'size' => 30),
        array('linha' => 5,
            'nome' => 'email',
            'label' => 'E-mail:',
            'tipo' => 'email',
            'title' => 'E-mail',
            'col' => 4,
            'size' => 100),
    ));

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
        case "gravar" :
            $objeto->$fase($id);
            break;
    }

    $page->terminaPagina();
} else {
    loadPage("../../areaServidor/sistema/login.php");
}