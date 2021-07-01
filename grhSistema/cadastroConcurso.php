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

# Apaga a origem
set_session('origem');

# Permissão de Acesso
$acesso = Verifica::acesso($idUsuario, 2);

if ($acesso) {

    # Conecta ao Banco de Dados
    $intra = new Intra();
    $pessoal = new Pessoal();

    # Verifica se veio menu grh e registra o acesso no log
    $grh = get('grh', false);
    if ($grh) {
        # Grava no log a atividade
        $atividade = "Visualizou o cadastro de concurso";
        $data = date("Y-m-d H:i:s");
        $intra->registraLog($idUsuario, $data, $atividade, null, null, 7);
    }

    # Verifica a fase do programa
    $fase = get('fase', 'listar');
    $subFase = get('subFase', 1);
    $parametroCargo = get('cargo');
    $idServidorPesquisado = get('idServidorPesquisado', get_session('idServidorPesquisado'));

    # Verifica se o $idServidor tem valor dai preenche o id apartir dele
    if (!empty($idServidorPesquisado)) {
        $id = $pessoal->get_idConcurso($idServidorPesquisado);
    } else {
        $id = soNumeros(get('id'));
    }

    $idConcursoPublicacao = soNumeros(get('idConcursoPublicacao'));

    # Pega os parâmetros
    $parametroTipo = get('parametroTipo', get_session('parametroTipo', 1));

    # Joga os parâmetros par as sessions 
    set_session('parametroTipo', $parametroTipo);

    # Pega os dados do concurso
    $concurso = new Concurso($id);

    # Pega os dados do concurso
    if (!vazio($id)) {
        $dados = $concurso->get_dados();
        $tipo = $dados["tipo"];
    }

    # Começa uma nova página
    $page = new Page();
    if ($fase == "uploadPublicacao") {
        $page->set_ready('$(document).ready(function(){
                                $("form input").change(function(){
                                    $("form p").text(this.files.length + " arquivo(s) selecionado");
                                });
                            });');
    }
    $page->iniciaPagina();

    # Cabeçalho da Página
    if ($fase <> "relatorio") {
        AreaServidor::cabecalho();
    }

    # Abre um novo objeto Modelo
    $objeto = new Modelo();

    ################################################################
    # Nome do Modelo (aparecerá nos fildset e no caption da tabela)
    if ($parametroTipo == 1) {
        $objeto->set_nome('Concursos para Administrativo e Técnico');
    } else {
        $objeto->set_nome('Concursos para Professor');
    }

    # botão de voltar da lista
    $objeto->set_voltarLista('grh.php');

    # select da lista
    $select = 'SELECT idConcurso,
                      anobase,
                      dtPublicacaoEdital,
                      regime,
                      CASE tipo
                        WHEN 1 THEN "Adm & Tec"
                        WHEN 2 THEN "Professor"
                        ELSE "--"
                      END,
                      orgExecutor,
                      idConcurso,
                      idConcurso,
                      idConcurso
                 FROM tbconcurso LEFT JOIN tbplano USING (idPlano)
                WHERE true';

    if (!vazio($parametroTipo)) {
        $select .= ' AND tipo = ' . $parametroTipo;
    }

    $select .= ' ORDER BY anobase desc, dtPublicacaoEdital desc';

    $objeto->set_selectLista($select);

    # select do edita
    $objeto->set_selectEdita('SELECT anobase,
                                     edital,
                                     dtPublicacaoEdital,
                                     regime,
                                     tipo,
                                     orgExecutor,
                                     idPlano,
                                     obs
                                FROM tbconcurso
                               WHERE idConcurso = ' . $id);

    # Caminhos
    $objeto->set_linkEditar('?fase=editar');
    $objeto->set_linkGravar('?fase=gravar');
    $objeto->set_linkListar('cadastroConcurso.php?fase=editar&id=' . $id);

    if (vazio($id)) {
        $objeto->set_voltarForm('?');
    } else {
        $objeto->set_voltarForm('cadastroConcurso.php?fase=editar&id=' . $id);
    }

    # Parametros da tabela
    $objeto->set_label(array("id", "Ano Base", "Publicação <br/>do Edital", "Regime", "Tipo", "Executor", "Ativos", "Inativos", "Total"));
    $objeto->set_width(array(5, 12, 12, 12, 12, 22, 5, 5, 5));
    $objeto->set_align(array("center"));

    $objeto->set_rowspan(1);
    $objeto->set_grupoCorColuna(1);

    $objeto->set_funcao(array(null, null, 'date_to_php'));

    $objeto->set_classe(array(null, null, null, null, null, null, "Pessoal", "Pessoal", "Pessoal"));
    $objeto->set_metodo(array(null, null, null, null, null, null, "get_servidoresAtivosConcurso", "get_servidoresInativosConcurso", "get_servidoresConcurso"));

    $objeto->set_excluirCondicional('?fase=excluir', 0, 8, "==");

    # Classe do banco de dados
    $objeto->set_classBd('Pessoal');

    # Nome da tabela
    $objeto->set_tabela('tbconcurso');

    # Nome do campo id
    $objeto->set_idCampo('idConcurso');

    # Tipo de label do formulário
    $objeto->set_formlabelTipo(1);

    # Foco do form
    $objeto->set_formFocus('anobase');

    # Pega os dados da combo de Plano e Cargos
    $result = $pessoal->select('SELECT idPlano, 
                                      numDecreto
                                  FROM tbplano
                              ORDER BY numDecreto');

    # Campos para o formulario
    $objeto->set_campos(array(
        array('linha' => 1,
            'nome' => 'anobase',
            'label' => 'Ano:',
            'tipo' => 'texto',
            'autofocus' => true,
            'required' => true,
            'col' => 2,
            'size' => 4,
            'title' => 'Ano base do concurso'),
        array('linha' => 2,
            'nome' => 'edital',
            'label' => 'Processo do Edital:',
            'tipo' => 'texto',
            'title' => 'Número do processo do edital do concurso',
            'col' => 3,
            'size' => 20),
        array('linha' => 2,
            'nome' => 'dtPublicacaoEdital',
            'label' => 'Data da Publicação do Edital:',
            'tipo' => 'data',
            'title' => 'Data da Publicação do Edital',
            'col' => 3,
            'size' => 20),
        array('linha' => 3,
            'nome' => 'regime',
            'label' => 'Regime:',
            'tipo' => 'combo',
            'array' => array("CLT", "Estatutário"),
            'required' => true,
            'col' => 3,
            'size' => 20),
        array('linha' => 3,
            'nome' => 'tipo',
            'label' => 'Tipo:',
            'tipo' => 'combo',
            'required' => true,
            'array' => array(array(null, null),
                array(1, "Adm & Tec"),
                array(2, "Professor")),
            'col' => 3,
            'size' => 20),
        array('linha' => 3,
            'nome' => 'orgExecutor',
            'label' => 'Executor:',
            'tipo' => 'texto',
            'col' => 4,
            'size' => 30),
        array('linha' => 3,
            'nome' => 'idPlano',
            'label' => 'Plano de Cargos:',
            'tipo' => 'combo',
            'array' => $result,
            'col' => 3,
            'size' => 30),
        array('linha' => 4,
            'col' => 12,
            'nome' => 'obs',
            'label' => 'Observação:',
            'tipo' => 'textarea',
            'size' => array(80, 5))));

    # idUsuário para o LogLicença sem vencimentosLicença sem vencimentos
    $objeto->set_idUsuario($idUsuario);

    $objeto->set_botaoVoltarLista(false);
    $objeto->set_botaoIncluir(false);

    if (!vazio($id)) {
        $ativos = $pessoal->get_servidoresAtivosConcurso($id);
        $inativos = $pessoal->get_servidoresInativosConcurso($id);
        $vagas = $concurso->get_numVagasConcurso($id);
        $publicacao = $concurso->get_numPublicacaoConcurso($id);
    }

    ################################################################

    switch ($fase) {
        case "" :
        case "listar" :

            # Limita o tamanho da tela
            $grid = new Grid();
            $grid->abreColuna(12);

            # Cria um menu
            $menu1 = new MenuBar();

            # Voltar
            $botaoVoltar = new Link("Voltar", "grh.php");
            $botaoVoltar->set_class('button');
            $botaoVoltar->set_title('Voltar a página anterior');
            $botaoVoltar->set_accessKey('V');
            $menu1->add_link($botaoVoltar, "left");

            # Adm & Tec
            $botaoAdmin = new Button("Adm & Tec", "?parametroTipo=1");
            $botaoAdmin->set_title("Exibe os concursos para os cargos administrativos e técnicos");
            if ($parametroTipo == 1) {
                $botaoAdmin->set_class("hollow button");
            }
            $menu1->add_link($botaoAdmin, "right");

            # Professores
            $botaoProf = new Button("Professor", "?parametroTipo=2");
            $botaoProf->set_title("Exibe os concursos para os cargos de docentes");
            if ($parametroTipo == 2) {
                $botaoProf->set_class("hollow button");
            }
            $menu1->add_link($botaoProf, "right");

            # Vagas
            if ($parametroTipo == 1) {
                $botaoVaga = new Button("Vagas", "areaVagasAdmTec.php");
                $botaoVaga->set_title("Vagas de Adm & Tec");
            } else {
                $botaoVaga = new Button("Vagas", "areaVagasDocentes.php?origem=cadastroConcurso");
                $botaoVaga->set_title("Vagas de Docentes");
            }
            $menu1->add_link($botaoVaga, "right");

            # Incluir
            $botaoInserir = new Button("Incluir", "?fase=editar");
            $botaoInserir->set_title("Incluir");
            $menu1->add_link($botaoInserir, "right");

            # Relatórios
            $imagem = new Imagem(PASTA_FIGURAS . 'print.png', null, 15, 15);
            $botaoRel = new Button();
            $botaoRel->set_title("Relatório dessa pesquisa");
            $botaoRel->set_url("../grhRelatorios/acumulacao.geral.php");
            $botaoRel->set_target("_blank");
            $botaoRel->set_imagem($imagem);
            #$menu1->add_link($botaoRel,"right");

            $menu1->show();

            $grid->fechaColuna();
            $grid->fechaGrid();

            $objeto->listar();
            break;

        ################################################################

        case "editar" :

            # Limita a Tela 
            $grid = new Grid();
            $grid->abreColuna(12);

            if (vazio($id)) {
                # Informa a origem
                set_session('origem', 'cadastroConcurso.php');

                # Vai para a rotina de inclusão
                loadPage("?fase=editardeFato");
            } else {

                # Rotina de edição
                $menu1 = new MenuBar();

                # Voltar
                $botaoVoltar = new Link("Voltar", "?");
                $botaoVoltar->set_class('button');
                $botaoVoltar->set_title('Voltar a página anterior');
                $botaoVoltar->set_accessKey('V');
                $menu1->add_link($botaoVoltar, "left");

                # Incluir Publicação
                $botaoInserir = new Button("Incluir Publicação", "cadastroConcursoPublicacao.php?fase=editar&idConcurso=" . $id);
                $botaoInserir->set_title("Incluir Publicação");
                $menu1->add_link($botaoInserir, "right");

                $menu1->show();

                $grid->fechaColuna();

                #######################################################
                # Menu

                $grid->abreColuna(3);

                # Exibe os dados do Concurso
                $concurso->exibeDadosConcurso($id, true);

                $painel = new Callout();
                $painel->abre();

                # Inicia o Menu de Cargos                
                $menu = new Menu("menuProcedimentos");
                $menu->add_item('titulo', 'Menu');
                $menu->add_item('link', "<b>Publicações ($publicacao)</b>", '?fase=editar&id=' . $id);
                $menu->add_item('link', "Vagas ($vagas)", '?fase=concursoVagas&id=' . $id);
                if ($tipo == 1) {
                    $menu->add_item('link', "Classificação", '?fase=classificacao&id=' . $id);
                }
                $menu->add_item('link', "Servidores Ativos ($ativos)", '?fase=listaServidoresAtivos&id=' . $id);
                $menu->add_item('link', "Servidores Inativos ($inativos)", '?fase=listaServidoresInativos&id=' . $id);

                $menu->show();

                $painel->fecha();

                $grid->fechaColuna();

                #######################################################3

                $grid->abreColuna(9);

                # Exibe as Publicações

                $select = "SELECT idConcursoPublicacao,
                                 data,
                                 pag,
                                 idConcursoPublicacao,
                                 idConcursoPublicacao,
                                 idConcursoPublicacao,
                                 idConcursoPublicacao
                            FROM tbconcursopublicacao
                           WHERE idConcurso = $id  
                        ORDER BY data desc, idConcursoPublicacao desc";

                $conteudo = $pessoal->select($select);
                $numConteudo = $pessoal->count($select);

                if ($numConteudo > 0) {
                    # Monta a tabela
                    $tabela = new Tabela();
                    $tabela->set_conteudo($conteudo);
                    $tabela->set_label(array("Descrição", "Data", "Pag", "Ver", "Upload"));
                    $tabela->set_titulo("Publicações");
                    $tabela->set_funcao(array(null, "date_to_php"));
                    $tabela->set_align(array("left"));
                    $tabela->set_width(array(40, 10, 10, 10, 10));
                    $tabela->set_numeroOrdem(true);
                    $tabela->set_numeroOrdemTipo('d');

                    $tabela->set_classe(array("ConcursoPublicacao", null, null, "ConcursoPublicacao"));
                    $tabela->set_metodo(array("exibeDescricao", null, null, "exibePublicacao"));

                    $tabela->set_editar('cadastroConcursoPublicacao.php?fase=editar&idConcurso=' . $id);
                    $tabela->set_idCampo('idConcursoPublicacao');

                    $tabela->set_excluir('cadastroConcursoPublicacao.php?fase=excluir&idConcurso=' . $id);
                    $tabela->set_idCampo('idConcursoPublicacao');

                    # Botão de Upload
                    $botao = new BotaoGrafico();
                    $botao->set_label('');
                    $botao->set_url("cadastroConcurso.php?fase=uploadPublicacao&id=$id&idConcursoPublicacao=");
                    $botao->set_imagem(PASTA_FIGURAS . 'upload.png', 20, 20);

                    # Coloca o objeto link na tabela			
                    $tabela->set_link(array(null, null, null, null, $botao));

                    $tabela->show();
                } else {
                    tituloTable("Publicações");
                    callout("Nenhum Registro Encontrado", "secondary");
                }

                $grid->fechaColuna();

                #######################################################
            }

            $grid->fechaColuna();
            $grid->fechaGrid();
            break;

        ################################################################

        case "concursoVagas" :

            # Limita a Tela 
            $grid = new Grid();
            $grid->abreColuna(12);

            # Rotina de edição
            $menu1 = new MenuBar();

            # Voltar
            $botaoVoltar = new Link("Voltar", "?");
            $botaoVoltar->set_class('button');
            $botaoVoltar->set_title('Voltar a página anterior');
            $botaoVoltar->set_accessKey('V');
            $menu1->add_link($botaoVoltar, "left");

            # Incluir Vaga
            if ($tipo == 1) {
                $botaoInserir = new Button("Incluir Vaga", "cadastroConcursoVaga.php?fase=editar&idConcurso={$id}");
                $botaoInserir->set_title("Incluir Vaga");
                $menu1->add_link($botaoInserir, "right");
            } else {

                # Relatórios
                $imagem = new Imagem(PASTA_FIGURAS . 'print.png', null, 15, 15);
                $botaoRel = new Button();
                $botaoRel->set_title("Relatório de vagas desse concurao");
                $botaoRel->set_url("../grhRelatorios/concurso.vagas.docentes.php?id=" . $id);
                $botaoRel->set_target("_blank");
                $botaoRel->set_imagem($imagem);
                $menu1->add_link($botaoRel, "right");
            }

            $menu1->show();

            $grid->fechaColuna();

            #######################################################
            # Menu

            $grid->abreColuna(3);

            # Exibe os dados do Concurso
            $concurso->exibeDadosConcurso($id, true);

            $painel = new Callout();
            $painel->abre();

            # Inicia o Menu de Cargos
            $menu = new Menu("menuProcedimentos");
            $menu->add_item('titulo', 'Menu');

            $menu->add_item('link', "Publicações ($publicacao)", '?fase=editar&id=' . $id);
            $menu->add_item('link', "<b>Vagas ($vagas)</b>", '?fase=concursoVagas&id=' . $id);
            if ($tipo == 1) {
                $menu->add_item('link', "Classificação", '?fase=classificacao&id=' . $id);
            }
            $menu->add_item('link', "Servidores Ativos ($ativos)", '?fase=listaServidoresAtivos&id=' . $id);
            $menu->add_item('link', "Servidores Inativos ($inativos)", '?fase=listaServidoresInativos&id=' . $id);

            $menu->show();

            $painel->fecha();

            if ($tipo == 1) {
                # Exibe os servidores deste concurso
                $concurso->exibeQuadroServidoresConcursoPorCargo($id);
            }

            $grid->fechaColuna();

            #######################################################3

            $grid->abreColuna(9);

            if ($tipo == 1) {

                # Exibe as vagas 
                $select = "SELECT sigla,
                                  vagasNovas,
                                  vagasReposicao,
                                  (COALESCE(vagasNovas,0) + COALESCE(vagasReposicao,0)),
                                  idConcursoVaga
                             FROM tbconcursovaga JOIN tbtipocargo USING (idTipoCargo)
                            WHERE idConcurso = {$id}
                         ORDER BY 1 DESC";

                $conteudo = $pessoal->select($select);
                $numConteudo = $pessoal->count($select);

                if ($numConteudo > 0) {
                    # Monta a tabela
                    $tabela = new Tabela();
                    $tabela->set_conteudo($conteudo);
                    $tabela->set_titulo("Vagas Disponibilizadas");
                    $tabela->set_label(array("Cargo", "Vagas Novas", "Vagas de Reposição", "Total"));
                    #$tabela->set_width(array(40, 15, 15, 15));
                    $tabela->set_align(array("left"));

                    $tabela->set_colunaSomatorio([1, 2, 3]);
                    $tabela->set_textoSomatorio("Total:");
                    $tabela->set_totalRegistro(false);

                    #$tabela->set_classe(array(null, null, null, null, "VagaAdm", "VagaAdm", "VagaAdm"));
                    #$tabela->set_metodo(array(null, null, null, null, "get_servidoresAtivosVagaIdConcurso", "get_servidoresInativosVagaIdConcurso", "get_servidoresVaga"));

                    $tabela->set_editar('cadastroConcursoVaga.php?fase=editar&idConcurso=' . $id);
                    $tabela->set_excluir('cadastroConcursoVaga.php?fase=excluir&idConcurso=' . $id);
                    $tabela->set_idCampo('idConcursoVaga');

                    $tabela->show();
                } else {
                    tituloTable("Vagas de Servidores Administrativos e Técnicos");
                    callout("Nenhuma vaga cadastrada", "secondary");
                }

                # Exibe os problemas (se tiver)
                $problemas = [];

                $vagaAdm = new VagaAdm();

                # Verifica se tem servidores sem cargo cadastrado neste concurso
                $semCargo = $vagaAdm->get_numSemCargo($id);
                if ($semCargo > 0) {
                    $problemas[] = "Existe(m) {$semCargo} servidor(es) empossado(s) neste concurso sem cargo cadastrado!!";
                }

                # Verifica se o número de servidores ativos é menor ou igual ao número de vagas
                $empossadosAtivos = $vagaAdm->get_numServidoresAtivosConcurso($id);
                $vagas = $vagaAdm->get_numVagas($id);
                if ($empossadosAtivos > $vagas) {
                    $problemas[] = "Existe(m) {$empossadosAtivos} servidor(es) ativo(s) deste concurso, mas só existem {$vagas} vagas disponibilizadas!!<br/>Deve ter algum servidor cadastrado em concurso errado";
                }

                # Verifica se teva algum empossado nesse concurso
                $empossados = $vagaAdm->get_numServidoresConcurso($id);
                if ($empossados == 0) {
                    $problemas[] = "Não existe nenhum servidor empossado por este concurso! Isto está certo?";
                }

                # Verifica se o número de empossados por cada cargo é menor ou igual ao número de vagas
                # Pega os cargos disponibilizados pelo concurso
                $cargos = $vagaAdm->get_CargosEVagas($id);
                foreach ($cargos as $item) {
                    if ($vagaAdm->get_numServidoresAtivosConcurso($id, $item[0]) > $item[1]) {
                        $problemas[] = "Existe(m) {$vagaAdm->get_numServidoresAtivosConcurso($id, $item[0])} servidor(es) ativos empossado(s) neste concurso para o cargo de {$pessoal->get_nomeTipoCargo($item[0])}, mas só existem {$item[1]} vagas disponíveis para este cargo!!";
                    }
                }

                # Verifica se tem servidor de cargo não disponibilizado
                # Pega os cargos disponibilizados pelo concurso
                $cargosEmpossados = $vagaAdm->get_CargosEmpossados($id);
                $cargos = $vagaAdm->get_Cargos($id);

                foreach ($cargosEmpossados as $semp) {
                    $achei = false;
                    foreach ($cargos as $cdisp) {
                        if ($semp[0] == $cdisp[0]) {
                            $achei = true;
                            continue;
                        }

                        if (empty($semp[0])) {
                            $achei = true;
                            continue;
                        }
                    }
                    if (!$achei) {
                        $problemas[] = "Existem servidores empossados em cargo não disponibilizado!";
                    }
                }

                if (count($problemas) > 0) {

                    titulotable("Observações");
                    $painel = new Callout();
                    $painel->abre();

                    # Marcador
                    $flag = 0;

                    foreach ($problemas as $item) {
                        p($item, "pproblemas");
                        $flag++;
                        if ($flag <> count($problemas)) {
                            hr("vagasAdm");
                        }
                    }

                    $painel->fecha();
                }
            } else {
                # Exibe as vagas de Docente
                $select = 'SELECT tblotacao.DIR,
                                     tblotacao.GER,
                                     tbcargo.nome,
                                     area,
                                     idServidor,
                                     tbvagahistorico.obs,
                                     idVagaHistorico
                                FROM tbvagahistorico JOIN tbconcurso USING (idConcurso)
                                                     JOIN tblotacao USING (idLotacao)
                                                     JOIN tbvaga USING (idVaga)
                                                     JOIN tbcargo USING (idCargo)
                               WHERE idConcurso = ' . $id . ' ORDER BY tblotacao.DIR, tblotacao.GER desc';

                $conteudo = $pessoal->select($select);
                $numConteudo = $pessoal->count($select);

                if ($numConteudo > 0) {
                    # Monta a tabela
                    $tabela = new Tabela();
                    $tabela->set_conteudo($conteudo);
                    $tabela->set_align(array("center", "center", "center", "left", "left"));
                    $tabela->set_label(array("Centro", "Laboratório", "Cargo", "Área", "Servidor", "Obs"));
                    $tabela->set_titulo("Vagas de Professores");
                    $tabela->set_classe(array(null, null, null, null, "Vaga"));
                    $tabela->set_metodo(array(null, null, null, null, "get_Nome"));
                    $tabela->set_numeroOrdem(true);

                    $tabela->set_rowspan(0);
                    $tabela->set_grupoCorColuna(0);

                    $tabela->show();
                } else {
                    tituloTable("Vagas de Professores");
                    callout("Nenhuma vaga cadastrada", "secondary");
                }
            }

            $grid->fechaColuna();
            $grid->fechaGrid();
            break;

        ################################################################

        case "editardeFato" :
            $objeto->editar($id);
            break;

        ################################################################        

        case "excluir" :
            $objeto->set_linkListar('?');
        case "gravar" :
            $objeto->$fase($id);
            break;

        ################################################################

        case "aguarde" :
            br(10);
            aguarde();
            br();
            loadPage('?fase=listaServidores&id=' . $id);
            break;

        ################################################################

        case "listaServidoresAtivos" :

            # Limita o tamanho da tela
            $grid = new Grid();
            $grid->abreColuna(12);

            # Informa a origem
            set_session('origem', 'cadastroConcurso.php?fase=listaServidoresAtivos&id=' . $id);

            $vagaAdm = new VagaAdm();

            # Cria um menu
            $menu = new MenuBar();

            # Voltar
            $linkVoltar = new Link("Voltar", "?");
            $linkVoltar->set_class('button');
            $linkVoltar->set_title('Volta para a página anterior');
            $linkVoltar->set_accessKey('V');
            $menu->add_link($linkVoltar, "left");

            # Relatório
            $imagem2 = new Imagem(PASTA_FIGURAS . 'print.png', null, 15, 15);
            $botaoRel = new Button();
            $botaoRel->set_title("Relatório dos Servidores");
            $botaoRel->set_target("_blank");
            $botaoRel->set_url("?fase=relatorio&subFase=1&id=$id");
            $botaoRel->set_imagem($imagem2);
            $menu->add_link($botaoRel, "right");

            $menu->show();

            $grid->fechaColuna();

            #######################################################
            # Menu

            $grid->abreColuna(3);

            # Exibe os dados do Concurso
            $concurso->exibeDadosConcurso($id, true);

            $painel = new Callout();
            $painel->abre();

            # Inicia o Menu de Cargos                
            $menu = new Menu("menuProcedimentos");
            $menu->add_item('titulo', 'Menu');
            $menu->add_item('link', "Publicações ($publicacao)", '?fase=editar&id=' . $id);
            $menu->add_item('link', "Vagas ($vagas)", '?fase=concursoVagas&id=' . $id);
            if ($tipo == 1) {
                $menu->add_item('link', "Classificação", '?fase=classificacao&id=' . $id);
            }
            $menu->add_item('link', "<b>Servidores Ativos ($ativos)</b>", '?fase=listaServidoresAtivos&id=' . $id);
            $menu->add_item('link', "Servidores Inativos ($inativos)", '?fase=listaServidoresInativos&id=' . $id);
            $menu->show();

            $painel->fecha();

            if ($tipo == 1) {
                # Exibe os servidores deste concurso
                $concurso->exibeQuadroServidoresConcursoPorCargo($id);
            }

            $grid->fechaColuna();

            #######################################################

            $grid->abreColuna(9);

            # Cria um sub menu
            $menu = new MenuBar("small button-group");

            # Cria botões com os cargos
            $select = "SELECT idTipoCargo, cargo FROM tbtipocargo ORDER BY 1";
            $conteudo = $pessoal->select($select);

            $botaocargo = new Button("Todos", "?fase=listaServidoresAtivos&id={$id}");
            $botaocargo->set_title("Todos os Cargos");
            if ($parametroCargo == "Todos" OR empty($parametroCargo)) {
                $botaocargo->set_class("hollow button");
            } else {
                $botaocargo->set_class("button");
            }
            $menu->add_link($botaocargo, "right");

            foreach ($conteudo as $item) {
                $numero = $vagaAdm->get_servidoresAtivosVaga($id, $item[0]);
                if ($numero > 0) {
                    # cargos
                    $botaocargo = new Button($item[1], "?fase=listaServidoresAtivos&id={$id}&cargo={$item[0]}");
                    $botaocargo->set_title($pessoal->get_nomeTipoCargo($item[0]));
                    if ($parametroCargo == $item[0]) {
                        $botaocargo->set_class("hollow button");
                    } else {
                        $botaocargo->set_class("button");
                    }

                    $menu->add_link($botaocargo, "right");
                }
            }

            $menu->show();

            # Lista de Servidores Ativos
            $lista = new ListaServidores($pessoal->get_nomeTipoCargo($parametroCargo) . ' Servidores Ativos');
            $lista->set_situacao(1);
            $lista->set_tipoCargo($parametroCargo);
            $lista->set_concurso($id);
            $lista->showTabela();

            $grid->fechaColuna();
            $grid->fechaGrid();
            break;

################################################################

        case "listaServidoresInativos" :

            # Limita o tamanho da tela
            $grid = new Grid();
            $grid->abreColuna(12);

            # Informa a origem
            set_session('origem', 'cadastroConcurso.php?fase=listaServidoresInativos&id=' . $id);

            $vagaAdm = new VagaAdm();

            # Cria um menu
            $menu = new MenuBar();

            # Voltar
            $linkVoltar = new Link("Voltar", "?");
            $linkVoltar->set_class('button');
            $linkVoltar->set_title('Volta para a página anterior');
            $linkVoltar->set_accessKey('V');
            $menu->add_link($linkVoltar, "left");

            # Relatório
            $imagem2 = new Imagem(PASTA_FIGURAS . 'print.png', null, 15, 15);
            $botaoRel = new Button();
            $botaoRel->set_title("Relatório dos Servidores");
            $botaoRel->set_target("_blank");
            $botaoRel->set_url("?fase=relatorio&subFase=2&id=$id");
            $botaoRel->set_imagem($imagem2);
            $menu->add_link($botaoRel, "right");

            $menu->show();

            $grid->fechaColuna();

            #######################################################3
            # Menu

            $grid->abreColuna(3);

            # Exibe os dados do Concurso
            $concurso->exibeDadosConcurso($id, true);

            $painel = new Callout();
            $painel->abre();

            # Inicia o Menu de Cargos                
            $menu = new Menu("menuProcedimentos");
            $menu->add_item('titulo', 'Menu');
            $menu->add_item('link', "Publicações ($publicacao)", '?fase=editar&id=' . $id);
            $menu->add_item('link', "Vagas ($vagas)", '?fase=concursoVagas&id=' . $id);
            if ($tipo == 1) {
                $menu->add_item('link', "Classificação", '?fase=classificacao&id=' . $id);
            }
            $menu->add_item('link', "Servidores Ativos ($ativos)", '?fase=listaServidoresAtivos&id=' . $id);
            $menu->add_item('link', "<b>Servidores Inativos ($inativos)</b>", '?fase=listaServidoresInativos&id=' . $id);
            $menu->show();

            $painel->fecha();

            if ($tipo == 1) {
                # Exibe os servidores deste concurso
                $concurso->exibeQuadroServidoresConcursoPorCargo($id);
            }

            $grid->fechaColuna();

            #######################################################3

            $grid->abreColuna(9);

            # Cria um sub menu
            $menu = new MenuBar("small button-group");

            # Cria botões com os cargos
            $select = "SELECT idTipoCargo, cargo FROM tbtipocargo ORDER BY 1";
            $conteudo = $pessoal->select($select);

            $botaocargo = new Button("Todos", "?fase=listaServidoresInativos&id={$id}");
            $botaocargo->set_title("Todos os Cargos");
            if ($parametroCargo == "Todos" OR empty($parametroCargo)) {
                $botaocargo->set_class("hollow button");
            } else {
                $botaocargo->set_class("button");
            }
            $menu->add_link($botaocargo, "right");

            foreach ($conteudo as $item) {
                $numero = $vagaAdm->get_servidoresAtivosVaga($id, $item[0]);
                if ($numero > 0) {
                    # cargos
                    $botaocargo = new Button($item[1], "?fase=listaServidoresInativos&id={$id}&cargo={$item[0]}");
                    $botaocargo->set_title($pessoal->get_nomeTipoCargo($item[0]));
                    if ($parametroCargo == $item[0]) {
                        $botaocargo->set_class("hollow button");
                    } else {
                        $botaocargo->set_class("button");
                    }

                    $menu->add_link($botaocargo, "right");
                }
            }

            $menu->show();

            # Lista de Servidores Inativos
            $lista = new ListaServidores('Servidores Inativos');
            $lista->set_situacao(1);
            $lista->set_situacaoSinal("<>");
            $lista->set_tipoCargo($parametroCargo);
            $lista->set_concurso($id);
            $lista->showTabela();

            $grid->fechaColuna();
            $grid->fechaGrid();
            break;

################################################################

        case "relatorio" :
            if ($subFase == 1) {
                # Lista de Servidores Ativos
                $lista = new ListaServidores('Servidores Ativos');
                $lista->set_situacao(1);
                $lista->set_concurso($id);
                $lista->showRelatorio();
            } else {
                # Lista de Servidores Inativos
                $lista = new ListaServidores('Servidores Inativos');
                $lista->set_situacao(1);
                $lista->set_situacaoSinal("<>");
                $lista->set_concurso($id);
                $lista->showRelatorio();
            }
            break;

################################################################

        case "grafico" :
            # Botão voltar
            botaoVoltar('?');

            # Exibe o Título
            $grid = new Grid();
            $grid->abreColuna(12);

            # Pega os dados
            $selectGrafico = 'SELECT anoBase, count(tbservidor.idServidor) 
                                FROM tbservidor LEFT JOIN tbconcurso ON (tbservidor.idConcurso = tbconcurso.idConcurso)
                               WHERE tbservidor.situacao = 1
                                 AND tbservidor.idPerfil = 1
                            GROUP BY anoBase';

            $servidores = $pessoal->select($selectGrafico);

            titulo('Servidores Estatutários Concursados por Concurso');

            $grid3 = new Grid();
            $grid3->abreColuna(4);
            br();

            # Tabela
            $tabela = new Tabela();
            $tabela->set_conteudo($servidores);
            $tabela->set_label(array("Concurso", "Servidores"));
            $tabela->set_width(array(80, 20));
            $tabela->set_align(array("left", "center"));
            $tabela->show();

            $grid3->fechaColuna();
            $grid3->abreColuna(8);

            $chart = new Chart("Pie", $servidores);
            $chart->show();

            $grid3->fechaColuna();
            $grid3->fechaGrid();

            $grid->fechaColuna();
            $grid->fechaGrid();
            break;

################################################################

        case "listaVagasConcurso" :
            # Limita o tamanho da tela
            $grid = new Grid();
            $grid->abreColuna(12);

            $concurso = new Concurso($id);
            $dados = $concurso->get_dados();

            $anoBase = $dados["anobase"];
            $dtPublicacao = $dados["dtPublicacaoEdital"];

            if (!vazio($dtPublicacao)) {
                $dtPublicacao = date_to_php($dtPublicacao);
            }

            $titulo = "Vagas do concurso de $anoBase<br>Edital Publicado em $dtPublicacao";

            # Cria um menu
            $menu = new MenuBar();

            # Voltar
            $linkVoltar = new Link("Voltar", "?");
            $linkVoltar->set_class('button');
            $linkVoltar->set_title('Volta para a página anterior');
            $linkVoltar->set_accessKey('V');
            $menu->add_link($linkVoltar, "left");

            $menu->show();

            # Conecta com o banco de dados
            $servidor = new Pessoal();

            $select = 'SELECT concat(tbconcurso.anobase," - Edital: ",DATE_FORMAT(tbconcurso.dtPublicacaoEdital,"%d/%m/%Y")) as concurso,
                             concat(IFnull(tblotacao.GER,"")," - ",IFnull(tblotacao.nome,"")) as lotacao,
                             area,
                             idServidor,
                             tbvagahistorico.obs,
                             idVagaHistorico
                        FROM tbvagahistorico JOIN tbconcurso USING (idConcurso)
                                             JOIN tblotacao USING (idLotacao)
                       WHERE idConcurso = ' . $id . ' ORDER BY tbconcurso.dtPublicacaoEdital desc';

            $conteudo = $pessoal->select($select);
            $numConteudo = $pessoal->count($select);

            if ($numConteudo > 0) {
                # Monta a tabela
                $tabela = new Tabela();
                $tabela->set_conteudo($conteudo);
                $tabela->set_align(array("left", "left", "left", "left", "left"));
                $tabela->set_label(array("Concurso", "Laboratório", "Área", "Servidor", "Obs"));
                $tabela->set_titulo($titulo);
                $tabela->set_classe(array(null, null, null, "Vaga"));
                $tabela->set_metodo(array(null, null, null, "get_Nome"));
                $tabela->set_numeroOrdem(true);
                $tabela->show();
            } else {
                tituloTable($titulo);
                callout("Nenhuma vaga cadastrada", "secondary");
            }

            $grid->fechaColuna();
            $grid->fechaGrid();
            break;

        ################################################################

        case "classificacao" :

            # Limita o tamanho da tela
            $grid = new Grid();
            $grid->abreColuna(12);

            $vagaAdm = new VagaAdm();

            # Cria um menu
            $menu = new MenuBar();

            # Voltar
            $linkVoltar = new Link("Voltar", "?");
            $linkVoltar->set_class('button');
            $linkVoltar->set_title('Volta para a página anterior');
            $linkVoltar->set_accessKey('V');
            $menu->add_link($linkVoltar, "left");

            $menu->show();

            $grid->fechaColuna();

            #######################################################3
            # Menu

            $grid->abreColuna(3);

            # Exibe os dados do Concurso
            $concurso->exibeDadosConcurso($id, true);

            $painel = new Callout();
            $painel->abre();

            # Inicia o Menu de Cargos                
            $menu = new Menu("menuProcedimentos");
            $menu->add_item('titulo', 'Menu');
            $menu->add_item('link', "Publicações ($publicacao)", '?fase=editar&id=' . $id);
            $menu->add_item('link', "Vagas ($vagas)", '?fase=concursoVagas&id=' . $id);
            $menu->add_item('link', "<b>Classificação</b>", '?fase=classificacao&id=' . $id);
            $menu->add_item('link', "Servidores Ativos ($ativos)", '?fase=listaServidoresAtivos&id=' . $id);
            $menu->add_item('link', "Servidores Inativos ($inativos)", '?fase=listaServidoresInativos&id=' . $id);
            $menu->show();

            $painel->fecha();

            if ($tipo == 1) {
                # Exibe os servidores deste concurso
                $concurso->exibeQuadroServidoresConcursoPorCargo($id);
            }

            $grid->fechaColuna();

            #######################################################3

            $grid->abreColuna(9);

            # Cria um sub menu
            $menu = new MenuBar("small button-group");

            # Cria botões com os cargos
            $select = "SELECT idTipoCargo, cargo FROM tbtipocargo ORDER BY 1";
            $conteudo = $pessoal->select($select);

            $botaocargo = new Button("Todos", "?fase=classificacao&id={$id}");
            $botaocargo->set_title("Todos os Cargos");
            if ($parametroCargo == "Todos" OR empty($parametroCargo)) {
                $botaocargo->set_class("hollow button");
            } else {
                $botaocargo->set_class("button");
            }
            $menu->add_link($botaocargo, "right");

            foreach ($conteudo as $item) {
                $numero = $vagaAdm->get_servidoresAtivosVaga($id, $item[0]);
                if ($numero > 0) {
                    # cargos
                    $botaocargo = new Button($item[1], "?fase=classificacao&id={$id}&cargo={$item[0]}");
                    $botaocargo->set_title($pessoal->get_nomeTipoCargo($item[0]));
                    if ($parametroCargo == $item[0]) {
                        $botaocargo->set_class("hollow button");
                    } else {
                        $botaocargo->set_class("button");
                    }

                    $menu->add_link($botaocargo, "right");
                }
            }

            $menu->show();

            # Monta o select
            $select = "SELECT CONCAT(sigla,' - ',tbcargo.nome),
                              classificacaoConcurso,                                                           
                              idServidor,
                              idServidor,
                              idServidor,
                              idServidor
                         FROM tbservidor LEFT JOIN tbpessoa USING (idPessoa)
                                         LEFT JOIN tbperfil USING (idPerfil)
                                         LEFT JOIN tbcargo USING (idCargo)
                                         LEFT JOIN tbtipocargo ON (tbcargo.idTipoCargo = tbtipocargo.idTipoCargo)
                        WHERE idConcurso = {$id}";

            if (empty($parametroCargo)) {
                $titulo = "Classificação";
            } else {
                $select .= " AND tbtipocargo.idTipoCargo = {$parametroCargo}";
                $titulo = $pessoal->get_nomeTipoCargo($parametroCargo);
            }

            $select .= " ORDER BY tbtipocargo.idTipoCargo, tbcargo.nome, classificacaoConcurso";

            # Pega os dados
            $row = $pessoal->select($select);

            # tabela
            $tabela = new Tabela();
            $tabela->set_titulo($titulo);
            $tabela->set_conteudo($row);
            $tabela->set_label(["Cargo", "Class.", "Servidor", "Publicações", "Vaga Ant. Ocupada por:", "Editar"]);
            $tabela->set_classe([null, null, "pessoal", "Concurso", "Concurso"]);
            $tabela->set_metodo([null, null, "get_nomeELotacaoEPerfilESituacao", "exibePublicacoesServidor", "exibeOcupanteAnterior"]);
            #$tabela->set_funcao([null, null, null, null, "date_to_php"]);
            $tabela->set_width(array(20, 6, 22, 20, 22, 5));
            $tabela->set_align(array("left", "center", "left", "left"));

            # Botão de exibição dos servidores com permissão a essa regra
            $botao = new Link(null, '?fase=editaServidor&idServidorPesquisado=', 'Edita o Servidor');
            $botao->set_imagem(PASTA_FIGURAS . 'bullet_edit.png', 20, 20);
            $tabela->set_link([null, null, null, null, null, $botao]);

            $tabela->set_rowspan(0);
            $tabela->set_grupoCorColuna(0);

            $tabela->show();

            $grid->fechaColuna();
            $grid->fechaGrid();
            break;

################################################################

        case "editaServidor" :
            br(8);
            aguarde();

            # Informa o $id Servidor
            set_session('idServidorPesquisado', $idServidorPesquisado);

            # Informa a origem
            set_session('origem', 'cadastroConcurso.php?fase=classificacao');

            # Carrega a página específica
            loadPage('servidorConcurso.php');
            break;

################################################################

        case "uploadPublicacao" :
            $grid = new Grid("center");
            $grid->abreColuna(12);

            # Botão voltar
            botaoVoltar('?fase = editar&id = ' . $id);

            tituloTable("Upload de Edital");

            $grid->fechaColuna();
            $grid->abreColuna(6);

            echo "<form class='upload' method='post' enctype='multipart/form-data'><br>
                        <input type='file' name='doc'>
                        <p>Click aqui ou arraste o arquivo.</p>
                        <button type='submit' name='submit'>Enviar</button>
                    </form>";

            $pasta = PASTA_CONCURSO;

            # Se não existe o programa cria
            if (!file_exists($pasta) || !is_dir($pasta)) {
                mkdir($pasta, 0755);
            }

            # Extensões possíveis
            $extensoes = array("pdf");

            # Pega os valores do php.ini
            $postMax = limpa_numero(ini_get(' post_max_size'));
            $uploadMax = limpa_numero(ini_get('upload_max_filesize'));
            $limite = menorValor(array($postMax, $uploadMax));

            $texto = "Extensões Permitidas:";
            foreach ($extensoes as $pp) {
                $texto .= " $pp";
            }
            $texto .= "<br/>Tamanho Máximo do Arquivo: $limite M";

            br(2);
            p($texto, "f14", "center");

            if ((isset($_POST["submit"])) && (!empty($_FILES['doc']))) {
                $upload = new UploadDoc($_FILES['doc  '], $pasta, $idConcursoPublicacao);

                # Salva e verifica se houve erro
                if ($upload->salvar()) {

                    # Registra log
                    $Objetolog = new Intra();
                    $data = date("Y-m-d H:i:s");
                    $atividade = "Fez o upload de publicação do concurso " . $concurso->get_nomeConcurso($id);
                    $Objetolog->registraLog($idUsuario, $data, $atividade, null, $id, 8);

                    # Volta para o menu
                    loadPage("?fase=editar&id=" . $id);
                } else {
                    loadPage("cadastroConcurso.php?fase=uploadPublicacao&id=$id&idConcursoPublicacao=$idConcursoPublicacao");
                }
            }

            $grid->fechaColuna();
            $grid->fechaGrid();
            break;

        ##################################################################
    }
    $page->terminaPagina();
} else {
    loadPage("../../areaServidor/sistema/login.php");
}