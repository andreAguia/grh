<?php

/**
 * Cadastro de Concursos
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

    # Verifica se veio menu grh e registra o acesso no log
    $grh = get('grh', false);
    if ($grh) {
        # Grava no log a atividade
        $atividade = "Visualizou o cadastro de candidatos";
        $data = date("Y-m-d H:i:s");
        $intra->registraLog($idUsuario, $data, $atividade, null, null, 7);
    }

    # Pega a fase
    $fase = get('fase', 'aguardaLista');

    # pega o id (se tiver)
    $id = soNumeros(get('id'));

    # Pega o idConcurso
    $idConcurso = get_session("idConcurso");

    # Volta quando não temos o idconcurso
    if (empty($idConcurso)) {
        $fase = "nenhum";
        loadPage("areaConcursoAdm.php");
    } else {
        # Pega as variáveis
        $idServidorPesquisado = get('idServidorPesquisado');
        $concurso = new Concurso($idConcurso);

        $parametroCargoCandidato = post('parametroCargoCandidato', get_session('parametroCargoCandidato', '*'));
        $parametroNome = post('parametroNome', get_session('parametroNome'));
        $parametroCota = post('parametroCota', get_session('parametroCota', 'AC'));

        set_session('parametroCargoCandidato', $parametroCargoCandidato);
        set_session('parametroNome', $parametroNome);
        set_session('parametroCota', $parametroCota);
    }

    # Começa uma nova página
    $page = new Page();
    $page->iniciaPagina();

    # Cabeçalho da Página
    if ($fase <> "relatorio1" AND $fase <> "relatorio2") {
        AreaServidor::cabecalho();
    }

    $grid = new Grid();
    $grid->abreColuna(12);

################################################################

    switch ($fase) {
        case "":
        case "aguardaLista" :

            # Cria um menu
            $menu1 = new MenuBar();

            # Voltar
            $botaoVoltar = new Link("Voltar", "areaConcursoAdm.php");
            $botaoVoltar->set_class('button');
            $botaoVoltar->set_title('Voltar a página anterior');
            $botaoVoltar->set_accessKey('V');
            $menu1->add_link($botaoVoltar, "left");

            $menu1->show();

            $grid->fechaColuna();

            #######################################################

            $grid->abreColuna(3);

            # Exibe os dados do Concurso
            $concurso->exibeDadosConcurso($idConcurso, true);

            # menu
            $concurso->exibeMenu($idConcurso, "Candidatos");

            # Exibe os servidores deste concurso
            $concurso->exibeQuadroServidoresConcursoPorCargo($idConcurso);

            $grid->fechaColuna();

            #######################################################3

            $grid->abreColuna(9);

            br(4);
            aguarde();
            br();

            # Limita a tela
            $grid1 = new Grid("center");
            $grid1->abreColuna(5);
            p("Aguarde...", "center");
            $grid1->fechaColuna();
            $grid1->fechaGrid();

            loadPage('?fase=listaCandidatos');
            break;

            $grid->fechaColuna();
            $grid->fechaGrid();
            break;

        ################################################################

        case "listaCandidatos" :

            # Cria um menu
            $menu1 = new MenuBar();

            # Voltar
            $botaoVoltar = new Link("Voltar", "areaConcursoAdm.php");
            $botaoVoltar->set_class('button');
            $botaoVoltar->set_title('Voltar a página anterior');
            $botaoVoltar->set_accessKey('V');
            $menu1->add_link($botaoVoltar, "left");

            # Importar
            $botaoImportar = new Link("Importar", "importaCandidatos.php");
            $botaoImportar->set_class('success button');
            $botaoImportar->set_title('Faz a importação do petec');
            if (Verifica::acesso($idUsuario, 1)) {
                $menu1->add_link($botaoImportar, "right");
            }

            # Relatório
            $imagem2 = new Imagem(PASTA_FIGURAS . 'print.png', null, 15, 15);
            $botaoRel = new Button();
            $botaoRel->set_title("Relatório dos Candidatos no número de vagas");
            $botaoRel->set_target("_blank");
            $botaoRel->set_url("?fase=relatorio");
            $botaoRel->set_imagem($imagem2);
            #$menu1->add_link($botaoRel, "right");

            $menu1->show();

            $grid->fechaColuna();

            #######################################################
            # Menu

            $grid->abreColuna(3);

            # Exibe os dados do Concurso
            $concurso->exibeDadosConcurso($idConcurso, true);

            # menu
            $concurso->exibeMenu($idConcurso, "Candidatos");

            # Exibe os servidores deste concurso
            $concurso->exibeQuadroServidoresConcursoPorCargo($idConcurso);

            # Relatórios
            $menu = new Menu("menuProcedimentos");
            $menu->add_item('titulo', 'Relatórios de Candidatos');
            $menu->add_item('linkWindow', 'Na Vaga Com CPF / E-mail / Tel', '?fase=relatorio1');
            $menu->add_item('linkWindow', 'Na Vaga Com Pontuação', '?fase=relatorio2');

            $menu->show();

            $grid->fechaColuna();

            #######################################################3

            $grid->abreColuna(9);

            # Formulário
            $form = new Form('?');

            $controle = new Input('parametroNome', 'texto', 'Nome:', 1);
            $controle->set_size(50);
            $controle->set_title('Filtra por Nome');
            $controle->set_valor($parametroNome);
            $controle->set_onChange('formPadrao.submit();');
            $controle->set_linha(1);
            $controle->set_col(4);
            #$form->add_item($controle);
            # Cargo
            $result = $pessoal->select("SELECT DISTINCT cargo,
                                               cargo
                                          FROM tbcandidato
                                          WHERE idConcurso = {$idConcurso}
                                       ORDER BY cargo");

            # acrescenta todos
            array_unshift($result, ['*', '-- Todos --']);

            $controle = new Input('parametroCargoCandidato', 'combo', 'Cargo:', 1);
            $controle->set_size(30);
            $controle->set_title('Filtra por Cargo');
            $controle->set_autofocus(true);
            $controle->set_array($result);
            $controle->set_valor($parametroCargoCandidato);
            $controle->set_onChange('formPadrao.submit();');
            $controle->set_linha(1);
            $controle->set_col(9);
            $form->add_item($controle);

            $controle = new Input('parametroCota', 'combo', 'Cota:', 1);
            $controle->set_size(30);
            $controle->set_title('Filtra por Cota');
            $controle->set_array([
                ["AC", "Ampla Concorrência"],
                ["PCD", "PCD"],
                ["Ni", "Negros e Índios"],
                ["Hipo", "Hipossuficiente Econômico"]
            ]);
            $controle->set_valor($parametroCota);
            $controle->set_onChange('formPadrao.submit();');
            $controle->set_linha(1);
            $controle->set_col(3);
            if ($parametroCargoCandidato <> "*") {
                $form->add_item($controle);
            }

            $form->show();

            # Rotina quando se seleciona um cargo
            if ($parametroCargoCandidato <> "*") {

                # Pega o número de vagas de acordo com a cota
                switch ($parametroCota) {
                    // Ampla Concorrência
                    case "AC":
                        $numeroVagas = $concurso->get_numVagasAcAprovadas($idConcurso, $parametroCargoCandidato);
                        break;

                    // Pcd
                    case "PCD":
                        $numeroVagas = $concurso->get_numVagasPcdAprovadas($idConcurso, $parametroCargoCandidato);
                        break;

                    // Negros e Índios
                    case "Ni":
                        $numeroVagas = $concurso->get_numVagasNiAprovadas($idConcurso, $parametroCargoCandidato);
                        break;

                    // Hipossuficiente Econômico
                    case "Hipo":
                        $numeroVagas = $concurso->get_numVagasHipoAprovadas($idConcurso, $parametroCargoCandidato);
                        break;
                }

                $cadastroReserva = 5 * $numeroVagas;
                $foraCadastro = $numeroVagas + $cadastroReserva;

                /*
                 * Quando se tem número de vagas cadastrados
                 */
                if (!empty($numeroVagas)) {

                    /*
                     * Candidatos na Vaga
                     */
                    # Monta o select
                    $select = "(SELECT 'Vaga',
                              inscricao,
                              nome,
                              cotas,                             
                              CONVERT(notaFinal, DECIMAL(10,2)),
                              idCandidato
                         FROM tbcandidato
                        WHERE idConcurso = {$idConcurso}";

                    # Pega o candidato de acordo com a cota
                    switch ($parametroCota) {

                        // Pcd
                        case "PCD":
                            $select .= " AND cotas = 'PCD'";
                            break;

                        // Negros e Índios
                        case "Ni":
                            $select .= " AND cotas = 'Ni'";
                            break;

                        // Hipossuficiente Econômico
                        case "Hipo":
                            $select .= " AND cotas = 'Hipo'";
                            break;
                    }

                    # nome
                    if (!is_null($parametroNome)) {
                        $select .= " AND nome LIKE '%{$parametroNome}%'";
                    }

                    # cargo
                    if ($parametroCargoCandidato <> "*") {
                        $select .= " AND cargo = '{$parametroCargoCandidato}'";
                    }

                    $select .= " ORDER BY 5 DESC LIMIT {$numeroVagas}) UNION ";

                    /*
                     * Candidatos no Cadastro de reserva
                     */

                    # Monta o select
                    $select .= "(SELECT 'Cadastro de Reserva',
                              inscricao,
                              nome,
                              cotas,
                              CONVERT(notaFinal, DECIMAL(10,2)),
                              idCandidato
                         FROM tbcandidato
                        WHERE idConcurso = {$idConcurso}";

                    # Pega o candidato de acordo com a cota
                    switch ($parametroCota) {

                        // Pcd
                        case "PCD":
                            $select .= " AND cotas = 'PCD'";
                            break;

                        // Negros e Índios
                        case "Ni":
                            $select .= " AND cotas = 'Ni'";
                            break;

                        // Hipossuficiente Econômico
                        case "Hipo":
                            $select .= " AND cotas = 'Hipo'";
                            break;
                    }

                    # nome
                    if (!is_null($parametroNome)) {
                        $select .= " AND nome LIKE '%{$parametroNome}%'";
                    }

                    # cargo
                    if ($parametroCargoCandidato <> "*") {
                        $select .= " AND cargo = '{$parametroCargoCandidato}'";
                    }

                    $select .= " ORDER BY 5 DESC LIMIT {$numeroVagas}, {$cadastroReserva}) UNION ";

                    /*
                     * Candidatos FORA no Cadastro de reserva
                     */

                    # Monta o select
                    $select .= "(SELECT '---',
                              inscricao,
                              nome,
                              cotas,                              
                              CONVERT(notaFinal, DECIMAL(10,2)),
                              idCandidato
                         FROM tbcandidato
                        WHERE idConcurso = {$idConcurso}";

                    # Pega o candidato de acordo com a cota
                    switch ($parametroCota) {

                        // Pcd
                        case "PCD":
                            $select .= " AND cotas = 'PCD'";
                            break;

                        // Negros e Índios
                        case "Ni":
                            $select .= " AND cotas = 'Ni'";
                            break;

                        // Hipossuficiente Econômico
                        case "Hipo":
                            $select .= " AND cotas = 'Hipo'";
                            break;
                    }

                    # nome
                    if (!is_null($parametroNome)) {
                        $select .= " AND nome LIKE '%{$parametroNome}%'";
                    }

                    # cargo
                    if ($parametroCargoCandidato <> "*") {
                        $select .= " AND cargo = '{$parametroCargoCandidato}'";
                    }

                    $select .= " ORDER BY 5 DESC LIMIT {$foraCadastro}, 10000)"; // Gambiarra para pegar os registroa a partir das vagas até o fim
                    ##########
                    # Pega os dados
                    $row = $pessoal->select($select);

                    # Titulo
                    titulotable("Cadastro de Candidatos Aprovados");

                    # tabela
                    $tabela = new Tabela();
                    $tabela->set_titulo($parametroCargoCandidato);
                    if ($parametroCargoCandidato <> "*") {

                        # Pega o número de vagas de acordo com a cota
                        switch ($parametroCota) {
                            // Ampla Concorrência
                            case "AC":
                                $subtitulo = "Ampla Concorrência";
                                break;

                            // Pcd
                            case "PCD":
                                $subtitulo = "PCD";
                                break;

                            // Negros e Índios
                            case "Ni":
                                $subtitulo = "Negros e Índios";
                                break;

                            // Hipossuficiente Econômico
                            case "Hipo":
                                $subtitulo = "Hipossuficiente Econômico";
                                break;
                        }


                        if (empty($numeroVagas)) {
                            $tabela->set_subtitulo($parametroCargoCandidato);
                        } else {
                            $tabela->set_subtitulo("{$subtitulo} - {$numeroVagas} Vagas");
                        }
                    } else {
                        $tabela->set_subtitulo("Todos os Cargos");
                    }
                    $tabela->set_conteudo($row);
                    $tabela->set_label(["Situação", "Inscrição", "Candidato", "Cota", "Nota Final", "Editar"]);
                    $tabela->set_width([10, 10, 40, 10, 15]);
                    $tabela->set_align(["center", "center", "left", "center"]);
                    $tabela->set_numeroOrdem(true);
                    $tabela->set_funcao([null, null, "plm",]);

                    # Botão Editar
                    $botao = new Link(null, "?fase=editaCandidato&id=", 'Acessa os dados do Candidato');
                    $botao->set_imagem(PASTA_FIGURAS . 'bullet_edit.png', 20, 20);

                    # Coloca o objeto link na tabela			
                    $tabela->set_link([null, null, null, null, null, $botao]);

                    $tabela->set_rowspan(0);
                    $tabela->set_grupoCorColuna(0);

                    $tabela->set_formatacaoCondicional(array(
                        array('coluna' => 0,
                            'valor' => 'Vaga',
                            'operador' => '=',
                            'id' => "naVaga"),
                        array('coluna' => 0,
                            'valor' => 'Cadastro de Reserva',
                            'operador' => '=',
                            'id' => "reserva")));

                    $tabela->set_mensagemPosTabela("O Cadastro de Reserva é de 5 vezes o número de vagas");
                    $tabela->show();

                    ####################################################################################
                } else {

                    br(5);
                    p("Não há vagas cadastradas !", "f14", "center");
//                    /*
//                     * Quando não tem número de vagas cadastradas
//                     */
//                    # Monta o select
//                    $select = "SELECT inscricao,
//                              nome,
//                              cargo,                              
//                              CONVERT(notaFinal, DECIMAL(10,2))
//                         FROM tbcandidato
//                        WHERE idConcurso = {$idConcurso}";
//
//                    # nome
//                    if (!is_null($parametroNome)) {
//                        $select .= " AND nome LIKE '%{$parametroNome}%'";
//                    }
//
//                    # cargo
//                    if ($parametroCargoCandidato <> "*") {
//                        $select .= " AND cargo = '{$parametroCargoCandidato}'";
//                    }
//
//                    $select .= " ORDER BY 4 ";
//
//                    # Pega os dados
//                    $row = $pessoal->select($select);
//
//                    # tabela
//                    $tabela = new Tabela();
//                    $tabela->set_titulo("Cadastro de Candidatos Aprovados");
//                    if ($parametroCargoCandidato <> "*") {
//
//                        if (empty($numeroVagas)) {
//                            $tabela->set_subtitulo("{$parametroCargoCandidato}");
//                        } else {
//                            $tabela->set_subtitulo("{$parametroCargoCandidato}<br/>{$numeroVagas} Vagas");
//                        }
//                    } else {
//                        $tabela->set_subtitulo("Todos os Cargos");
//                    }
//                    $tabela->set_conteudo($row);
//                    $tabela->set_label(["Inscrição", "Candidato", "Cargo", "Nota Final"]);
//                    $tabela->set_width([10, 30, 45, 15]);
//                    $tabela->set_align(["center", "lrft", "left", "center"]);
//                    $tabela->set_numeroOrdem(true);
//                    $tabela->set_funcao([null, "plm", "plm"]);
//                    $tabela->show();
                }
            } else {
                # Rotina para todos os cargos
                $numeroVagas = null;

                # Monta o select
                $select = "SELECT inscricao,
                              nome,
                              cargo,                              
                              CONVERT(notaFinal, DECIMAL(10,2))
                         FROM tbcandidato
                        WHERE idConcurso = {$idConcurso}";

                # nome
                if (!is_null($parametroNome)) {
                    $select .= " AND nome LIKE '%{$parametroNome}%'";
                }

                $select .= " ORDER BY 4 desc";

                # Pega os dados
                $row = $pessoal->select($select);

                # tabela
                $tabela = new Tabela();
                $tabela->set_titulo("Cadastro de Candidatos Aprovados");
                $tabela->set_subtitulo("Todos os Cargos");
                $tabela->set_conteudo($row);
                $tabela->set_label(["Inscrição", "Candidato", "Cargo", "Nota Final"]);
                $tabela->set_width([10, 30, 50, 10]);
                $tabela->set_align(["center", "left", "left"]);
                $tabela->set_numeroOrdem(true);
                $tabela->set_funcao([null, "plm", "plm"]);
                $tabela->show();
            }

            $grid->fechaColuna();
            $grid->fechaGrid();

            # Grava no log a atividade            
            $data = date("Y-m-d H:i:s");
            $atividade = "Visualizou o cadastro de candidatos do cargo {$parametroCargoCandidato}";
            $intra->registraLog($idUsuario, $data, $atividade, null, null, 7);
            break;

        ################################################################

        case "relatorio1":

            /*
             *  Candidatos de um cargo
             */
            if ($parametroCargoCandidato <> "*") {
                $numeroVagas = $concurso->get_numVagasAcAprovadas($idConcurso, $parametroCargoCandidato);
                $cadastroReserva = 3 * $numeroVagas;
                $foraCadastro = $numeroVagas + $cadastroReserva;

                # Monta o select
                $select = "SELECT inscricao,
                              nome,
                              cpf,
                              email,
                              celular,
                              CONVERT(notaFinal, DECIMAL(10,2))
                         FROM tbcandidato
                        WHERE idConcurso = {$idConcurso}";

                # nome
                if (!is_null($parametroNome)) {
                    $select .= " AND nome LIKE '%{$parametroNome}%'";
                }

                # cargo
                if ($parametroCargoCandidato <> "*") {
                    $select .= " AND cargo = '{$parametroCargoCandidato}'";
                }

                $select .= " ORDER BY 6 DESC LIMIT {$numeroVagas} ";

                $row = $pessoal->select($select);

                # tabela
                $relatorio = new Relatorio();
                $relatorio->set_titulo("Cadastro de Candidatos Aprovados");

                if (empty($numeroVagas)) {
                    $relatorio->set_subtitulo("{$parametroCargoCandidato}");
                } else {
                    $relatorio->set_subtitulo("{$parametroCargoCandidato}<br/>{$numeroVagas} Vagas");
                }

                $relatorio->set_conteudo($row);
                $relatorio->set_label(["Inscrição", "Candidato", "Pontuação"]);
                #$relatorio->set_width([10, 20, 55, 15]);
                $relatorio->set_label(["Inscrição", "Candidato", "CPF", "E-mail", "Telefone"]);
                $relatorio->set_align(["center", "left", "center", "left"]);
                $relatorio->set_numeroOrdem(true);
                $relatorio->set_funcao([null, "plm"]);
                $relatorio->show();
            } else {
                /*
                 *  Todos os cargos
                 */
                # Pega os cargos
                $result = $pessoal->select('SELECT DISTINCT cargo
                                              FROM tbcandidato
                                          ORDER BY cargo');

                $primeiro = true;

                foreach ($result as $item) {

                    $numeroVagas = $concurso->get_numVagasAcAprovadas($idConcurso, $item["cargo"]);
                    $cadastroReserva = 3 * $numeroVagas;
                    $foraCadastro = $numeroVagas + $cadastroReserva;

                    # Monta o select
                    $select = "SELECT inscricao,
                              nome,
                              cpf,
                              email,
                              celular,
                              CONCAT(cargo,'<br/>{$numeroVagas} Vagas'),
                              CONVERT(notaFinal, DECIMAL(10,2))
                         FROM tbcandidato
                        WHERE idConcurso = {$idConcurso}";

                    # nome
                    if (!is_null($parametroNome)) {
                        $select .= " AND nome LIKE '%{$parametroNome}%'";
                    }

                    # cargo
                    $select .= " AND cargo = '{$item["cargo"]}'";

                    $select .= " ORDER BY 7 DESC LIMIT {$numeroVagas}";

                    $row = $pessoal->select($select);

                    # tabela
                    $relatorio = new Relatorio();

                    if ($primeiro) {
                        $relatorio->set_titulo("Cadastro de Candidatos Aprovados");
                        $primeiro = false;
                        $relatorio->set_dataImpressao(false);
                    } else {
                        $relatorio->set_dataImpressao(false);
                        $relatorio->set_cabecalhoRelatorio(false);
                        $relatorio->set_menuRelatorio(false);
                        #$relatorio->set_linhaNomeColuna(false);
                        $relatorio->set_log(false);
                    }

                    $relatorio->set_totalRegistro(false);
                    $relatorio->set_conteudo($row);
                    $relatorio->set_label(["Inscrição", "Candidato", "CPF", "E-mail", "Telefone", "Cargo"]);
                    #$tabela->set_width([10, 20, 55, 15]);
                    $relatorio->set_align(["center", "left", "center", "left"]);
                    $relatorio->set_numeroOrdem(true);
                    $relatorio->set_funcao([null, "plm"]);
                    $relatorio->set_numGrupo(5);

                    $relatorio->show();
                }
            }
            break;
        ################################################################     

        case "relatorio2":

            /*
             *  Candidatos de um cargo
             */
            if ($parametroCargoCandidato <> "*") {
                $numeroVagas = $concurso->get_numVagasAcAprovadas($idConcurso, $parametroCargoCandidato);
                $cadastroReserva = 3 * $numeroVagas;
                $foraCadastro = $numeroVagas + $cadastroReserva;

                # Monta o select
                $select = "SELECT inscricao,
                              nome,
                              CONVERT(notaFinal, DECIMAL(10,2))
                         FROM tbcandidato
                        WHERE idConcurso = {$idConcurso}";

                # nome
                if (!is_null($parametroNome)) {
                    $select .= " AND nome LIKE '%{$parametroNome}%'";
                }

                # cargo
                if ($parametroCargoCandidato <> "*") {
                    $select .= " AND cargo = '{$parametroCargoCandidato}'";
                }

                $select .= " ORDER BY 3 DESC LIMIT {$numeroVagas} ";

                $row = $pessoal->select($select);

                # tabela
                $tabela = new Relatorio();
                $tabela->set_titulo("Cadastro de Candidatos Aprovados");

                if (empty($numeroVagas)) {
                    $tabela->set_subtitulo("{$parametroCargoCandidato}");
                } else {
                    $tabela->set_subtitulo("{$parametroCargoCandidato}<br/>{$numeroVagas} Vagas");
                }

                $tabela->set_conteudo($row);
                $tabela->set_label(["Inscrição", "Candidato", "Pontuação"]);
                #$tabela->set_width([10, 20, 55, 15]);
                $tabela->set_align(["center", "left", "center"]);
                $tabela->set_numeroOrdem(true);
                $tabela->set_funcao([null, "plm"]);
                $tabela->show();
            } else {
                /*
                 *  Todos os cargos
                 */
                # Pega os cargos
                $result = $pessoal->select('SELECT DISTINCT cargo
                                              FROM tbcandidato
                                          ORDER BY cargo');

                $primeiro = true;

                foreach ($result as $item) {

                    $numeroVagas = $concurso->get_numVagasAcAprovadas($idConcurso, $item["cargo"]);
                    $cadastroReserva = 3 * $numeroVagas;
                    $foraCadastro = $numeroVagas + $cadastroReserva;

                    # Monta o select
                    $select = "SELECT inscricao,
                                       nome,
                                       CONVERT(notaFinal, DECIMAL(10,2)),
                                       CONCAT(cargo,'<br/>{$numeroVagas} Vagas')
                         FROM tbcandidato
                        WHERE idConcurso = {$idConcurso}";

                    # nome
                    if (!is_null($parametroNome)) {
                        $select .= " AND nome LIKE '%{$parametroNome}%'";
                    }

                    # cargo
                    $select .= " AND cargo = '{$item["cargo"]}'";

                    $select .= " ORDER BY 3 DESC LIMIT {$numeroVagas}";

                    $row = $pessoal->select($select);

                    # tabela
                    $relatorio = new Relatorio();

                    if ($primeiro) {
                        $relatorio->set_titulo("Cadastro de Candidatos Aprovados");
                        $primeiro = false;
                        $relatorio->set_dataImpressao(false);
                    } else {
                        $relatorio->set_dataImpressao(false);
                        $relatorio->set_cabecalhoRelatorio(false);
                        $relatorio->set_menuRelatorio(false);
                        #$relatorio->set_linhaNomeColuna(false);
                        $relatorio->set_log(false);
                    }

                    $relatorio->set_totalRegistro(false);
                    $relatorio->set_conteudo($row);
                    $relatorio->set_label(["Inscrição", "Candidato", "Pontuação", "Cargo"]);
                    #$tabela->set_width([10, 20, 55, 15]);
                    $relatorio->set_align(["center", "left", "center"]);
                    $relatorio->set_numeroOrdem(true);
                    $relatorio->set_funcao([null, "plm"]);
                    $relatorio->set_numGrupo(3);

                    $relatorio->show();
                }
            }
            break;
        ##############################################################################################################

        case "editaCandidato" :
            br(8);
            aguarde();

            # Informa o $id Servidor
            set_session('idCandidatoPesquisado', $id);

            # Informa a origem
            set_session('origem', "cadastroCandidatoAdm.php");

            # Carrega a página específica
            loadPage('cadastroCandidatoAdmEdita.php');
            break;

        ################################################################      
    }
    $grid->fechaColuna();
    $grid->fechaGrid();

    $page->terminaPagina();
} else {
    loadPage("../../areaServidor/sistema/login.php");
}
