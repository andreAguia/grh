<?php

/**
 * Histórico de Progressões
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
    $intra = new Intra();
    $pessoal = new Pessoal();
    $avaliacao = new Avaliacao();

    # Verifica a fase do programa
    $fase = get('fase', 'listar');

    # Verifica se veio menu grh e registra o acesso no log
    $grh = get('grh', false);
    if ($grh) {
        # Grava no log a atividade
        $atividade = "Cadastro do servidor - Avaliações de desempenho e qualidade";
        $data = date("Y-m-d H:i:s");
        $intra->registraLog($idUsuario, $data, $atividade, null, null, 7, $idServidorPesquisado);
    }

    # Verifica de onde veio
    $origem = get_session("origem");

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
    if ($fase <> "listar") {
        $objeto->set_rotinaExtra("get_DadosServidor");
        $objeto->set_rotinaExtraParametro($idServidorPesquisado);
    }

    # Nome do Modelo (aparecerá nos fildset e no caption da tabela)
    $objeto->set_nome('Avaliações de Desempenho e Qualidade');

    # botão de voltar da lista
    if (empty($origem)) {
        $objeto->set_voltarLista('servidorMenu.php');
    } else {
        $objeto->set_voltarLista($origem);
    }

    # select da lista
    $objeto->set_selectLista("SELECT CASE tipo
                                          WHEN 1 THEN 'Estágio' 
                                          WHEN 2 THEN 'Anual'
                                     END,
                                     referencia,
                                     CONCAT(DATE_FORMAT(dtPeriodo1,'%d/%m/%Y'),' - ',DATE_FORMAT(dtPeriodo2,'%d/%m/%Y')),
                                     idAvaliacao,
                                     idAvaliacao,
                                     idAvaliacao,
                                     idAvaliacao,
                                     idAvaliacao,
                                     idAvaliacao
                                FROM tbavaliacao
                               WHERE idServidor = {$idServidorPesquisado}
                            ORDER BY dtPeriodo1 DESC");

    # select do edita
    $objeto->set_selectEdita('SELECT tipo,
                                     referencia,
                                     dtPeriodo1,
                                     dtPeriodo2,
                                     nota1,
                                     nota2,
                                     nota3,
                                     dtPublicacao,
                                     pgPublicacao,
                                     obs,
                                     idServidor
                                FROM tbavaliacao
                               WHERE idAvaliacao = ' . $id);

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
    $objeto->set_label(["Tipo", "Referencia", "Período", "Nota 1", "Nota 2", "Nota 3", "Total", "Publicação", "Obs"]);
    $objeto->set_width([6, 6, 19, 10, 10, 10, 10, 15, 5]);
    #$objeto->set_align(array("center", "left", "center", "center", "left"));
    #$objeto->set_funcao(array("date_to_php", null, null, "date_to_php"));
    $objeto->set_classe([null, null, null, "Avaliacao", "Avaliacao", "Avaliacao", "Avaliacao", "Avaliacao", "Avaliacao"]);
    $objeto->set_metodo([null, null, null, "exibeNota1", "exibeNota2", "exibeNota3", "exibeTotal", "exibePublicacao", "exibeObs"]);
    $objeto->set_rowspan(1);
    $objeto->set_grupoCorColuna(1);

    # Classe do banco de dados
    $objeto->set_classBd('pessoal');

    # Nome da tabela
    $objeto->set_tabela('tbavaliacao');

    # Nome do campo id
    $objeto->set_idCampo('idAvaliacao');

    # Tipo de label do formulário
    $objeto->set_formLabelTipo(1);

    # Cria um array com os anos possíveis
    $anoInicial = $pessoal->get_anoAdmissao($idServidorPesquisado);
    $anoAtual = date('Y');
    $referenciasPossiveis = arrayPreenche($anoInicial, $anoAtual + 1);

    array_unshift($referenciasPossiveis, "AV4");
    array_unshift($referenciasPossiveis, "AV3");
    array_unshift($referenciasPossiveis, "AV2");
    array_unshift($referenciasPossiveis, "AV1");
    array_unshift($referenciasPossiveis, null);

    # Propoe os períodos (quando for inclusão)
    if ($fase == "editar" AND empty($id)) {
        $avaliacao = new Avaliacao();
        $dados = $avaliacao->getPeriodoEAno($idServidorPesquisado);
        $dtPeriodo1 = date_to_bd($dados[0]);
        $dtPeriodo2 = date_to_bd($dados[1]);
        $tipo = $dados[2];
        $referencia = $dados[3];
    } else {
        $dtPeriodo1 = null;
        $dtPeriodo2 = null;
        $tipo = null;
        $referencia = null;
    }


    # Campos para o formulario
    $objeto->set_campos(array(
        array('nome' => 'tipo',
            'label' => 'Tipo:',
            'tipo' => 'combo',
            'autofocus' => true,
            'col' => 3,
            'required' => true,
            'array' => [[null, null], [1, "Estágio Probatório"], [2, "Anual"]],
            'size' => 10,
            'padrao' => $tipo,
            'title' => 'Tipo de Avaliação',
            'linha' => 1),
        array('nome' => 'referencia',
            'label' => 'Referência:',
            'tipo' => 'combo',
            'array' => $referenciasPossiveis,
            'size' => 10,
            'col' => 3,
            'required' => true,
            'padrao' => $referencia,
            'title' => 'Valor',
            'linha' => 1),
        array('nome' => 'dtPeriodo1',
            'label' => 'Data Inicial do Período:',
            'tipo' => 'data',
            'size' => 15,
            'col' => 3,
            'required' => true,
            'padrao' => $dtPeriodo1,
            'title' => 'Data Inicial do Período',
            'linha' => 2),
        array('nome' => 'dtPeriodo2',
            'label' => 'Data Final do Período:',
            'tipo' => 'data',
            'size' => 15,
            'col' => 3,
            'required' => true,
            'padrao' => $dtPeriodo2,
            'title' => 'Data Final do Período',
            'linha' => 2),
        array('nome' => 'nota1',
            'label' => 'Nota 1:',
            'tipo' => 'texto',
            //'required' => true,
            'size' => 6,
            'col' => 2,
            'title' => 'Nota da primeira avaliação.',
            'linha' => 3),
        array('nome' => 'nota2',
            'label' => 'Nota 2:',
            'tipo' => 'texto',
            //'required' => true,
            'size' => 6,
            'col' => 2,
            'title' => 'Nota da segunda avaliação.',
            'linha' => 3),
        array('nome' => 'nota3',
            'label' => 'Nota 3:',
            'tipo' => 'texto',
            //'required' => true,
            'size' => 6,
            'col' => 2,
            'title' => 'Nota da terceira avaliação.',
            'linha' => 3),
        array('nome' => 'dtPublicacao',
            'label' => 'Data da Pub. no DOERJ:',
            'tipo' => 'data',
            'size' => 15,
            'col' => 3,
            'title' => 'Data da Publicação no DOERJ.',
            'linha' => 2),
        array('nome' => 'pgPublicacao',
            'label' => 'Página:',
            'tipo' => 'texto',
            'size' => 5,
            'col' => 1,
            'title' => 'Página da publicação no DOERJ.',
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

    # Log
    $objeto->set_idUsuario($idUsuario);
    $objeto->set_idServidorPesquisado($idServidorPesquisado);

    ################################################################

    switch ($fase) {
        case "" :
        case "listar" :
            # Retira os botões da classe
            $objeto->set_botaoVoltarLista(false);
            $objeto->set_botaoIncluir(false);

            # Limita o tamanho da tela
            $grid = new Grid();
            $grid->abreColuna(12);

            # Cria um menu
            $menu = new MenuBar();

            # Botão voltar
            if (empty($origem)) {
                $linkBotao1 = new Link("Voltar", 'servidorMenu.php');
            } else {
                $linkBotao1 = new Link("Voltar", $origem);
            }

            $linkBotao1->set_class('button');
            $linkBotao1->set_title('Volta para a página anterior');
            $linkBotao1->set_accessKey('V');
            $menu->add_link($linkBotao1, "left");

            # Afastamentos
            $botaoAfast = new Button('Todos os Afastamentos', 'servidorAfastamentos.php?volta=0');
            $botaoAfast->set_title("Verifica todos os afastamentos deste servidor");
            $botaoAfast->set_target("_blank");
            $menu->add_link($botaoAfast, "right");

            # Incluir
            if (Verifica::acesso($idUsuario, [1, 2])) {
                $botaoIncluir = new Button("Incluir", '?fase=editar');
                $botaoIncluir->set_title("Incluir novo registro");
                $menu->add_link($botaoIncluir, "right");
            }

            $menu->show();

            # Exibe os dados do servidor
            get_DadosServidor($idServidorPesquisado);

            $grid->fechaColuna();
            $grid->abreColuna(3);

            $processoSei = $avaliacao->getProcessoSei($idServidorPesquisado);
            $processoFisico = $avaliacao->getProcessoFisico($idServidorPesquisado);

            tituloTable("N° do Processo:");
            $painel = new Callout();
            $painel->abre();

            if (empty($processoSei)) {
                p("---", 'f14', "center");
            } else {
                p($processoSei, 'f14', "center");
            }

            # Verifica se tem processo antigo
            if (!is_null($processoFisico)) {
                p($processoFisico, "processoAntigoReducao");
            }

            $div = new Div("divEditaProcesso");
            $div->abre();
            if (empty($processoSei)) {
                $link = new Link("Incluir Processo", 'servidorProcessoAvaliacao.php', "Inclui o número do processo");
            } else {
                $link = new Link("Editar Processo", 'servidorProcessoAvaliacao.php', "Edita o número do processo");
            }
            $link->set_id("editaProcesso");
            $link->show();
            $div->fecha();

            $painel->fecha();

            $grid->fechaColuna();

            $grid->abreColuna(9);
            $objeto->listar();
            $grid->fechaColuna();
            $grid->fechaGrid();

            break;

        case "editar" :
        case "excluir" :
            $objeto->$fase($id);
            break;

        case "gravar" :
            $objeto->gravar($id, "servidorAvaliacaoExtra.php");
            break;
    }
    $page->terminaPagina();
} else {
    loadPage("../../areaServidor/sistema/login.php");
}