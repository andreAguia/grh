<?php

/**
 * Cadastro de Tipos/Modalidades de Licença
 *  
 * By Alat
 */
# Reservado para o servidor logado
$idUsuario = null;

# Configuração
include ("_config.php");

# Permissão de Acesso
$acesso = Verifica::acesso($idUsuario, 2);

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
        $atividade = "Visualizou o cadastro de licenças e afastamentos";
        $data = date("Y-m-d H:i:s");
        $intra->registraLog($idUsuario, $data, $atividade, null, null, 7);
    }

    # pega o id (se tiver)
    $id = soNumeros(get('id'));

    # Pega o parametro de pesquisa (se tiver)
    if (is_null(post('parametro'))) {     # Se o parametro n?o vier por post (for nulo)
        $parametro = retiraAspas(get_session('sessionParametro')); # passa o parametro da session para a variavel parametro retirando as aspas
    } else {
        $parametro = post('parametro');                # Se vier por post, retira as aspas e passa para a variavel parametro
        set_session('sessionParametro', $parametro);    # transfere para a session para poder recuperá-lo depois
    }

    # Começa uma nova página
    $page = new Page();
    $page->iniciaPagina();

    # Cabeçalho da Página
    AreaServidor::cabecalho();

    # Abre um novo objeto Modelo
    $objeto = new Modelo();

    ################################################################
    # Nome do Modelo (aparecerá nos fildset e no caption da tabela)
    $objeto->set_nome('Licenças e Afastamentos');

    # botão de voltar da lista
    $objeto->set_voltarLista('grh.php');

    # controle de pesquisa
    $objeto->set_parametroLabel('Pesquisar');
    $objeto->set_parametroValue($parametro);

    # select da lista
    $objeto->set_selectLista('SELECT idTpLicenca,
                                     idTpLicenca,
                                      periodo,
                                      pericia,
                                      publicacao,
                                      processo,                                  
                                      dtPeriodo,
                                      limite_sexo,
                                      if(tempoServico = 1,"Sim","Não"),
                                      idTpLicenca
                                 FROM tbtipolicenca
                                WHERE nome LIKE "%' . $parametro . '%"
                                   OR idTpLicenca LIKE "%' . $parametro . '%"
                             ORDER BY tbtipolicenca.nome');

    # select do edita
    $objeto->set_selectEdita('SELECT nome,
                                     lei,
                                     periodo,
                                     pericia,
                                     publicacao,
                                     processo,                                  
                                     dtPeriodo,
                                     limite_sexo,
                                     tempoServico,
                                     documentacao,
                                     obs
                                FROM tbtipolicenca
                               WHERE idTpLicenca = ' . $id);

    # Caminhos
    $objeto->set_linkEditar('?fase=editar');

    if (Verifica::acesso($idUsuario, 1)) { // Somente administradores
        #$objeto->set_linkExcluir('?fase=excluir');
    }
    $objeto->set_linkGravar('?fase=gravar');
    $objeto->set_linkListar('?fase=listar');

    # Parametros da tabela
    $objeto->set_label(["id", "Licença / Afastamento", "Período</br>(em dias)", "Perícia", "Publicação", "Processo", "Período Aquisitivo", "Gênero", "Interrompe TS"]);
    $objeto->set_width([5, 27, 9,9, 9, 9, 9, 9, 9]);
    $objeto->set_align(["center", "left"]);
    $objeto->set_classe([null, "Licenca"]);
    $objeto->set_metodo([null, "exibeNome"]);

    # Classe do banco de dados
    $objeto->set_classBd('Pessoal');

    # Nome da tabela
    $objeto->set_tabela('tbtipolicenca');

    # Nome do campo id
    $objeto->set_idCampo('idTpLicenca');

    # Tipo de label do formulário
    $objeto->set_formlabelTipo(1);

    # Campos para o formulario
    $objeto->set_campos(array(
        array('linha' => 1,
            'nome' => 'nome',
            'title' => 'Nome do Afastamento ou Licença',
            'label' => 'Nome do Afastamento ou Licença',
            'tipo' => 'texto',
            'autofocus' => true,
            'col' => 12,
            'size' => 100),
        array('linha' => 2,
            'nome' => 'lei',
            'title' => 'Lei',
            'label' => 'Lei:',
            'tipo' => 'texto',
            'col' => 10,
            'size' => 200),
        array('linha' => 2,
            'nome' => 'periodo',
            'title' => 'Período (em dias) da licença/afastamento',
            'label' => 'Período (em dias):',
            'tipo' => 'texto',
            'col' => 2,
            'size' => 10),
        array('linha' => 4,
            'nome' => 'pericia',
            'title' => 'informa se essa licença/afastamento necessita de perícia',
            'label' => 'Perícia:',
            'tipo' => 'combo',
            'array' => array("Sim", "Não"),
            'size' => 10),
        array('linha' => 4,
            'nome' => 'publicacao',
            'title' => 'informa se essa licença/afastamento necessita de publicação',
            'label' => 'Publicação:',
            'tipo' => 'combo',
            'array' => array("Sim", "Não"),
            'size' => 10),
        array('linha' => 4,
            'nome' => 'processo',
            'title' => 'informa se essa licença/afastamento necessita de processo',
            'label' => 'Processo:',
            'tipo' => 'combo',
            'array' => array("Sim", "Não"),
            'size' => 10),
        array('linha' => 4,
            'nome' => 'dtPeriodo',
            'title' => 'informa se essa licença/afastamento necessita de período aquisitivo',
            'label' => 'Período Aquisitivo:',
            'tipo' => 'combo',
            'array' => array("Sim", "Não"),
            'size' => 10),
        array('linha' => 4,
            'nome' => 'limite_sexo',
            'title' => 'informa se essa licença/afastamento é limitada a servidores de algum sexo',
            'label' => 'Somente ao sexo:',
            'tipo' => 'combo',
            'array' => array("Todos", "Masculino", "Feminino"),
            'size' => 20),
        array('linha' => 4,
            'nome' => 'tempoServico',
            'title' => 'informa se essa licença/afastamento interrompe a contagem do tempo de serviço',
            'label' => 'Interrompe contagem de TS:',
            'tipo' => 'combo',
            'array' => array(array(1, "Sim"), array(0, "Não")),
            'size' => 10),
        array('linha' => 5,
            'nome' => 'documentacao',
            'label' => 'Documentação:',
            'tipo' => 'textarea',
            'size' => array(80, 6)),
        array('linha' => 6,
            'nome' => 'obs',
            'label' => 'Observação:',
            'tipo' => 'textarea',
            'size' => array(80, 5))));

    # idUsuário para o Log
    $objeto->set_idUsuario($idUsuario);

    # Relatório
    $imagem2 = new Imagem(PASTA_FIGURAS . 'print.png', null, 15, 15);
    $botaoRel = new Button();
    $botaoRel->set_title("Exibe Relatório dos Afastamentos e Licenças");
    $botaoRel->set_url("../grhRelatorios/tiposLicenca.php");
    $botaoRel->set_target("_blank");
    $botaoRel->set_imagem($imagem2);
    #$botaoRel->set_accessKey('R');

    $objeto->set_botaoListarExtra(array($botaoRel));

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