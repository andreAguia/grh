<?php

/**
 * Cadastro de Feriados
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
        $atividade = "Cadastro do servidor - Controle de entrega de declaração de acumulações de cargos públicos";
        $data = date("Y-m-d H:i:s");
        $intra->registraLog($idUsuario, $data, $atividade, null, null, 7, $idServidorPesquisado);
    }

    # Verifica a fase do programa
    $fase = get('fase', 'listar');

    # pega o id (se tiver)
    $id = soNumeros(get('id'));

    # Verifica se veio da área de Redução
    $origem = get_session("origem");

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
    $objeto->set_nome('Controle da Entrega da Declaração Anual de Acumulação de Cargo Público');

    # botão de voltar da lista
    if (empty($origem)) {
        $voltar = 'servidorMenu.php';
    } else {
        $voltar = $origem;
    }

    # botão de voltar da lista
    $objeto->set_voltarLista($voltar);

    # select da lista
    $objeto->set_selectLista("SELECT anoReferencia,
                       dtEntrega, 
                       IF(acumula,'<span id=\'vermelho\'>SIM</span>','<span id=\'verde\'>Não</span>'),
                       processo,
                       obs,
                       idAcumulacaoDeclaracao,
                       idAcumulacaoDeclaracao
                  FROM tbacumulacaodeclaracao 
                WHERE idServidor = {$idServidorPesquisado}
                ORDER BY anoReferencia desc");

    # select do edita
    $objeto->set_selectEdita('SELECT anoReferencia,
                                     dtEntrega,
                                     processo,
                                     acumula,
                                     obs,
                                     idServidor
                                FROM tbacumulacaodeclaracao
                               WHERE idAcumulacaoDeclaracao = ' . $id);

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
    $objeto->set_label(array("Referência", "Entregue em", "Acumula?", "Processo", "Obs"));
    $objeto->set_width(array(10, 15, 10, 20, 35));
    $objeto->set_align(array("center", "center", "center", "left", "left"));
    $objeto->set_funcao(array(null, "date_to_php"));

    $objeto->set_formatacaoCondicional(array(
        array('coluna' => 2,
            'valor' => 'SIM',
            'operador' => '=',
            'id' => 'problemas')));

    # Classe do banco de dados
    $objeto->set_classBd('Pessoal');

    # Nome da tabela
    $objeto->set_tabela('tbacumulacaodeclaracao');

    # Nome do campo id
    $objeto->set_idCampo('idAcumulacaoDeclaracao');

    # Tipo de label do formulário
    $objeto->set_formlabelTipo(1);

    # servidor
    $servidor = $pessoal->select('SELECT idServidor, tbpessoa.nome
                                   FROM tbservidor JOIN tbpessoa USING(idPessoa)
                                  WHERE idPerfil = 1
                               ORDER BY situacao, tbpessoa.nome');
    array_unshift($servidor, [null, null]);

    $declaracao = new AcumulacaoDeclaracao();
    $anoDisponível = $declaracao->getProximoAnoReferencia($idServidorPesquisado);

    # Cria um array com os anos possíveis
    $anoInicial = 2019;
    $anoAtual = date('Y');
    $anoExercicio = arrayPreenche($anoInicial, $anoAtual + 1, "d");

    # Campos para o formulario
    $objeto->set_campos(array(
        array(
            'linha' => 1,
            'nome' => 'anoReferencia',
            'label' => 'Ano Referência:',
            'tipo' => 'combo',
            'array' => $anoExercicio,
            'padrao' => $anoDisponível,
            'required' => true,
            'col' => 2,
            'size' => 8),
        array(
            'nome' => 'dtEntrega',
            'label' => 'Data da Entrega:',
            'tipo' => 'date',
            'size' => 20,
            'required' => true,
            "autofocus" => true,
            'title' => 'Data da entega',
            'col' => 3,
            'padrao' => date('Y-m-d'),
            'linha' => 1),
        array(
            'linha' => 1,
            'nome' => 'processo',
            'label' => 'Processo:',
            'tipo' => 'texto',
            'required' => true,
            'col' => 4,
            'size' => 50),
        array(
            'linha' => 1,
            'nome' => 'acumula',
            'label' => 'Acumula?:',
            'tipo' => 'simnao',
            'col' => 3,
            'size' => 5),
        array(
            'linha' => 2,
            'col' => 12,
            'nome' => 'obs',
            'label' => 'Observação:',
            'tipo' => 'textarea',
            'size' => array(80, 5)),
        array(
            'nome' => 'idServidor',
            'label' => 'idServidor:',
            'tipo' => 'hidden',
            'padrao' => $idServidorPesquisado,
            'size' => 5,
            'title' => 'Matrícula',
            'linha' => 3)));
    
    # Procedimentos
    $botaoProcedimentos = new Link("Procedimentos", "?fase=procedimentos");
    $botaoProcedimentos->set_class('button');
    $botaoProcedimentos->set_title('Procedimentos');
    $botaoProcedimentos->set_target("_blank");

    # Site
    $botaoSite = new Button("Site da GRH");
    $botaoSite->set_target('_blank');
    $botaoSite->set_title("Pagina no site da GRH sobre Redução da Carga Horária");
    $botaoSite->set_url("https://uenf.br/dga/grh/gerencia-de-recursos-humanos/acumulacao-de-cargos/declaracao-anual-de-acumulacao-de-cargos/");

    $objeto->set_botaoListarExtra([$botaoProcedimentos, $botaoSite]);

    # idUsuário para o Log
    $objeto->set_idUsuario($idUsuario);
    $objeto->set_idServidorPesquisado($idServidorPesquisado);

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
        
    ###################################################################

        case "procedimentos" :
            $grid = new Grid();
            $grid->abreColuna(12);
            br();

            $rotina = new Rotina();
            $rotina->exibeRotina(8);

            $grid->fechaColuna();
            $grid->fechaGrid();
            break;

        ###################################################################    
    }

    $page->terminaPagina();
} else {
    loadPage("../../areaServidor/sistema/login.php");
}