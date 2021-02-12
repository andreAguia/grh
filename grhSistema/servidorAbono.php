<?php

/**
 * Controle do Abono de Permanencia
 *  
 * By Alat
 */
# Inicia as variáveis que receberão as sessions
$idUsuario = null;              # Servidor logado
$idServidorPesquisado = null; # Servidor Editado na pesquisa do sistema do GRH
# Configuração
include ("_config.php");

# Permissão de Acesso
$acesso = Verifica::acesso($idUsuario, 2);

if ($acesso) {
    # Conecta ao Banco de Dados
    $pessoal = new Pessoal();
    $intra = new Intra();

    # Verifica se veio menu grh e registra o acesso no log
    $grh = get('grh', false);
    if ($grh) {
        # Grava no log a atividade
        $atividade = "Cadastro do servidor - Cadastro de abono permanência";
        $data = date("Y-m-d H:i:s");
        $intra->registraLog($idUsuario, $data, $atividade, null, null, 7, $idServidorPesquisado);
    }

    # Verifica a fase do programa
    $fase = get('fase', 'listar');

    # pega o id (se tiver)
    $id = soNumeros(get('id'));

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
    $objeto->set_nome('Cadastro de Abono Permanência');

    # botão de voltar da lista
    $objeto->set_voltarLista('servidorMenu.php');

    # select da lista
    $objeto->set_selectLista('SELECT processo,
                                     dtPublicacao,
                                     pgPublicacao,
                                     if(status = 1,"Deferido","Indeferido"),
                                     data,
                                     obs,
                                     idAbono
                                FROM tbabono
                               WHERE idServidor = ' . $idServidorPesquisado . '
                            ORDER BY dtPublicacao desc');

    # select do edita
    $objeto->set_selectEdita('SELECT processo,
                                     dtPublicacao,
                                     pgPublicacao,
                                     status,
                                     data,
                                     obs,
                                     idServidor
                                FROM tbabono
                               WHERE idAbono = ' . $id);

    # Caminhos
    $objeto->set_linkEditar('?fase=editar');
    $objeto->set_linkExcluir('?fase=excluir');
    $objeto->set_linkGravar('?fase=gravar');
    $objeto->set_linkListar('?fase=listar');

    # Parametros da tabela
    $objeto->set_label(array("Processo", "Publicaçao", "Pag", "Status", "Data", "Obs"));
    $objeto->set_width(array(15,10,5,10,10,40));	
    $objeto->set_align(array("left","center","center","center","center","left"));
    $objeto->set_funcao(array(null, "date_to_php", null, null, "date_to_php"));

    # Classe do banco de dados
    $objeto->set_classBd('pessoal');

    # Nome da tabela
    $objeto->set_tabela('tbabono');

    # Nome do campo id
    $objeto->set_idCampo('idAbono');

    # Tipo de label do formulário
    $objeto->set_formLabelTipo(1);

    # Campos para o formulario
    $objeto->set_campos(array(array('nome' => 'processo',
            'label' => 'Processo:',
            'tipo' => 'texto',
            'size' => 30,
            'required' => true,
            'autofocus' => true,
            'title' => 'O numero do processo.',
            'col' => 3,
            'linha' => 1),
        array('nome' => 'dtPublicacao',
            'label' => 'Publicaçao:',
            'tipo' => 'data',
            'size' => 10,
            'col' => 3,
            'required' => true,
            'title' => 'A data da publicaçao no DOERJ.',
            'linha' => 2),
        array('nome' => 'pgPublicacao',
            'label' => 'Página:',
            'tipo' => 'texto',
            'size' => 10,
            'col' => 2,
            'title' => 'A página da Publicação no DOERJ.',
            'linha' => 2),
        array('nome' => 'status',
            'label' => 'Status:',
            'tipo' => 'combo',
            'array' => array(array(null, ""), array(1, "Deferido"), array(2, "Indeferido")),
            'size' => 20,
            'title' => 'Se o processo foi deferido ou indeferido',
            'col' => 3,
            'required' => true,
            'linha' => 3),
        array('nome' => 'data',
            'label' => 'Data:',
            'tipo' => 'data',
            'size' => 10,
            'col' => 3,
            'title' => 'A data em que o servidor passou a receber.',
            'linha' => 3),
        array('linha' => 4,
            'col' => 12,
            'nome' => 'obs',
            'label' => 'Obs:',
            'tipo' => 'textarea',
            'fieldset' => 'fecha',
            'title' => 'Observações.',
            'size' => array(80, 4)),
        array('nome' => 'idServidor',
            'label' => 'idServidor',
            'tipo' => 'hidden',
            'padrao' => $idServidorPesquisado,
            'size' => 5,
            'title' => 'Matrícula',
            'linha' => 6)));

    # site da grh
    $botao1 = new Button("Site da GRH");
    $botao1->set_target('_blank');
    $botao1->set_title("Pagina no site da GRH sobre Abono Permanencia");
    $botao1->set_url("http://uenf.br/dga/grh/gerencia-de-recursos-humanos/abono-de-permanencia/");

    $objeto->set_botaoListarExtra(array($botao1));

    # Log
    $objeto->set_idUsuario($idUsuario);
    $objeto->set_idServidorPesquisado($idServidorPesquisado);

    ################################################################

    switch ($fase) {
        case "" :
        case "listar" :
        case "editar" :
        case "excluir" :
            $objeto->$fase($id);
            break;

        case "gravar" :
            $objeto->gravar($id, "servidorAbonoExtra.php");
            break;
    }

    $page->terminaPagina();
} else {
    loadPage("../../areaServidor/sistema/login.php");
}