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
        $atividade = "Visualizou o cadastro de concurso administrativo";
        $data = date("Y-m-d H:i:s");
        $intra->registraLog($idUsuario, $data, $atividade, null, null, 7);
    }

    # Pega a fase
    $fase = get('fase', 'aguardaClassificacao');

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

        $parametroCargo = post('parametroCargo', get_session('parametroCargo', '*'));
        $parametroSituacao = post('parametroSituacao', get_session('parametroSituacao', '*'));
        $parametroVaga = post('parametroVaga', get_session('parametroVaga', '*'));

        set_session('parametroCargo', $parametroCargo);
        set_session('parametroSituacao', $parametroSituacao);
        set_session('parametroVaga', $parametroVaga);
    }

    # Começa uma nova página
    $page = new Page();
    $page->iniciaPagina();

    # Cabeçalho da Página
    if ($fase <> "relatorioAtivos"
            AND $fase <> "relatorioInativos"
            AND $fase <> "relatorioClassificacao"
            AND $fase <> "relatorioTodos") {

        AreaServidor::cabecalho();
    }

    $grid = new Grid();
    $grid->abreColuna(12);

################################################################

    switch ($fase) {
        case "":
        case "aguardaClassificacao" :

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
            $concurso->exibeMenu($idConcurso, "Classificação");

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

            loadPage('?fase=classificacao');
            break;

            $grid->fechaColuna();
            $grid->fechaGrid();
            break;

        ################################################################

        case "classificacao" :

            # Cria um menu
            $menu1 = new MenuBar();

            # Voltar
            $botaoVoltar = new Link("Voltar", "areaConcursoAdm.php");
            $botaoVoltar->set_class('button');
            $botaoVoltar->set_title('Voltar a página anterior');
            $botaoVoltar->set_accessKey('V');
            $menu1->add_link($botaoVoltar, "left");

            # Relatório
            $imagem2 = new Imagem(PASTA_FIGURAS . 'print.png', null, 15, 15);
            $botaoRel = new Button();
            $botaoRel->set_title("Relatório dos Servidores");
            $botaoRel->set_target("_blank");
            $botaoRel->set_url("?fase=relatorioClassificacao");
            $botaoRel->set_imagem($imagem2);
            $menu1->add_link($botaoRel, "right");

            $menu1->show();

            $grid->fechaColuna();

            #######################################################
            # Menu

            $grid->abreColuna(3);

            # Exibe os dados do Concurso
            $concurso->exibeDadosConcurso($idConcurso, true);

            # menu
            $concurso->exibeMenu($idConcurso, "Classificação");

            # Exibe os servidores deste concurso
            $concurso->exibeQuadroServidoresConcursoPorCargo($idConcurso);

            $grid->fechaColuna();

            #######################################################3

            $grid->abreColuna(9);

            # Formulário
            $form = new Form('?fase=aguardaClassificacao');

            # cargos por nivel
            $result = $pessoal->select('SELECT cargo,
                                                cargo
                                           FROM tbtipocargo
                                          WHERE cargo <> "Professor Associado" 
                                            AND cargo <> "Professor Titular" 
                                       ORDER BY 2');

            # acrescenta todos
            array_unshift($result, ['*', '-- Todos --']);

            $controle = new Input('parametroCargo', 'combo', 'Cargo - Área - Função:', 1);
            $controle->set_size(30);
            $controle->set_title('Filtra por Cargo');
            $controle->set_array($result);
            $controle->set_valor($parametroCargo);
            $controle->set_onChange('formPadrao.submit();');
            $controle->set_autofocus(true);
            $controle->set_linha(1);
            $controle->set_col(6);
            $form->add_item($controle);

            $controle = new Input('parametroSituacao', 'combo', 'Situação:', 1);
            $controle->set_size(30);
            $controle->set_title('Filtra por Situação');
            $controle->set_array([['*', '-- Todos --'], [1, 'Ativos'], [2, 'Inativos']]);
            $controle->set_valor($parametroSituacao);
            $controle->set_onChange('formPadrao.submit();');
            $controle->set_linha(1);
            $controle->set_col(3);
            $form->add_item($controle);

            $controle = new Input('parametroVaga', 'combo', 'Vaga Preenchida?:', 1);
            $controle->set_size(30);
            $controle->set_title('Filtra por Vaga preenchioda Sim ou Não');
            $controle->set_array([['*', '-- Todos --'], [1, 'Sim'], [2, 'Não']]);
            $controle->set_valor($parametroVaga);
            $controle->set_onChange('formPadrao.submit();');
            $controle->set_linha(1);
            $controle->set_col(3);
            $form->add_item($controle);

            $form->show();

            # Monta o select
            $select = "SELECT CONCAT(sigla,' - ',tbcargo.nome),
                              idServidor, 
                              cotasConcurso,
                              idServidor,
                              idServidor,
                              idServidor,
                              idServidor,
                              idServidor,
                              idServidor
                         FROM tbservidor LEFT JOIN tbpessoa USING (idPessoa)
                                         LEFT JOIN tbperfil USING (idPerfil)
                                         LEFT JOIN tbcargo USING (idCargo)
                                         LEFT JOIN tbtipocargo ON (tbcargo.idTipoCargo = tbtipocargo.idTipoCargo)
                        WHERE idConcurso = {$idConcurso}";

            # cargo
            if ($parametroCargo <> "*") {
                if (is_numeric($parametroCargo)) {
                    $select .= ' AND (tbcargo.idcargo = "' . $parametroCargo . '")';
                    $titulo = $pessoal->get_nomeCompletoCargo($parametroCargo);
                    $atividade = "Visualizou a classificação do cargo " . $titulo . " concurso " . $concurso->get_nomeConcurso($idConcurso);
                } else { # senão é nivel do cargo
                    $select .= ' AND (tbtipocargo.cargo = "' . $parametroCargo . '")';
                    $titulo = $parametroCargo;
                    $atividade = "Visualizou a classificação do cargo " . $parametroCargo . " concurso " . $concurso->get_nomeConcurso($idConcurso);
                }
            } else {
                $titulo = "Classificação Geral";
                $atividade = "Visualizou a classificação do concurso " . $concurso->get_nomeConcurso($idConcurso);
            }

            if ($parametroSituacao <> "*") {
                if ($parametroSituacao == 1) {
                    $select .= ' AND situacao = 1';
                }

                if ($parametroSituacao == 2) {
                    $select .= ' AND situacao <> 1';
                }
            }

            if ($parametroVaga <> "*") {
                if ($parametroVaga == 1) {
                    $select .= ' AND situacao <> 1 AND idServidor IN (SELECT idServidorOcupanteAnterior FROM tbservidor WHERE idServidorOcupanteAnterior IS NOT NULL AND idServidorOcupanteAnterior <> 0)';
                }

                if ($parametroVaga == 2) {
                    $select .= ' AND situacao <> 1 AND idServidor NOT IN (SELECT idServidorOcupanteAnterior FROM tbservidor WHERE idServidorOcupanteAnterior IS NOT NULL AND idServidorOcupanteAnterior <> 0)';
                }
            }

            $select .= " ORDER BY tbtipocargo.idTipoCargo, tbcargo.nome, instituicaoConcurso, cotasConcurso, classificacaoConcurso, dtAdmissao desc";

            # Pega os dados
            $row = $pessoal->select($select);

            # tabela
            $tabela = new Tabela();
            $tabela->set_titulo("Classificação - {$titulo}");
            $tabela->set_conteudo($row);
            $tabela->set_label(["Cargo", "Class.", "Cota", "Servidor", "Publicações", "Vaga Anterior<br/>Ocupada por:", "Vaga já foi Preenchida?", "Obs", "Editar"]);
            $tabela->set_width(array(15, 5, 5, 20, 20, 15, 15));
            $tabela->set_align(["left", "center", "center", "left", "left"]);

            $tabela->set_classe([null, "Concurso", null, "pessoal", "Concurso", "Concurso", "Concurso", "Concurso"]);
            $tabela->set_metodo([null, "exibeClassificacaoServidor", null, "get_nomeELotacaoESituacaoEAdmissao", "exibePublicacoesServidor", "exibeOcupanteAnterior", "servidorInativoVagaPreenchida", "exibeObs"]);
            $tabela->set_funcao([null, null, "trataNulo"]);

            # Botão de exibição dos servidores com permissão a essa regra
            $botao = new Link(null, '?fase=editaServidor&idServidorPesquisado=', 'Edita o Servidor');
            $botao->set_imagem(PASTA_FIGURAS . 'bullet_edit.png', 20, 20);
            $tabela->set_link([null, null, null, null, null, null, null, null, $botao]);

            $tabela->set_rowspan(0);
            $tabela->set_grupoCorColuna(0);

            $tabela->show();

            $grid->fechaColuna();
            $grid->fechaGrid();

            # Grava no log a atividade            
            $data = date("Y-m-d H:i:s");
            $intra->registraLog($idUsuario, $data, $atividade, null, null, 7);
            break;

        ################################################################

        case "editaServidor" :
            br(8);
            aguarde();

            # Informa o $id Servidor
            set_session('idServidorPesquisado', $idServidorPesquisado);

            # Informa a origem
            set_session('origem', 'cadastroConcursoAdm.php');

            # Carrega a página específica
            loadPage('servidorConcurso.php');
            break;

        ################################################################

        case "aguardaListaServidoresAtivos" :

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
            # Menu

            $grid->abreColuna(3);

            # Exibe os dados do Concurso
            $concurso->exibeDadosConcurso($idConcurso, true);

            # menu
            $concurso->exibeMenu($idConcurso, "Servidores Ativos");

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

            loadPage('?fase=listaServidoresAtivos');
            break;

            $grid->fechaColuna();
            $grid->fechaGrid();
            break;

        ################################################################

        case "listaServidoresAtivos" :

            # Limita o tamanho da tela
            $grid = new Grid();
            $grid->abreColuna(12);

            # Informa a origem
            set_session('origem', 'cadastroConcursoAdm.php?fase=aguardaListaServidoresAtivos');

            $vagaAdm = new VagaAdm();

            # Cria um menu
            $menu = new MenuBar();

            # Voltar
            $botaoVoltar = new Link("Voltar", "areaConcursoAdm.php");
            $botaoVoltar->set_class('button');
            $botaoVoltar->set_title('Voltar a página anterior');
            $botaoVoltar->set_accessKey('V');
            $menu->add_link($botaoVoltar, "left");

            # Relatório
            $imagem2 = new Imagem(PASTA_FIGURAS . 'print.png', null, 15, 15);
            $botaoRel = new Button();
            $botaoRel->set_title("Relatório dos Servidores");
            $botaoRel->set_target("_blank");
            $botaoRel->set_url("?fase=relatorioAtivos");
            $botaoRel->set_imagem($imagem2);
            $menu->add_link($botaoRel, "right");

            $menu->show();

            $grid->fechaColuna();

            #######################################################
            # Menu
            $grid->abreColuna(3);

            # Exibe os dados do Concurso
            $concurso->exibeDadosConcurso($idConcurso, true);

            # menu
            $concurso->exibeMenu($idConcurso, "Servidores Ativos");

            # Exibe os servidores deste concurso
            $concurso->exibeQuadroServidoresConcursoPorCargo($idConcurso);

            $grid->fechaColuna();

            #######################################################

            $grid->abreColuna(9);

            # Formulário
            $form = new Form('?fase=aguardaListaServidoresAtivos');

            # cargos por nivel
            $result = $pessoal->select('SELECT cargo,
                                               cargo
                                          FROM tbtipocargo
                                         WHERE cargo <> "Professor Associado" 
                                           AND cargo <> "Professor Titular" 
                                      ORDER BY 2');

            # acrescenta todos
            array_unshift($result, array('*', '-- Todos --'));

            $controle = new Input('parametroCargo', 'combo', 'Cargo:', 1);
            $controle->set_size(30);
            $controle->set_title('Filtra por Cargo');
            $controle->set_array($result);
            $controle->set_valor($parametroCargo);
            $controle->set_onChange('formPadrao.submit();');
            $controle->set_autofocus(true);
            $controle->set_linha(1);
            $controle->set_col(12);
            $form->add_item($controle);

            $form->show();

            # Lista de Servidores
            if ($parametroCargo <> "*") {
                $lista = new ListaServidores("{$parametroCargo} - Servidores Ativos");
                $lista->set_cargo($parametroCargo);
                $atividade = "Visualizou os servidores ativos do cargo " . $parametroCargo . " do concurso " . $concurso->get_nomeConcurso($idConcurso);
            } else {
                $lista = new ListaServidores("Servidores Ativos");
                $atividade = "Visualizou os servidores ativos do concurso " . $concurso->get_nomeConcurso($idConcurso);
            }
            $lista->set_situacao(1);
            $lista->set_concurso($idConcurso);
            $lista->showTabela();

            $grid->fechaColuna();
            $grid->fechaGrid();

            # Grava no log a atividade
            $data = date("Y-m-d H:i:s");
            $intra->registraLog($idUsuario, $data, $atividade, null, null, 7);
            break;

        ################################################################

        case "aguardaListaServidoresInativos" :

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
            # Menu

            $grid->abreColuna(3);

            # Exibe os dados do Concurso
            $concurso->exibeDadosConcurso($idConcurso, true);

            # menu
            $concurso->exibeMenu($idConcurso, "Servidores Inativos");

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

            loadPage('?fase=listaServidoresInativos');
            break;

            $grid->fechaColuna();
            $grid->fechaGrid();
            break;

        ################################################################

        case "listaServidoresInativos" :

            # Limita o tamanho da tela
            $grid = new Grid();
            $grid->abreColuna(12);

            # Informa a origem
            set_session('origem', 'cadastroConcursoAdm.php?fase=aguardaListaServidoresInativos');

            $vagaAdm = new VagaAdm();

            # Cria um menu
            $menu = new MenuBar();

            # Voltar
            $botaoVoltar = new Link("Voltar", "areaConcursoAdm.php");
            $botaoVoltar->set_class('button');
            $botaoVoltar->set_title('Voltar a página anterior');
            $botaoVoltar->set_accessKey('V');
            $menu->add_link($botaoVoltar, "left");

            # Relatório
            $imagem2 = new Imagem(PASTA_FIGURAS . 'print.png', null, 15, 15);
            $botaoRel = new Button();
            $botaoRel->set_title("Relatório dos Servidores");
            $botaoRel->set_target("_blank");
            $botaoRel->set_url("?fase=relatorioInativos");
            $botaoRel->set_imagem($imagem2);
            $menu->add_link($botaoRel, "right");

            $menu->show();

            $grid->fechaColuna();

            #######################################################3
            # Menu

            $grid->abreColuna(3);

            # Exibe os dados do Concurso
            $concurso->exibeDadosConcurso($idConcurso, true);

            # menu
            $concurso->exibeMenu($idConcurso, "Servidores Inativos");

            # Exibe os servidores deste concurso
            $concurso->exibeQuadroServidoresConcursoPorCargo($idConcurso);

            $grid->fechaColuna();

            #######################################################

            $grid->abreColuna(9);

            # Formulário
            $form = new Form('?fase=aguardaListaServidoresInativos');

            # cargos por nivel
            $result = $pessoal->select('SELECT cargo,
                                               cargo
                                          FROM tbtipocargo
                                         WHERE cargo <> "Professor Associado" 
                                           AND cargo <> "Professor Titular" 
                                      ORDER BY 2');

            # acrescenta todos
            array_unshift($result, array('*', '-- Todos --'));

            $controle = new Input('parametroCargo', 'combo', 'Cargo:', 1);
            $controle->set_size(30);
            $controle->set_title('Filtra por Cargo');
            $controle->set_array($result);
            $controle->set_valor($parametroCargo);
            $controle->set_onChange('formPadrao.submit();');
            $controle->set_autofocus(true);
            $controle->set_linha(1);
            $controle->set_col(12);
            $form->add_item($controle);

            $form->show();

            # Lista de Servidores
            if ($parametroCargo <> "*") {
                $lista = new ListaServidores("{$parametroCargo} - Servidores Inativos");
                $lista->set_cargo($parametroCargo);
                $atividade = "Visualizou os servidores inativos do cargo " . $parametroCargo . " do concurso " . $concurso->get_nomeConcurso($idConcurso);
            } else {
                $lista = new ListaServidores("Servidores Inativos");
                $atividade = "Visualizou os servidores inativos do concurso " . $concurso->get_nomeConcurso($idConcurso);
            }
            $lista->set_situacao(1);
            $lista->set_situacaoSinal("<>");
            $lista->set_concurso($idConcurso);
            $lista->showTabela();

            $grid->fechaColuna();
            $grid->fechaGrid();

            # Grava no log a atividade

            $data = date("Y-m-d H:i:s");
            $intra->registraLog($idUsuario, $data, $atividade, null, null, 7);
            break;

        ################################################################

        case "aguardaListaServidoresTodos" :

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
            # Menu

            $grid->abreColuna(3);

            # Exibe os dados do Concurso
            $concurso->exibeDadosConcurso($idConcurso, true);

            # menu
            $concurso->exibeMenu($idConcurso, "Todos os Servidores");

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

            loadPage('?fase=listaServidoresTodos');
            break;

            $grid->fechaColuna();
            $grid->fechaGrid();
            break;

        ################################################################

        case "listaServidoresTodos" :

            # Limita o tamanho da tela
            $grid = new Grid();
            $grid->abreColuna(12);

            # Informa a origem
            set_session('origem', 'cadastroConcursoAdm.php?fase=aguardaListaServidoresTodos');

            $vagaAdm = new VagaAdm();

            # Cria um menu
            $menu = new MenuBar();

            # Voltar
            $botaoVoltar = new Link("Voltar", "areaConcursoAdm.php");
            $botaoVoltar->set_class('button');
            $botaoVoltar->set_title('Voltar a página anterior');
            $botaoVoltar->set_accessKey('V');
            $menu->add_link($botaoVoltar, "left");

            # Relatório
            $imagem2 = new Imagem(PASTA_FIGURAS . 'print.png', null, 15, 15);
            $botaoRel = new Button();
            $botaoRel->set_title("Relatório dos Servidores");
            $botaoRel->set_target("_blank");
            $botaoRel->set_url("?fase=relatorioTodos");
            $botaoRel->set_imagem($imagem2);
            $menu->add_link($botaoRel, "right");

            $menu->show();

            $grid->fechaColuna();

            #######################################################
            # Menu
            $grid->abreColuna(3);

            # Exibe os dados do Concurso
            $concurso->exibeDadosConcurso($idConcurso, true);

            # menu
            $concurso->exibeMenu($idConcurso, "Todos os Servidores");

            # Exibe os servidores deste concurso
            $concurso->exibeQuadroServidoresConcursoPorCargo($idConcurso);

            $grid->fechaColuna();

            #######################################################

            $grid->abreColuna(9);

            # Formulário
            $form = new Form('?fase=aguardaListaServidoresTodos');

            # cargos por nivel
            $result = $pessoal->select('SELECT cargo,
                                               cargo
                                          FROM tbtipocargo
                                         WHERE cargo <> "Professor Associado" 
                                           AND cargo <> "Professor Titular" 
                                      ORDER BY 2');

            # acrescenta todos
            array_unshift($result, array('*', '-- Todos --'));

            $controle = new Input('parametroCargo', 'combo', 'Cargo:', 1);
            $controle->set_size(30);
            $controle->set_title('Filtra por Cargo');
            $controle->set_array($result);
            $controle->set_valor($parametroCargo);
            $controle->set_onChange('formPadrao.submit();');
            $controle->set_autofocus(true);
            $controle->set_linha(1);
            $controle->set_col(12);
            $form->add_item($controle);

            $form->show();

            # Lista de Servidores
            if ($parametroCargo <> "*") {
                $lista = new ListaServidores("{$parametroCargo} - Servidores Ativos e Inativos");
                $lista->set_cargo($parametroCargo);
                $atividade = "Visualizou os todos os servidores ativos e inativos do cargo " . $parametroCargo . " do concurso " . $concurso->get_nomeConcurso($idConcurso);
            } else {
                $lista = new ListaServidores("Servidores Ativos e Inativos");
                $atividade = "Visualizou todos os servidores ativos e inativos do concurso " . $concurso->get_nomeConcurso($idConcurso);
            }

            $lista->set_concurso($idConcurso);
            $lista->showTabela();

            $grid->fechaColuna();
            $grid->fechaGrid();

            # Grava no log a atividade
            $data = date("Y-m-d H:i:s");
            $intra->registraLog($idUsuario, $data, $atividade, null, null, 7);
            break;

        ################################################################


        case "relatorioAtivos" :
            # Lista de Servidores Ativos
            $lista = new ListaServidores('Servidores Ativos');
            $lista->set_situacao(1);
            $lista->set_concurso($idConcurso);
            if ($parametroCargo <> "*") {
                $lista->set_cargo($parametroCargo);
            }
            $lista->showRelatorio();
            break;

        ################################################################

        case "relatorioInativos" :
            # Lista de Servidores Inativos
            $lista = new ListaServidores('Servidores Inativos');
            $lista->set_situacao(1);
            $lista->set_situacaoSinal("<>");
            $lista->set_concurso($idConcurso);
            if ($parametroCargo <> "*") {
                $lista->set_cargo($parametroCargo);
            }
            $lista->showRelatorio();
            break;

        ################################################################

        case "relatorioTodos" :
            # Lista de Servidores ativos e Inativos
            $lista = new ListaServidores('Servidores Ativos e Inativos');
            $lista->set_concurso($idConcurso);
            if ($parametroCargo <> "*") {
                $lista->set_cargo($parametroCargo);
            }
            $lista->showRelatorio();
            break;

        ###############################################################

        case "relatorioClassificacao" :
            # Pega os dados
            $dados = $concurso->get_dados($idConcurso);            
                
            # Monta o select
            $select = "SELECT CONCAT(sigla,' - ',tbcargo.nome),
                              idServidor, 
                              cotasConcurso,
                              idServidor,
                              idServidor,
                              idServidor,
                              idServidor,
                              idServidor
                         FROM tbservidor LEFT JOIN tbpessoa USING (idPessoa)
                                         LEFT JOIN tbperfil USING (idPerfil)
                                         LEFT JOIN tbcargo USING (idCargo)
                                         LEFT JOIN tbtipocargo ON (tbcargo.idTipoCargo = tbtipocargo.idTipoCargo)
                        WHERE idConcurso = {$idConcurso}";

            # cargo
            if ($parametroCargo <> "*") {
                if (is_numeric($parametroCargo)) {
                    $select .= ' AND (tbcargo.idcargo = "' . $parametroCargo . '")';
                    $titulo = $pessoal->get_nomeCompletoCargo($parametroCargo);
                    $atividade = "Visualizou a classificação do cargo " . $titulo . " concurso " . $concurso->get_nomeConcurso($idConcurso);
                } else { # senão é nivel do cargo
                    $select .= ' AND (tbtipocargo.cargo = "' . $parametroCargo . '")';
                    $titulo = $parametroCargo;
                    $atividade = "Visualizou a classificação do cargo " . $parametroCargo . " concurso " . $concurso->get_nomeConcurso($idConcurso);
                }
            } else {
                $titulo = "Classificação Geral";
                $atividade = "Visualizou a classificação do concurso " . $concurso->get_nomeConcurso($idConcurso);
            }

            if ($parametroSituacao <> "*") {
                if ($parametroSituacao == 1) {
                    $select .= ' AND situacao = 1';
                }

                if ($parametroSituacao == 2) {
                    $select .= ' AND situacao <> 1';
                }
            }

            if ($parametroVaga <> "*") {
                if ($parametroVaga == 1) {
                    $select .= ' AND situacao <> 1 AND idServidor IN (SELECT idServidorOcupanteAnterior FROM tbservidor WHERE idServidorOcupanteAnterior IS NOT NULL AND idServidorOcupanteAnterior <> 0)';
                }

                if ($parametroVaga == 2) {
                    $select .= ' AND situacao <> 1 AND idServidor NOT IN (SELECT idServidorOcupanteAnterior FROM tbservidor WHERE idServidorOcupanteAnterior IS NOT NULL AND idServidorOcupanteAnterior <> 0)';
                }
            }

            $select .= " ORDER BY tbtipocargo.idTipoCargo, tbcargo.nome, instituicaoConcurso, cotasConcurso, classificacaoConcurso, dtAdmissao desc";

            # Pega os dados
            $row = $pessoal->select($select);

            # tabela
            $relatorio = new Relatorio();
            $relatorio->set_titulo("Concurso {$dados["anobase"]}");            
            $relatorio->set_subtitulo($titulo);
            $relatorio->set_conteudo($row);
            $relatorio->set_label(["Cargo", "Class.", "Cota", "Servidor", "Publicações", "Vaga Anterior<br/>Ocupada por:", "Vaga já foi Preenchida?", "Obs"]);
            $relatorio->set_width([15, 5, 5, 20, 20, 15, 15]);
            $relatorio->set_align(["left", "center", "center", "left", "left"]);

            $relatorio->set_classe([null, "Concurso", null, "pessoal", "Concurso", "Concurso", "Concurso", "Concurso"]);
            $relatorio->set_metodo([null, "exibeClassificacaoServidor", null, "get_nomeELotacaoESituacaoEAdmissao", "exibePublicacoesServidor", "exibeOcupanteAnterior", "servidorInativoVagaPreenchida", "exibeObsRel"]);
            $relatorio->set_funcao([null, null, "trataNulo"]);
            
            $relatorio->set_bordaInterna(true);

            $relatorio->set_numGrupo(0);
//            $relatorio->set_grupoCorColuna(0);

            $relatorio->show();

            break;

        ################################################################
    }
    $grid->fechaColuna();
    $grid->fechaGrid();

    $page->terminaPagina();
} else {
    loadPage("../../areaServidor/sistema/login.php");
}
