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
        $atividade = "Visualizou a área de controle de avaliação funcional";
        $data = date("Y-m-d H:i:s");
        $intra->registraLog($idUsuario, $data, $atividade, null, null, 7);
    }

    # pega o id (se tiver)
    $id = soNumeros(get('id'));

    # Pega os parâmetros  
    $parametroLotacao = post('parametroLotacao', get_session('parametroLotacao', $pessoal->get_idLotacao($intra->get_idServidor($idUsuario))));

    # Joga os parâmetros par as sessions
    set_session('parametroLotacao', $parametroLotacao);

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
            array_unshift($result, array("*", 'Todas'));

            $controle = new Input('parametroLotacao', 'combo', 'Lotação:', 1);
            $controle->set_size(30);
            $controle->set_title('Filtra por Lotação');
            $controle->set_array($result);
            $controle->set_valor($parametroLotacao);
            $controle->set_onChange('formPadrao.submit();');
            $controle->set_linha(1);
            $controle->set_col(6);
            $controle->set_autofocus(true);
            $form->add_item($controle);

            $form->show();

            ##############
            # Pega os dados

            $select = "SELECT tbservidor.idfuncional,
                              tbservidor.idServidor,
                              tbservidor.idServidor,
                              tbservidor.idServidor,
                              CASE tipo
                                    WHEN 1 THEN 'Estágio' 
                                    WHEN 2 THEN 'Anual'
                               END,
                               referencia,
                               CONCAT(DATE_FORMAT(dtPeriodo1,'%d/%m/%Y'),' - ',DATE_FORMAT(dtPeriodo2,'%d/%m/%Y')),
                               idAvaliacao,
                               tbservidor.idServidor
                         FROM tbservidor JOIN tbpessoa USING (idPessoa)
                                         JOIN tbavaliacao USING (idServidor)
                                         JOIN tbhistlot USING (idServidor)
                                         JOIN tblotacao ON (tbhistlot.lotacao=tblotacao.idLotacao)
                        WHERE situacao = 1
                          AND tbhistlot.data = (select max(data) from tbhistlot where tbhistlot.idServidor = tbservidor.idServidor)";

            # Verifica se tem filtro por lotação
            if ($parametroLotacao <> "*") {  // senão verifica o da classe
                if (is_numeric($parametroLotacao)) {
                    $select .= " AND (tblotacao.idlotacao = {$parametroLotacao})";
                } else { # senão é uma diretoria genérica
                    $select .= " AND (tblotacao.DIR = '{$parametroLotacao}')";
                }
            }

            $select .= "ORDER BY tbpessoa.nome";

            $result = $pessoal->select($select);

            $tabela = new Tabela();
            $tabela->set_titulo('Controle de Avaliação Funcional');
            #$tabela->set_subtitulo('Filtro: '.$relatorioParametro);
            $tabela->set_label(["IdFuncional", "Servidor", "Processo", "Tipo", "Referencia", "Período", "Obs"]);
            #$tabela->set_width([10, 40, 40]);
            $tabela->set_conteudo($result);
            $tabela->set_align(["center", "left"]);
            $tabela->set_classe([null, "pessoal", "Avaliacao", null, null, null, "Avaliacao"]);
            $tabela->set_metodo([null, "get_nomeECargoELotacao", "exibeProcesso", null, null, null, "exibeObs"]);
            #$tabela->set_funcao([null, null, "date_to_php"]);
            $tabela->set_rowspan(1);
            $tabela->set_grupoCorColuna(1);

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
            set_session('origem', 'areaAvaliacao.php');

            # Carrega a página específica
            loadPage('servidorAvaliacao.php');
            break;

        ################################################################
        # Relatório
        case "relatorio" :

            if ($parametroVacinado == "Sim") {

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

                $select .= " AND tbservidor.idServidor IN (SELECT idServidor FROM tbvacina)
                ORDER BY lotacao, tbpessoa.nome";

                $result = $pessoal->select($select);

                $relatorio = new Relatorio();
                $relatorio->set_titulo('Relatório de Servidores Vacinados');
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
                $relatorio->set_titulo('Relatório de Servidores Não Vacinados');
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


