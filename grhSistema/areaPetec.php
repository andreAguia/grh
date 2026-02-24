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
    $petec = new Petec();

    # Verifica a fase do programa
    $fase = get('fase', "geral");
    $portaria = get('portaria', "geral");

    # pega o id (se tiver)
    $id = soNumeros(get('id'));

    # Pega os parâmetros
    $parametroLotacao = post('parametroLotacao', get_session('parametroLotacao', 66));
    $parametroInscricao = post('parametroInscricao', get_session('parametroInscricao', "Todos"));
    $parametroMarcador = post('parametroMarcador', get_session('parametroMarcador', 4));

    # Joga os parâmetros par as sessions
    set_session('parametroLotacao', $parametroLotacao);
    set_session('parametroInscricao', $parametroInscricao);
    set_session('parametroMarcador', $parametroMarcador);

    # Label da Lotação
    if (is_numeric($parametroLotacao)) {
        $labelLotação = $pessoal->get_nomeLotacao2($parametroLotacao);
    } else { # senão é uma diretoria genérica
        $labelLotação = $parametroLotacao;
    }

    # Verifica se veio menu grh e registra o acesso no log
    $grh = get('grh', false);
    if ($grh) {
        # Grava no log a atividade
        $atividade = "Visualizou a área de Petec";
        $data = date("Y-m-d H:i:s");
        $intra->registraLog($idUsuario, $data, $atividade, null, null, 7);
    } else {
        if ($fase == "geral" OR $fase == "481" OR $fase == "473" OR $fase == "418") {

            # Grava no log a atividade
            $atividade = "Pesquisou na área de Petec<br/>Lotação: {$labelLotação}<br/>Inscrição: {$parametroInscricao}<br/>Portaria: {$fase}";
            $data = date("Y-m-d H:i:s");
            $intra->registraLog($idUsuario, $data, $atividade, null, null, 7);
        }
    }

    # Começa uma nova página
    $page = new Page();
    $page->iniciaPagina();

    # Cabeçalho da Página
    if ($fase <> "relatorio") {
        AreaServidor::cabecalho();
    }

################################################################

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

    # Geral
    $botao1 = new Link("Geral", "?fase=geral");
    if ($fase == "geral") {
        $botao1->set_class('button');
    } else {
        $botao1->set_class('hollow button');
    }
    $menu1->add_link($botao1, "right");

    # Portaria 418/25
    $botao1 = new Link("Portaria 418/25", "?fase=418");
    if ($fase == "418") {
        $botao1->set_class('button');
    } else {
        $botao1->set_class('hollow button');
    }
    $menu1->add_link($botao1, "right");

    # Portaria 473/25
    $botao1 = new Link("Portaria 473/25", "?fase=473");
    if ($fase == "473") {
        $botao1->set_class('button');
    } else {
        $botao1->set_class('hollow button');
    }
    $menu1->add_link($botao1, "right");

    # Portaria 481/25
    $botao1 = new Link("Portaria 481/25", "?fase=481");
    if ($fase == "481") {
        $botao1->set_class('button');
    } else {
        $botao1->set_class('hollow button');
    }
    $menu1->add_link($botao1, "right");

    # Importar
    $botaoImportar = new Link("Importar", "importaPetec.php");
    $botaoImportar->set_class('success button');
    $botaoImportar->set_title('Faz a importação do petec');
    if (Verifica::acesso($idUsuario, 1)) {
        $menu1->add_link($botaoImportar, "right");
    }

    # Relatórios
    $imagem = new Imagem(PASTA_FIGURAS . 'print.png', null, 15, 15);
    $botaoRel = new Button();
    $botaoRel->set_title("Relatório dessa pesquisa");
    $botaoRel->set_url("?fase=relatorio");
    $botaoRel->set_target("_blank");
    $botaoRel->set_imagem($imagem);
    #$menu1->add_link($botaoRel, "right");

    $menu1->show();

    # Formulário de Pesquisa
    $form = new Form("?fase={$fase}");

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

    $controle = new Input('parametroLotacao', 'combo', 'Lotação:', 1);
    $controle->set_size(30);
    $controle->set_title('Filtra por Lotação');
    $controle->set_array($result);
    $controle->set_valor($parametroLotacao);
    $controle->set_onChange('formPadrao.submit();');
    $controle->set_linha(1);
    if ($fase <> "geral") {
        $controle->set_col(8);
    } else {
        $controle->set_col(12);
    }
    $form->add_item($controle);

    /*
     *  Inscricão
     */

    $controle = new Input('parametroInscricao', 'combo', "Inscrição", 1);
    $controle->set_size(30);
    $controle->set_title('Filtra por Lotação');
    $controle->set_array(["Todos", "Inscritos", "NÃO Inscritos"]);
    $controle->set_valor($parametroInscricao);
    $controle->set_onChange('formPadrao.submit();');
    $controle->set_linha(1);
    $controle->set_col(4);

    if ($fase <> "geral") {
        $form->add_item($controle);
    }

    $form->show();

    $grid->fechaColuna();

    ##############

    $grid->abreColuna(12, 12, 4);

    # Link para editar o servidor
    $linkservidor = "?fase=editaServidor&portaria={$fase}";

    # Quadro de Inscritos
    $petec->exibeQuadroInscritosPetec($parametroLotacao);

    # Quadro das Portarias
    $petec->exibeQuadroPortariasPetec();

    $grid->fechaColuna();

    ##############

    $grid->abreColuna(12, 12, 8);

    switch ($fase) {

        #######################################################

        /*
         * Geral
         */

        case "geral" :

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
            #$width[] = 28;

            $petecArray = $formacao->get_arrayMarcadores("Petec");

            foreach ($petecArray as $item) {
                $label[] = $item[1];
                $align[] = "center";
                $classe[] = "Petec";
                $metodo[] = "somatorioHoras{$item[0]}"; // Gambiarra para fazer funcionar. Depois eu vejo um modo melhor de fazer isso...
                #$width[] = 24;
            }

            $label[] = "Editar";

            $tabela = new Tabela();
            $tabela->set_titulo("Análise Da Entrega de Certificados - Geral");
            $tabela->set_subtitulo($labelLotação);
            $tabela->set_conteudo($result2);

            $tabela->set_label($label);
            $tabela->set_align($align);
            #$tabela->set_width($width);

            $tabela->set_classe($classe);
            $tabela->set_metodo($metodo);
            $tabela->set_bordaInterna(true);

            # Botão Editar
            $botao = new Link(null, "{$linkservidor}&id=", 'Acessa o servidor');
            $botao->set_imagem(PASTA_FIGURAS . 'bullet_edit.png', 20, 20);

            # Coloca o objeto link na tabela			
            $tabela->set_link([null, null, null, null, $botao]);
            $tabela->show();

            break;

        #######################################################
        /*
         * Petec - Portaria 418/25
         */

        case "418" :

            ## Sem Petec

            $novoArray = array();

            $select2 = "SELECT tbservidor.idServidor
                         FROM tbservidor LEFT JOIN tbpessoa USING (idPessoa)
                                         LEFT JOIN tbperfil USING (idPerfil)
                                              JOIN tbhistlot USING (idServidor)
                                              JOIN tblotacao ON (tbhistlot.lotacao=tblotacao.idLotacao)
                        WHERE tbhistlot.data = (select max(data) from tbhistlot where tbhistlot.idServidor = tbservidor.idServidor)
                        AND tbperfil.tipo <> 'Outros'
                        AND situacao = 1";

            # Verifica se tem filtro por lotação
            if ($parametroLotacao <> "Todos") {  // senão verifica o da classe
                if (is_numeric($parametroLotacao)) {
                    $select2 .= " AND tblotacao.idlotacao = {$parametroLotacao}";
                } else { # senão é uma diretoria genérica
                    $select2 .= " AND tblotacao.DIR = '{$parametroLotacao}'";
                }
            }

            # Inscrição
            if ($parametroInscricao <> "Todos") {
                if ($parametroInscricao == "Inscritos") {
                    $select2 .= " AND tbservidor.petec1 = 's'";
                } else {
                    $select2 .= " AND (tbservidor.petec1 <> 's' OR tbservidor.petec1 IS NULL)";
                }
            }

            $select2 .= " ORDER BY tbpessoa.nome";

            $result2 = $pessoal->select($select2);

            # Percorre o array
            foreach ($result2 as $item) {
                if (!$petec->temPetec($item["idServidor"], 4)) {
                    $novoArray[] = [$item["idServidor"], $item["idServidor"], $item["idServidor"], $item["idServidor"], $item["idServidor"], $item["idServidor"]];
                }
            }

            $tabela = new Tabela();
            $tabela->set_titulo("Portaria {$fase}/25");
            $tabela->set_subtitulo('NÃO Entregaram Certificados');
            $tabela->set_label(["IdFuncional<br/>Matrícula", "Inscrito?", "Servidor", "Perfil", "Editar"]);
            $tabela->set_width([15, 15, 60, 10, 10]);
            $tabela->set_conteudo($novoArray);
            $tabela->set_align(["center", "center", "left"]);
            $tabela->set_classe(['pessoal', "Petec", "pessoal", "pessoal"]);
            $tabela->set_metodo(["get_idFuncionalEMatricula", "exibeIncricaoPetec1", "get_nomeECargoELotacao", "get_perfil"]);

            $tabela->set_rowspan(0);
            $tabela->set_grupoCorColuna(0);

            # Botão Editar
            $botao = new Link(null, "{$linkservidor}&id=", 'Acessa o servidor');
            $botao->set_imagem(PASTA_FIGURAS . 'bullet_edit.png', 20, 20);

            # Coloca o objeto link na tabela			
            $tabela->set_link([null, null, null, null, $botao]);
            $tabela->show();

            ### Com Petec
            # Monta o select
            $select = "SELECT tbservidor.idServidor,
                              tbservidor.idServidor,
                              tbservidor.idServidor,
                              idFormacao,
                              idFormacao
                         FROM tbformacao LEFT JOIN tbpessoa USING (idPessoa)
                                              JOIN tbservidor USING (idPessoa)
                                         LEFT JOIN tbperfil USING (idPerfil)
                                              JOIN tbescolaridade USING (idEscolaridade)
                                              JOIN tbhistlot USING (idServidor)
                                              JOIN tblotacao ON (tbhistlot.lotacao=tblotacao.idLotacao)
                        WHERE tbhistlot.data = (select max(data) from tbhistlot where tbhistlot.idServidor = tbservidor.idServidor)
                        AND tbperfil.tipo <> 'Outros'
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
                    $select .= " AND tblotacao.idlotacao = {$parametroLotacao}";
                } else { # senão é uma diretoria genérica
                    $select .= " AND tblotacao.DIR = '{$parametroLotacao}'";
                }
            }

            # Inscrição
            if ($parametroInscricao <> "Todos") {
                if ($parametroInscricao == "Inscritos") {
                    $select .= " AND tbservidor.petec1 = 's'";
                } else {
                    $select .= " AND (tbservidor.petec1 <> 's' OR tbservidor.petec1 IS NULL)";
                }
            }

            $select .= " ORDER BY tbpessoa.nome, tbformacao.anoTerm";

            $result = $pessoal->select($select);

            $tabela = new Tabela();
            $tabela->set_titulo("Portaria {$fase}/25");
            $tabela->set_subtitulo('Entregaram Certficados');
            $tabela->set_label(["IdFuncional<br/>Matrícula", "Inscrito?", "Servidor", "Curso", "Certificado"]);
            $tabela->set_width([15, 15, 25, 35, 5]);
            $tabela->set_conteudo($result);
            $tabela->set_align(["center", "center", "left", "left"]);
            $tabela->set_classe(["pessoal", "Petec", "pessoal", "Formacao", "Formacao"]);
            $tabela->set_metodo(["get_idFuncionalEMatricula", "exibeIncricaoPetec1", "get_nomeECargoELotacao", "exibeCurso", "exibeCertificado"]);

            $tabela->set_rowspan(2);
            $tabela->set_grupoCorColuna(2);

            $tabela->set_idCampo('idServidor');
            $tabela->set_editar($linkservidor);
            $tabela->show();

            break;

        ##############
        /*
         * Petec - Portaria 473/25
         */

        case "473" :

            ### Sem Petec

            $novoArray = array();

            $select2 = "SELECT tbservidor.idServidor
                         FROM tbservidor LEFT JOIN tbpessoa USING (idPessoa)
                                         LEFT JOIN tbperfil USING (idPerfil)   
                                              JOIN tbhistlot USING (idServidor)
                                              JOIN tblotacao ON (tbhistlot.lotacao=tblotacao.idLotacao)
                        WHERE tbhistlot.data = (select max(data) from tbhistlot where tbhistlot.idServidor = tbservidor.idServidor)
                        AND tbperfil.tipo <> 'Outros'
                        AND situacao = 1";

            # Verifica se tem filtro por lotação
            if ($parametroLotacao <> "Todos") {  // senão verifica o da classe
                if (is_numeric($parametroLotacao)) {
                    $select2 .= " AND (tblotacao.idlotacao = {$parametroLotacao})";
                } else { # senão é uma diretoria genérica
                    $select2 .= " AND (tblotacao.DIR = '{$parametroLotacao}')";
                }
            }

            # Inscrição
            if ($parametroInscricao <> "Todos") {
                if ($parametroInscricao == "Inscritos") {
                    $select2 .= " AND tbservidor.petec1 = 's'";
                } else {
                    $select2 .= " AND (tbservidor.petec1 <> 's' OR tbservidor.petec1 IS NULL)";
                }
            }

            $select2 .= " ORDER BY tbpessoa.nome";
            $result2 = $pessoal->select($select2);

            # Percorre o array
            foreach ($result2 as $item) {
                if (!$petec->temPetec($item["idServidor"], 5)) {
                    $novoArray[] = [$item["idServidor"], $item["idServidor"], $item["idServidor"], $item["idServidor"], $item["idServidor"], $item["idServidor"], $item["idServidor"], $item["idServidor"]];
                }
            }

            $tabela = new Tabela();
            $tabela->set_titulo("Portaria {$fase}/25");
            $tabela->set_subtitulo('NÃO Entregaram Certficados');
            #$tabela->set_subtitulo('Filtro: '.$relatorioParametro);
            $tabela->set_label(["IdFuncional<br/>Matrícula", "Inscrito?", "Servidor", "Perfil", "Editar"]);
            $tabela->set_width([15, 15, 60, 10, 10]);
            $tabela->set_conteudo($novoArray);
            $tabela->set_align(["center", "center", "left"]);
            $tabela->set_classe(['pessoal', "Petec", "pessoal", "pessoal"]);
            $tabela->set_metodo(["get_idFuncionalEMatricula", "exibeIncricaoPetec1", "get_nomeECargoELotacao", "get_perfil"]);

            $tabela->set_rowspan(0);
            $tabela->set_grupoCorColuna(0);

            # Botão Editar
            $botao = new Link(null, "{$linkservidor}&id=", 'Acessa o servidor');
            $botao->set_imagem(PASTA_FIGURAS . 'bullet_edit.png', 20, 20);

            # Coloca o objeto link na tabela			
            $tabela->set_link([null, null, null, null, $botao]);
            $tabela->show();

            ### Com PETEC
            # Monta o select
            $select = "SELECT tbservidor.idServidor,
                              tbservidor.idServidor,
                              tbservidor.idServidor,
                              idFormacao,
                              idFormacao
                         FROM tbformacao LEFT JOIN tbpessoa USING (idPessoa)
                                              JOIN tbservidor USING (idPessoa)
                                         LEFT JOIN tbperfil USING (idPerfil)
                                              JOIN tbescolaridade USING (idEscolaridade)
                                              JOIN tbhistlot USING (idServidor)
                                              JOIN tblotacao ON (tbhistlot.lotacao=tblotacao.idLotacao)
                        WHERE tbhistlot.data = (select max(data) from tbhistlot where tbhistlot.idServidor = tbservidor.idServidor)
                        AND tbperfil.tipo <> 'Outros'
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

            # Inscrição
            if ($parametroInscricao <> "Todos") {
                if ($parametroInscricao == "Inscritos") {
                    $select .= " AND tbservidor.petec1 = 's'";
                } else {
                    $select .= " AND (tbservidor.petec1 <> 's' OR tbservidor.petec1 IS NULL)";
                }
            }

            $select .= " ORDER BY tbpessoa.nome, tbformacao.anoTerm";

            $result = $pessoal->select($select);

            $tabela = new Tabela();
            $tabela->set_titulo("Portaria {$fase}/25");
            $tabela->set_subtitulo('Entregaram Certficados');
            $tabela->set_label(["IdFuncional<br/>Matrícula", "Inscrito?", "Servidor", "Curso", "Certificado"]);
            $tabela->set_width([15, 15, 25, 35, 5]);
            $tabela->set_conteudo($result);
            $tabela->set_align(["center", "center", "left", "left"]);
            $tabela->set_classe(["pessoal", "Petec", "pessoal", "Formacao", "Formacao"]);
            $tabela->set_metodo(["get_idFuncionalEMatricula", "exibeIncricaoPetec1", "get_nomeECargoELotacao", "exibeCurso", "exibeCertificado"]);

            $tabela->set_rowspan(2);
            $tabela->set_grupoCorColuna(2);

            $tabela->set_idCampo('idServidor');
            $tabela->set_editar($linkservidor);
            $tabela->show();

            break;

        ##############
        /*
         * Petec - Portaria 481/25
         */

        case "481":

            ## Sem Petec

            $novoArray = array();

            $select2 = "SELECT tbservidor.idServidor
                         FROM tbservidor LEFT JOIN tbpessoa USING (idPessoa)
                                         LEFT JOIN tbperfil USING (idPerfil)
                                              JOIN tbhistlot USING (idServidor)
                                              JOIN tblotacao ON (tbhistlot.lotacao=tblotacao.idLotacao)
                        WHERE tbhistlot.data = (select max(data) from tbhistlot where tbhistlot.idServidor = tbservidor.idServidor)
                        AND tbperfil.tipo <> 'Outros'
                        AND situacao = 1";

            # Verifica se tem filtro por lotação
            if ($parametroLotacao <> "Todos") {  // senão verifica o da classe
                if (is_numeric($parametroLotacao)) {
                    $select2 .= " AND (tblotacao.idlotacao = {$parametroLotacao})";
                } else { # senão é uma diretoria genérica
                    $select2 .= " AND (tblotacao.DIR = '{$parametroLotacao}')";
                }
            }

            # Inscrição
            if ($parametroInscricao <> "Todos") {
                if ($parametroInscricao == "Inscritos") {
                    $select2 .= " AND tbservidor.petec2 = 's'";
                } else {
                    $select2 .= " AND (tbservidor.petec2 <> 's' OR tbservidor.petec2 IS NULL)";
                }
            }

            $select2 .= " ORDER BY tbpessoa.nome";
            $result2 = $pessoal->select($select2);

            # Percorre o array
            foreach ($result2 as $item) {
                if (!$petec->temPetec($item["idServidor"], 6)) {
                    $novoArray[] = [$item["idServidor"], $item["idServidor"], $item["idServidor"], $item["idServidor"], $item["idServidor"], $item["idServidor"], $item["idServidor"], $item["idServidor"]];
                }
            }

            $tabela = new Tabela();
            $tabela->set_titulo("Portaria {$fase}/25");
            $tabela->set_subtitulo('NÃO Entregaram Certficados');
            #$tabela->set_subtitulo('Filtro: '.$relatorioParametro);
            $tabela->set_label(["IdFuncional<br/>Matrícula", "Inscrito?", "Servidor", "Perfil", "Editar"]);
            $tabela->set_width([15, 15, 60, 10, 10]);
            $tabela->set_conteudo($novoArray);
            $tabela->set_align(["center", "center", "left"]);
            $tabela->set_classe(['pessoal', "Petec", "pessoal", "pessoal"]);
            $tabela->set_metodo(["get_idFuncionalEMatricula", "exibeIncricaoPetec2", "get_nomeECargoELotacao", "get_perfil"]);
            $tabela->set_rowspan(0);
            $tabela->set_grupoCorColuna(0);

            # Botão Editar
            $botao = new Link(null, "{$linkservidor}&id=", 'Acessa o servidor');
            $botao->set_imagem(PASTA_FIGURAS . 'bullet_edit.png', 20, 20);

            # Coloca o objeto link na tabela			
            $tabela->set_link([null, null, null, null, $botao]);
            $tabela->show();

            ## Com Petec
            # Monta o select
            $select = "SELECT tbservidor.idServidor,
                              tbservidor.idServidor,
                              tbservidor.idServidor,
                              idFormacao,
                              idFormacao
                         FROM tbformacao LEFT JOIN tbpessoa USING (idPessoa)
                                              JOIN tbservidor USING (idPessoa)
                                         LEFT JOIN tbperfil USING (idPerfil)     
                                              JOIN tbescolaridade USING (idEscolaridade)
                                              JOIN tbhistlot USING (idServidor)
                                              JOIN tblotacao ON (tbhistlot.lotacao=tblotacao.idLotacao)
                        WHERE tbhistlot.data = (select max(data) from tbhistlot where tbhistlot.idServidor = tbservidor.idServidor)
                        AND tbperfil.tipo <> 'Outros'
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

            # Inscrição
            if ($parametroInscricao <> "Todos") {
                if ($parametroInscricao == "Inscritos") {
                    $select .= " AND tbservidor.petec2 = 's'";
                } else {
                    $select .= " AND (tbservidor.petec2 <> 's' OR tbservidor.petec2 IS NULL)";
                }
            }

            $select .= " ORDER BY tbpessoa.nome, tbformacao.anoTerm";

            $result = $pessoal->select($select);

            $tabela = new Tabela();
            $tabela->set_titulo("Portaria {$fase}/25");
            $tabela->set_subtitulo('Entregaram Certficados');
            $tabela->set_label(["IdFuncional<br/>Matrícula", "Inscrito?", "Servidor", "Curso", "Certificado"]);
            $tabela->set_width([15, 15, 25, 35, 5]);
            $tabela->set_conteudo($result);
            $tabela->set_align(["center", "center", "left", "left"]);
            $tabela->set_classe(["pessoal", "Petec", "pessoal", "Formacao", "Formacao"]);
            $tabela->set_metodo(["get_idFuncionalEMatricula", "exibeIncricaoPetec2", "get_nomeECargoELotacao", "exibeCurso", "exibeCertificado"]);

            $tabela->set_rowspan(2);
            $tabela->set_grupoCorColuna(2);

            $tabela->set_idCampo('idServidor');
            $tabela->set_editar($linkservidor);
            $tabela->show();

            break;

        ################################################################

        case "editaServidor" :
            br(8);
            aguarde();

            # Informa o $id Servidor
            set_session('idServidorPesquisado', $id);

            # Informa a origem
            set_session('origem', "areaPetec.php?fase={$portaria}");

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
    $grid->fechaColuna();
    $grid->fechaGrid();

    $page->terminaPagina();
} else {
    loadPage("../../areaServidor/sistema/login.php");
}


