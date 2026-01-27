<?php

/**
 * Área de Licença Prêmio
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
    $formacao = new Formacao();

    # Verifica a fase do programa
    $fase = get('fase');
    $aba = get('aba', 1);

    # Verifica se veio menu grh e registra o acesso no log
    $grh = get('grh', false);
    if ($grh) {
        # Grava no log a atividade
        $atividade = "Visualizou a área de formação";
        $data = date("Y-m-d H:i:s");
        $intra->registraLog($idUsuario, $data, $atividade, null, null, 7);
    }

    # pega o id (se tiver)
    $id = soNumeros(get('id'));

    # Pega os parâmetros    
    $parametroNivel = post('parametroNivel', get_session('parametroNivel', 'Todos'));
    $parametroPerfil = post('parametroPerfil', get_session('parametroPerfil', 'Todos'));
    $parametroLotacao = post('parametroLotacao', get_session('parametroLotacao', 66));
    $parametroEscolaridade = post('parametroEscolaridade', get_session('parametroEscolaridade', 'Todos'));
    $parametroCurso = post('parametroCurso', get_session('parametroCurso', 'Todos'));
    $parametroInstituicao = post('parametroInstituicao', get_session('parametroInstituicao', 'Todos'));
    $parametroAno = post('parametroAno', get_session('parametroAno', date("Y")));
    $parametroMarcador = post('parametroMarcador', get_session('parametroMarcador', 4));
    $parametroSituacao = post('parametroSituacao', get_session('parametroSituacao', 1));
    $parametroEscopo = post('parametroEscopo', get_session('parametroEscopo', 1));

    # Joga os parâmetros par as sessions   
    set_session('parametroNivel', $parametroNivel);
    set_session('parametroEscolaridade', $parametroEscolaridade);
    set_session('parametroCurso', $parametroCurso);
    set_session('parametroInstituicao', $parametroInstituicao);
    set_session('parametroLotacao', $parametroLotacao);
    set_session('parametroPerfil', $parametroPerfil);
    set_session('parametroAno', $parametroAno);
    set_session('parametroMarcador', $parametroMarcador);
    set_session('parametroSituacao', $parametroSituacao);
    set_session('parametroEscopo', $parametroEscopo);

    # Começa uma nova página
    $page = new Page();
    $page->iniciaPagina();

    # Cabeçalho da Página
    if ($fase <> "relatorio") {
        AreaServidor::cabecalho();
    }

################################################################

    switch ($fase) {
        case "" :
            br(4);
            aguarde();
            br();

            # Limita a tela
            $grid1 = new Grid("center");
            $grid1->abreColuna(5);
            p("Aguarde...", "center");
            $grid1->fechaColuna();
            $grid1->fechaGrid();

            loadPage("?fase=exibeLista&aba={$aba}");
            break;

################################################################

        case "exibeLista" :
            $grid = new Grid();
            $grid->abreColuna(12);
            br();

            # Cria um menu
            $menu1 = new MenuBar();

            # Voltar
            $botaoVoltar = new Link("Voltar", "grh.php");
            $botaoVoltar->set_class('button');
            $botaoVoltar->set_title('Voltar a página anterior');
            $botaoVoltar->set_accessKey('V');
            $menu1->add_link($botaoVoltar, "left");

            # Relatórios
            $imagem = new Imagem(PASTA_FIGURAS . 'print.png', null, 15, 15);
            $botaoRel = new Button();
            $botaoRel->set_title("Relatório dessa pesquisa");
            $botaoRel->set_url("?fase=relatorio");
            $botaoRel->set_target("_blank");
            $botaoRel->set_imagem($imagem);
            #$menu1->add_link($botaoRel, "right");

            $menu1->show();

            $grid->fechaColuna();

            ##############

            $grid->abreColuna(8);

            # Formulário de Pesquisa
            $form = new Form('?');

            /*
             *  Lotação
             */
            $result = $pessoal->select('(SELECT idlotacao, concat(IFnull(tblotacao.DIR,"")," - ",IFnull(tblotacao.GER,"")," - ",IFnull(tblotacao.nome,"")) lotacao
                                              FROM tblotacao
                                             WHERE ativo) UNION (SELECT distinct DIR, DIR
                                              FROM tblotacao
                                             WHERE ativo)
                                          ORDER BY 2');
            array_unshift($result, array("Todos", 'Todas'));

            $controle = new Input('parametroLotacao', 'combo', 'Lotação do Servidor:', 1);
            $controle->set_size(30);
            $controle->set_title('Filtra por Lotação');
            $controle->set_array($result);
            $controle->set_valor($parametroLotacao);
            $controle->set_onChange('formPadrao.submit();');
            $controle->set_linha(1);
            $controle->set_col(12);
            $form->add_item($controle);

            $form->show();

            $grid->fechaColuna();

            ##############

            $grid->abreColuna(4);

            $formacao->exibeQuadroPetec();

            $grid->fechaColuna();

            ##############

            $grid->abreColuna(12);

            # Menu de Abas
            $petec = $formacao->get_arrayMarcadores("Petec");

            $tab = new Tab([
                "Geral",
                "Petec - Portaria 418/25",
                "Petec - Portaria 473/25",
                "Petec - Portaria 481/25"
                    ], $aba);

            #######################################################

            /*
             * Geral
             */

            $tab->abreConteudo();
            
            # Dados
            $abaRetorno = 1;

            # Monta o select
            $select = "SELECT tbservidor.idServidor,
                              tbservidor.idServidor,
                              tbservidor.idServidor,
                              tbservidor.idServidor,
                              tbservidor.idServidor,
                              tbservidor.idServidor
                         FROM tbservidor LEFT JOIN tbpessoa USING (idPessoa)
                                              JOIN tbhistlot USING (idServidor)
                                              JOIN tblotacao ON (tbhistlot.lotacao=tblotacao.idLotacao)
                        WHERE tbhistlot.data = (select max(data) from tbhistlot where tbhistlot.idServidor = tbservidor.idServidor)
                        AND situacao = 1";

            # Verifica se tem filtro por lotação
            if ($parametroLotacao <> "Todos") {  // senão verifica o da classe
                if (is_numeric($parametroLotacao)) {
                    $select .= " AND (tblotacao.idlotacao = {$parametroLotacao})";
                } else { # senão é uma diretoria genérica
                    $select .= " AND (tblotacao.DIR = '{$parametroLotacao}')";
                }
            }

            $select .= " ORDER BY tbpessoa.nome";

            $result2 = $pessoal->select($select);

            # Define as colunas
            $label[] = "Servidores";
            $align[] = "left";
            $classe[] = "Pessoal";
            $metodo[] = "get_nomeECargoELotacaoEPerfilESituacao";

            $petec = $formacao->get_arrayMarcadores("Petec");

            foreach ($petec as $item) {
                $label[] = $item[1];
                $align[] = "center";
                $classe[] = "Formacao";
                $metodo[] = "somatorioHoras{$item[0]}"; // Gambiarra para fazer funcionar. Depois eu vejo um modo melhor de fazer isso...
            }

            $label[] = "Editar";

            $tabela = new Tabela();
            $tabela->set_titulo("Análise Geral");
            $tabela->set_conteudo($result2);

            $tabela->set_label($label);
            $tabela->set_align($align);

            $tabela->set_classe($classe);
            $tabela->set_metodo($metodo);
            $tabela->set_bordaInterna(true);

            # Botão Editar
            $botao = new Link(null, "?fase=editaServidor&aba={$abaRetorno}&id=", 'Acessa o servidor');
            $botao->set_imagem(PASTA_FIGURAS . 'bullet_edit.png', 20, 20);

            # Coloca o objeto link na tabela			
            $tabela->set_link([null, null, null, null, $botao]);
            $tabela->show();

            $tab->fechaConteudo();

            #######################################################
            /*
             * Petec - Portaria 418/25
             */

            $tab->abreConteudo();
            
            # Dados
            $abaRetorno = 2;
            
            # Monta o select
            $select = "SELECT tbservidor.idServidor,
                              tbescolaridade.escolaridade,
                              idFormacao,
                              idFormacao,
                              idFormacao
                         FROM tbformacao LEFT JOIN tbpessoa USING (idPessoa)
                                              JOIN tbservidor USING (idPessoa)
                                              JOIN tbescolaridade USING (idEscolaridade)
                                              JOIN tbhistlot USING (idServidor)
                                              JOIN tblotacao ON (tbhistlot.lotacao=tblotacao.idLotacao)
                        WHERE tbhistlot.data = (select max(data) from tbhistlot where tbhistlot.idServidor = tbservidor.idServidor)
                        AND situacao = 1";

            if ($parametroMarcador <> "Todos") {

                $select .= " AND ("
                        . "tbformacao.marcador1 = 4 OR "
                        . "tbformacao.marcador2 = 4 OR "
                        . "tbformacao.marcador3 = 4 OR "
                        . "tbformacao.marcador4 = 4)";
            }

            # Verifica se tem filtro por lotação
            if ($parametroLotacao <> "Todos") {  // senão verifica o da classe
                if (is_numeric($parametroLotacao)) {
                    $select .= " AND (tblotacao.idlotacao = {$parametroLotacao})";
                } else { # senão é uma diretoria genérica
                    $select .= " AND (tblotacao.DIR = '{$parametroLotacao}')";
                }
            }

            $select .= " ORDER BY tbpessoa.nome, tbformacao.anoTerm";

            $result = $pessoal->select($select);

            $tabela = new Tabela();
            $tabela->set_titulo('Servidores COM PETEC');
            $tabela->set_label(["Servidor", "Nível do Curso", "Marcadores", "Curso", "Certificado"]);
            $tabela->set_conteudo($result);
            $tabela->set_align(["left", "center", "center", "left"]);
            $tabela->set_classe(["pessoal", null, "Formacao", "Formacao", "Formacao"]);
            $tabela->set_metodo(["get_nomeECargoELotacaoEPerfilESituacao", null, "exibeMarcador", "exibeCurso", "exibeCertificado"]);

            $tabela->set_rowspan(0);
            $tabela->set_grupoCorColuna(0);

            $tabela->set_idCampo('idServidor');
            $tabela->set_editar("?fase=editaServidor&aba={$abaRetorno}");
            $tabela->show();

            ## Sem Petec

            $novoArray = array();

            $select2 = "SELECT tbservidor.idServidor
                         FROM tbservidor LEFT JOIN tbpessoa USING (idPessoa)
                                              JOIN tbhistlot USING (idServidor)
                                              JOIN tblotacao ON (tbhistlot.lotacao=tblotacao.idLotacao)
                        WHERE tbhistlot.data = (select max(data) from tbhistlot where tbhistlot.idServidor = tbservidor.idServidor)
                        AND situacao = 1";

            # Verifica se tem filtro por lotação
            if ($parametroLotacao <> "Todos") {  // senão verifica o da classe
                if (is_numeric($parametroLotacao)) {
                    $select2 .= " AND (tblotacao.idlotacao = {$parametroLotacao})";
                } else { # senão é uma diretoria genérica
                    $select2 .= " AND (tblotacao.DIR = '{$parametroLotacao}')";
                }
            }

            $select2 .= " ORDER BY tbpessoa.nome";
            $result2 = $pessoal->select($select2);

            # Percorre o array
            foreach ($result2 as $item) {
                if (!$formacao->temPetec($item["idServidor"], 4)) {
                    $novoArray[] = [$item["idServidor"], $item["idServidor"], $item["idServidor"], $item["idServidor"], $item["idServidor"], $item["idServidor"], $item["idServidor"]];
                }
            }

            $tabela = new Tabela();
            $tabela->set_titulo('Servidores SEM PETEC');
            #$tabela->set_subtitulo('Filtro: '.$relatorioParametro);
            $tabela->set_label(["IdFuncional<br/>Matrícula", "Servidor", "Cargo", "Lotação", "Perfil", "Editar"]);
            $tabela->set_conteudo($novoArray);
            $tabela->set_align(["center", "left", "left", "center", "left"]);
            $tabela->set_classe(['pessoal', "pessoal", "pessoal", "pessoal", "pessoal"]);
            $tabela->set_metodo(["get_idFuncionalEMatricula", "get_nome", "get_cargo", "get_lotacao", "get_perfil"]);

            if ($parametroSituacao == 1) {
                $tabela->set_rowspan(0);
                $tabela->set_grupoCorColuna(0);
            }

            # Botão Editar
            $botao = new Link(null, "?fase=editaServidor&aba={$abaRetorno}&id=", 'Acessa o servidor');
            $botao->set_imagem(PASTA_FIGURAS . 'bullet_edit.png', 20, 20);

            # Coloca o objeto link na tabela			
            $tabela->set_link([null, null, null, null, null, $botao]);
            $tabela->show();

            $tab->fechaConteudo();

            ##############
            /*
             * Petec - Portaria 473/25
             */

            $tab->abreConteudo();
            
            # Dados
            $abaRetorno = 3;
            
            # Monta o select
            $select = "SELECT tbservidor.idServidor,
                              tbescolaridade.escolaridade,
                              idFormacao,
                              idFormacao,
                              idFormacao
                         FROM tbformacao LEFT JOIN tbpessoa USING (idPessoa)
                                              JOIN tbservidor USING (idPessoa)
                                              JOIN tbescolaridade USING (idEscolaridade)
                                              JOIN tbhistlot USING (idServidor)
                                              JOIN tblotacao ON (tbhistlot.lotacao=tblotacao.idLotacao)
                        WHERE tbhistlot.data = (select max(data) from tbhistlot where tbhistlot.idServidor = tbservidor.idServidor)
                        AND situacao = 1";

            if ($parametroMarcador <> "Todos") {

                $select .= " AND ("
                        . "tbformacao.marcador1 = 5 OR "
                        . "tbformacao.marcador2 = 5 OR "
                        . "tbformacao.marcador3 = 5 OR "
                        . "tbformacao.marcador4 = 5)";
            }

            # Verifica se tem filtro por lotação
            if ($parametroLotacao <> "Todos") {  // senão verifica o da classe
                if (is_numeric($parametroLotacao)) {
                    $select .= " AND (tblotacao.idlotacao = {$parametroLotacao})";
                } else { # senão é uma diretoria genérica
                    $select .= " AND (tblotacao.DIR = '{$parametroLotacao}')";
                }
            }

            $select .= " ORDER BY tbpessoa.nome, tbformacao.anoTerm";

            $result = $pessoal->select($select);

            $tabela = new Tabela();
            $tabela->set_titulo('Servidores COM PETEC');
            $tabela->set_label(["Servidor", "Nível do Curso", "Marcadores", "Curso", "Certificado"]);
            $tabela->set_conteudo($result);
            $tabela->set_align(["left", "center", "center", "left"]);
            $tabela->set_classe(["pessoal", null, "Formacao", "Formacao", "Formacao"]);
            $tabela->set_metodo(["get_nomeECargoELotacaoEPerfilESituacao", null, "exibeMarcador", "exibeCurso", "exibeCertificado"]);

            $tabela->set_rowspan(0);
            $tabela->set_grupoCorColuna(0);

            $tabela->set_idCampo('idServidor');
            $tabela->set_editar("?fase=editaServidor&aba={$abaRetorno}");
            $tabela->show();

            ## Sem Petec

            $novoArray = array();

            $select2 = "SELECT tbservidor.idServidor
                         FROM tbservidor LEFT JOIN tbpessoa USING (idPessoa)
                                              JOIN tbhistlot USING (idServidor)
                                              JOIN tblotacao ON (tbhistlot.lotacao=tblotacao.idLotacao)
                        WHERE tbhistlot.data = (select max(data) from tbhistlot where tbhistlot.idServidor = tbservidor.idServidor)
                        AND situacao = 1";

            # Verifica se tem filtro por lotação
            if ($parametroLotacao <> "Todos") {  // senão verifica o da classe
                if (is_numeric($parametroLotacao)) {
                    $select2 .= " AND (tblotacao.idlotacao = {$parametroLotacao})";
                } else { # senão é uma diretoria genérica
                    $select2 .= " AND (tblotacao.DIR = '{$parametroLotacao}')";
                }
            }

            $select2 .= " ORDER BY tbpessoa.nome";
            $result2 = $pessoal->select($select2);

            # Percorre o array
            foreach ($result2 as $item) {
                if (!$formacao->temPetec($item["idServidor"], 5)) {
                    $novoArray[] = [$item["idServidor"], $item["idServidor"], $item["idServidor"], $item["idServidor"], $item["idServidor"], $item["idServidor"], $item["idServidor"]];
                }
            }

            $tabela = new Tabela();
            $tabela->set_titulo('Servidores SEM PETEC');
            #$tabela->set_subtitulo('Filtro: '.$relatorioParametro);
            $tabela->set_label(["IdFuncional<br/>Matrícula", "Servidor", "Cargo", "Lotação", "Perfil", "Editar"]);
            $tabela->set_conteudo($novoArray);
            $tabela->set_align(["center", "left", "left", "center", "left"]);
            $tabela->set_classe(['pessoal', "pessoal", "pessoal", "pessoal", "pessoal"]);
            $tabela->set_metodo(["get_idFuncionalEMatricula", "get_nome", "get_cargo", "get_lotacao", "get_perfil"]);

            if ($parametroSituacao == 1) {
                $tabela->set_rowspan(0);
                $tabela->set_grupoCorColuna(0);
            }

            # Botão Editar
            $botao = new Link(null, "?fase=editaServidor&aba={$abaRetorno}&id=", 'Acessa o servidor');
            $botao->set_imagem(PASTA_FIGURAS . 'bullet_edit.png', 20, 20);

            # Coloca o objeto link na tabela			
            $tabela->set_link([null, null, null, null, null, $botao]);
            $tabela->show();

            $tab->fechaConteudo();

            ##############
            /*
             * Petec - Portaria 481/25
             */

            $tab->abreConteudo();
            
            # Dados
            $abaRetorno = 4;

            ## Com Petec

            # Monta o select
            $select = "SELECT tbservidor.idServidor,
                              tbescolaridade.escolaridade,
                              idFormacao,
                              idFormacao,
                              idFormacao
                         FROM tbformacao LEFT JOIN tbpessoa USING (idPessoa)
                                              JOIN tbservidor USING (idPessoa)
                                              JOIN tbescolaridade USING (idEscolaridade)
                                              JOIN tbhistlot USING (idServidor)
                                              JOIN tblotacao ON (tbhistlot.lotacao=tblotacao.idLotacao)
                        WHERE tbhistlot.data = (select max(data) from tbhistlot where tbhistlot.idServidor = tbservidor.idServidor)
                        AND situacao = 1";

            if ($parametroMarcador <> "Todos") {

                $select .= " AND ("
                        . "tbformacao.marcador1 = 6 OR "
                        . "tbformacao.marcador2 = 6 OR "
                        . "tbformacao.marcador3 = 6 OR "
                        . "tbformacao.marcador4 = 6)";
            }

            # Verifica se tem filtro por lotação
            if ($parametroLotacao <> "Todos") {  // senão verifica o da classe
                if (is_numeric($parametroLotacao)) {
                    $select .= " AND (tblotacao.idlotacao = {$parametroLotacao})";
                } else { # senão é uma diretoria genérica
                    $select .= " AND (tblotacao.DIR = '{$parametroLotacao}')";
                }
            }

            $select .= " ORDER BY tbpessoa.nome, tbformacao.anoTerm";

            $result = $pessoal->select($select);

            $tabela = new Tabela();
            $tabela->set_titulo('Servidores COM PETEC');
            #$tabela->set_subtitulo('Filtro: '.$relatorioParametro);
            $tabela->set_label(["Servidor", "Nível do Curso", "Marcadores", "Curso", "Certificado"]);
            $tabela->set_conteudo($result);
            $tabela->set_align(["left", "center", "center", "left"]);
            $tabela->set_classe(["pessoal", null, "Formacao", "Formacao", "Formacao"]);
            $tabela->set_metodo(["get_nomeECargoELotacaoEPerfilESituacao", null, "exibeMarcador", "exibeCurso", "exibeCertificado"]);

            $tabela->set_rowspan(0);
            $tabela->set_grupoCorColuna(0);

            $tabela->set_idCampo('idServidor');
            $tabela->set_editar("?fase=editaServidor&aba={$abaRetorno}");
            $tabela->show();

            ## Sem Petec

            $novoArray = array();

            $select2 = "SELECT tbservidor.idServidor
                         FROM tbservidor LEFT JOIN tbpessoa USING (idPessoa)
                                              JOIN tbhistlot USING (idServidor)
                                              JOIN tblotacao ON (tbhistlot.lotacao=tblotacao.idLotacao)
                        WHERE tbhistlot.data = (select max(data) from tbhistlot where tbhistlot.idServidor = tbservidor.idServidor)
                        AND situacao = 1";

            # Verifica se tem filtro por lotação
            if ($parametroLotacao <> "Todos") {  // senão verifica o da classe
                if (is_numeric($parametroLotacao)) {
                    $select2 .= " AND (tblotacao.idlotacao = {$parametroLotacao})";
                } else { # senão é uma diretoria genérica
                    $select2 .= " AND (tblotacao.DIR = '{$parametroLotacao}')";
                }
            }

            $select2 .= " ORDER BY tbpessoa.nome";
            $result2 = $pessoal->select($select2);

            # Percorre o array
            foreach ($result2 as $item) {
                if (!$formacao->temPetec($item["idServidor"], 6)) {
                    $novoArray[] = [$item["idServidor"], $item["idServidor"], $item["idServidor"], $item["idServidor"], $item["idServidor"], $item["idServidor"], $item["idServidor"]];
                }
            }

            $tabela = new Tabela();
            $tabela->set_titulo('Servidores SEM PETEC');
            #$tabela->set_subtitulo('Filtro: '.$relatorioParametro);
            $tabela->set_label(["IdFuncional<br/>Matrícula", "Servidor", "Cargo", "Lotação", "Perfil", "Editar"]);
            $tabela->set_conteudo($novoArray);
            $tabela->set_align(["center", "left", "left", "center", "left"]);
            $tabela->set_classe(['pessoal', "pessoal", "pessoal", "pessoal", "pessoal"]);
            $tabela->set_metodo(["get_idFuncionalEMatricula", "get_nome", "get_cargo", "get_lotacao", "get_perfil"]);

            if ($parametroSituacao == 1) {
                $tabela->set_rowspan(0);
                $tabela->set_grupoCorColuna(0);
            }

            # Botão Editar
            $botao = new Link(null, "?fase=editaServidor&aba={$abaRetorno}&id=", 'Acessa o servidor');
            $botao->set_imagem(PASTA_FIGURAS . 'bullet_edit.png', 20, 20);

            # Coloca o objeto link na tabela			
            $tabela->set_link([null, null, null, null, null, $botao]);
            $tabela->show();

            $tab->fechaConteudo();
            
            $grid->fechaColuna();
            $grid->fechaGrid();
            break;

        ################################################################

        case "editaServidor" :
            br(8);
            aguarde();

            # Informa o $id Servidor
            set_session('idServidorPesquisado', $id);

            # Informa a origem
            set_session('origem', "areaPetec.php?aba={$aba}");

            # Carrega a página específica
            loadPage('servidorFormacao.php');
            break;

        ################################################################
        # Relatório
        case "relatorio" :

            $subTitulo = null;

            # Pega os dados
            $select = "SELECT tbservidor.idServidor,
                              tbescolaridade.escolaridade,
                              idFormacao
                         FROM tbformacao LEFT JOIN tbpessoa USING (idPessoa)
                                              JOIN tbservidor USING (idPessoa)
                                         LEFT JOIN tbescolaridade USING (idEscolaridade)
                                              JOIN tbhistlot USING (idServidor)
                                              JOIN tblotacao ON (tbhistlot.lotacao=tblotacao.idLotacao)                                 
                                         LEFT JOIN tbcargo USING (idCargo)
                                         LEFT JOIN tbtipocargo USING (idTipoCargo)
                        WHERE tbhistlot.data = (select max(data) from tbhistlot where tbhistlot.idServidor = tbservidor.idServidor)";

            $select .= " AND situacao = 1";

            if ($parametroPerfil <> "Todos") {
                $select .= " AND idPerfil = {$parametroPerfil}";
                $subTitulo .= "Filtro Perfil: {$pessoal->get_perfilNome($parametroPerfil)}<br/>";
            }

            if ($parametroNivel <> "Todos") {
                $select .= " AND tbtipocargo.nivel = '{$parametroNivel}'";
                $subTitulo .= "Filtro Cargo Efetivo de Nível: {$parametroNivel}<br/>";
            }

            if ($parametroEscolaridade <> "Todos") {
                $select .= " AND tbformacao.idEscolaridade = {$parametroEscolaridade}";
                $subTitulo .= "Filtro Curso de Nível: {$pessoal->get_escolaridade($parametroEscolaridade)}<br/>";
            }

            if ($parametroCurso <> "Todos") {
                $select .= " AND tbformacao.habilitacao LIKE '%{$parametroCurso}%'";
                $subTitulo .= "Filtro Curso: {$parametroCurso}<br/>";
            }

            if ($parametroInstituicao <> "Todos") {
                $select .= " AND tbformacao.instEnsino LIKE '%{$parametroInstituicao}%'";
                $subTitulo .= "Filtro Instituição: {$parametroInstituicao}<br/>";
            }

            if ($parametroAno <> "Todos") {
                $select .= " AND tbformacao.anoTerm = '{$parametroAno}'";
                $subTitulo .= "Filtro Ano: {$parametroAno}<br/>";
            }

            if ($parametroMarcador <> "Todos") {
                $select .= " AND (tbformacao.marcador1 = {$parametroMarcador} OR tbformacao.marcador2 = {$parametroMarcador} OR tbformacao.marcador3 = {$parametroMarcador} OR tbformacao.marcador4 = {$parametroMarcador})";
                $subTitulo .= "Filtro Marcador: {$formacao->get_marcador($parametroMarcador)}<br/>";
            }

            # Verifica se tem filtro por lotação
            if ($parametroLotacao <> "Todos") {  // senão verifica o da classe
                if (is_numeric($parametroLotacao)) {
                    $select .= " AND (tblotacao.idlotacao = {$parametroLotacao})";
                    $subTitulo .= "Filtro Lotação: {$pessoal->get_nomeLotacao($parametroLotacao)}<br/>";
                } else { # senão é uma diretoria genérica
                    $select .= " AND (tblotacao.DIR = '{$parametroLotacao}')";
                    $subTitulo .= "Filtro Lotação: {$parametroLotacao}<br/>";
                }
            }

            $select .= ' ORDER BY tbpessoa.nome, tbformacao.anoTerm';
            #echo $select;
            # Monta o Relatório
            $relatorio = new Relatorio();
            $relatorio->set_titulo('Relatório Geral de Formação Servidores');

            if (!is_null($subTitulo)) {
                $relatorio->set_subtitulo($subTitulo);
            }

            $result = $pessoal->select($select);

            $relatorio->set_label(["Servidor", "Nível do Curso", "Curso"]);
            $relatorio->set_conteudo($result);
            $relatorio->set_align(["left", "center", "left"]);
            $relatorio->set_classe(["pessoal", null, "Formacao"]);
            $relatorio->set_metodo(["get_nomeECargoELotacaoEId", null, "exibeCurso"]);
            #$relatorio->set_rowspan(0);
            $relatorio->set_bordaInterna(true);
            $relatorio->show();
            break;
    }

    $page->terminaPagina();
} else {
    loadPage("../../areaServidor/sistema/login.php");
}


