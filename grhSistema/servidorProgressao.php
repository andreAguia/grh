<?php

/**
 * Histórico de Progressões e Enquadramentos
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

    # Verifica a fase do programa
    $fase = get('fase', 'listar');

    $intra = new Intra();

    # Verifica se veio menu grh e registra o acesso no log
    $grh = get('grh', false);
    if ($grh) {
        # Grava no log a atividade
        $atividade = "Cadastro do servidor - Histórico de progressão e enquadramento";
        $data = date("Y-m-d H:i:s");
        $intra->registraLog($idUsuario, $data, $atividade, null, null, 7, $idServidorPesquisado);
    }

    # Verifica de onde veio
    $origem = get_session("origem");

    # pega o id (se tiver)
    $id = soNumeros(get('id'));

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
    # Exibe os dados do Servidor
    $objeto->set_rotinaExtra("get_DadosServidor");
    $objeto->set_rotinaExtraParametro($idServidorPesquisado);

    # Nome do Modelo (aparecerá nos fildset e no caption da tabela)
    $objeto->set_nome('Cadastro de Progressões e Enquadramentos do Servidor');

    # botão de voltar da lista
    if ($origem == "areaProgressao.php") {
        $objeto->set_voltarLista($origem);
    } else {
        $objeto->set_voltarLista('servidorMenu.php');
    }

    # ordenação
    if (is_null($orderCampo)) {
        $orderCampo = "1";
    }

    if (is_null($orderTipo)) {
        $orderTipo = 'desc';
    }

    # select da lista
    $objeto->set_selectLista('SELECT tbprogressao.dtInicial,
                                     tbtipoprogressao.nome,
                                     idClasse,
                                     dtPublicacao,
                                     tbprogressao.obs,
                                     tbprogressao.idProgressao
                                FROM tbprogressao JOIN tbtipoprogressao ON (tbprogressao.idTpProgressao = tbtipoprogressao.idTpProgressao)
                               WHERE idServidor = ' . $idServidorPesquisado . '
                            ORDER BY ' . $orderCampo . ' ' . $orderTipo);

    # select do edita
    $objeto->set_selectEdita('SELECT dtInicial,
                                     idTpProgressao,
                                     idClasse,
                                     documento,
                                     numProcesso,
                                     dtPublicacao,
                                     obs,
                                     idServidor
                                FROM tbprogressao
                               WHERE idProgressao = ' . $id);

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
    $objeto->set_label(array("Data Inicial", "Tipo", "Valor", "DOERJ", "Obs"));
    $objeto->set_width(array(10, 20, 20, 10, 30));
    $objeto->set_align(array("center", "left", "center", "center", "left"));
    $objeto->set_funcao(array("date_to_php", null, null, "date_to_php"));
    $objeto->set_classe(array(null, null, "PlanoCargos"));
    $objeto->set_metodo(array(null, null, "evibeValor"));

    # Formatação condicional
    $objeto->set_formatacaoCondicional(array(array('coluna' => 1,
            'valor' => "Importado",
            'operador' => '=',
            'id' => 'importado')));

    # Classe do banco de dados
    $objeto->set_classBd('pessoal');

    # Nome da tabela
    $objeto->set_tabela('tbprogressao');

    # Nome do campo id
    $objeto->set_idCampo('idProgressao');

    # Tipo de label do formulário
    $objeto->set_formLabelTipo(1);

    # Pega os dados da combo prograssao
    $lista = new Pessoal();
    $result1 = $lista->select('SELECT idTpProgressao, 
                                      nome
                                 FROM tbtipoprogressao
                             ORDER BY nome');
    array_push($result1, array(null, null)); # Adiciona o valor de nulo
    # Pega os dados da combo classe
    $nivel = $lista->get_nivelCargo($idServidorPesquisado);
    $idCargo = $lista->get_idCargo($idServidorPesquisado);

    $combo = 'SELECT idClasse, concat("R$ ",Valor," - ",faixa," ( ",tbplano.numdecreto," - ",DATE_FORMAT(tbplano.dtPublicacao,"%d/%m/%Y")," )") as classe 
                FROM tbclasse JOIN tbplano ON (tbplano.idPlano = tbclasse.idPlano)
               WHERE nivel = "' . $nivel . '"';

    if ($idCargo == 128) {
        $combo .= ' AND (SUBSTRING(faixa, 1, 1) = "E" OR faixa = "Associado" OR SUBSTRING(faixa, 1, 1) = "I")';
    }

    if ($idCargo == 129) {
        $combo .= ' AND (SUBSTRING(faixa, 1, 1) = "F" OR faixa = "Titular" OR SUBSTRING(faixa, 1, 1) = "X")';
    }

    $combo .= ' ORDER BY tbplano.planoAtual, tbplano.dtPublicacao, tbplano.planoAtual, SUBSTRING(faixa, 1, 1), valor';


    $result2 = $lista->select($combo);

    array_unshift($result2, array(null, null)); # Adiciona o valor de nulo
    # Campos para o formulario
    $objeto->set_campos(array(array('nome' => 'dtInicial',
            'label' => 'Data Inicial:',
            'tipo' => 'data',
            'size' => 20,
            'required' => true,
            'autofocus' => true,
            'col' => 3,
            'title' => 'Data inícial da Progressão ou Enquadramento.',
            'linha' => 1),
        array('nome' => 'idTpProgressao',
            'label' => 'Tipo:',
            'tipo' => 'combo',
            'col' => 3,
            'required' => true,
            'array' => $result1,
            'size' => 20,
            'title' => 'Tipo de Progressão / Enquadramento',
            'linha' => 1),
        array('nome' => 'idClasse',
            'label' => 'Classe:',
            'tipo' => 'combo',
            'array' => $result2,
            'size' => 20,
            'col' => 6,
            'required' => true,
            'title' => 'Valor',
            'linha' => 1),
        array('nome' => 'documento',
            'label' => 'Documento:',
            'tipo' => 'texto',
            'size' => 30,
            'col' => 4,
            'title' => 'Documento comunicando a nova progressão.',
            'linha' => 2),
        array('nome' => 'numProcesso',
            'label' => 'Processo:',
            'tipo' => 'texto',
            'size' => 30,
            'col' => 3,
            'title' => 'Número do Processo',
            'linha' => 2),
        array('nome' => 'dtPublicacao',
            'label' => 'Data da Pub. no DOERJ:',
            'tipo' => 'data',
            'size' => 20,
            'col' => 3,
            'title' => 'Data da Publicação no DOERJ.',
            'linha' => 2),
        array('linha' => 3,
            'nome' => 'obs',
            'col' => 12,
            'label' => 'Observação:',
            'tipo' => 'textarea',
            'size' => array(80, 5)),
        array('nome' => 'idServidor',
            'label' => 'idServidor:',
            'tipo' => 'hidden',
            'padrao' => $idServidorPesquisado,
            'size' => 5,
            'title' => 'Matrícula',
            'linha' => 8)));


    # tabela
    $botao = new Button("Tabela", "tabelaSalarial.php");
    $botao->set_title("Exibe a tabela salarial do plano de cargos requisitado");
    $botao->set_target("_blank");

    # Relatório
    $imagem = new Imagem(PASTA_FIGURAS . 'print.png', null, 15, 15);
    $botaoRel = new Button();
    $botaoRel->set_imagem($imagem);
    $botaoRel->set_title("Imprimir Relatório de Histórico de Progressões e Enquadramentos");
    $botaoRel->set_url("../grhRelatorios/servidorProgressao.php");
    $botaoRel->set_target("_blank");

    $objeto->set_botaoListarExtra(array($botao, $botaoRel));

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
            $objeto->gravar($id, "servidorProgressaoExtra.php");
            break;
    }
    $page->terminaPagina();
} else {
    loadPage("../../areaServidor/sistema/login.php");
}