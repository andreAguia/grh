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

# Cadastro de reserva
$cadReserva = 5;

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
        $parametroCota = post('parametroCota', get_session('parametroCota', 'Ac'));

        # Define os dados de acordo com as cotas
        switch ($parametroCota) {
            // Ampla Concorrência
            case "Ac":
                $numeroVagas = $concurso->get_numVagasAcAprovadas($idConcurso, $parametroCargoCandidato);
                $campo = "classifAc";
                $campoVaga = "vagas";
                $subtitulo = "Ampla Concorrência";
                break;

            // Pcd
            case "Pcd":
                $numeroVagas = $concurso->get_numVagasPcdAprovadas($idConcurso, $parametroCargoCandidato);
                $campo = "classifPcd";
                $campoVaga = "vagasPcd";
                $subtitulo = "Cota: PCD";
                break;

            // Negros e Indígenas
            case "Ni":
                $numeroVagas = $concurso->get_numVagasNiAprovadas($idConcurso, $parametroCargoCandidato);
                $campo = "classifNi";
                $campoVaga = "vagasNi";
                $subtitulo = "Cota: Negros e Indígenas";
                break;

            // Hipossuficiente Econômico
            case "Hipo":
                $numeroVagas = $concurso->get_numVagasHipoAprovadas($idConcurso, $parametroCargoCandidato);
                $campo = "classifHipo";
                $campoVaga = "vagasHipo";
                $subtitulo = "Cota: Hipossuficiente Econômico";
                break;
        }

        # Define o cadastro de reserva, quando se tem o número de vagas
        if (empty($numeroVagas)) {
            $numeroVagas = null;
            $cadastroReserva = null;
            $foraCadastro = null;
            if ($parametroCargoCandidato <> "*") {
                $subtitulo .= " - SEM Vagas";
            }
        } else {
            $cadastroReserva = $cadReserva * $numeroVagas;
            $foraCadastro = $numeroVagas + $cadastroReserva;
            $subtitulo .= " - {$numeroVagas} Vaga(s)";
        }

        # Retira as cotas quando se escolhe todos os cargos
        if ($parametroCargoCandidato == "*") {
            $subtitulo = "Todos os Cargos - {$subtitulo}";
        } else {
            $parametroNome = null;
        }

        set_session('parametroCargoCandidato', $parametroCargoCandidato);
        set_session('parametroNome', $parametroNome);
        set_session('parametroCota', $parametroCota);
    }

    # Começa uma nova página
    $page = new Page();
    $page->iniciaPagina();

    # Cabeçalho da Página
    if ($fase <> "relatorio1"
            AND $fase <> "relatorio2"
            AND $fase <> "relatorio3"
            AND $fase <> "relatorio4"
            AND $fase <> "relatorio5"
            AND $fase <> "relatorio6"
            AND $fase <> "relatorio7"
            AND $fase <> "relatorio8"
            . "") {
        AreaServidor::cabecalho();
    }

    # Define array de Cotas
    $concurso2025 = new ConcursoAdm2025();
    $arrayCotas = $concurso2025->get_arrayCotas();
    #$idConcurso - $concurso2025->get_idConcurso();

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

        ###################################################################

        /*
         * Lista Candidatos
         */

        case "listaCandidatos" :

            # Cria um menu
            $menu1 = new MenuBar();

            # Voltar
            $botaoVoltar = new Link("Voltar", "areaConcursoAdm.php");
            $botaoVoltar->set_class('button');
            $botaoVoltar->set_title('Voltar a página anterior');
            $botaoVoltar->set_accessKey('V');
            $menu1->add_link($botaoVoltar, "left");

            # Vagas
            $botaoVagas = new Link("Vagas", "?fase=exibeVagas");
            $botaoVagas->set_class('button');
            $botaoVagas->set_title('Exibe a tabela de vagas');
            $botaoVagas->set_target("_blank");
            $menu1->add_link($botaoVagas, "right");

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

            ###################################################################                       

            $grid->abreColuna(3);

            # Exibe os dados do Concurso
            $concurso->exibeDadosConcurso($idConcurso, true);

            # menu
            $concurso->exibeMenu($idConcurso, "Candidatos");

            # Exibe os servidores deste concurso
            $concurso->exibeQuadroServidoresConcursoPorCargo($idConcurso);

            # Relatórios
            if (empty($parametroNome)) {
                $menu = new Menu("menuProcedimentos");
                $menu->add_item('titulo', 'Relatórios de Candidatos');
                $menu->add_item('titulo1', 'Candidatos na Vaga');
                $menu->add_item('linkWindow', 'Com CPF / E-mail / Tel', '?fase=relatorio1');
                $menu->add_item('linkWindow', 'Com CPF / Ident e Nascimento', '?fase=relatorio2');
                $menu->add_item('linkWindow', 'Com Pontuação', '?fase=relatorio3');
                $menu->add_item('linkWindow', 'Com Lotação', '?fase=relatorio8');

                $menu->add_item('titulo1', 'Todos os Candidatos Aprovados');
                $menu->add_item('linkWindow', 'Nome, CPF, CI e Cargo - Para a Perícia', '?fase=relatorio4');
                $menu->add_item('linkWindow', 'Nome e CPF - Para o Restaurante', '?fase=relatorio5');
                $menu->add_item('linkWindow', 'Nome, Nascimento, Cota - Para Recepção', '?fase=relatorio9');

                $menu->show();
            }

            $grid->fechaColuna();

            ###################################################################
            # Campos de Pesquisa
            $grid->abreColuna(9);

            # Formulário
            $form = new Form('?');

            # Cargo
            $result = $pessoal->select("SELECT DISTINCT cargoConcurso,
                                               cargoConcurso
                                          FROM tbconcursovagadetalhada
                                          WHERE idConcurso = {$idConcurso}
                                       ORDER BY cargoConcurso");

            array_unshift($result, ['*', '-- Todos --']);

            $controle = new Input('parametroCargoCandidato', 'combo', 'Cargo:', 1);
            $controle->set_size(30);
            $controle->set_title('Filtra por Cargo');
            $controle->set_autofocus(true);
            $controle->set_array($result);
            $controle->set_valor($parametroCargoCandidato);
            $controle->set_onChange('formPadrao.submit();');
            $controle->set_linha(1);
            $controle->set_col(12);
            $form->add_item($controle);

            # Nome
            $controle = new Input('parametroNome', 'texto', 'Nome:', 1);
            $controle->set_size(50);
            $controle->set_title('Filtra por Nome');
            $controle->set_valor($parametroNome);
            $controle->set_onChange('formPadrao.submit();');
            $controle->set_linha(1);
            $controle->set_col(7);
            if ($parametroCargoCandidato == "*") {
                $form->add_item($controle);
            }

            # Cotas
            $controle = new Input('parametroCota', 'combo', 'Cota:', 1);
            $controle->set_size(30);
            $controle->set_title('Filtra por Cota');
            $controle->set_array($arrayCotas);
            $controle->set_valor($parametroCota);
            $controle->set_onChange('formPadrao.submit();');
            $controle->set_linha(1);
            $controle->set_col(5);
            $form->add_item($controle);

            $form->show();

            ###################################################################

            /*
             * Rotina quando se seleciona um cargo
             */
            if ($parametroCargoCandidato <> "*") {

                $candidato = new Candidato();
                $candidato->exibeTabelaVagasCargo($parametroCargoCandidato);

                /*
                 * Quando se tem número de vagas cadastrados
                 */
                if (!empty($numeroVagas)) {

                    /*
                     * Candidatos Aprovado no número de Vagas
                     */
                    # Monta o select
                    $select = "SELECT {$campo},
                              if({$campo} <= tbconcursovagadetalhada.{$campoVaga},'Vaga',if({$campo} BETWEEN tbconcursovagadetalhada.{$campoVaga} AND tbconcursovagadetalhada.{$campoVaga}*{$cadReserva}+tbconcursovagadetalhada.{$campoVaga},'Cadastro de Reserva','---')),
                              inscricao,
                              idCandidato,
                              dtNascimento,
                              idCandidato,
                              classifAc,
                              CONVERT(notaFinal, DECIMAL(10,2)),
                              idCandidato
                         FROM tbcandidato JOIN tbconcursovagadetalhada ON (tbcandidato.cargo = tbconcursovagadetalhada. cargoConcurso)
                        WHERE tbcandidato.idConcurso = {$idConcurso}";

                    # Cota
                    if ($parametroCota <> "Ac") {
                        $select .= " AND {$campo} IS NOT NULL";
                    }

                    # nome
                    if (!is_null($parametroNome)) {

                        # Verifica se tem espaços
                        if (strpos($parametroNome, ' ') !== false) {
                            # Separa as palavras
                            $palavras = explode(' ', $parametroNome);

                            # Percorre as palavras
                            foreach ($palavras as $item) {
                                $select .= ' AND (nome LIKE "%' . $item . '%")';
                            }
                        } else {
                            $select .= " AND nome LIKE '%{$parametroNome}%'";
                        }
                    }

                    # cargo
                    if ($parametroCargoCandidato <> "*") {
                        $select .= " AND cargo = '{$parametroCargoCandidato}'";
                    }

                    # Ordenação
                    $select .= " ORDER BY {$campo}";

                    # Pega os dados
                    $row = $pessoal->select($select);

                    # tabela
                    $tabela = new Tabela();
                    $tabela->set_titulo("Candidatos Aprovados");
                    $tabela->set_subtitulo($subtitulo);
                    $tabela->set_conteudo($row);
                    $tabela->set_label(["#", "Situação", "Inscrição", "Candidato", "Nascimento", "Cota", "Ampla Concorrência", "Nota Final", "Editar"]);
                    $tabela->set_width([5, 10, 10, 30, 10, 10, 10, 10]);
                    $tabela->set_align(["center", "center", "center", "left", "center"]);
                    $tabela->set_funcao(["trataNulo", null, null, "plm", "date_to_php"]);

                    $tabela->set_classe([null, null, null, "Candidato", null, "Candidato"]);
                    $tabela->set_metodo([null, null, null, "get_nomeELotacao", null, "exibeCotas"]);

                    # Botão Editar
                    $botao = new Link(null, "?fase=editaCandidato&id=", 'Acessa os dados do Candidato');
                    $botao->set_imagem(PASTA_FIGURAS . 'bullet_edit.png', 20, 20);

                    # Coloca o objeto link na tabela			
                    $tabela->set_link([null, null, null, null, null, null, null, null, $botao]);

                    $tabela->set_rowspan(1);
                    $tabela->set_grupoCorColuna(1);

                    $tabela->set_formatacaoCondicional(array(
                        array('coluna' => 1,
                            'valor' => 'Vaga',
                            'operador' => '=',
                            'id' => "naVaga"),
                        array('coluna' => 1,
                            'valor' => 'Cadastro de Reserva',
                            'operador' => '=',
                            'id' => "reserva")));

                    $tabela->set_mensagemPosTabela("O Cadastro de Reserva é de 5 vezes o número de vagas");
                    $tabela->show();

                    ####################################################################################
                } else {
                    /*
                     * Quando não tem número de vagas cadastradas
                     */

                    # Monta o select
                    $select = "SELECT {$campo},
                              '---',
                              inscricao,
                              idCandidato,
                              dtNascimento,
                              idCandidato,                                    
                              CONVERT(notaFinal, DECIMAL(10,2)),
                              idCandidato
                         FROM tbcandidato
                        WHERE idConcurso = {$idConcurso}";

                    # Pega o candidato de acordo com a cota
                    if ($parametroCota <> "Ac") {
                        $select .= " AND {$campo} IS NOT NULL";
                    }

                    # nome
                    if (!is_null($parametroNome)) {

                        # Verifica se tem espaços
                        if (strpos($parametroNome, ' ') !== false) {
                            # Separa as palavras
                            $palavras = explode(' ', $parametroNome);

                            # Percorre as palavras
                            foreach ($palavras as $item) {
                                $select .= ' AND (nome LIKE "%' . $item . '%")';
                            }
                        } else {
                            $select .= " AND nome LIKE '%{$parametroNome}%'";
                        }
                    }

                    # cargo
                    if ($parametroCargoCandidato <> "*") {
                        $select .= " AND cargo = '{$parametroCargoCandidato}'";
                    }

                    # Ordena de acorto do as cotas
                    $select .= " ORDER BY {$campo}";

                    # Pega os dados
                    $row = $pessoal->select($select);

                    # tabela
                    $tabela = new Tabela();
                    $tabela->set_titulo("Candidatos Aprovados");
                    $tabela->set_subtitulo($subtitulo);
                    $tabela->set_conteudo($row);
                    $tabela->set_label(["#", "Situação", "Inscrição", "Candidato", "Nascimento", "Cota", "Nota Final", "Editar"]);
                    $tabela->set_width([5, 10, 10, 30, 10, 10, 15]);
                    $tabela->set_align(["center", "center", "center", "left", "center"]);
                    $tabela->set_funcao([null, null, null, "plm", "date_to_php"]);

                    $tabela->set_classe([null, null, null, "Candidato", null, "Candidato"]);
                    $tabela->set_metodo([null, null, null, "get_nomeELotacao", null, "exibeCotas"]);

                    # Botão Editar
                    $botao = new Link(null, "?fase=editaCandidato&id=", 'Acessa os dados do Candidato');
                    $botao->set_imagem(PASTA_FIGURAS . 'bullet_edit.png', 20, 20);

                    # Coloca o objeto link na tabela			
                    $tabela->set_link([null, null, null, null, null, null, null, $botao]);

                    $tabela->set_rowspan(0);
                    $tabela->set_grupoCorColuna(0);
                    $tabela->show();
                }
            } else {

                /*
                 *  Rotina para todos os cargos
                 */

                # Monta o select
                $select = "SELECT if({$campo} <= tbconcursovagadetalhada.{$campoVaga},'<span class=\'label success\'>Vaga</label>',if({$campo} BETWEEN tbconcursovagadetalhada.{$campoVaga} AND tbconcursovagadetalhada.{$campoVaga}*{$cadReserva}+tbconcursovagadetalhada.{$campoVaga},'<span class=\'label warning\'>Reserva</label>','---')),
                                  inscricao,
                                  idCandidato,
                                  tbconcursovagadetalhada.{$campoVaga},
                                  classifAc,                              
                                  classifPcd,
                                  classifNi,
                                  classifHipo,
                                  CONVERT(notaFinal, DECIMAL(10,2)),
                                  idCandidato
                             FROM tbcandidato JOIN tbconcursovagadetalhada ON (tbcandidato.cargo = tbconcursovagadetalhada. cargoConcurso)
                            WHERE tbcandidato.idConcurso = {$idConcurso}";

                # Pega o candidato de acordo com a cota
                if ($parametroCota <> "Ac") {
                    $select .= " AND {$campo} IS NOT NULL";
                }

                # nome
                if (!is_null($parametroNome)) {

                    # Verifica se tem espaços
                    if (strpos($parametroNome, ' ') !== false) {
                        # Separa as palavras
                        $palavras = explode(' ', $parametroNome);

                        # Percorre as palavras
                        foreach ($palavras as $item) {
                            $select .= ' AND (nome LIKE "%' . $item . '%")';
                        }
                    } else {
                        $select .= " AND nome LIKE '%{$parametroNome}%'";
                    }
                }

                $select .= " ORDER BY cargo, {$campo}";

                # Pega os dados
                $row = $pessoal->select($select);

                # tabela
                $tabela = new Tabela();
                $tabela->set_titulo("Cadastro de Candidatos Aprovados");
                $tabela->set_subtitulo($subtitulo);
                $tabela->set_conteudo($row);
                $tabela->set_label(["Situação", "Inscrição", "Candidato", "Vagas", "AC", "PCD", "NI", "HIPO", "Nota Final", "Editar"]);
                $tabela->set_width([10, 10, 40, 5, 5, 5, 5, 5, 5, 5, 5]);
                $tabela->set_align(["center", "center", "left"]);
                #$tabela->set_funcao([null, null, "plm", "plm"]);

                $tabela->set_classe([null, null, "Candidato"]);
                $tabela->set_metodo([null, null, "get_nomeECargoELotacao"]);

                # Botão Editar
                $botao = new Link(null, "?fase=editaCandidato&id=", 'Acessa os dados do Candidato');
                $botao->set_imagem(PASTA_FIGURAS . 'bullet_edit.png', 20, 20);
                $tabela->set_link([null, null, null, null, null, null, null, null, null, $botao]);

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

        /*
         * Candidatos Duplicados
         */

        case "duplicados":

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
            $botaoRel->set_title("Relatório dos Candidatos Duplicados");
            $botaoRel->set_target("_blank");
            $botaoRel->set_url("?fase=relatorio6");
            $botaoRel->set_imagem($imagem2);
            $menu1->add_link($botaoRel, "right");

            $menu1->show();

            $grid->fechaColuna();

            ###################################################################                       

            $grid->abreColuna(3);

            # Exibe os dados do Concurso
            $concurso->exibeDadosConcurso($idConcurso, true);

            # menu
            $concurso->exibeMenu($idConcurso, "Duplicados");

            # Exibe os servidores deste concurso
            $concurso->exibeQuadroServidoresConcursoPorCargo($idConcurso);
            $grid->fechaColuna();

            ###################################################################
            # Campos de Pesquisa
            $grid->abreColuna(9);

            # Define o array da tabela
            $arrayTabela = [];
            $resultadoFinal = [];

            # Pega os cargos
            $result = $pessoal->select('SELECT DISTINCT cargoConcurso
                                          FROM tbconcursovagadetalhada
                                      ORDER BY cargoConcurso');

            # Percorre os cargos
            foreach ($result as $item) {

                foreach ($arrayCotas as $cota) {

                    switch ($cota[0]) {
                        // Ampla Concorrência
                        case "Ac":
                            $numeroVagas = $concurso->get_numVagasAcAprovadas($idConcurso, $item["cargoConcurso"]);
                            $campo = "classifAc";
                            $campoVaga = "vagas";
                            $subtitulo = "Ampla Concorrência";
                            break;

                        // Pcd
                        case "Pcd":
                            $numeroVagas = $concurso->get_numVagasPcdAprovadas($idConcurso, $item["cargoConcurso"]);
                            $campo = "classifPcd";
                            $campoVaga = "vagasPcd";
                            $subtitulo = "Cota: PCD";
                            break;

                        // Negros e Indígenas
                        case "Ni":
                            $numeroVagas = $concurso->get_numVagasNiAprovadas($idConcurso, $item["cargoConcurso"]);
                            $campo = "classifNi";
                            $campoVaga = "vagasNi";
                            $subtitulo = "Cota: Negros e Indígenas";
                            break;

                        // Hipossuficiente Econômico
                        case "Hipo":
                            $numeroVagas = $concurso->get_numVagasHipoAprovadas($idConcurso, $item["cargoConcurso"]);
                            $campo = "classifHipo";
                            $campoVaga = "vagasHipo";
                            $subtitulo = "Cota: Hipossuficiente Econômico";
                            break;
                    }

                    # Pega os candidatos desse cargo e dessa cota
                    $select = "SELECT inscricao,
                                      nome,
                                      cargo,
                                      classifAc,
                                      classifPcd,
                                      classifNi,
                                      classifHipo,
                                      idCandidato
                                 FROM tbcandidato LEFT JOIN tbconcursovagadetalhada ON (tbcandidato.cargo = tbconcursovagadetalhada. cargoConcurso)
                                WHERE tbcandidato.idConcurso = {$idConcurso}
                                  AND {$campo} <= tbconcursovagadetalhada.{$campoVaga}
                                  AND cargo = '{$item["cargoConcurso"]}'
                             ORDER BY {$campo}";

                    # Passa para o array
                    $arrayTabela = array_merge($arrayTabela, $pessoal->select($select));
                }
            }

            # Ordena por nome
            usort($arrayTabela, function ($a, $b) {
                return strcmp($a['nome'], $b['nome']);
            });

            // 1. Extrai a coluna de nome e conta quantas vezes cada um aparece
            $nomes = array_column($arrayTabela, 'nome');
            $contagem = array_count_values($nomes);

            // 2. Filtra o array mantendo apenas os registros cujo nome aparece > 1 vez
            $duplicados = array_filter($arrayTabela, function ($arrayTabela) use ($contagem) {
                return $contagem[$arrayTabela['nome']] > 1;
            });

            # Relatório
            $tabela = new Tabela();
            $tabela->set_titulo("Relatório de Candidatos Duplicados");
            #$relatorio->set_subtitulo($subtitulo);
            $tabela->set_conteudo($duplicados);
            $tabela->set_label(["Inscrição", "Nome", "Cargo", "Ac", "Pcd", "Ni", "Hipo", "Editar"]);
            $tabela->set_align(["center", "left", "left"]);
            $tabela->set_funcao([null, "plm", "plm"]);

            # Botão Editar
            $botao = new Link(null, "?fase=editaCandidatoDuplicado&id=", 'Acessa os dados do Candidato');
            $botao->set_imagem(PASTA_FIGURAS . 'bullet_edit.png', 20, 20);
            $tabela->set_link([null, null, null, null, null, null, null, $botao]);

            $tabela->set_rowspan(1);
            $tabela->set_grupoCorColuna(1);
            $tabela->show();
            break;

        ################################################################

        /*
         * Relatórios
         */

        ################################################################

        case "relatorio1":

            /*
             *  Candidatos de um cargo
             */

            if ($parametroCargoCandidato <> "*") {

                # Monta o select
                $select = "SELECT {$campo},
                              inscricao,
                              nome,
                              cpf,
                              email,
                              celular,
                              CONVERT(notaFinal, DECIMAL(10,2))
                         FROM tbcandidato JOIN tbconcursovagadetalhada ON (tbcandidato.cargo = tbconcursovagadetalhada. cargoConcurso)
                        WHERE tbcandidato.idConcurso = {$idConcurso}
                          AND {$campo} <= tbconcursovagadetalhada.{$campoVaga}";

                # Pega o candidato de acordo com a cota
                if ($parametroCota <> "Ac") {
                    $select .= " AND {$campo} IS NOT NULL";
                }

                # nome
                if (!is_null($parametroNome)) {
                    $select .= " AND nome LIKE '%{$parametroNome}%'";
                }

                # cargo
                if ($parametroCargoCandidato <> "*") {
                    $select .= " AND cargo = '{$parametroCargoCandidato}'";
                }

                # Ordenação
                if (empty($numeroVagas)) {
                    $select .= " ORDER BY {$campo}";
                } else {
                    $select .= " ORDER BY {$campo} LIMIT {$numeroVagas}";
                }

                $row = $pessoal->select($select);

                # Esvazia o array quando não tem vagas
                if (empty($numeroVagas)) {
                    $row = array();
                }

                # tabela
                $relatorio = new Relatorio();
                $relatorio->set_titulo("Relatório de Candidatos Aprovados");
                $relatorio->set_tituloLinha2(plm($parametroCargoCandidato));
                $relatorio->set_subtitulo($subtitulo);
                $relatorio->set_conteudo($row);
                $relatorio->set_label(["#", "Inscrição", "Candidato", "CPF", "E-mail", "Telefone"]);
                $relatorio->set_align(["center", "center", "left", "center", "left"]);
                $relatorio->set_funcao([null, null, "plm"]);
                $relatorio->show();
            } else {
                #################################################################

                /*
                 *  Todos os cargos
                 */


                # Pega os cargos
                $result = $pessoal->select('SELECT DISTINCT cargoConcurso
                                              FROM tbconcursovagadetalhada
                                          ORDER BY cargoConcurso');

                $primeiro = true;

                foreach ($result as $item) {

                    # No Relatório tem que saber 
                    # o número de vagas de cada cargo
                    # Então tem que definir para cada um
                    # Define os dados de acordo com as cotas
                    switch ($parametroCota) {
                        // Ampla Concorrência
                        case "Ac":
                            $numeroVagas = $concurso->get_numVagasAcAprovadas($idConcurso, $item["cargoConcurso"]);
                            $campo = "classifAc";
                            $subtitulo = "Ampla Concorrência";
                            break;

                        // Pcd
                        case "Pcd":
                            $numeroVagas = $concurso->get_numVagasPcdAprovadas($idConcurso, $item["cargoConcurso"]);
                            $campo = "classifPcd";
                            $subtitulo = "Cota: PCD";
                            break;

                        // Negros e Indígenas
                        case "Ni":
                            $numeroVagas = $concurso->get_numVagasNiAprovadas($idConcurso, $item["cargoConcurso"]);
                            $campo = "classifNi";
                            $subtitulo = "Cota: Negros e Indígenas";
                            break;

                        // Hipossuficiente Econômico
                        case "Hipo":
                            $numeroVagas = $concurso->get_numVagasHipoAprovadas($idConcurso, $item["cargoConcurso"]);
                            $campo = "classifHipo";
                            $subtitulo = "Cota: Hipossuficiente Econômico";
                            break;
                    }

                    # Define o cadastro de reserva, quando se tem o número de vagas
                    if (empty($numeroVagas)) {
                        $numeroVagas = 0;
                        $cadastroReserva = 0;
                        $foraCadastro = 0;
                    } else {
                        $cadastroReserva = $cadReserva * $numeroVagas;
                        $foraCadastro = $numeroVagas + $cadastroReserva;

                        # Monta o select
                        if ($primeiro) {
                            $select = "(SELECT {$campo},";
                            $primeiro = false;
                        } else {
                            $select .= ") UNION (SELECT {$campo},";
                        }

                        $select .= " inscricao,
                              nome,
                              cpf,
                              email,
                              celular,
                              CONCAT(cargo,'<br/>{$numeroVagas} Vagas')
                         FROM tbcandidato
                        WHERE idConcurso = {$idConcurso}";

                        # Pega o candidato de acordo com a cota
                        if ($parametroCota <> "Ac") {
                            $select .= " AND {$campo} IS NOT NULL";
                        }

                        # nome
                        if (!is_null($parametroNome)) {
                            $select .= " AND nome LIKE '%{$parametroNome}%'";
                        }

                        # cargo
                        $select .= " AND cargo = '{$item["cargoConcurso"]}'";

                        # Ordenação
                        $select .= " ORDER BY {$campo} LIMIT {$numeroVagas}";
                    }
                }

                $select .= ")";
                $row = $pessoal->select($select);

                # Relatório
                $relatorio = new Relatorio();
                $relatorio->set_titulo("Relatório de Candidatos Aprovados");
                $relatorio->set_subtitulo($subtitulo);
                $relatorio->set_conteudo($row);
                $relatorio->set_label(["#", "Inscrição", "Candidato", "CPF", "E-mail", "Telefone", "Cargo"]);
                $relatorio->set_align(["center", "center", "left", "center", "left"]);
                $relatorio->set_funcao([null, null, "plm", null, null, null, "plm"]);
                $relatorio->set_numGrupo(6);
                $relatorio->show();
            }
            break;
        ################################################################     

        case "relatorio2":

            /*
             *  Candidatos de um cargo
             */
            if ($parametroCargoCandidato <> "*") {

                # Monta o select
                $select = "SELECT {$campo},
                              inscricao,
                              nome,
                              cpf,
                              identidade,
                              dtNascimento,
                              CONVERT(notaFinal, DECIMAL(10,2))
                         FROM tbcandidato
                        WHERE idConcurso = {$idConcurso}";

                # Pega o candidato de acordo com a cota
                if ($parametroCota <> "Ac") {
                    $select .= " AND {$campo} IS NOT NULL";
                }

                # nome
                if (!is_null($parametroNome)) {
                    $select .= " AND nome LIKE '%{$parametroNome}%'";
                }

                # cargo
                if ($parametroCargoCandidato <> "*") {
                    $select .= " AND cargo = '{$parametroCargoCandidato}'";
                }

                # Ordenação
                if (empty($numeroVagas)) {
                    $select .= " ORDER BY {$campo}";
                } else {
                    $select .= " ORDER BY {$campo} LIMIT {$numeroVagas}";
                }

                $row = $pessoal->select($select);

                # Esvazia o array quando não tem vagas
                if (empty($numeroVagas)) {
                    $row = array();
                }

                # tabela
                $relatorio = new Relatorio();
                $relatorio->set_titulo("Relatório de Candidatos Aprovados");
                $relatorio->set_tituloLinha2(plm($parametroCargoCandidato));
                $relatorio->set_subtitulo($subtitulo);
                $relatorio->set_conteudo($row);
                $relatorio->set_label(["#", "Inscrição", "Candidato", "CPF", "Identidade", "Nascimento", "Pontuação"]);
                $relatorio->set_align(["center", "center", "left", "center"]);
                $relatorio->set_funcao([null, null, "plm", null, null, "date_to_php"]);
                $relatorio->show();
            } else {
                #################################################################

                /*
                 *  Todos os cargos
                 */


                # Pega os cargos
                $result = $pessoal->select('SELECT DISTINCT cargoConcurso
                                              FROM tbconcursovagadetalhada
                                          ORDER BY cargoConcurso');

                $primeiro = true;

                foreach ($result as $item) {

                    # No Relatório tem que saber 
                    # o número de vagas de cada cargo
                    # Então tem que definir para cada um
                    # Define os dados de acordo com as cotas
                    switch ($parametroCota) {
                        // Ampla Concorrência
                        case "Ac":
                            $numeroVagas = $concurso->get_numVagasAcAprovadas($idConcurso, $item["cargoConcurso"]);
                            $campo = "classifAc";
                            $subtitulo = "Ampla Concorrência";
                            break;

                        // Pcd
                        case "Pcd":
                            $numeroVagas = $concurso->get_numVagasPcdAprovadas($idConcurso, $item["cargoConcurso"]);
                            $campo = "classifPcd";
                            $subtitulo = "Cota: PCD";
                            break;

                        // Negros e Indígenas
                        case "Ni":
                            $numeroVagas = $concurso->get_numVagasNiAprovadas($idConcurso, $item["cargoConcurso"]);
                            $campo = "classifNi";
                            $subtitulo = "Cota: Negros e Indígenas";
                            break;

                        // Hipossuficiente Econômico
                        case "Hipo":
                            $numeroVagas = $concurso->get_numVagasHipoAprovadas($idConcurso, $item["cargoConcurso"]);
                            $campo = "classifHipo";
                            $subtitulo = "Cota: Hipossuficiente Econômico";
                            break;
                    }

                    # Define o cadastro de reserva, quando se tem o número de vagas
                    if (empty($numeroVagas)) {
                        $numeroVagas = 0;
                        $cadastroReserva = 0;
                        $foraCadastro = 0;
                    } else {
                        $cadastroReserva = $cadReserva * $numeroVagas;
                        $foraCadastro = $numeroVagas + $cadastroReserva;

                        # Monta o select
                        if ($primeiro) {
                            $select = "(SELECT {$campo},";
                            $primeiro = false;
                        } else {
                            $select .= ") UNION (SELECT {$campo},";
                        }

                        $select .= " inscricao,
                              nome,
                              CONCAT(cargo,'<br/>{$numeroVagas} Vagas'),
                              cpf,
                              identidade,
                              dtNascimento
                         FROM tbcandidato
                        WHERE idConcurso = {$idConcurso}";

                        # Pega o candidato de acordo com a cota
                        if ($parametroCota <> "Ac") {
                            $select .= " AND {$campo} IS NOT NULL";
                        }

                        # nome
                        if (!is_null($parametroNome)) {
                            $select .= " AND nome LIKE '%{$parametroNome}%'";
                        }

                        # cargo
                        $select .= " AND cargo = '{$item["cargoConcurso"]}'";

                        # Ordenação
                        $select .= " ORDER BY {$campo} LIMIT {$numeroVagas}";
                    }
                }

                $select .= ")";
                $row = $pessoal->select($select);

                # Relatório
                $relatorio = new Relatorio();
                $relatorio->set_titulo("Relatório de Candidatos Aprovados");
                $relatorio->set_subtitulo($subtitulo);
                $relatorio->set_conteudo($row);
                $relatorio->set_label(["#", "Inscrição", "Candidato", "Cargo", "CPF", "Identidade", "Nascimento"]);
                $relatorio->set_align(["center", "center", "left", "left"]);
                $relatorio->set_funcao([null, null, "plm", "plm", null, null, "date_to_php"]);
                $relatorio->set_numGrupo(3);
                $relatorio->show();
            }
            break;

        ################################################################  

        case "relatorio3":

            /*
             *  Candidatos de um cargo
             */
            if ($parametroCargoCandidato <> "*") {

                # Monta o select
                $select = "SELECT {$campo},
                              inscricao,
                              nome,
                              CONVERT(notaFinal, DECIMAL(10,2))
                         FROM tbcandidato
                        WHERE idConcurso = {$idConcurso}";

                # Pega o candidato de acordo com a cota
                if ($parametroCota <> "Ac") {
                    $select .= " AND {$campo} IS NOT NULL";
                }

                # nome
                if (!is_null($parametroNome)) {
                    $select .= " AND nome LIKE '%{$parametroNome}%'";
                }

                # cargo
                if ($parametroCargoCandidato <> "*") {
                    $select .= " AND cargo = '{$parametroCargoCandidato}'";
                }

                # Ordenação
                if (empty($numeroVagas)) {
                    $select .= " ORDER BY {$campo}";
                } else {
                    $select .= " ORDER BY {$campo} LIMIT {$numeroVagas}";
                }

                $row = $pessoal->select($select);

                # Esvazia o array quando não tem vagas
                if (empty($numeroVagas)) {
                    $row = array();
                }

                # tabela
                $relatorio = new Relatorio();
                $relatorio->set_titulo("Relatório de Candidatos Aprovados");
                $relatorio->set_tituloLinha2(plm($parametroCargoCandidato));
                $relatorio->set_subtitulo($subtitulo);
                $relatorio->set_conteudo($row);
                $relatorio->set_label(["#", "Inscrição", "Candidato", "Pontuação"]);
                $relatorio->set_align(["center", "center", "left", "center"]);
                $relatorio->set_funcao([null, null, "plm"]);
                $relatorio->show();
            } else {
                #################################################################

                /*
                 *  Todos os cargos
                 */


                # Pega os cargos
                $result = $pessoal->select('SELECT DISTINCT cargoConcurso
                                              FROM tbconcursovagadetalhada
                                          ORDER BY cargoConcurso');

                $primeiro = true;

                foreach ($result as $item) {

                    # No Relatório tem que saber 
                    # o número de vagas de cada cargo
                    # Então tem que definir para cada um
                    # Define os dados de acordo com as cotas
                    switch ($parametroCota) {
                        // Ampla Concorrência
                        case "Ac":
                            $numeroVagas = $concurso->get_numVagasAcAprovadas($idConcurso, $item["cargoConcurso"]);
                            $campo = "classifAc";
                            $subtitulo = "Ampla Concorrência";
                            break;

                        // Pcd
                        case "Pcd":
                            $numeroVagas = $concurso->get_numVagasPcdAprovadas($idConcurso, $item["cargoConcurso"]);
                            $campo = "classifPcd";
                            $subtitulo = "Cota: PCD";
                            break;

                        // Negros e Indígenas
                        case "Ni":
                            $numeroVagas = $concurso->get_numVagasNiAprovadas($idConcurso, $item["cargoConcurso"]);
                            $campo = "classifNi";
                            $subtitulo = "Cota: Negros e Indígenas";
                            break;

                        // Hipossuficiente Econômico
                        case "Hipo":
                            $numeroVagas = $concurso->get_numVagasHipoAprovadas($idConcurso, $item["cargoConcurso"]);
                            $campo = "classifHipo";
                            $subtitulo = "Cota: Hipossuficiente Econômico";
                            break;
                    }

                    # Define o cadastro de reserva, quando se tem o número de vagas
                    if (empty($numeroVagas)) {
                        $numeroVagas = 0;
                        $cadastroReserva = 0;
                        $foraCadastro = 0;
                    } else {
                        $cadastroReserva = $cadReserva * $numeroVagas;
                        $foraCadastro = $numeroVagas + $cadastroReserva;

                        # Monta o select
                        if ($primeiro) {
                            $select = "(SELECT {$campo},";
                            $primeiro = false;
                        } else {
                            $select .= ") UNION (SELECT {$campo},";
                        }

                        $select .= " inscricao,
                                       nome,
                                       CONVERT(notaFinal, DECIMAL(10,2)),
                                       CONCAT(cargo,'<br/>{$numeroVagas} Vagas')
                         FROM tbcandidato
                        WHERE idConcurso = {$idConcurso}";

                        # Pega o candidato de acordo com a cota
                        if ($parametroCota <> "Ac") {
                            $select .= " AND {$campo} IS NOT NULL";
                        }

                        # nome
                        if (!is_null($parametroNome)) {
                            $select .= " AND nome LIKE '%{$parametroNome}%'";
                        }

                        # cargo
                        $select .= " AND cargo = '{$item["cargoConcurso"]}'";

                        # Ordenação
                        $select .= " ORDER BY {$campo} LIMIT {$numeroVagas}";
                    }
                }

                $select .= ")";
                $row = $pessoal->select($select);

                # Relatório
                $relatorio = new Relatorio();
                $relatorio->set_titulo("Relatório de Candidatos Aprovados");
                $relatorio->set_subtitulo($subtitulo);
                $relatorio->set_conteudo($row);
                $relatorio->set_label(["#", "Inscrição", "Candidato", "Pontuação", "Cargo"]);
                $relatorio->set_align(["center", "center", "left"]);
                $relatorio->set_funcao([null, null, "plm"]);
                $relatorio->set_numGrupo(4);
                $relatorio->show();
            }
            break;

        ################################################################  

        case "relatorio4":

            /*
             *  Todos os Candidatod de Todos os cargos
             * Com Nome, CPF, CI e Cargo
             */

            # Define o array da tabela
            $arrayTabela = [];
            $resultadoFinal = [];

            # Pega os cargos
            $result = $pessoal->select('SELECT DISTINCT cargoConcurso
                                          FROM tbconcursovagadetalhada
                                      ORDER BY cargoConcurso');

            # Percorre os cargos
            foreach ($result as $item) {

                foreach ($arrayCotas as $cota) {

                    switch ($cota[0]) {
                        // Ampla Concorrência
                        case "Ac":
                            $numeroVagas = $concurso->get_numVagasAcAprovadas($idConcurso, $item["cargoConcurso"]);
                            $campo = "classifAc";
                            $campoVaga = "vagas";
                            $subtitulo = "Ampla Concorrência";
                            break;

                        // Pcd
                        case "Pcd":
                            $numeroVagas = $concurso->get_numVagasPcdAprovadas($idConcurso, $item["cargoConcurso"]);
                            $campo = "classifPcd";
                            $campoVaga = "vagasPcd";
                            $subtitulo = "Cota: PCD";
                            break;

                        // Negros e Indígenas
                        case "Ni":
                            $numeroVagas = $concurso->get_numVagasNiAprovadas($idConcurso, $item["cargoConcurso"]);
                            $campo = "classifNi";
                            $campoVaga = "vagasNi";
                            $subtitulo = "Cota: Negros e Indígenas";
                            break;

                        // Hipossuficiente Econômico
                        case "Hipo":
                            $numeroVagas = $concurso->get_numVagasHipoAprovadas($idConcurso, $item["cargoConcurso"]);
                            $campo = "classifHipo";
                            $campoVaga = "vagasHipo";
                            $subtitulo = "Cota: Hipossuficiente Econômico";
                            break;
                    }

                    # Pega os candidaatos desse cargo e dessa cota
                    $select = "SELECT inscricao,
                                      nome,
                                      cpf,
                                      identidade,
                                      cargo
                                 FROM tbcandidato LEFT JOIN tbconcursovagadetalhada ON (tbcandidato.cargo = tbconcursovagadetalhada. cargoConcurso)
                                WHERE tbcandidato.idConcurso = {$idConcurso}
                                  AND {$campo} <= tbconcursovagadetalhada.{$campoVaga}
                                  AND cargo = '{$item["cargoConcurso"]}'
                             ORDER BY {$campo}";

                    # Passa para o array
                    $arrayTabela = array_merge($arrayTabela, $pessoal->select($select));
                }
            }

            # Ordena por nome
            usort($arrayTabela, function ($a, $b) {
                return strcmp($a['nome'], $b['nome']);
            });

            $anterior = null;
            # Retira as duplicatas
            foreach ($arrayTabela as $item) {
                # Verifica se é diferente ao anterior
                if ($item['nome'] <> $anterior) {
                    $anterior = $item['nome'];
                    $resultadoFinal[] = $item;
                }
            }

            # Relatório
            $relatorio = new Relatorio();
            $relatorio->set_titulo("Relatório de Candidatos Aprovados");
            #$relatorio->set_subtitulo($subtitulo);
            $relatorio->set_conteudo($resultadoFinal);
            $relatorio->set_label(["Inscrição", "Nome", "Cpf", "CI", "Cargo"]);
            $relatorio->set_align(["center", "left", "center", "center", "left"]);
            $relatorio->set_funcao([null, "plm", null, null, "plm"]);
            #$relatorio->set_numGrupo(2);
            $relatorio->show();

            break;

        ################################################################  

        case "relatorio5":


            /*
             *  Todos os Candidatod de Todos os cargos
             */

            # Define o array da tabela
            $arrayTabela = [];
            $resultadoFinal = [];

            # Pega os cargos
            $result = $pessoal->select('SELECT DISTINCT cargoConcurso
                                          FROM tbconcursovagadetalhada
                                      ORDER BY cargoConcurso');

            # Percorre os cargos
            foreach ($result as $item) {

                foreach ($arrayCotas as $cota) {

                    switch ($cota[0]) {
                        // Ampla Concorrência
                        case "Ac":
                            $numeroVagas = $concurso->get_numVagasAcAprovadas($idConcurso, $item["cargoConcurso"]);
                            $campo = "classifAc";
                            $campoVaga = "vagas";
                            $subtitulo = "Ampla Concorrência";
                            break;

                        // Pcd
                        case "Pcd":
                            $numeroVagas = $concurso->get_numVagasPcdAprovadas($idConcurso, $item["cargoConcurso"]);
                            $campo = "classifPcd";
                            $campoVaga = "vagasPcd";
                            $subtitulo = "Cota: PCD";
                            break;

                        // Negros e Indígenas
                        case "Ni":
                            $numeroVagas = $concurso->get_numVagasNiAprovadas($idConcurso, $item["cargoConcurso"]);
                            $campo = "classifNi";
                            $campoVaga = "vagasNi";
                            $subtitulo = "Cota: Negros e Indígenas";
                            break;

                        // Hipossuficiente Econômico
                        case "Hipo":
                            $numeroVagas = $concurso->get_numVagasHipoAprovadas($idConcurso, $item["cargoConcurso"]);
                            $campo = "classifHipo";
                            $campoVaga = "vagasHipo";
                            $subtitulo = "Cota: Hipossuficiente Econômico";
                            break;
                    }

                    # Pega os candidaatos desse cargo e dessa cota
                    $select = "SELECT inscricao,
                                      nome,
                                      cpf
                                 FROM tbcandidato LEFT JOIN tbconcursovagadetalhada ON (tbcandidato.cargo = tbconcursovagadetalhada. cargoConcurso)
                                WHERE tbcandidato.idConcurso = {$idConcurso}
                                  AND {$campo} <= tbconcursovagadetalhada.{$campoVaga}
                                  AND cargo = '{$item["cargoConcurso"]}'
                             ORDER BY {$campo}";

                    # Passa para o array
                    $arrayTabela = array_merge($arrayTabela, $pessoal->select($select));
                }
            }

            # Ordena por nome
            usort($arrayTabela, function ($a, $b) {
                return strcmp($a['nome'], $b['nome']);
            });

            $anterior = null;
            # Retira as duplicatas
            foreach ($arrayTabela as $item) {
                # Verifica se é diferente ao anterior
                if ($item['nome'] <> $anterior) {
                    $anterior = $item['nome'];
                    $resultadoFinal[] = $item;
                }
            }

            # Relatório
            $relatorio = new Relatorio();
            $relatorio->set_titulo("Relatório de Candidatos Aprovados");
            #$relatorio->set_subtitulo($subtitulo);
            $relatorio->set_conteudo($resultadoFinal);
            $relatorio->set_label(["Inscrição", "Nome", "Cpf"]);
            $relatorio->set_align(["center", "left"]);
            $relatorio->set_funcao([null, "plm"]);
            #$relatorio->set_numGrupo(2);
            $relatorio->show();

            break;

        ################################################################  

        case "relatorio6":


            /*
             *  Todos os Candidatod de Todos os cargos
             */

            # Define o array da tabela
            $arrayTabela = [];
            $resultadoFinal = [];

            # Pega os cargos
            $result = $pessoal->select('SELECT DISTINCT cargoConcurso
                                          FROM tbconcursovagadetalhada
                                      ORDER BY cargoConcurso');

            # Percorre os cargos
            foreach ($result as $item) {

                foreach ($arrayCotas as $cota) {

                    switch ($cota[0]) {
                        // Ampla Concorrência
                        case "Ac":
                            $numeroVagas = $concurso->get_numVagasAcAprovadas($idConcurso, $item["cargoConcurso"]);
                            $campo = "classifAc";
                            $campoVaga = "vagas";
                            $subtitulo = "Ampla Concorrência";
                            break;

                        // Pcd
                        case "Pcd":
                            $numeroVagas = $concurso->get_numVagasPcdAprovadas($idConcurso, $item["cargoConcurso"]);
                            $campo = "classifPcd";
                            $campoVaga = "vagasPcd";
                            $subtitulo = "Cota: PCD";
                            break;

                        // Negros e Indígenas
                        case "Ni":
                            $numeroVagas = $concurso->get_numVagasNiAprovadas($idConcurso, $item["cargoConcurso"]);
                            $campo = "classifNi";
                            $campoVaga = "vagasNi";
                            $subtitulo = "Cota: Negros e Indígenas";
                            break;

                        // Hipossuficiente Econômico
                        case "Hipo":
                            $numeroVagas = $concurso->get_numVagasHipoAprovadas($idConcurso, $item["cargoConcurso"]);
                            $campo = "classifHipo";
                            $campoVaga = "vagasHipo";
                            $subtitulo = "Cota: Hipossuficiente Econômico";
                            break;
                    }

                    # Pega os candidatos desse cargo e dessa cota
                    $select = "SELECT inscricao,
                                      nome,
                                      cargo,
                                      classifAc,
                                      classifPcd,
                                      classifNi,
                                      classifHipo
                                 FROM tbcandidato LEFT JOIN tbconcursovagadetalhada ON (tbcandidato.cargo = tbconcursovagadetalhada. cargoConcurso)
                                WHERE tbcandidato.idConcurso = {$idConcurso}
                                  AND {$campo} <= tbconcursovagadetalhada.{$campoVaga}
                                  AND cargo = '{$item["cargoConcurso"]}'
                             ORDER BY {$campo}";

                    # Passa para o array
                    $arrayTabela = array_merge($arrayTabela, $pessoal->select($select));
                }
            }

            # Ordena por nome
            usort($arrayTabela, function ($a, $b) {
                return strcmp($a['nome'], $b['nome']);
            });

            // 1. Extrai a coluna de nome e conta quantas vezes cada um aparece
            $nomes = array_column($arrayTabela, 'nome');
            $contagem = array_count_values($nomes);

            // 2. Filtra o array mantendo apenas os registros cujo nome aparece > 1 vez
            $duplicados = array_filter($arrayTabela, function ($arrayTabela) use ($contagem) {
                return $contagem[$arrayTabela['nome']] > 1;
            });

            # Relatório
            $relatorio = new Relatorio();
            $relatorio->set_titulo("Relatório de Candidatos Duplicados");
            #$relatorio->set_subtitulo($subtitulo);
            $relatorio->set_conteudo($duplicados);
            $relatorio->set_label(["Inscrição", "Nome", "Cargo", "Ac", "Pcd", "Ni", "Hipo"]);
            $relatorio->set_align(["center", "left", "left"]);
            $relatorio->set_funcao([null, "plm", "plm"]);

//            $relatorio->set_classe([null, null, "Candidato"]);
//            $relatorio->set_metodo([null, null, "exibeCotas"]);

            $relatorio->set_numGrupo(1);
            $relatorio->show();
            break;

        ################################################################  

        case "relatorio7":


            /*
             *  Todos os Candidatod de Todos os cargos
             */

            br(10);
            p("Em Desenvolvimento", "f16", "center");

//            # Define o array da tabela
//            $arrayTabela = [];
//            $resultadoFinal = [];
//
//            # Pega os cargos
//            $result = $pessoal->select('SELECT DISTINCT cargoConcurso
//                                          FROM tbconcursovagadetalhada
//                                      ORDER BY cargoConcurso');
//
//            # Percorre os cargos
//            foreach ($result as $item) {
//
//                foreach ($arrayCotas as $cota) {
//
//                    switch ($cota[0]) {
//                        // Ampla Concorrência
//                        case "Ac":
//                            $numeroVagas = $concurso->get_numVagasAcAprovadas($idConcurso, $item["cargoConcurso"]);
//                            $campo = "classifAc";
//                            $campoVaga = "vagas";
//                            $subtitulo = "Ampla Concorrência";
//                            break;
//
//                        // Pcd
//                        case "Pcd":
//                            $numeroVagas = $concurso->get_numVagasPcdAprovadas($idConcurso, $item["cargoConcurso"]);
//                            $campo = "classifPcd";
//                            $campoVaga = "vagasPcd";
//                            $subtitulo = "Cota: PCD";
//                            break;
//
//                        // Negros e Indígenas
//                        case "Ni":
//                            $numeroVagas = $concurso->get_numVagasNiAprovadas($idConcurso, $item["cargoConcurso"]);
//                            $campo = "classifNi";
//                            $campoVaga = "vagasNi";
//                            $subtitulo = "Cota: Negros e Indígenas";
//                            break;
//
//                        // Hipossuficiente Econômico
//                        case "Hipo":
//                            $numeroVagas = $concurso->get_numVagasHipoAprovadas($idConcurso, $item["cargoConcurso"]);
//                            $campo = "classifHipo";
//                            $campoVaga = "vagasHipo";
//                            $subtitulo = "Cota: Hipossuficiente Econômico";
//                            break;
//                    }
//
//                    # Pega os candidatos desse cargo e dessa cota
//                    $select = "SELECT inscricao,
//                                      nome,
//                                      cargo                                      
//                                 FROM tbcandidato LEFT JOIN tbconcursovagadetalhada ON (tbcandidato.cargo = tbconcursovagadetalhada. cargoConcurso)
//                                WHERE tbcandidato.idConcurso = {$idConcurso}
//                                  AND {$campo} <= tbconcursovagadetalhada.{$campoVaga}
//                                  AND cargo = '{$item["cargoConcurso"]}'
//                             ORDER BY {$campo}";
//
//                    # Passa para o array
//                    $arrayTabela = array_merge($arrayTabela, $pessoal->select($select));
//                }
//            }
//
//            # Ordena por nome
//            usort($arrayTabela, function ($a, $b) {
//                return strcmp($a['nome'], $b['nome']);
//            });
//
//            // 1. Extrai a coluna de nome e conta quantas vezes cada um aparece
//            $nomes = array_column($arrayTabela, 'nome');
//            $contagem = array_count_values($nomes);
//
//            // 2. Filtra o array mantendo apenas os registros cujo nome aparece > 1 vez
//            $duplicados = array_filter($arrayTabela, function ($arrayTabela) use ($contagem) {
//                return $contagem[$arrayTabela['nome']] > 1;
//            });
//
//            # Relatório
//            $relatorio = new Relatorio();
//            $relatorio->set_titulo("Relatório de Candidatos Duplicados");
//            #$relatorio->set_subtitulo($subtitulo);
//            $relatorio->set_conteudo($duplicados);
//            $relatorio->set_label(["Inscrição", "Nome", "Cargo"]);
//            $relatorio->set_align(["center", "left", "left"]);
//            $relatorio->set_funcao([null, "plm", "plm"]);
//
//            #$relatorio->set_classe([null, null, "Candidato"]);
//            #$relatorio->set_metodo([null, null, "exibeCotas"]);
//
//            #$relatorio->set_numGrupo(2);
//            $relatorio->show();
            break;

        ################################################################     

        case "relatorio8":

            /*
             *  Candidatos de um cargo
             */
            if ($parametroCargoCandidato <> "*") {

                # Monta o select
                $select = "SELECT {$campo},
                              inscricao,
                              nome,
                              idLotacao
                         FROM tbcandidato
                        WHERE idConcurso = {$idConcurso}";

                # Pega o candidato de acordo com a cota
                if ($parametroCota <> "Ac") {
                    $select .= " AND {$campo} IS NOT NULL";
                }

                # nome
                if (!is_null($parametroNome)) {
                    $select .= " AND nome LIKE '%{$parametroNome}%'";
                }

                # cargo
                if ($parametroCargoCandidato <> "*") {
                    $select .= " AND cargo = '{$parametroCargoCandidato}'";
                }

                # Ordenação
                if (empty($numeroVagas)) {
                    $select .= " ORDER BY {$campo}";
                } else {
                    $select .= " ORDER BY {$campo} LIMIT {$numeroVagas}";
                }

                $row = $pessoal->select($select);

                # Esvazia o array quando não tem vagas
                if (empty($numeroVagas)) {
                    $row = array();
                }

                # Relatório
                $relatorio = new Relatorio();
                $relatorio->set_titulo("Relatório de Candidatos Aprovados");
                $relatorio->set_tituloLinha2(plm($parametroCargoCandidato));
                $relatorio->set_subtitulo($subtitulo);
                $relatorio->set_conteudo($row);
                $relatorio->set_label(["#", "Inscrição", "Candidato", "Lotação"]);
                $relatorio->set_align(["center", "center", "left", "left"]);
                $relatorio->set_funcao([null, null, "plm"]);

                $relatorio->set_classe([null, null, null, "Pessoal"]);
                $relatorio->set_metodo([null, null, null, "get_nomeLotacao"]);

                $relatorio->show();
            } else {
                #################################################################

                /*
                 *  Todos os cargos
                 */


                # Pega os cargos
                $result = $pessoal->select('SELECT DISTINCT cargoConcurso
                                              FROM tbconcursovagadetalhada
                                          ORDER BY cargoConcurso');

                $primeiro = true;

                foreach ($result as $item) {

                    # No Relatório tem que saber 
                    # o número de vagas de cada cargo
                    # Então tem que definir para cada um
                    # Define os dados de acordo com as cotas
                    switch ($parametroCota) {
                        // Ampla Concorrência
                        case "Ac":
                            $numeroVagas = $concurso->get_numVagasAcAprovadas($idConcurso, $item["cargoConcurso"]);
                            $campo = "classifAc";
                            $subtitulo = "Ampla Concorrência";
                            break;

                        // Pcd
                        case "Pcd":
                            $numeroVagas = $concurso->get_numVagasPcdAprovadas($idConcurso, $item["cargoConcurso"]);
                            $campo = "classifPcd";
                            $subtitulo = "Cota: PCD";
                            break;

                        // Negros e Indígenas
                        case "Ni":
                            $numeroVagas = $concurso->get_numVagasNiAprovadas($idConcurso, $item["cargoConcurso"]);
                            $campo = "classifNi";
                            $subtitulo = "Cota: Negros e Indígenas";
                            break;

                        // Hipossuficiente Econômico
                        case "Hipo":
                            $numeroVagas = $concurso->get_numVagasHipoAprovadas($idConcurso, $item["cargoConcurso"]);
                            $campo = "classifHipo";
                            $subtitulo = "Cota: Hipossuficiente Econômico";
                            break;
                    }

                    # Define o cadastro de reserva, quando se tem o número de vagas
                    if (empty($numeroVagas)) {
                        $numeroVagas = 0;
                        $cadastroReserva = 0;
                        $foraCadastro = 0;
                    } else {
                        $cadastroReserva = $cadReserva * $numeroVagas;
                        $foraCadastro = $numeroVagas + $cadastroReserva;

                        # Monta o select
                        if ($primeiro) {
                            $select = "(SELECT {$campo},";
                            $primeiro = false;
                        } else {
                            $select .= ") UNION (SELECT {$campo},";
                        }

                        $select .= " inscricao,
                              tbcandidato.nome,
                              cargo,
                              concat(IFnull(tblotacao.DIR,''),' - ',IFnull(tblotacao.GER,''),' - ',IFnull(tblotacao.nome,'')) as lotacao
                         FROM tbcandidato LEFT JOIN tblotacao USING(idLotacao)
                        WHERE idConcurso = {$idConcurso}";

                        # Pega o candidato de acordo com a cota
                        if ($parametroCota <> "Ac") {
                            $select .= " AND {$campo} IS NOT NULL";
                        }

                        # nome
                        if (!is_null($parametroNome)) {
                            $select .= " AND tbcandidato.nome LIKE '%{$parametroNome}%'";
                        }

                        # cargo
                        $select .= " AND cargo = '{$item["cargoConcurso"]}'";

                        # Ordenação
                        $select .= " ORDER BY {$campo} LIMIT {$numeroVagas}";
                    }
                }

                $select .= ")";
                $row = $pessoal->select($select);

                # Ordena por lotacao
                usort($row, function ($a, $b) {
                    return strcmp($a['lotacao'], $b['lotacao']);
                });

                # Relatório
                $relatorio = new Relatorio();
                $relatorio->set_titulo("Relatório de Candidatos Aprovados");
                $relatorio->set_subtitulo($subtitulo);
                $relatorio->set_conteudo($row);
                $relatorio->set_label(["#", "Inscrição", "Candidato", "Cargo", "Lotação"]);
                $relatorio->set_align(["center", "center", "left", "left"]);
                $relatorio->set_funcao([null, null, "plm", "plm"]);
                #$relatorio->set_width([10, 15, 20, 30, 20]);

                $relatorio->set_classe([null, null, null, null, "Pessoal"]);
                $relatorio->set_metodo([null, null, null, null, "get_nomeLotacao"]);

                $relatorio->set_numGrupo(4);
                $relatorio->show();
            }
            break;

        ################################################################  

        case "relatorio9":


            /*
             *  Todos os Candidatod de Todos os cargos
             */

            # Define o array da tabela
            $arrayTabela = [];
            $resultadoFinal = [];

            # Pega os cargos
            $result = $pessoal->select('SELECT DISTINCT cargoConcurso
                                          FROM tbconcursovagadetalhada
                                      ORDER BY cargoConcurso');

            # Percorre os cargos
            foreach ($result as $item) {

                foreach ($arrayCotas as $cota) {

                    switch ($cota[0]) {
                        // Ampla Concorrência
                        case "Ac":
                            $numeroVagas = $concurso->get_numVagasAcAprovadas($idConcurso, $item["cargoConcurso"]);
                            $campo = "classifAc";
                            $campoVaga = "vagas";
                            $subtitulo = "Ampla Concorrência";
                            break;

                        // Pcd
                        case "Pcd":
                            $numeroVagas = $concurso->get_numVagasPcdAprovadas($idConcurso, $item["cargoConcurso"]);
                            $campo = "classifPcd";
                            $campoVaga = "vagasPcd";
                            $subtitulo = "Cota: PCD";
                            break;

                        // Negros e Indígenas
                        case "Ni":
                            $numeroVagas = $concurso->get_numVagasNiAprovadas($idConcurso, $item["cargoConcurso"]);
                            $campo = "classifNi";
                            $campoVaga = "vagasNi";
                            $subtitulo = "Cota: Negros e Indígenas";
                            break;

                        // Hipossuficiente Econômico
                        case "Hipo":
                            $numeroVagas = $concurso->get_numVagasHipoAprovadas($idConcurso, $item["cargoConcurso"]);
                            $campo = "classifHipo";
                            $campoVaga = "vagasHipo";
                            $subtitulo = "Cota: Hipossuficiente Econômico";
                            break;
                    }

                    # Pega os candidaatos desse cargo e dessa cota
                    $select = "SELECT inscricao,
                                      nome,
                                      dtNascimento,
                                      idCandidato,
                                      tipoDeficiencia,
                                      CONCAT(cidade,' - ',estado)
                                 FROM tbcandidato LEFT JOIN tbconcursovagadetalhada ON (tbcandidato.cargo = tbconcursovagadetalhada. cargoConcurso)
                                WHERE tbcandidato.idConcurso = {$idConcurso}
                                  AND {$campo} <= tbconcursovagadetalhada.{$campoVaga}
                                  AND cargo = '{$item["cargoConcurso"]}'
                             ORDER BY {$campo}";

                    # Passa para o array
                    $arrayTabela = array_merge($arrayTabela, $pessoal->select($select));
                }
            }

            # Ordena por nome
            usort($arrayTabela, function ($a, $b) {
                return strcmp($a['nome'], $b['nome']);
            });

            $anterior = null;
            # Retira as duplicatas
            foreach ($arrayTabela as $item) {
                # Verifica se é diferente ao anterior
                if ($item['nome'] <> $anterior) {
                    $anterior = $item['nome'];
                    $resultadoFinal[] = $item;
                }
            }

            # Relatório
            $relatorio = new Relatorio();
            $relatorio->set_titulo("Relatório de Candidatos Aprovados");
            #$relatorio->set_subtitulo($subtitulo);
            $relatorio->set_conteudo($resultadoFinal);
            $relatorio->set_label(["Inscrição", "Nome", "Nascimento", "Cota", "Defic", "Cidade"]);
            $relatorio->set_align(["center", "left", "center", "center", "center", "left"]);
            $relatorio->set_funcao([null, "plm", "date_to_php"]);

            $relatorio->set_classe([null, null, null, "Candidato"]);
            $relatorio->set_metodo([null, null, null, "exibeCotas"]);

            #$relatorio->set_numGrupo(2);
            $relatorio->show();

            break;

        ################################################################                    
        case "editaCandidato" :
            br(8);
            aguarde();

            # Informa o $id Servidor
            set_session('idCandidatoPesquisado', $id);

            # Informa a origem
            set_session('origem', "cadastroCandidatosAdm2025.php");

            # Carrega a página específica
            loadPage('cadastroCandidatosAdm2025EditaProva.php');
            break;

        ################################################################                    
        case "editaCandidatoDuplicado" :
            br(8);
            aguarde();

            # Informa o $id Servidor
            set_session('idCandidatoPesquisado', $id);

            # Informa a origem
            set_session('origem', "cadastroCandidatosAdm2025.php?fase=duplicados");

            # Carrega a página específica
            loadPage('cadastroCandidatosAdm2025EditaProva.php');
            break;

        ################################################################

        case "exibeVagas" :

            br();
            $candidato = new Candidato();
            $candidato->exibeTabelaVagasCargo();
            break;

        ################################################################      
    }
    $grid->fechaColuna();
    $grid->fechaGrid();

    $page->terminaPagina();
} else {
    loadPage("../../areaServidor/sistema/login.php");
}
    