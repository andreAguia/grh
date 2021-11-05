<?php

/**
 * Área de Férias
 * 
 * Por data de fruição
 *  
 * By Alat
 */
# Reservado para o servidor logado
$idUsuario = null;

# Configuração
include ("_config.php");

# Verifica a fase do programa
$fase = get('fase', 'listar');
$idConcurso = get('idConcurso');

# Limpa as sessões
if ($fase <> "aguardaPlanilha" AND $fase <> "planilha" AND $fase <> "editar") {
    set_session('idConcurso');
    set_session('parametroCargo');
    #set_session('origem', basename(__FILE__));
    set_session('parametroLotacao');
    set_session('parametroPerfil');
    set_session('parametroSituacao');
    set_session('parametroConcurso');
}

# Pega os parâmetros
$parametroCargo = post('parametroCargo', get_session('parametroCargo', '*'));
$parametroLotacao = post('parametroLotacao', get_session('parametroLotacao', '*'));
$parametroPerfil = post('parametroPerfil', get_session('parametroPerfil', '*'));
$parametroSituacao = post('parametroSituacao', get_session('parametroSituacao', '*'));
$parametroConcurso = post('parametroConcurso', get_session('parametroConcurso', '*'));

# Joga os parâmetros para as sessions
set_session('parametroCargo', $parametroCargo);
set_session('parametroLotacao', $parametroLotacao);
set_session('parametroPerfil', $parametroPerfil);
set_session('parametroSituacao', $parametroSituacao);
set_session('parametroConcurso', $parametroConcurso);

# Coloca o tipo do concurso na session
set_session('concursoTipo', 1);

# Permissão de Acesso
$acesso = Verifica::acesso($idUsuario, 2);

if ($acesso) {
    # Conecta ao Banco de Dados
    $intra = new Intra();
    $pessoal = new Pessoal();
    $concurso = new Concurso();

    # Verifica se veio menu grh e registra o acesso no log
    $grh = get('grh', false);
    if ($grh) {
        # Grava no log a atividade
        $atividade = "Visualizou a área de concurso de adm e Tec";
        $data = date("Y-m-d H:i:s");
        $intra->registraLog($idUsuario, $data, $atividade, null, null, 7);
    }

    # pega o id (se tiver)
    $id = soNumeros(get('id'));
    $idServidor = get('idServidor');

    # Começa uma nova página
    $page = new Page();
    $page->iniciaPagina();

    # Cabeçalho da Página
    if ($fase <> "relatorioInativos" AND $fase <> "relatorioAtivos" AND $fase <> "relatorio") {
        AreaServidor::cabecalho();
    }

    $grid = new Grid();
    $grid->abreColuna(12);

    ################################################################

    switch ($fase) {
        case "listar" :

            # Cria um menu
            $menu1 = new MenuBar();

            # Voltar
            $botaoVoltar = new Link("Voltar", "grh.php");
            $botaoVoltar->set_class('button');
            $botaoVoltar->set_title('Voltar a página anterior');
            $botaoVoltar->set_accessKey('V');
            $menu1->add_link($botaoVoltar, "left");

            # Vagas
            $botaoVoltar = new Link("Vagas", "areaVagasAdm.php");
            $botaoVoltar->set_class('button');
            $botaoVoltar->set_title('Exibe as vagas dos concursos');
            $menu1->add_link($botaoVoltar, "right");

            # Planilha
            $botaoPlanilha = new Link("Planilha", "?fase=aguardaPlanilha");
            $botaoPlanilha->set_class('button');
            $botaoPlanilha->set_title('Exibe uma listagem para se copiar para uma planilha');
            $menu1->add_link($botaoPlanilha, "right");

            # Novo Concurso
            $botaoVoltar = new Link("Novo Concurso", "cadastroConcurso.php?fase=editar");
            $botaoVoltar->set_class('button');
            $menu1->add_link($botaoVoltar, "right");

            $menu1->show();

            # Monta a tabala
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
                      tbplano.numDecreto,
                      idConcurso,
                      idConcurso,
                      idConcurso,
                      idConcurso,
                      idConcurso
                 FROM tbconcurso LEFT JOIN tbplano USING (idPlano)
                WHERE true
                  AND tipo = 1 
             ORDER BY anobase desc, dtPublicacaoEdital desc';

            $resumo = $pessoal->select($select);

            # Monta a tabela
            $tabela = new Tabela();
            $tabela->set_conteudo($resumo);
            $tabela->set_titulo("Concursos para Servidores Administrativos & Técnicos");
            $tabela->set_label(["id", "Ano Base", "Publicação <br/>do Edital", "Regime", "Tipo", "Executor", "Plano de Cargos", "Servidores Ativos", "Ver", "Servidores Inativos", "Ver", "Total", "Acessar"]);
            $tabela->set_colspanLabel([null, null, null, null, null, null, null, 2, null, 2]);
            $tabela->set_align(["center"]);
            $tabela->set_width([5, 8, 10, 10, 10, 10, 17, 5, 5, 5, 5, 5]);
            $tabela->set_funcao([null, null, 'date_to_php']);
            $tabela->set_classe([null, null, null, null, null, null, null, "Concurso", null, "Concurso", null, "Concurso"]);
            $tabela->set_metodo([null, null, null, null, null, null, null, "get_numServidoresAtivosConcurso", null, "get_numServidoresInativosConcurso", null, "get_numServidoresConcurso"]);
            $tabela->set_excluirCondicional('cadastroConcurso.php?fase=excluir', 0, 11, "==");
            $tabela->set_rowspan(1);
            $tabela->set_grupoCorColuna(1);

            # Ver servidores ativos
            $servAtivos = new Link(null, "?fase=aguardeAtivos&id=");
            $servAtivos->set_imagem(PASTA_FIGURAS_GERAIS . 'olho.png', 20, 20);
            $servAtivos->set_title("Exibe os servidores ativos");

            # Ver servidores inativos
            $servInativos = new Link(null, '?fase=aguardeInativos&id=');
            $servInativos->set_imagem(PASTA_FIGURAS_GERAIS . 'olho.png', 20, 20);
            $servInativos->set_title("Exibe os servidores inativos");

            # Botão Editar
            $botao = new Link(null, '?fase=acessaConcurso&idConcurso=', 'Acessa a página do concurso');
            $botao->set_imagem(PASTA_FIGURAS . 'bullet_edit.png', 20, 20);

            # Coloca o objeto link na tabela			
            $tabela->set_link([null, null, null, null, null, null, null, null, $servAtivos, null, $servInativos, null, $botao]);

            $tabela->show();
            break;

        ################################################################
        # Chama o menu do Servidor que se quer editar
        case "acessaConcurso" :
            set_session('idConcurso', $idConcurso);
            loadPage('cadastroConcursoAdm.php');
            break;

        ################################################################

        case "aguardaPlanilha" :

            br(8);
            aguarde();
            br();

            # Limita a tela
            $grid1 = new Grid("center");
            $grid1->abreColuna(5);
            p("Aguarde...", "center");
            $grid1->fechaColuna();
            $grid1->fechaGrid();

            loadPage('?fase=planilha');
            break;

        ################################################################

        case "planilha" :

            # Cria um menu
            $menu1 = new MenuBar();

            # Voltar
            $botaoVoltar = new Link("Voltar", "?");
            $botaoVoltar->set_class('button');
            $botaoVoltar->set_title('Voltar a página anterior');
            $botaoVoltar->set_accessKey('V');
            $menu1->add_link($botaoVoltar, "left");

            $menu1->show();

            # Formulário
            $form = new Form('?fase=aguardaPlanilha');

            # Situação
            $result = $pessoal->select('SELECT idsituacao, situacao
                                              FROM tbsituacao                                
                                          ORDER BY 1');
            array_unshift($result, array('*', '-- Todos --'));

            $controle = new Input('parametroSituacao', 'combo', 'Situação:', 1);
            $controle->set_size(30);
            $controle->set_title('Filtra por Situação');
            $controle->set_array($result);
            $controle->set_valor($parametroSituacao);
            $controle->set_onChange('formPadrao.submit();');
            $controle->set_linha(1);
            $controle->set_col(4);
            $form->add_item($controle);

            # Perfil
            $result = $pessoal->select('SELECT idperfil, nome
                                              FROM tbperfil                                
                                          ORDER BY 1');
            array_unshift($result, array('*', '-- Todos --'));

            $controle = new Input('parametroPerfil', 'combo', 'Perfil:', 1);
            $controle->set_size(30);
            $controle->set_title('Filtra por Perfil');
            $controle->set_array($result);
            $controle->set_valor($parametroPerfil);
            $controle->set_onChange('formPadrao.submit();');
            $controle->set_linha(1);
            $controle->set_col(4);
            $form->add_item($controle);

            # concurso
            $result = $pessoal->select("SELECT idconcurso,
                                               concat(anoBase,' - Edital: ',DATE_FORMAT(dtPublicacaoEdital,'%d/%m/%Y')) as concurso
                                          FROM tbconcurso
                                         WHERE tipo = 1     
                                      ORDER BY dtPublicacaoEdital desc");
            array_unshift($result, array('*', '-- Todos --'));

            $controle = new Input('parametroConcurso', 'combo', 'Concurso:', 1);
            $controle->set_size(30);
            $controle->set_title('Filtra por Concurso');
            $controle->set_array($result);
            $controle->set_valor($parametroConcurso);
            $controle->set_onChange('formPadrao.submit();');
            $controle->set_linha(1);
            $controle->set_col(4);
            $form->add_item($controle);

            # Cargos
            $result1 = $pessoal->select('SELECT tbcargo.idCargo, 
                                                concat(tbtipocargo.cargo," - ",tbarea.area," - ",tbcargo.nome) as cargo
                                           FROM tbcargo LEFT JOIN tbtipocargo USING (idTipoCargo)
                                                        LEFT JOIN tbarea USING (idArea)
                                          WHERE tbtipocargo.tipo = "Adm/Tec"
                                      ORDER BY 2');

            # cargos por nivel
            $result2 = $pessoal->select('SELECT cargo,cargo FROM tbtipocargo WHERE cargo <> "Professor Associado" AND cargo <> "Professor Titular" ORDER BY 2');

            # junta os dois
            $result = array_merge($result2, $result1);

            # acrescenta todos
            array_unshift($result, array('*', '-- Todos --'));

            $controle = new Input('parametroCargo', 'combo', 'Cargo:', 1);
            $controle->set_size(30);
            $controle->set_title('Filtra por Cargo');
            $controle->set_array($result);
            $controle->set_valor($parametroCargo);
            $controle->set_onChange('formPadrao.submit();');
            $controle->set_linha(1);
            $controle->set_col(6);
            $form->add_item($controle);

            # Lotação
            $result = $pessoal->select('(SELECT idlotacao, concat(IFnull(tblotacao.DIR,"")," - ",IFnull(tblotacao.GER,"")," - ",IFnull(tblotacao.nome,"")) lotacao
                                              FROM tblotacao
                                             WHERE ativo) UNION (SELECT distinct DIR, DIR
                                              FROM tblotacao
                                             WHERE ativo)
                                          ORDER BY 2');
            array_unshift($result, array('*', '-- Todos --'));

            $controle = new Input('parametroLotacao', 'combo', 'Lotação:', 1);
            $controle->set_size(30);
            $controle->set_title('Filtra por Lotação');
            $controle->set_array($result);
            $controle->set_valor($parametroLotacao);
            $controle->set_onChange('formPadrao.submit();');
            $controle->set_linha(1);
            $controle->set_col(6);
            $form->add_item($controle);

            $form->show();

            # Monta a tabala
            $select = 'SELECT tbservidor.idServidor,
                              tbservidor.idServidor,
                              tbservidor.idServidor,
                              dtAdmissao,
                              dtDemissao,
                              tbservidor.idServidor,
                              idConcurso,
                              tbservidor.idServidor,
                              dtPublicAtoInvestidura,
                              dtPublicTermoPosse,
                              tbservidor.idServidor
                         FROM tbservidor LEFT JOIN tbpessoa USING (idPessoa)
                                         LEFT JOIN tbcargo USING (idCargo)
                                              JOIN tbtipocargo USING (idTipoCargo) 
                                         LEFT JOIN tbconcurso USING (idConcurso)
                                         LEFT JOIN tbhistlot ON (tbservidor.idServidor = tbhistlot.idServidor)
                                         LEFT JOIN tblotacao ON (tbhistlot.lotacao=tblotacao.idLotacao)
                        WHERE (idPerfil = 1 OR idPerfil = 4)
                          AND tbhistlot.data = (select max(data) from tbhistlot where tbhistlot.idServidor = tbservidor.idServidor)
                          AND tbtipocargo.tipo = "Adm/Tec"';

            # situação
            if ($parametroSituacao <> "*") {
                $select .= ' AND situacao = ' . $parametroSituacao;
            }

            # cargo
            if ($parametroCargo <> "*") {
                if (is_numeric($parametroCargo)) {
                    $select .= ' AND (tbcargo.idcargo = "' . $parametroCargo . '")';
                } else { # senão é nivel do cargo
                    $select .= ' AND (tbtipocargo.cargo = "' . $parametroCargo . '")';
                }
            }

            # perfil
            if ($parametroPerfil <> "*") {
                $select .= ' AND (idPerfil = "' . $parametroPerfil . '")';
            }

            # concurso
            if ($parametroConcurso <> "*") {
                $select .= ' AND (idConcurso = "' . $parametroConcurso . '")';
            }

            # lotacao
            if ($parametroLotacao <> "*") {
                # Verifica se o que veio é numérico
                if (is_numeric($parametroLotacao)) {
                    $select .= ' AND (tblotacao.idlotacao = "' . $parametroLotacao . '")';
                } else { # senão é uma diretoria genérica
                    $select .= ' AND (tblotacao.DIR = "' . $parametroLotacao . '")';
                }
            }


            $select .= ' ORDER BY tbpessoa.nome';

            $resumo = $pessoal->select($select);

            # Monta a tabela
            $tabela = new Tabela();
            $tabela->set_conteudo($resumo);
            $tabela->set_titulo("Relação Geral de Concursados Administrativos e Técnicos");
            $tabela->set_label(["id / Matrícula", "Servidor", "Perfil", "Admissão", "Saída", "Situação", "Concurso", "Vaga Anteriormente Ocupada por:", "Ato de Investidura", "Termo de Posse", "Acessar"]);
            $tabela->set_align(["center", "left"]);
            $tabela->set_funcao([null, null, null, 'date_to_php', 'date_to_php', null, null, null, 'date_to_php', 'date_to_php']);
            $tabela->set_classe(["Pessoal", "Pessoal", "Pessoal", null, null, "Pessoal", "Concurso", "Concurso"]);
            $tabela->set_metodo(["get_idFuncionalEMatricula", "get_nomeECargoELotacao", "get_perfil", null, null, "get_situacao", "get_nomeConcurso", "exibeOcupanteAnterior"]);

            $botao = new Link(null, '?fase=editar&idServidor=', 'Acessa o servidor');
            $botao->set_imagem(PASTA_FIGURAS . 'bullet_edit.png', 20, 20);
            $tabela->set_link([null, null, null, null, null, null, null, null, null, null, $botao]);
            $tabela->show();
            break;

        ################################################################
        # Chama o menu do Servidor que se quer editar
        case "editar" :
            br(8);
            aguarde();

            # Informa o $id Servidor
            set_session('idServidorPesquisado', $idServidor);

            # Informa a origem
            set_session('origem', 'areaConcursoAdm.php?fase=aguardaPlanilha');

            # Carrega a página específica
            loadPage('servidorConcurso.php');
            break;

        ################################################################

        case "aguardeAtivos" :
            br(10);
            aguarde("Montando a Listagem");
            br();
            loadPage('?fase=exibeServidoresAtivos&id=' . $id);
            break;

        ################################################################

        case "aguardeInativos" :
            br(10);
            aguarde("Montando a Listagem");
            br();
            loadPage('?fase=exibeServidoresInativos&id=' . $id);
            break;

        ################################################################

        case "exibeServidoresAtivos" :
            # Limita o tamanho da tela
            $grid = new Grid();
            $grid->abreColuna(12);

            # Informa a origem
            set_session('origem', 'areaConcursoAdm.php?fase=exibeServidoresAtivos&id=' . $id);

            # Cria um menu
            $menu = new MenuBar();

            # Botão voltar
            $btnVoltar = new Button("Voltar", "?");
            $btnVoltar->set_title('Volta para a página anterior');
            $btnVoltar->set_accessKey('V');
            $menu->add_link($btnVoltar, "left");

            # Relatório
            $imagem2 = new Imagem(PASTA_FIGURAS . 'print.png', null, 15, 15);
            $botaoRel = new Button();
            $botaoRel->set_title("Relatório dos Servidores");
            $botaoRel->set_target("_blank");
            $botaoRel->set_url("?fase=relatorioAtivos&id=$id");
            $botaoRel->set_imagem($imagem2);
            $menu->add_link($botaoRel, "right");

            $menu->show();

            # Lista de Servidores Ativos
            $lista = new ListaServidores('Servidores Ativos - Concurso: ' . $concurso->get_nomeConcurso($id));
            $lista->set_situacao(1);
            $lista->set_concurso($id);
            $lista->showTabela();

            $grid->fechaColuna();
            $grid->fechaGrid();
            break;

        ################################################################

        case "exibeServidoresInativos" :
            # Limita o tamanho da tela
            $grid = new Grid();
            $grid->abreColuna(12);

            # Informa a origem
            set_session('origem', 'areaConcursoAdm.php?fase=exibeServidoresInativos&id=' . $id);

            # Cria um menu
            $menu = new MenuBar();

            # Botão voltar
            $btnVoltar = new Button("Voltar", "?");
            $btnVoltar->set_title('Volta para a página anterior');
            $btnVoltar->set_accessKey('V');
            $menu->add_link($btnVoltar, "left");

            # Relatório
            $imagem2 = new Imagem(PASTA_FIGURAS . 'print.png', null, 15, 15);
            $botaoRel = new Button();
            $botaoRel->set_title("Relatório dos Servidores");
            $botaoRel->set_target("_blank");
            $botaoRel->set_url("?fase=relatorioInativos&id=$id");
            $botaoRel->set_imagem($imagem2);
            $menu->add_link($botaoRel, "right");

            $menu->show();

            # Lista de Servidores Inativos
            $lista = new ListaServidores('Servidores Inativos - Concurso: ' . $concurso->get_nomeConcurso($id));
            $lista->set_situacao(1);
            $lista->set_situacaoSinal("<>");
            $lista->set_concurso($id);
            $lista->showTabela();

            $grid->fechaColuna();
            $grid->fechaGrid();
            break;

        ################################################################

        case "relatorioAtivos" :
            # Lista de Servidores Ativos
            $lista = new ListaServidores('Servidores Ativos - Concurso: ' . $concurso->get_nomeConcurso($id));
            $lista->set_situacao(1);
            $lista->set_concurso($id);
            $lista->showRelatorio();
            break;

        ################################################################

        case "relatorioInativos" :
            # Lista de Servidores Inativos
            $lista = new ListaServidores('Servidores Inativos - Concurso: ' . $concurso->get_nomeConcurso($id));
            $lista->set_situacao(1);
            $lista->set_situacaoSinal("<>");
            $lista->set_concurso($id);
            $lista->showRelatorio();
            break;

        ################################################################
    }

    $grid->fechaColuna();
    $grid->fechaGrid();

    $page->terminaPagina();
} else {
    loadPage("../../areaServidor/sistema/login.php");
}