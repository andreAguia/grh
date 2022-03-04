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
$acesso = Verifica::acesso($idUsuario, 2);

if ($acesso) {
    # Conecta ao Banco de Dados
    $intra = new Intra();
    $pessoal = new Pessoal();

    # Verifica a fase do programa
    $fase = get('fase');

    # Verifica se veio menu grh e registra o acesso no log
    $grh = get('grh', false);
    if ($grh) {
        # Grava no log a atividade
        $atividade = "Visualizou a área de controle de vacina";
        $data = date("Y-m-d H:i:s");
        $intra->registraLog($idUsuario, $data, $atividade, null, null, 7);
    }

    # pega o id (se tiver)
    $id = soNumeros(get('id'));

    # Pega os parâmetros  
    $parametroLotacao = post('parametroLotacao', get_session('parametroLotacao', $pessoal->get_idLotacao($intra->get_idServidor($idUsuario))));
    $parametroVacinado = post('parametroVacinado', get_session('parametroVacinado', 'Sim'));
    $parametroJustificativa = post('parametroJustificativa', get_session('parametroJustificativa', 'Todos'));

    # Joga os parâmetros par as sessions
    set_session('parametroLotacao', $parametroLotacao);
    set_session('parametroVacinado', $parametroVacinado);
    set_session('parametroJustificativa', $parametroJustificativa);

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

            loadPage('?fase=exibeLista');
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

            # Cadastro de tipos de vacinas
            $botaoTipo = new Button("Tipos de Vacinas", "cadastroTipoVacina.php?origem=1");
            $botaoTipo->set_title("Cadastro dos Tipo de Vacinas");
            $menu1->add_link($botaoTipo, "right");

            # Relatórios
            $imagem = new Imagem(PASTA_FIGURAS . 'print.png', null, 15, 15);
            $botaoRel = new Button();
            $botaoRel->set_title("Relatório dessa pesquisa");
            #$botaoRel->set_url("../grhRelatorios/vacina.geral.php");
            $botaoRel->set_url("?fase=relatorio");
            $botaoRel->set_target("_blank");
            $botaoRel->set_imagem($imagem);
            $menu1->add_link($botaoRel, "right");

            $menu1->show();

            # Formulário de Pesquisa
            $form = new Form('?');

            # Lotação
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
            $controle->set_col(6);
            $form->add_item($controle);

            # Vacinado
            $controle = new Input('parametroVacinado', 'combo', 'Entregou Comprovante?', 1);
            $controle->set_size(30);
            $controle->set_title('Filtra Vacinados /  não Vacinados');
            $controle->set_array([["Sim", "Sim"], ["Não", "Não"]]);
            $controle->set_valor($parametroVacinado);
            $controle->set_onChange('formPadrao.submit();');
            $controle->set_linha(1);
            $controle->set_col(2);
            $form->add_item($controle);

            if ($parametroVacinado == "Não") {
                # Com justificativa
                $controle = new Input('parametroJustificativa', 'combo', 'Entregou Justificativa?', 1);
                $controle->set_size(30);
                $controle->set_title('Filtra Justificado /  não Justificado');
                $controle->set_array([["Todos", "Todos"], ["Sim", "Sim"], ["Não", "Não"]]);
                $controle->set_valor($parametroJustificativa);
                $controle->set_onChange('formPadrao.submit();');
                $controle->set_linha(1);
                $controle->set_col(2);
                $form->add_item($controle);
            }

            $form->show();

            $grid->fechaColuna();
            $grid->abreColuna(3);

            $vacina = new Vacina();
            $vacina->exibeQuadroVacinas($parametroLotacao);
            if ($parametroVacinado == "Sim") {
                $vacina->exibeQuadroQuantidadeDoses($parametroLotacao);
                $vacina->exibeQuadroDosesPorVacina($parametroLotacao);
            }

            $grid->fechaColuna();
            $grid->abreColuna(9);

            ##############
            /*
             * Vacinados
             */

            if ($parametroVacinado == "Sim") {

                $select = "SELECT tbservidor.idServidor,
                                  tbservidor.idServidor                    
                         FROM tbservidor JOIN tbpessoa USING (idPessoa)
                                         JOIN tbhistlot USING (idServidor)
                                         JOIN tblotacao ON (tbhistlot.lotacao=tblotacao.idLotacao)                         
                                         
                        WHERE situacao = 1
                          AND tbhistlot.data = (select max(data) from tbhistlot where tbhistlot.idServidor = tbservidor.idServidor)
                          AND tbservidor.idServidor IN (SELECT idServidor FROM tbvacina) ";

                # Verifica se tem filtro por lotação
                if ($parametroLotacao <> "Todos") {
                    if (is_numeric($parametroLotacao)) {
                        $select .= " AND (tblotacao.idlotacao = {$parametroLotacao})";
                    } else { # senão é uma diretoria genérica
                        $select .= " AND (tblotacao.DIR = '{$parametroLotacao}')";
                    }
                }

                $select .= "ORDER BY tbpessoa.nome";

                $result = $pessoal->select($select);

                $tabela = new Tabela();
                if ($parametroLotacao <> "Todos") {
                    if (is_numeric($parametroLotacao)) {
                        $tabela->set_titulo("Servidores que Entregaram Comprovante de Vacinação - {$pessoal->get_nomeLotacao($parametroLotacao)}");
                    } else {
                        $tabela->set_titulo("Servidores que Entregaram Comprovante de Vacinação - {$parametroLotacao}");
                    }
                } else {
                    $tabela->set_titulo('Servidores que Entregaram Comprovante de Vacinação');
                }
                $tabela->set_label(["Servidor", "Vacinas"]);
                $tabela->set_width([50, 45]);
                $tabela->set_conteudo($result);
                $tabela->set_align(["left", "left"]);
                $tabela->set_classe(["pessoal", "Vacina"]);
                $tabela->set_metodo(["get_nomeECargoELotacao", "exibeVacinas"]);
                $tabela->set_idCampo('idServidor');
                $tabela->set_editar('?fase=editaServidor');
                $tabela->show();
            } else {
                /*
                 * Não Vacinados
                 */
                # Avisa da retirada dos servidores cedidos do relatório
                if ($parametroLotacao == "Todos" OR $parametroLotacao == "Reitoria") {
                    callout("A pedido do Reitor, os servidores cedidos aparecem abaixo, mas não aparecerão no relatório.");
                }

                $select = "SELECT tbservidor.idServidor,
                                  tbservidor.idServidor,
                                  tbservidor.justificativaVacina
                             FROM tbservidor JOIN tbpessoa USING (idPessoa)
                                             JOIN tbhistlot USING (idServidor)
                                             JOIN tblotacao ON (tbhistlot.lotacao=tblotacao.idLotacao)
                        WHERE situacao = 1
                          AND tbhistlot.data = (select max(data) from tbhistlot where tbhistlot.idServidor = tbservidor.idServidor)
                          AND tbservidor.idServidor NOT IN (SELECT idServidor FROM tbvacina)";

                # Verifica se tem filtro por lotação
                if ($parametroLotacao <> "Todos") {
                    if (is_numeric($parametroLotacao)) {
                        $select .= " AND (tblotacao.idlotacao = {$parametroLotacao})";
                    } else { # senão é uma diretoria genérica
                        $select .= " AND (tblotacao.DIR = '{$parametroLotacao}')";
                    }
                }

                if ($parametroJustificativa == "Sim") {
                    $select .= " AND tbservidor.justificativaVacina <> '' ";
                }

                if ($parametroJustificativa == "Não") {
                    $select .= " AND (tbservidor.justificativaVacina = '' OR tbservidor.justificativaVacina is null)";
                }

                $select .= "ORDER BY tbpessoa.nome";

                $result = $pessoal->select($select);

                $tabela = new Tabela();
                if ($parametroLotacao <> "Todos") {
                    if (is_numeric($parametroLotacao)) {
                        $tabela->set_titulo("Servidores que NÃO Entregaram Comprovante de Vacinação - {$pessoal->get_nomeLotacao($parametroLotacao)}");
                    } else {
                        $tabela->set_titulo("Servidores que NÃO Entregaram Comprovante de Vacinação - {$parametroLotacao}");
                    }
                } else {
                    $tabela->set_titulo('Servidores que NÃO Entregaram Comprovante de Vacinação');
                }
                $tabela->set_label(["Servidor", "E-mail", "Justificativa"]);
                $tabela->set_width([30, 20, 45]);
                $tabela->set_conteudo($result);
                $tabela->set_align(["left", "center", "left"]);
                $tabela->set_classe(["pessoal", "pessoal"]);
                $tabela->set_metodo(["get_nomeECargoELotacao", "get_emails"]);
                $tabela->set_idCampo('idServidor');
                $tabela->set_editar('?fase=editaServidor');
                $tabela->show();
            }

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
            set_session('origem', 'areaVacina.php');

            # Carrega a página específica
            loadPage('servidorVacina.php');
            break;

        ################################################################
        # Relatório
        case "relatorio" :

            # inicia o subtítulo
            $subTitulo = null;

            ######

            /*
             * Vacinados
             */

            # Verifica se é ou não de vacinados
            if ($parametroVacinado == "Sim") {


                $select = "SELECT tbpessoa.nome,
                                  tbservidor.idServidor,
                                  concat(IFnull(tblotacao.UADM,''),' - ',IFnull(tblotacao.DIR,''),' - ',IFnull(tblotacao.GER,''),' - ',IFnull(tblotacao.nome,'')) lotacao,
                                  tbservidor.idServidor
                         FROM tbservidor JOIN tbpessoa USING (idPessoa)
                                         JOIN tbhistlot USING (idServidor)
                                         JOIN tblotacao ON (tbhistlot.lotacao=tblotacao.idLotacao)                         
                                         
                        WHERE situacao = 1
                          AND tbhistlot.data = (select max(data) from tbhistlot where tbhistlot.idServidor = tbservidor.idServidor)
                          AND tbservidor.idServidor IN (SELECT idServidor FROM tbvacina) ";

                # Verifica se tem filtro por lotação
                if ($parametroLotacao <> "Todos") {
                    if (is_numeric($parametroLotacao)) {
                        $select .= " AND (tblotacao.idlotacao = {$parametroLotacao})";
                    } else { # senão é uma diretoria genérica
                        $select .= " AND (tblotacao.DIR = '{$parametroLotacao}')";
                    }
                }

                $select .= "ORDER BY lotacao, tbpessoa.nome";

                $result = $pessoal->select($select);

                $relatorio = new Relatorio();
                $relatorio->set_titulo('Servidores que Entregaram Comprovante de Vacinação');

                if (!is_numeric($parametroLotacao) AND $parametroLotacao <> "Todos") {
                    $relatorio->set_tituloLinha3($parametroLotacao);
                }
                $relatorio->set_label(["Nome", "Cargo", "Lotação", "Vacinas"]);
                $relatorio->set_width([30, 30, 0, 40]);
                $relatorio->set_conteudo($result);
                $relatorio->set_align(["left", "left", "left", "left"]);
                $relatorio->set_classe([null, "pessoal", null, "Vacina"]);
                $relatorio->set_metodo([null, "get_cargo", null, "exibeVacinas"]);
                $relatorio->set_bordaInterna(true);
                $relatorio->set_numGrupo(2);
                $relatorio->show();
            }

            ######

            /*
             * Não Vacinados
             */

            if ($parametroVacinado == "Não") {
                $relatorio = new Relatorio();

                $select = "SELECT tbpessoa.nome,
                                  tbservidor.idServidor,
                                  concat(IFnull(tblotacao.UADM,''),' - ',IFnull(tblotacao.DIR,''),' - ',IFnull(tblotacao.GER,''),' - ',IFnull(tblotacao.nome,'')) lotacao
                             FROM tbservidor JOIN tbpessoa USING (idPessoa)
                                             JOIN tbhistlot USING (idServidor)
                                             JOIN tblotacao ON (tbhistlot.lotacao=tblotacao.idLotacao)
                        WHERE situacao = 1
                          AND tbhistlot.data = (select max(data) from tbhistlot where tbhistlot.idServidor = tbservidor.idServidor)
                          AND tbservidor.idServidor NOT IN (SELECT idServidor FROM tbvacina)";

                # Verifica se tem filtro por lotação
                if ($parametroLotacao <> "Todos") {
                    if (is_numeric($parametroLotacao)) {
                        $select .= " AND (tblotacao.idlotacao = {$parametroLotacao})";
                    } else { # senão é uma diretoria genérica
                        $select .= " AND (tblotacao.DIR = '{$parametroLotacao}')";
                        $select .= " AND (tblotacao.idlotacao <> 113)"; // Retira os cedidos da Uenf (as pedido do Reitor)
                    }
                } else {
                    $select .= " AND (tblotacao.idlotacao <> 113)"; // Retira os cedidos da Uenf (as pedido do Reitor)
                }

                if ($parametroJustificativa == "Sim") {
                    $select .= " AND tbservidor.justificativaVacina <> '' ";
                }

                if ($parametroJustificativa == "Não") {
                    $select .= " AND (tbservidor.justificativaVacina = '' OR tbservidor.justificativaVacina is null)";
                    $relatorio->set_tituloLinha2("Lista de Não Aptos - Portaria Reitoria 115/2022");
                }

                $select .= "ORDER BY lotacao, tbpessoa.nome";

                $result = $pessoal->select($select);

                
                $relatorio->set_titulo('Servidores que NÃO Entregaram Comprovante de Vacinação');

                if (!is_numeric($parametroLotacao) AND $parametroLotacao <> "Todos") {
                    $relatorio->set_tituloLinha3($parametroLotacao);
                }

                $relatorio->set_label(["Servidor", "Cargo", "Lotação"]);
                #$relatorio->set_width([30, 30, 0, 40]);
                $relatorio->set_conteudo($result);
                $relatorio->set_align(["left", "left", "left", "left"]);
                $relatorio->set_classe([null, "pessoal"]);
                $relatorio->set_metodo([null, "get_cargo"]);
                $relatorio->set_numGrupo(2);
                #$relatorio->set_bordaInterna(true);
                $relatorio->show();
            }
            break;
    }

    $page->terminaPagina();
} else {
    loadPage("../../areaServidor/sistema/login.php");
}


