<?php

/**
 * Dados Gerais do servidor
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
        $atividade = "Cadastro do servidor - Cadastro de concurso";
        $data = date("Y-m-d H:i:s");
        $intra->registraLog($idUsuario, $data, $atividade, null, null, 7, $idServidorPesquisado);
    }

    # Verifica a fase do programa
    if (Verifica::acesso($idUsuario, 12)) {
        $fase = get('fase', 'editar');
    } else {
        $fase = get('fase', 'ver');
    }

    # Verifica de onde veio
    $origem = get_session("origem");

    # Começa uma nova página
    $page = new Page();
    $page->iniciaPagina();

    # Cabeçalho da Página
    AreaServidor::cabecalho();

    # Pega o idConcurso
    $idConcursoServidor = $pessoal->get_idConcurso($idServidorPesquisado);

    # Abre um novo objeto Modelo
    $objeto = new Modelo();

    ################################################################
    # Exibe os dados do Servidor
    $objeto->set_rotinaExtra("get_DadosServidor");
    $objeto->set_rotinaExtraParametro($idServidorPesquisado);

    # Nome do Modelo (aparecerá nos fildset e no caption da tabela)
    $objeto->set_nome('Dados do Concurso');

    # Pega o tipo do cargo (Adm & Tec ou Professor)
    $tipoCargo = $pessoal->get_cargoTipo($idServidorPesquisado);

    # select do edita
    if ($tipoCargo == "Adm/Tec") {
        $objeto->set_selectEdita("SELECT idConcurso,
                                     idServidorOcupanteAnterior,
                                     dtPublicConcursoResultado,
                                     pgPublicConcursoResultado,
                                     classificacaoConcurso,
                                     cotasConcurso,
                                     instituicaoConcurso,
                                     dtPublicConvocacao,
                                     pgPublicConvocacao,
                                     dtPublicResultadoExameMedico,
                                     pgPublicResultadoExameMedico,
                                     dtPublicAtoNomeacao,
                                     pgPublicAtoNomeacao,
                                     dtPublicAtoInvestidura,
                                     pgPublicAtoInvestidura,
                                     dtPublicTermoPosse,
                                     pgPublicTermoPosse,
                                     obsConcurso
                                FROM tbservidor
                               WHERE idServidor = {$idServidorPesquisado}");
    } else {
        $objeto->set_selectEdita("SELECT idConcurso,
                                     dtPublicConcursoResultado,
                                     pgPublicConcursoResultado,
                                     dtPublicConvocacao,
                                     pgPublicConvocacao,
                                     dtPublicResultadoExameMedico,
                                     pgPublicResultadoExameMedico,
                                     dtPublicAtoNomeacao,
                                     pgPublicAtoNomeacao,
                                     dtPublicAtoInvestidura,
                                     pgPublicAtoInvestidura,
                                     dtPublicTermoPosse,
                                     pgPublicTermoPosse,
                                     obsConcurso
                                FROM tbservidor
                               WHERE idServidor = {$idServidorPesquisado}");
    }

    # Habilita o modo leitura para usuario de regra 12
    if (Verifica::acesso($idUsuario, 12)) {
        $objeto->set_modoLeitura(true);
    }

    # Caminhos
    $objeto->set_linkGravar('?fase=gravar');

    # botão voltar
    if (is_null($origem)) {
        $objeto->set_voltarForm('servidorMenu.php');
        $objeto->set_linkListar('servidorMenu.php');
    } else {
        $objeto->set_voltarForm($origem);
        $objeto->set_linkListar($origem);
    }

    # retira o botão incluir
    $objeto->set_botaoIncluir(false);

    # Classe do banco de dados
    $objeto->set_classBd('Pessoal');

    # Nome da tabela
    $objeto->set_tabela('tbservidor');

    # Nome do campo id
    $objeto->set_idCampo('idServidor');

    # Trata o tipo
    if ($tipoCargo == "Adm/Tec") {
        /*
         *  Combo concurso
         */
        $select = "SELECT idconcurso,
                          concat(anoBase,' - Edital: ',DATE_FORMAT(dtPublicacaoEdital,'%d/%m/%Y')) as concurso
                     FROM tbconcurso
               WHERE tipo = 1     
            ORDER BY dtPublicacaoEdital desc";

        # Pega os dados da combo concurso
        $concurso = $pessoal->select($select);
        $idConcurso = null;

        array_unshift($concurso, array(null, null));

        /*
         *  Combo ocupante anterior
         */

        # Pega o cargo do servidor pesquisado
        $idCargoPesquisado = $pessoal->get_idTipoCargoServidor($idServidorPesquisado);

        # Pega a data de admissão do servidor pesquisado
        $dtAdmPesquisado = date_to_bd($pessoal->get_dtAdmissao($idServidorPesquisado));

        $select = "SELECT tbservidor.idServidor,
                          CONCAT(date_format(dtAdmissao,'%d/%m/%Y'),' - ',date_format(dtDemissao,'%d/%m/%Y'),' - ',UADM,' - ',DIR,' - ',GER,' - ',tbpessoa.nome),
                          tbcargo.nome
                     FROM tbservidor LEFT JOIN tbpessoa USING (idPessoa)
                                     LEFT JOIN tbcargo USING (idCargo)
                                     LEFT JOIN tbhistlot ON (tbservidor.idServidor = tbhistlot.idServidor)
                                     LEFT JOIN tblotacao ON (tbhistlot.lotacao=tblotacao.idLotacao)                           
               WHERE situacao <> 1
                 AND idTipoCargo = {$idCargoPesquisado}
                 AND dtDemissao < '{$dtAdmPesquisado}'
                 AND tbservidor.idServidor NOT IN (SELECT idServidorOcupanteAnterior FROM tbservidor WHERE idServidorOcupanteAnterior IS NOT null AND idServidor <> {$idServidorPesquisado})
                 AND (idPerfil = 1 OR idPerfil = 4)
                 AND idConcurso IS NOT NULL
                 AND tbhistlot.data = (select max(data) from tbhistlot where tbhistlot.idServidor = tbservidor.idServidor)
            ORDER BY tbcargo.nome, dtDemissao";

        # Pega os dados da combo concurso
        $ocupanteAnterior = $pessoal->select($select);

        array_unshift($ocupanteAnterior, array("0", "-- primeiro servidor a ocupar a vaga --"));
        array_unshift($ocupanteAnterior, array(null, null));
    } else {
        # Professor

        $vaga = new Vaga();
        # Preenche com o valor da tabela tbvagahistórico
        # Que é onde fica cadastrado o concurso dos docentes
        $idConcurso = $vaga->get_idConcursoProfessor($idServidorPesquisado);

        if (!empty($idConcurso)) {

            $select = "SELECT idconcurso,
                              concat(anoBase,' - Edital: ',DATE_FORMAT(dtPublicacaoEdital,'%d/%m/%Y')) as concurso
                         FROM tbconcurso
                   WHERE idConcurso = $idConcurso";

            # Pega os dados da combo concurso
            $concurso = $pessoal->select($select);
            array_unshift($concurso, array(null, null));
        } else {
            $concurso = null;
            $idConcurso = null;
        }
    }

    # Campos para o formulario
    $campos = array(
        array('linha' => 1,
            'nome' => 'idConcurso',
            'label' => 'Concurso:',
            'tipo' => 'combo',
            'array' => $concurso,
            'title' => 'Concurso',
            'padrao' => $idConcurso,
            'col' => 4,
            'size' => 15));

    if ($tipoCargo == "Adm/Tec") {
        array_push($campos,
                array('linha' => 1,
                    'nome' => 'idServidorOcupanteAnterior',
                    'label' => 'Vaga Anteriormente Ocupada por:',
                    'tipo' => 'combo',
                    'array' => $ocupanteAnterior,
                    'title' => 'Servidor que ocupava anteriormente esta vaga (quando houver)',
                    'optgroup' => true,
                    'padrao' => null,
                    'col' => 8,
                    'size' => 15));
    }

    array_push($campos,
            array('linha' => 2,
                'nome' => 'dtPublicConcursoResultado',
                'label' => 'Resultado Final do Concurso:',
                'tipo' => 'data',
                'title' => 'Data da publicação do resultado final do concurso',
                'fieldset' => "Publicações",
                'col' => 4,
                'size' => 15),
            array('linha' => 2,
                'nome' => 'pgPublicConcursoResultado',
                'label' => 'Página:',
                'tipo' => 'texto',
                'size' => 6,
                'title' => 'Página da publicação',
                'col' => 2)
    );

    if ($tipoCargo == "Adm/Tec") {
        array_push($campos,
                array('linha' => 2,
                    'nome' => 'classificacaoConcurso',
                    'label' => 'Classificação:',
                    'tipo' => 'numero',
                    'size' => 6,
                    'title' => 'Classificação final do concurso',
                    'col' => 2),
                array('linha' => 2,
                    'nome' => 'cotasConcurso',
                    'label' => 'Cota:',
                    'tipo' => 'texto',
                    'size' => 50,
                    'title' => 'Informar, se tiver, que tipo de cota é a vaga.',
                    'col' => 4)
        );

        if ($idConcursoServidor == 2) {
            array_push($campos,
                    array('linha' => 2,
                        'nome' => 'instituicaoConcurso',
                        'label' => 'Instituição Escolhida:',
                        'tipo' => 'combo',
                        'array' => [[null, null], ["Fenorte", "Fenorte"], ["Tecnorte", "Tecnorte"], ["Uenf", "Uenf"]],
                        'title' => 'Instituição do Concurso (quando houver)',
                        'padrao' => null,
                        'col' => 3,
                        'size' => 15));
        } else {
            array_push($campos,
                    array('linha' => 2,
                        'nome' => 'instituicaoConcurso',
                        'label' => 'Instituição Escolhida:',
                        'tipo' => 'hidden',
                        'array' => [[null, null], ["Fenorte", "Fenorte"], ["Tecnorte", "Tecnorte"], ["Uenf", "Uenf"]],
                        'title' => 'Instituição do Concurso (quando houver)',
                        'padrao' => null,
                        'col' => 3,
                        'size' => 15));
        }
    }

    array_push($campos,
            array('linha' => 3,
                'nome' => 'dtPublicConvocacao',
                'label' => 'Convocação:',
                'tipo' => 'data',
                'title' => 'Data da publicação da convocação do servidor',
                'col' => 4,
                'size' => 15),
            array('linha' => 3,
                'nome' => 'pgPublicConvocacao',
                'label' => 'Página:',
                'tipo' => 'texto',
                'size' => 6,
                'title' => 'Página da publicação',
                'col' => 2),
            array('linha' => 3,
                'nome' => 'dtPublicResultadoExameMedico',
                'label' => 'Resultado do Exame Médico:',
                'tipo' => 'data',
                'title' => 'Data da publicação do resultado do exame médico',
                'col' => 4,
                'size' => 15),
            array('linha' => 3,
                'nome' => 'pgPublicResultadoExameMedico',
                'label' => 'Página:',
                'tipo' => 'texto',
                'size' => 6,
                'title' => 'Página da publicação',
                'col' => 2),
            array('linha' => 4,
                'nome' => 'dtPublicAtoNomeacao',
                'label' => 'Ato de Nomeação:',
                'tipo' => 'data',
                'title' => 'Data da publicação do ato de nomeação',
                'col' => 4,
                'size' => 15),
            array('linha' => 4,
                'nome' => 'pgPublicAtoNomeacao',
                'label' => 'Página:',
                'tipo' => 'texto',
                'size' => 6,
                'title' => 'Página da publicação',
                'col' => 2),
            array('linha' => 4,
                'nome' => 'dtPublicAtoInvestidura',
                'label' => 'Ato de Investidura:',
                'tipo' => 'data',
                'title' => 'Data da publicação do ato de investidura',
                'col' => 4,
                'size' => 15),
            array('linha' => 4,
                'nome' => 'pgPublicAtoInvestidura',
                'label' => 'Página:',
                'tipo' => 'texto',
                'size' => 6,
                'title' => 'Página da publicação',
                'col' => 2),
            array('linha' => 6,
                'nome' => 'dtPublicTermoPosse',
                'label' => 'Termo de Posse:',
                'tipo' => 'data',
                'title' => 'Data da publicação do termo de posse',
                'col' => 4,
                'size' => 15),
            array('linha' => 6,
                'nome' => 'pgPublicTermoPosse',
                'label' => 'Página:',
                'tipo' => 'texto',
                'size' => 3,
                'title' => 'Página da publicação',
                'col' => 2),
            array('linha' => 7,
                'col' => 12,
                'fieldset' => "fecha",
                'nome' => 'obsConcurso',
                'label' => 'Observação:',
                'tipo' => 'textarea',
                'size' => array(80, 3)));

    $objeto->set_campos($campos);

    # Log
    $objeto->set_idUsuario($idUsuario);
    $objeto->set_idServidorPesquisado($idServidorPesquisado);

    ###############################################################
    # Inicia o Menu de Cargos
    if (!empty($idConcursoServidor)) {

        # Cadastro de concurso    
        if ($origem <> "cadastroConcursoAdm.php") {
            if (Verifica::acesso($idUsuario, [1, 2])) {
                $botao = new Button("Cadastro de Concurso", "?fase=acessaConcurso");
                $botao->set_title("Acessa o cadastro do concurso");
                $objeto->set_botaoEditarExtra(array($botao));
            }
        }

        $menu = new Menu("menuVertical");
        $menu->add_item('titulo', 'Menu');
        $menu->add_item('titulo1', 'Publicações Gerais');
        $select = "SELECT descricao,
                      data,
                      pag,
                      idConcursoPublicacao,
                      obs
                 FROM tbconcursopublicacao
                WHERE idConcurso = $idConcursoServidor  
             ORDER BY data, idConcursoPublicacao";

        $conteudo = $pessoal->select($select);

        # Preenche com os cargos
        foreach ($conteudo as $item) {
            $menu->add_item('linkWindow', date_to_php($item[1]) . ' - ' . $item[0], PASTA_CONCURSO . $item[3] . ".pdf", $item[4]);
            #$menu->add_item('linkWindow', plista(date_to_php($item[1]) . ' - ' . $item[0],$item[4]), PASTA_CONCURSO . $item[3] . ".pdf", $item[4]);
        }
        $objeto->set_menuLateralEditar($menu);
    }

    ################################################################
    switch ($fase) {
        case "ver" :
        case "editar" :
            # Verifica se é professor e informa a lotação de origem do concurso
            $idCargo = $pessoal->get_idCargo($idServidorPesquisado);
            if ($idCargo == 128 OR $idCargo == 129) {

                function exibeDadosVagas($array) {

                    $idVaga = $array[0];
                    $idServidor = $array[1];

                    $vaga = new Vaga();
                    $servidor = new Pessoal();
                    $conteudo = $vaga->get_dados($idVaga);

                    $painel = new Callout();
                    $painel->abre();

                    $grid = new Grid();
                    $grid->abreColuna(4);

                    titulotable("Vaga");
                    p($idVaga, "vagaId");

                    $grid->fechaColuna();
                    $grid->abreColuna(4);

                    titulotable("Lotação de Origem");

                    $centro = $conteudo["centro"];
                    $idCargo = $conteudo["idCargo"];

                    $labOrigem = $servidor->get_nomeLotacao3($vaga->get_laboratorioOrigem($idVaga));

                    $cargo = $servidor->get_nomeCargo($idCargo);
                    $status = $vaga->get_status($idVaga);

                    p($centro, "vagaCentro");
                    p($cargo, "vagaCargo");

                    $title = "O primeiro laboratório da vaga, para o qual a vaga foi criada,";

                    p("Laboratório de Origem:", "vagaOrigem", null, $title);
                    p($labOrigem, "vagaCargo", null, $title);

                    $grid->fechaColuna();
                    $grid->abreColuna(4);

                    if ($status == "Disponível") {
                        tituloTable("Vaga Disponível");
                        br();
                        p("Ninguém ocupa esta vaga atualmente!", "center", "f14");
                        br();
                    } else {
                        tituloTable("Ocupante Atual");
                        br();
                        $ocupante = $vaga->get_servidorOcupante($idVaga);

                        if ($vaga->get_laboratorioOrigem($idVaga) <> $servidor->get_idLotacao($idServidor)) {
                            p("Atenção !!<br/>Lotação atual diferente da<br/>Lotação de Origem do Concurso!", "pconcursadoLotacaoDiferente");
                        }
                    }

                    $grid->fechaColuna();
                    $grid->fechaGrid();
                    $painel->fecha();
                }

                $vaga = new Vaga();
                $objeto->set_rotinaExtraEditar("exibeDadosVagas");
                $objeto->set_rotinaExtraEditarParametro([$vaga->get_idVaga($idServidorPesquisado), $idServidorPesquisado]);
            }

            $objeto->$fase($idServidorPesquisado);
            break;

        case "gravar" :
            if ($tipoCargo == "Adm/Tec") {
                $objeto->$fase($idServidorPesquisado, 'servidorConcursoExtra.php');
            } else {
                $objeto->$fase($idServidorPesquisado);
            }
            break;

        ################################################################
        # Chama o menu do Servidor que se quer editar
        case "acessaConcurso" :
            set_session('idConcurso', $idConcursoServidor);
            set_session('origem', "servidorConcurso.php");
            loadPage('cadastroConcursoPublicacao.php');
            break;

        ################################################################
    }

    $page->terminaPagina();
} else {
    loadPage("../../areaServidor/sistema/login.php");
}