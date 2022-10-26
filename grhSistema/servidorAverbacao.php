<?php

/**
 * Cadastro de Tempo de Serviço
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
    $intra = new Intra();
    $pessoal = new Pessoal();
    $averbacao = new Averbacao();

    # Variáveis
    $empresaTipo = [
        [1, "Pública"],
        [2, "Privada"]
    ];

    $regime = [
        [1, "Celetista"],
        [2, "Estatutário"],
        [3, "Próprio"],
        [4, "Militar"]
    ];

    # Verifica se veio menu grh e registra o acesso no log
    $grh = get('grh', false);
    if ($grh) {
        # Grava no log a atividade
        $atividade = "Cadastro do servidor - Tempo de serviço averbado";
        $data = date("Y-m-d H:i:s");
        $intra->registraLog($idUsuario, $data, $atividade, null, null, 7, $idServidorPesquisado);
    }

    # Verifica a fase do programa
    $fase = get('fase', 'listar');

    # pega o id (se tiver)
    $id = soNumeros(get('id'));

    # Pega o parametro de pesquisa (se tiver)
    $parametro = retiraAspas(post('parametro', get('parametro')));

    # Começa uma nova página
    $page = new Page();
    $page->iniciaPagina();

    # Cabeçalho da Página
    AreaServidor::cabecalho();

    # Abre um novo objeto Modelo
    $objeto = new Modelo();

    ################################################################
    if ($fase <> "listar") {
        # Exibe os dados do Servidor
        $objeto->set_rotinaExtra("get_DadosServidor");
        $objeto->set_rotinaExtraParametro($idServidorPesquisado);
    }

    # Nome do Modelo (aparecerá nos fildset e no caption da tabela)
    $objeto->set_nome('Cadastro de Tempo de Serviço Averbado');

    # botão de voltar da lista$em
    $objeto->set_voltarLista('servidorMenu.php');

    $select = "SELECT dtInicial,
                      dtFinal,
                      dias,
                      idAverbacao,
                      idAverbacao,
                      empresa,
                      CASE empresaTipo ";

    foreach ($empresaTipo as $tipo) {
        $select .= " WHEN {$tipo[0]} THEN '{$tipo[1]}' ";
    }

    $select .= "      END,
                      CASE regime ";
    foreach ($regime as $tipo2) {
        $select .= " WHEN {$tipo2[0]} THEN '{$tipo2[1]}' ";
    }

    $select .= "      END,
                      cargo,
                      dtPublicacao,
                      processo,
                      idAverbacao
                 FROM tbaverbacao
                WHERE idServidor = {$idServidorPesquisado}
             ORDER BY dtInicial desc";

    # select da lista
    $objeto->set_selectLista($select);

    # select do edita
    $objeto->set_selectEdita('SELECT empresa,
                                     empresaTipo,
                                     dtPublicacao,
                                     processo,
                                     dtInicial,
                                     dtFinal,
                                     dias,
                                     regime,
                                     cargo,
                                     obs,
                                     idServidor
                                FROM tbaverbacao
                               WHERE idAverbacao = ' . $id);

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
    $objeto->set_label(["Data Inicial", "Data Final", "Dias Digitados", "Dias Calculados", "Dias Anteriores de 15/12/1998", "Empresa", "Tipo", "Regime", "Cargo", "Publicação", "Processo"]);
    #$objeto->set_width([8, 8, 8, 8, 8, 20, 8, 8, 8, 8]);
    $objeto->set_align(["center", "center", "center", "center", "center", "left"]);
    $objeto->set_funcao(["date_to_php", "date_to_php", null, null, null, null, null, null, null, "date_to_php"]);

    $objeto->set_colunaSomatorio([2, 3]);
    $objeto->set_textoSomatorio("Total de Dias:");
    $objeto->set_totalRegistro(false);

    $objeto->set_classe([null, null, null, "Averbacao", "Averbacao"]);
    $objeto->set_metodo([null, null, null, "getNumDias", "getDiasAnterior151298"]);

    $objeto->set_formatacaoCondicional(array(
        array('coluna' => 4,
            'valor' => 0,
            'operador' => '<>',
            'id' => 'diasAntes'),
        array('coluna' => 4,
            'valor' => 0,
            'operador' => '=',
            'id' => 'normal')
    ));

    # Classe do banco de dados
    $objeto->set_classBd('pessoal');

    # Nome da tabela
    $objeto->set_tabela('tbaverbacao');

    # Nome do campo id
    $objeto->set_idCampo('idAverbacao');

    # Tipo de label do formulário
    $objeto->set_formLabelTipo(1);

    # Campos para o formulario
    $objeto->set_campos(array(array('nome' => 'empresa',
            'label' => 'Empresa:',
            'tipo' => 'texto',
            'required' => true,
            'autofocus' => true,
            'size' => 80,
            'title' => 'Nome da Empresa.',
            'col' => 6,
            'linha' => 1),
        array('nome' => 'empresaTipo',
            'label' => 'Tipo:',
            'tipo' => 'combo',
            'required' => true,
            'array' => $empresaTipo,
            'size' => 20,
            'col' => 2,
            'title' => 'Tipo da Empresa',
            'linha' => 1),
        array('nome' => 'dtPublicacao',
            'label' => 'Data da Pub. no DOERJ:',
            'tipo' => 'data',
            'required' => true,
            'size' => 20,
            'col' => 3,
            'title' => 'Data da Publicação no DOERJ.',
            'linha' => 2),
        array('nome' => 'processo',
            'label' => 'Processo:',
            'tipo' => 'texto',
            'required' => true,
            'size' => 30,
            'col' => 3,
            'title' => 'Número do Processo',
            'linha' => 2),
        array('nome' => 'dtInicial',
            'label' => 'Data Inicial:',
            'tipo' => 'data',
            'notnull' => true,
            'size' => 20,
            'col' => 3,
            'required' => true,
            'title' => 'Data inícial do Período.',
            'linha' => 3),
        array('nome' => 'dtFinal',
            'label' => 'Data Final:',
            'tipo' => 'data',
            'required' => true,
            'size' => 20,
            'col' => 3,
            'notnull' => true,
            'title' => 'Data final do Período.',
            'linha' => 3),
        array('nome' => 'dias',
            'label' => 'Dias:',
            'tipo' => 'numero',
            'required' => true,
            'size' => 5,
            'col' => 2,
            'notnull' => true,
            'title' => 'Quantidade de Dias Averbado.',
            'linha' => 3),
        array('nome' => 'regime',
            'label' => 'Regime:',
            'tipo' => 'combo',
            'col' => 3,
            'required' => true,
            'array' => $regime,
            'size' => 20,
            'title' => 'Tipo do Regime',
            'linha' => 4),
        array('nome' => 'cargo',
            'label' => 'Cargo:',
            'tipo' => 'texto',
            'col' => 6,
            'size' => 100,
            'title' => 'Cargo',
            'linha' => 4),
        array('linha' => 9,
            'nome' => 'obs',
            'label' => 'Observação:',
            'tipo' => 'textarea',
            'size' => array(80, 5)),
        array('nome' => 'idServidor',
            'label' => 'idServidor:',
            'tipo' => 'hidden',
            'padrao' => $idServidorPesquisado,
            'size' => 5,
            'title' => 'Matrícula',
            'linha' => 10)));

    # Log
    $objeto->set_idUsuario($idUsuario);
    $objeto->set_idServidorPesquisado($idServidorPesquisado);

    ################################################################

    switch ($fase) {
        case "" :
        case "listar" :

            # Retira os botoes da classe modelo
            $objeto->set_botaoIncluir(false);
            $objeto->set_botaoVoltarLista(false);
            $objeto->set_comGridLista(false);

            # Limita a tela
            $grid = new Grid();
            $grid->abreColuna(12);

            # Cria um menu
            $menu1 = new MenuBar();

            # Voltar
            $linkVoltar = new Link("Voltar", 'servidorMenu.php');
            $linkVoltar->set_class('button');
            $menu1->add_link($linkVoltar, "left");

            $botaoAfast = new Button('Afastamentos', 'servidorAfastamentos.php?volta=0');
            $botaoAfast->set_title("Verifica todos os afastamentos deste servidor");
            $botaoAfast->set_target("_blank");
            $menu1->add_link($botaoAfast, "right");

            # Relatório
            $imagem = new Imagem(PASTA_FIGURAS . 'print.png', null, 15, 15);
            $botaoRel = new Button();
            $botaoRel->set_imagem($imagem);
            $botaoRel->set_title("Imprimir Relatório de Tempo de Serviço Averbado");
            $botaoRel->set_url("../grhRelatorios/servidorAverbacao.php");
            $botaoRel->set_target("_blank");
            $menu1->add_link($botaoRel, "right");

            # Incluir
            # Habilita o modo leitura para usuario de regra 12
            if (Verifica::acesso($idUsuario, [1, 2])) {
                $linkIncluir = new Link("Incluir", '?fase=editar');
                $linkIncluir->set_class('button');
                $linkIncluir->set_title('Incluir novas ferias');
                $menu1->add_link($linkIncluir, "right");
            }

            $menu1->show();

            # Exibe os dados do servidor pesquisado
            get_DadosServidor($idServidorPesquisado);

            $grid->fechaColuna();
            $grid->abreColuna(9);

            # Verifica a data de saída
            $dtSaida = $pessoal->get_dtSaida($idServidorPesquisado);      # Data de Saída de servidor inativo
            $dtHoje = date("Y-m-d");                                      # Data de hoje
            $dtFinal = null;
            $dtAdmissao = date_to_bd($pessoal->get_dtAdmissao($idServidorPesquisado));

            # Analisa a data
            if (!vazio($dtSaida)) {           // Se tem saída é a saída
                $dtFinal = date_to_bd($dtSaida);
            } else {                          // Não tem saída então é hoje
                $dtFinal = $dtHoje;
            }

            $mensagem1 = "Atenção: Nem a data final nem o número de dias são calculados pelo sistema. Estão conforme foram digitados pelo usuário, para refletir ao que foi publicado.<br/>
                 O problema consiste em que nem sempre o que se publica é fruto de um cálculo perfeito.<br/>
                 Dessa forma, para verificar possíveis equívocos, a tabela abaixo informa, além dos dias digitados, o cálculo desses dias considerando a data Inicial e a data Final.";

            # Verifica se tem sobreposição
            $averbacao = new Averbacao();
            if ($averbacao->tempoSobreposto($idServidorPesquisado)) {
                $mensagem2 = "Atenção - Períodos com Sobreposição de Dias !!!<br/>
                              Verifique se não há dias sobrepostos entre os períodos averbados<br/>ou se algum período averbado ultrapassa a data de admissão na UENF: " . date_to_php($dtAdmissao);

                calloutAlert($mensagem2);
            }


            callout($mensagem1);

            $grid->fechaColuna();
            $grid->abreColuna(3);

            $valores = [
                ["Privado", $averbacao->get_tempoAverbadoPrivado($idServidorPesquisado)],
                ["Público", $averbacao->get_tempoAverbadoPublico($idServidorPesquisado)],
            ];

            $tabela = new Tabela();
            $tabela->set_titulo("Tempo Averbado");
            $tabela->set_label(array('Tipo', 'Dias'));
            $tabela->set_totalRegistro(false);
            $tabela->set_align(array('left'));
            $tabela->set_conteudo($valores);
            $tabela->set_colunaSomatorio(1);
            $tabela->show();

            $grid->fechaColuna();
            $grid->abreColuna(12);

            $objeto->set_exibeTempoPesquisa(false);
            $objeto->listar();

            #############################################################
            # Exibe o timeline
            # Monta o select
            $select1 = "SELECT empresa,
                               dtInicial,
                               dtFinal
                          FROM tbaverbacao
                         WHERE idServidor = $idServidorPesquisado ORDER BY 2 desc";

            # Acessa o banco
            $pessoal = new Pessoal();
            $atividades1 = $pessoal->select($select1);
            $numAtividades = $pessoal->count($select1);
            $contador = $numAtividades; // Contador pra saber quando tirar a virgula no último valor do for each linhas abaixo.

            if ($numAtividades > 0) {

                tituloTable("Grafico");

                # Carrega a rotina do Google
                echo '<script type="text/javascript" src="' . PASTA_FUNCOES_GERAIS . '/loader.js"></script>';

                # Inicia o script
                echo "<script type='text/javascript'>";
                echo "google.charts.load('current', {'packages':['timeline'], 'language': 'pt-br'});
                      google.charts.setOnLoadCallback(drawChart);
                      function drawChart() {
                            var container = document.getElementById('timeline');
                            var chart = new google.visualization.Timeline(container);
                            var dataTable = new google.visualization.DataTable();";

                echo "dataTable.addColumn({ type: 'string' });
                      dataTable.addColumn({ type: 'date' });
                      dataTable.addColumn({ type: 'date' });";

                echo "dataTable.addRows([";

                $separador = '-';

                # inclui o tempo de uenf
                $dt1 = explode($separador, $dtAdmissao);
                $dt2 = explode($separador, $dtFinal);

                echo "['UENF', new Date($dt1[0], $dt1[1]-1, $dt1[2]), new Date($dt2[0], $dt2[1]-1, $dt2[2])]";

                if ($numAtividades > 0) {
                    echo ",";
                }

                foreach ($atividades1 as $row) {

                    # Trata as datas
                    $dt1 = explode($separador, $row['dtInicial']);
                    $dt2 = explode($separador, $row['dtFinal']);

                    echo "['" . $row['empresa'] . "', new Date($dt1[0], $dt1[1]-1, $dt1[2]), new Date($dt2[0], $dt2[1]-1, $dt2[2])]";

                    $contador--;

                    if ($contador > 0) {
                        echo ",";
                    }
                }


                echo "]);";

                echo "var options = {
                             timeline: { colorByRowLabel: true },
                             backgroundColor: '#f2f2f2',
                             timeline: { rowLabelStyle: {fontSize: 10}},
                             hAxis: { format: 'yyyy', },
                             };";

                echo "chart.draw(dataTable, options);";
                echo "}";
                echo "</script>";

                $altura = (($numAtividades + 1) * 45) + 50;
                echo '<div id="timeline" style="height: ' . $altura . 'px; width: 100%;"></div>';
            }

            $grid->fechaColuna();
            $grid->fechaGrid();
            break;

        case "editar" :
        case "excluir" :
            $objeto->$fase($id);
            break;

        case "gravar" :
            $objeto->gravar($id, "servidorAverbacaoExtra.php");
            break;
    }

    $page->terminaPagina();
} else {
    loadPage("../../areaServidor/sistema/login.php");
}