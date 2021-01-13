<?php

/**
 * Histórico de Férias de um servidor
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
        $atividade = "Cadastro do servidor - Histórico de férias";
        $data = date("Y-m-d H:i:s");
        $intra->registraLog($idUsuario, $data, $atividade, null, null, 7, $idServidorPesquisado);
    }

    # Verifica a fase do programa
    $fase = get('fase', 'listar');

    # Verifica se veio da área de férias
    $areaFerias = get_session("areaFerias");

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
    # Nome do Modelo (aparecerá nos fildset e no caption da tabela)
    $objeto->set_nome('Histórico de Férias');

    # botão de voltar da lista
    if ($areaFerias == "exercicio") {
        $voltarLista = 'areaFeriasExercicio.php';
    } elseif ($areaFerias == "fruicao") {
        $voltarLista = 'areaFeriasFruicao.php';
    } else {
        $voltarLista = 'servidorMenu.php';
    }

    # botão de voltar do formulário
    $objeto->set_linkListar('?fase=listar');

    # select da lista
    $objeto->set_selectLista('SELECT anoExercicio,
                                     status,
                                     dtInicial,
                                     numDias,
                                     ADDDATE(dtInicial,numDias-1),
                                     idFerias,
                                     obs,
                                     idFerias
                                FROM tbferias
                               WHERE idServidor = ' . $idServidorPesquisado . '
                            ORDER BY anoExercicio desc, dtInicial desc');

    # select do edita
    $objeto->set_selectEdita('SELECT anoExercicio,
                                     dtInicial,
                                     numDias,
                                     obs,                                     
                                     status,
                                     idServidor
                                FROM tbferias
                               WHERE idFerias = ' . $id);

    # Caminhos
    $objeto->set_linkEditar('?fase=editar');
    $objeto->set_linkExcluir('?fase=excluir');
    $objeto->set_linkGravar('?fase=gravar');

    # Parametros da tabela
    $objeto->set_label(array("Exercicio", "Status", "Data Inicial", "Dias", "Data Final", "Período", "Obs"));
    $objeto->set_align(array("center"));
    $objeto->set_funcao(array(null, null, 'date_to_php', null, 'date_to_php', null));
    $objeto->set_width(array(10, 10, 15, 10, 15, 10, 20));
    $objeto->set_classe(array(null, null, null, null, null, "pessoal"));
    $objeto->set_metodo(array(null, null, null, null, null, "get_feriasPeriodo"));

    $objeto->set_rowspan(0);
    $objeto->set_grupoCorColuna(0);

    # Classe do banco de dados
    $objeto->set_classBd('pessoal');

    # Nome da tabela
    $objeto->set_tabela('tbferias');

    # Nome do campo id
    $objeto->set_idCampo('idFerias');

    # Tipo de label do formulário
    $objeto->set_formLabelTipo(1);

    # Pega o valor para o anoexercicio
    $exercícioDisponivel = $pessoal->get_feriasExercicioDisponivel($idServidorPesquisado);

    # Campos para o formulario
    $objeto->set_campos(array(array('nome' => 'anoExercicio',
            'label' => 'Ano do Exercício:',
            'tipo' => 'numero',
            'size' => 7,
            'col' => 2,
            'padrao' => $exercícioDisponivel,
            'required' => true,
            'autofocus' => true,
            'title' => 'Ano de Exercício das Férias.',
            'linha' => 1),
        array('nome' => 'dtInicial',
            'label' => 'Data Inicial:',
            'tipo' => 'data',
            'size' => 20,
            'col' => 3,
            'required' => true,
            'title' => 'Data do início das férias.',
            'linha' => 1),
        array('nome' => 'numDias',
            'label' => 'Dias:',
            'tipo' => 'combo',
            'array' => array(null, 30, 20, 15, 10),
            'col' => 2,
            'size' => 5,
            'required' => true,
            'title' => 'Dias de Férias.',
            'linha' => 1),
        array('linha' => 3,
            'col' => 10,
            'nome' => 'obs',
            'label' => 'Observação:',
            'tipo' => 'textarea',
            'size' => array(40, 5)),
        array('nome' => 'idServidor',
            'label' => 'idServidor:',
            'tipo' => 'hidden',
            'padrao' => $idServidorPesquisado,
            'size' => 5,
            'title' => 'Matrícula',
            'linha' => 4),
        array('nome' => 'status',
            'label' => 'Status:',
            'tipo' => 'hidden',
            'size' => 20,
            'col' => 2,
            'title' => 'Status das férias',
            'linha' => 1)));

    # Log
    $objeto->set_idUsuario($idUsuario);
    $objeto->set_idServidorPesquisado($idServidorPesquisado);

    # Retira os botoes da classe modelo
    $objeto->set_botaoIncluir(false);
    $objeto->set_botaoVoltarLista(false);
    $objeto->set_comGridLista(false);

################################################################

    switch ($fase) {

        case "" :
        case "listar" :

            # Cria um menu
            $menu1 = new MenuBar();

            # Limita a tela
            $grid1 = new Grid();
            $grid1->abreColuna(12);

            # Voltar
            $linkVoltar = new Link("Voltar", $voltarLista);
            $linkVoltar->set_class('button');
            $menu1->add_link($linkVoltar, "left");

            # Relatório
            $imagem = new Imagem(PASTA_FIGURAS . 'print.png', null, 15, 15);
            $botaoRel = new Button();
            $botaoRel->set_imagem($imagem);
            $botaoRel->set_title("Imprimir Relatório de Histórico de Férias");
            $botaoRel->set_target("_blank");
            $botaoRel->set_url('../grhRelatorios/servidorFerias.php');
            $menu1->add_link($botaoRel, "right");

            # Incluir
            $linkIncluir = new Link("Incluir", '?fase=editar');
            $linkIncluir->set_class('button');
            $linkIncluir->set_title('Incluir novas ferias');
            $menu1->add_link($linkIncluir, "right");

            $menu1->show();

            # Exibe os dados do servidor pesquisado
            get_DadosServidor($idServidorPesquisado);

            $grid1->fechaColuna();
            $grid1->fechaGrid();

            $grid2 = new Grid();
            $grid2->abreColuna(3);

            $lista = $pessoal->get_feriasResumo($idServidorPesquisado);
            if (is_null($lista)) {
                br();
                tituloTable('Resumo');
                $callout = new Callout();
                $callout->abre();
                p('Nenhum item encontrado !!', 'center');
                $callout->fecha();
            } else {
                # Exibe a tabela
                $tabela = new Tabela();
                $tabela->set_conteudo($lista);
                $tabela->set_titulo('Resumo');
                $tabela->set_label(array("Exercício", "Dias", "Faltam"));
                $tabela->set_align(array("center"));
                $tabela->set_formatacaoCondicional(array(array('coluna' => 1,
                        'valor' => 30,
                        'operador' => '<>',
                        'id' => 'problemas')));
                $tabela->show();
            }

            $grid2->fechaColuna();
            $grid2->abreColuna(9);
            
            # Exibe as férias pendentes
            $ferias = new Ferias();
            $pendentes = $ferias->exibeFeriasPendentes($idServidorPesquisado);
            if(!empty($pendentes)){
                $callout = new Callout("warning");
                $callout->abre();
                p("Atenção: Férias Pendentes:<br/> {$pendentes}", 'center');
                $callout->fecha();
            }
            
            $objeto->listar();

            $grid2->fechaColuna();
            $grid2->fechaGrid();
            break;

        case "editar" :
            $objeto->editar($id);
            break;

        case "gravar" :
            $objeto->gravar($id, "servidorFeriasExtra.php");
            break;

        case "excluir" :
            $objeto->excluir($id);
            break;

################################################################

        case "resumo" :
            botaoVoltar("?");
            get_DadosServidor($idServidorPesquisado);

            $grid = new Grid();
            $grid->abreColuna(12);
            titulo("Resumo das Férias");
            br();
            $grid->fechaColuna();
            $grid->fechaGrid();

            $grid = new Grid("center");
            $grid->abreColuna(4);

            $lista = $pessoal->get_feriasResumo($idServidorPesquisado);
            $tabela = new Tabela();
            $tabela->set_conteudo($lista);
            $tabela->set_label(array("Exercício", "Dias", "Falta"));
            $tabela->set_align(array("center"));
            $tabela->show();

            $grid->fechaColuna();
            $grid->fechaGrid();
            break;

################################################################

        case 'avisoFerias':
            $id = get('id');

            # pega os dados do servidor
            $nome = $pessoal->get_nome($idServidorPesquisado);
            $cargo = $pessoal->get_cargo($idServidorPesquisado);
            $perfil = $pessoal->get_perfil($idServidorPesquisado);
            $lotacao = $pessoal->get_lotacao($idServidorPesquisado);

            # Select das férias
            $select = "SELECT anoExercicio,
                              DATE_FORMAT(dtInicial,'%d/%m/%Y'),
                              numDias,
                              DATE_FORMAT(ADDDATE(dtInicial,numDias-1),'%d/%m/%Y') as dtFinal
                         FROM tbferias
                        WHERE tbferias.idFerias = " . $id;

            # Acessa o Banco de dados
            $ferias = new Pessoal();
            $row = $ferias->select($select, false);
            $row = urlencode(serialize($row));  // Prepara para ser enviado por get
            # preenche outro array com o restante dos dados
            $servidor = array($nome, $cargo, $perfil, $lotacao, $idServidorPesquisado);
            $servidor = urlencode(serialize($servidor));  // Prepara para ser enviado por get        

            loadPage('../relatorios/avisoFerias.php?row=' . $row . '&servidor=' . $servidor, '_blank');  // envia um array pelo get
            loadPage('?');
            break;
    }

    $page->terminaPagina();
} else {
    loadPage("../../areaServidor/sistema/login.php");
}