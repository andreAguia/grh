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
    $parametroComprovante = post('parametroComprovante', get_session('parametroComprovante', 'Todos'));
    $parametroTipoVacina = post('parametroTipoVacina', get_session('parametroTipoVacina', 'Todos'));

    # Joga os parâmetros par as sessions
    set_session('parametroLotacao', $parametroLotacao);
    set_session('parametroVacinado', $parametroVacinado);
    set_session('parametroComprovante', $parametroComprovante);
    set_session('parametroTipoVacina', $parametroTipoVacina);

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

            # Tipo de Vacina
            $tipoVacina = $pessoal->select('SELECT idTipoVacina,
                                           nome
                                      FROM tbtipovacina
                                  ORDER BY nome');
            array_unshift($tipoVacina, array("Todos", 'Todas'));

            $controle = new Input('parametroTipoVacina', 'combo', 'Vacina:', 1);
            $controle->set_size(30);
            $controle->set_title('Filtra por Tipo de Vacina');
            $controle->set_array($tipoVacina);
            $controle->set_valor($parametroTipoVacina);
            $controle->set_onChange('formPadrao.submit();');
            $controle->set_linha(1);
            $controle->set_col(2);
            $form->add_item($controle);

            # Vacinado
            $controle = new Input('parametroVacinado', 'combo', 'Vacinado?:', 1);
            $controle->set_size(30);
            $controle->set_title('Filtra Vacinados /  não Vacinados');
            $controle->set_array([["Todos", "Todos"], ["Sim", "Sim"], ["Não", "Não"]]);
            $controle->set_valor($parametroVacinado);
            $controle->set_onChange('formPadrao.submit();');
            $controle->set_linha(1);
            if ($parametroVacinado == "Sim") {
                $controle->set_col(2);
            } else {
                $controle->set_col(4);
            }
            $form->add_item($controle);

            if ($parametroVacinado == "Sim") {
                # comprovante
                $controle = new Input('parametroComprovante', 'combo', 'Com Comprovante?:', 1);
                $controle->set_size(30);
                $controle->set_title('Filtra Comprovados /  não Comprovados');
                $controle->set_array([["Todos", "Todos"], ["Sim", "Sim"], ["Não", "Não"]]);
                $controle->set_valor($parametroComprovante);
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
            $vacina->exibeQuadroVacinadosNComprovados($parametroLotacao);
            $vacina->exibeQuadroDosesPorComprovante($parametroLotacao);
            $vacina->exibeQuadroDosesPorVacina($parametroLotacao);

            $grid->fechaColuna();
            $grid->abreColuna(9);

            ##############
            # Pega os dados

            $select = "SELECT tbservidor.idServidor,
                              tbvacina.data,
                              IFNULL(tbtipovacina.nome,'<span class=\'label alert\' title=\'NÃO informou o tipo da vacina\'>Não Informado</span>'),
                              if(comprovante,'<span class=\'label succes\' title=\'Servidor enviou o comprovante\'>Sim</span>','<span class=\'label alert\' title=\'Servidor NÃO enviou o comprovante\'>Não</span>')
                         FROM tbservidor LEFT JOIN tbvacina USING (idServidor)
                                         LEFT JOIN tbtipovacina ON (tbvacina.idTipoVacina = tbtipovacina.idTipoVacina)
                                         JOIN tbpessoa USING (idPessoa)
                                         JOIN tbhistlot USING (idServidor)
                                         JOIN tblotacao ON (tbhistlot.lotacao=tblotacao.idLotacao)
                        WHERE situacao = 1
                          AND tbhistlot.data = (select max(data) from tbhistlot where tbhistlot.idServidor = tbservidor.idServidor)";

            # Verifica se tem filtro por lotação
            if ($parametroLotacao <> "Todos") {
                if (is_numeric($parametroLotacao)) {
                    $select .= " AND (tblotacao.idlotacao = {$parametroLotacao})";
                } else { # senão é uma diretoria genérica
                    $select .= " AND (tblotacao.DIR = '{$parametroLotacao}')";
                }
            }

            if ($parametroTipoVacina <> "Todos") {
                $select .= " AND tbvacina.idTipoVacina = {$parametroTipoVacina} ";
            }

            # Não Vacinados
            if ($parametroVacinado == "Não") {
                $select .= " AND tbservidor.idServidor NOT IN (SELECT idServidor FROM tbvacina) ";
            }

            # Vacinados
            if ($parametroVacinado == "Sim") {
                $select .= " AND tbservidor.idServidor IN (SELECT idServidor FROM tbvacina) ";
            }

            if ($parametroVacinado == "Sim") {
                # Não Comprovados
                if ($parametroComprovante == "Não") {
                    $select .= " AND NOT comprovante ";
                }

                # Vacinados
                if ($parametroComprovante == "Sim") {
                    $select .= " AND comprovante ";
                }
            }


            $select .= "ORDER BY tbpessoa.nome";

            $result = $pessoal->select($select);

            $tabela = new Tabela();
            $tabela->set_titulo('Controle de Vacinação dos Servidores');
            #$tabela->set_subtitulo('Filtro: '.$relatorioParametro);
            $tabela->set_label(["Servidor", "Data", "Vacina", "Enviou Comprovante?"]);
            $tabela->set_width([50, 15, 20, 10]);
            $tabela->set_conteudo($result);
            $tabela->set_align(["left"]);
            $tabela->set_classe(["pessoal"]);
            $tabela->set_metodo(["get_nomeECargoELotacaoEId"]);
            #$tabela->set_metodo([null, "get_nomeECargoELotacao", "exibeVacinas"]);
            $tabela->set_funcao([null, "date_to_php"]);
            $tabela->set_totalRegistroTexto("N° de doses de vacinas: ");
            $tabela->set_rowspan(0);
            $tabela->set_grupoCorColuna(0);

            $tabela->set_idCampo('idServidor');
            $tabela->set_editar('?fase=editaServidor');
            $tabela->show();

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

            # Verifica se é ou não de vacinados
            if ($parametroVacinado == "Sim") {

                $select = "SELECT tbservidor.idServidor,
                              tbvacina.data,
                              IFNULL(tbtipovacina.nome,'Não Informado'),
                              if(comprovante,'Sim','Não'),
                              concat(IFnull(tblotacao.UADM,''),' - ',IFnull(tblotacao.DIR,''),' - ',IFnull(tblotacao.GER,''),' - ',IFnull(tblotacao.nome,'')) lotacao
                         FROM tbservidor LEFT JOIN tbvacina USING (idServidor)
                                         LEFT JOIN tbtipovacina ON (tbvacina.idTipoVacina = tbtipovacina.idTipoVacina)
                                         JOIN tbpessoa USING (idPessoa)
                                         JOIN tbhistlot USING (idServidor)
                                         JOIN tblotacao ON (tbhistlot.lotacao=tblotacao.idLotacao)
                        WHERE situacao = 1
                          AND tbhistlot.data = (select max(data) from tbhistlot where tbhistlot.idServidor = tbservidor.idServidor)";

                # Verifica se tem filtro por lotação
                if ($parametroLotacao <> "Todos") {  // senão verifica o da classe
                    if (is_numeric($parametroLotacao)) {
                        $select .= " AND (tblotacao.idlotacao = {$parametroLotacao})";
                    } else { # senão é uma diretoria genérica
                        $select .= " AND (tblotacao.DIR = '{$parametroLotacao}')";
                    }
                }

                # tipo de Vacina
                if ($parametroTipoVacina <> "Todos") {
                    $select .= " AND tbvacina.idTipoVacina = {$parametroTipoVacina} ";
                }

                # Não Vacinados
                if ($parametroVacinado == "Não") {
                    $select .= " AND tbservidor.idServidor NOT IN (SELECT idServidor FROM tbvacina) ";
                }

                # Vacinados
                if ($parametroVacinado == "Sim") {
                    $select .= " AND tbservidor.idServidor IN (SELECT idServidor FROM tbvacina) ";
                }

                if ($parametroVacinado == "Sim") {
                    # Não Comprovados
                    if ($parametroComprovante == "Não") {
                        $select .= " AND NOT comprovante ";
                        $subTitulo = "Que NÃO Entregaram Comprovante";
                    }

                    # Comprovados
                    if ($parametroComprovante == "Sim") {
                        $select .= " AND comprovante ";
                        $subTitulo = "Que Entregaram Comprovante";
                    }
                }


                $select .= "ORDER BY lotacao, tbpessoa.nome";

                $result = $pessoal->select($select);

                $relatorio = new Relatorio();
                $relatorio->set_titulo('Relatório de Servidores Que Informaram Terem Sido Vacinados');
                $relatorio->set_subtitulo($subTitulo);
                $relatorio->set_label(["Servidor", "Data", "Vacina", "Enviou Comprovante?", "Lotação"]);
                $relatorio->set_width([50, 15, 20, 10, 0]);
                $relatorio->set_align(["left"]);

                $relatorio->set_classe(["pessoal"]);
                $relatorio->set_metodo(["get_nomeECargoELotacaoEId"]);
                $relatorio->set_funcao([null, "date_to_php"]);

                $relatorio->set_conteudo($result);
                $relatorio->set_numGrupo(4);
                $relatorio->set_bordaInterna(true);

                $relatorio->set_totalRegistroTexto("N° de registros de vacinas: ");
                $relatorio->set_rowspan(0);
                $relatorio->set_grupoCorColuna(0);

                $relatorio->show();
            }

            ######

            /*
             * Não Vacinados
             */

            if ($parametroVacinado == "Não") {

                $select = "SELECT tbservidor.idfuncional,
                      tbpessoa.nome,
                      tbservidor.idServidor,
                      concat(IFnull(tblotacao.UADM,''),' - ',IFnull(tblotacao.DIR,''),' - ',IFnull(tblotacao.GER,''),' - ',IFnull(tblotacao.nome,'')) lotacao
                 FROM tbservidor JOIN tbpessoa USING (idPessoa)
                                 JOIN tbhistlot USING (idServidor)
                                 JOIN tblotacao ON (tbhistlot.lotacao=tblotacao.idLotacao)
                WHERE situacao = 1
                  AND tbhistlot.data = (select max(data) from tbhistlot where tbhistlot.idServidor = tbservidor.idServidor)";

                # Verifica se tem filtro por lotação
                if ($parametroLotacao <> "Todos") {  // senão verifica o da classe
                    if (is_numeric($parametroLotacao)) {
                        $select .= " AND (tblotacao.idlotacao = {$parametroLotacao})";
                    } else { # senão é uma diretoria genérica
                        $select .= " AND (tblotacao.DIR = '{$parametroLotacao}')";
                    }
                }

                $select .= " AND tbservidor.idServidor NOT IN (SELECT idServidor FROM tbvacina)
                ORDER BY lotacao, tbpessoa.nome";

                $result = $pessoal->select($select);

                $relatorio = new Relatorio();
                $relatorio->set_titulo('Relatório de Servidores Que Não Informaram Se Foram Vacinados');
                $relatorio->set_label(["IdFuncional", "Nome", "Cargo", "Lotação"]);
                $relatorio->set_width([10, 45, 45, 0]);
                $relatorio->set_align(["center", "left", "left"]);

                $relatorio->set_classe([null, null, "pessoal"]);
                $relatorio->set_metodo([null, null, "get_cargoSimples"]);

                $relatorio->set_conteudo($result);
                $relatorio->set_numGrupo(3);
                $relatorio->show();
            }

            ######

            /*
             * Todos
             */

            if ($parametroVacinado == "Todos") {

                $select = "SELECT tbservidor.idfuncional,
                      tbpessoa.nome,
                      tbservidor.idServidor,
                      concat(IFnull(tblotacao.UADM,''),' - ',IFnull(tblotacao.DIR,''),' - ',IFnull(tblotacao.GER,''),' - ',IFnull(tblotacao.nome,'')) lotacao,
                      tbservidor.idServidor
                 FROM tbservidor JOIN tbpessoa USING (idPessoa)
                                 JOIN tbhistlot USING (idServidor)
                                 JOIN tblotacao ON (tbhistlot.lotacao=tblotacao.idLotacao)
                WHERE situacao = 1
                  AND tbhistlot.data = (select max(data) from tbhistlot where tbhistlot.idServidor = tbservidor.idServidor)";

                # Verifica se tem filtro por lotação
                if ($parametroLotacao <> "Todos") {  // senão verifica o da classe
                    if (is_numeric($parametroLotacao)) {
                        $select .= " AND (tblotacao.idlotacao = {$parametroLotacao})";
                    } else { # senão é uma diretoria genérica
                        $select .= " AND (tblotacao.DIR = '{$parametroLotacao}')";
                    }
                }

                $select .= " ORDER BY lotacao, tbpessoa.nome";

                $result = $pessoal->select($select);

                $relatorio = new Relatorio();
                $relatorio->set_titulo('Relatório de Vacinação dos Servidores');
                $relatorio->set_label(["IdFuncional", "Nome", "Cargo", "Lotação", "Vacinas"]);
                $relatorio->set_width([10, 30, 30, 0, 30]);
                $relatorio->set_align(["center", "left", "left", "left", "left"]);

                $relatorio->set_classe([null, null, "pessoal", null, "Vacina"]);
                $relatorio->set_metodo([null, null, "get_cargoSimples", null, "exibeVacinas"]);

                $relatorio->set_conteudo($result);
                $relatorio->set_numGrupo(3);
                $relatorio->set_bordaInterna(true);
                $relatorio->show();
            }

            break;
    }

    $page->terminaPagina();
} else {
    loadPage("../../areaServidor/sistema/login.php");
}


