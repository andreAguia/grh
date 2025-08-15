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
    $parametroAno = post('parametroAno', get_session('parametroAno', date("Y")));

    # Joga os parâmetros par as sessions
    set_session('parametroLotacao', $parametroLotacao);
    set_session('parametroAno', $parametroAno);

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
            $botaoVoltar = new Link("Voltar", "areaAvaliacaoProcesso.php");
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
            $menu1->add_link($botaoRel, "right");

            $menu1->show();

            # Formulário de Pesquisa
            $form = new Form('?');

            # Cria um array com os anos possíveis
            $anoInicial = 1999;
            $anoAtual = date('Y');
            $anoRef = arrayPreenche($anoInicial, $anoAtual + 2, "d");
            array_unshift($anoRef, array("*", 'Todos'));

            $controle = new Input('parametroAno', 'combo', 'Ano Referência:', 1);
            $controle->set_size(8);
            $controle->set_title('Filtra por ano de referência');
            $controle->set_array($anoRef);
            $controle->set_valor(date("Y"));
            $controle->set_valor($parametroAno);
            $controle->set_onChange('formPadrao.submit();');
            $controle->set_linha(1);
            $controle->set_col(3);
            $controle->set_autofocus(true);
            $form->add_item($controle);

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
            $controle->set_col(9);
            $form->add_item($controle);

            $form->show();

            ##############
            # Monta o select
            if ($parametroAno <> "*") {
                $select = "(SELECT tbservidor.idfuncional, ";
            } else {
                $select = "SELECT tbservidor.idfuncional, ";
            }

            $select .= "      tbservidor.idServidor,
                              tbservidor.idServidor,
                              tbservidor.idServidor,
                              CASE tbavaliacao.tipo
                                    WHEN 1 THEN 'Estágio' 
                                    WHEN 2 THEN 'Anual'
                               END,
                               referencia,
                               CONCAT(DATE_FORMAT(dtPeriodo1,'%d/%m/%Y'),' - ',DATE_FORMAT(dtPeriodo2,'%d/%m/%Y')),
                               idAvaliacao,
                               tbservidor.idServidor,
                               tbpessoa.nome
                         FROM tbservidor JOIN tbpessoa USING (idPessoa)
                                         LEFT JOIN tbavaliacao USING (idServidor)
                                         JOIN tbhistlot USING (idServidor)
                                         JOIN tblotacao ON (tbhistlot.lotacao=tblotacao.idLotacao)
                        WHERE situacao = 1
                          AND idPerfil = 1 
                          AND tbhistlot.data = (select max(data) from tbhistlot where tbhistlot.idServidor = tbservidor.idServidor)";

            # Verifica se tem filtro por lotação
            if ($parametroLotacao <> "*") {  // senão verifica o da classe
                if (is_numeric($parametroLotacao)) {
                    $select .= " AND (tblotacao.idlotacao = {$parametroLotacao})";
                } else { # senão é uma diretoria genérica
                    $select .= " AND (tblotacao.DIR = '{$parametroLotacao}')";
                }
            }

            # Quando se informa o ano de referência
            if ($parametroAno <> "*") {
                # Filtra pelo ano de referência
                $select .= " AND referencia = '{$parametroAno}'";

                # Informa os servidores que não tem cadastrado esse ano específico
                $select .= ") UNION (
                    SELECT tbservidor.idfuncional,
                           tbservidor.idServidor,
                           tbservidor.idServidor,
                           tbservidor.idServidor,
                           '---',
                           '---',
                           '---',
                           '',
                           tbservidor.idServidor,
                           tbpessoa.nome
                      FROM tbservidor JOIN tbpessoa USING (idPessoa)
                                      JOIN tbhistlot USING (idServidor)
                                      JOIN tblotacao ON (tbhistlot.lotacao=tblotacao.idLotacao)
                        WHERE situacao = 1
                          AND idPerfil = 1
                          AND tbhistlot.data = (select max(data) from tbhistlot where tbhistlot.idServidor = tbservidor.idServidor)";

                # Verifica se tem filtro por lotação
                if ($parametroLotacao <> "*") {  // senão verifica o da classe
                    if (is_numeric($parametroLotacao)) {
                        $select .= " AND (tblotacao.idlotacao = {$parametroLotacao})";
                    } else { # senão é uma diretoria genérica
                        $select .= " AND (tblotacao.DIR = '{$parametroLotacao}')";
                    }
                }

                # Retirando os que tem
                $select .= " AND idServidor NOT IN (";

                $select .= "SELECT tbservidor.idServidor
                              FROM tbservidor JOIN tbpessoa USING (idPessoa)
                                         LEFT JOIN tbavaliacao USING (idServidor)
                                              JOIN tbhistlot USING (idServidor)
                                              JOIN tblotacao ON (tbhistlot.lotacao=tblotacao.idLotacao)
                              WHERE situacao = 1
                                AND idPerfil = 1
                                AND tbhistlot.data = (select max(data) from tbhistlot where tbhistlot.idServidor = tbservidor.idServidor)";

                # Verifica se tem filtro por lotação
                if ($parametroLotacao <> "*") {  // senão verifica o da classe
                    if (is_numeric($parametroLotacao)) {
                        $select .= " AND (tblotacao.idlotacao = {$parametroLotacao})";
                    } else { # senão é uma diretoria genérica
                        $select .= " AND (tblotacao.DIR = '{$parametroLotacao}')";
                    }
                }

                $select .= " AND referencia = '{$parametroAno}'))";
            }

            if ($parametroAno <> "*") {
                $select .= " ORDER BY 10";
            } else {
                $select .= " ORDER BY 10, 5, 8 DESC";
            }

            $result = $pessoal->select($select);

            $tabela = new Tabela();
            $tabela->set_titulo('Controle de Avaliação Funcional');
            $tabela->set_label(["IdFuncional", "Servidor", "Lotação", "Processo", "Tipo", "Referencia", "Período", "Obs"]);
            #$tabela->set_width([10, 40, 40]);
            $tabela->set_conteudo($result);
            $tabela->set_align(["center", "left"]);
            $tabela->set_classe([null, "pessoal", "pessoal", "Avaliacao", null, null, null, "Avaliacao"]);
            $tabela->set_metodo([null, "get_nomeECargo", "get_lotacao", "exibeProcesso", null, null, null, "exibeObs"]);
            #$tabela->set_funcao([null, null, "date_to_php"]);

            $tabela->set_rowspan([0, 1, 2, 3]);
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

            # Monta o select
            if ($parametroAno <> "*") {
                $select = "(SELECT tbservidor.idfuncional, ";
            } else {
                $select = "SELECT tbservidor.idfuncional, ";
            }

            $select .= "      tbservidor.idServidor,
                              tbservidor.idServidor,
                              CASE tipo
                                    WHEN 1 THEN 'Estágio' 
                                    WHEN 2 THEN 'Anual'
                               END,
                               referencia,
                               CONCAT(DATE_FORMAT(dtPeriodo1,'%d/%m/%Y'),' - ',DATE_FORMAT(dtPeriodo2,'%d/%m/%Y')),
                               idAvaliacao,
                               tbservidor.idServidor,
                               tbpessoa.nome
                         FROM tbservidor JOIN tbpessoa USING (idPessoa)
                                         LEFT JOIN tbavaliacao USING (idServidor)
                                         JOIN tbhistlot USING (idServidor)
                                         JOIN tblotacao ON (tbhistlot.lotacao=tblotacao.idLotacao)
                        WHERE situacao = 1
                          AND idPerfil = 1
                          AND tbhistlot.data = (select max(data) from tbhistlot where tbhistlot.idServidor = tbservidor.idServidor)";

            # Verifica se tem filtro por lotação
            if ($parametroLotacao <> "*") {  // senão verifica o da classe
                if (is_numeric($parametroLotacao)) {
                    $select .= " AND (tblotacao.idlotacao = {$parametroLotacao})";
                } else { # senão é uma diretoria genérica
                    $select .= " AND (tblotacao.DIR = '{$parametroLotacao}')";
                }
            }

            # Quando se informa o ano de referência
            if ($parametroAno <> "*") {
                # Filtra pelo ano de referência
                $select .= " AND referencia = '{$parametroAno}'";

                # Informa os servidores que não tem cadastrado esse ano específico
                $select .= ") UNION (
                    SELECT tbservidor.idfuncional,
                           tbservidor.idServidor,
                           tbservidor.idServidor,
                           '---',
                           '---',
                           '---',
                           '',
                           tbservidor.idServidor,
                           tbpessoa.nome
                      FROM tbservidor JOIN tbpessoa USING (idPessoa)
                                      JOIN tbhistlot USING (idServidor)
                                      JOIN tblotacao ON (tbhistlot.lotacao=tblotacao.idLotacao)
                      WHERE situacao = 1
                        AND idPerfil = 1
                        AND tbhistlot.data = (select max(data) from tbhistlot where tbhistlot.idServidor = tbservidor.idServidor)";

                # Verifica se tem filtro por lotação
                if ($parametroLotacao <> "*") {  // senão verifica o da classe
                    if (is_numeric($parametroLotacao)) {
                        $select .= " AND (tblotacao.idlotacao = {$parametroLotacao})";
                    } else { # senão é uma diretoria genérica
                        $select .= " AND (tblotacao.DIR = '{$parametroLotacao}')";
                    }
                }

                # Retirando os que tem
                $select .= " AND idServidor NOT IN (";

                $select .= "SELECT tbservidor.idServidor
                              FROM tbservidor JOIN tbpessoa USING (idPessoa)
                                         LEFT JOIN tbavaliacao USING (idServidor)
                                              JOIN tbhistlot USING (idServidor)
                                              JOIN tblotacao ON (tbhistlot.lotacao=tblotacao.idLotacao)
                              WHERE situacao = 1
                                AND idPerfil = 1
                                AND tbhistlot.data = (select max(data) from tbhistlot where tbhistlot.idServidor = tbservidor.idServidor)";

                # Verifica se tem filtro por lotação
                if ($parametroLotacao <> "*") {
                    if (is_numeric($parametroLotacao)) {
                        $select .= " AND (tblotacao.idlotacao = {$parametroLotacao})";
                    } else { # senão é uma diretoria genérica
                        $select .= " AND (tblotacao.DIR = '{$parametroLotacao}')";
                    }
                }

                $select .= " AND referencia = '{$parametroAno}'))";
            }

            if ($parametroAno <> "*") {
                $select .= " ORDER BY 9";
            } else {
                $select .= " ORDER BY 9, 4, 7 DESC";
            }

            $result = $pessoal->select($select);

            $relatorio = new Relatorio();
            $relatorio->set_conteudo($result);
            $relatorio->set_titulo('Relatório de Avaliações');

            # Informa a lotação
            if ($parametroLotacao <> "*") {
                if (is_numeric($parametroLotacao)) {
                    $relatorio->set_subtitulo($pessoal->get_nomeLotacao($parametroLotacao));
                } else {
                    $relatorio->set_subtitulo($parametroLotacao);
                }
            }

            # Informa o ano de referência
            if ($parametroAno <> "*") {
                $relatorio->set_tituloLinha2($parametroAno);
            } else {
                $relatorio->set_rowspan(1);
            }

            $relatorio->set_label(["IdFuncional", "Servidor", "Processo", "Tipo", "Referencia", "Período"]);
            $relatorio->set_align(["center", "left"]);
            $relatorio->set_classe([null, "pessoal", "Avaliacao", null, null, null, "Avaliacao"]);
            $relatorio->set_metodo([null, "get_nomeECargoELotacao", "exibeProcesso"]);

            $relatorio->set_bordaInterna(true);
            $relatorio->show();
            break;
    }

    $page->terminaPagina();
} else {
    loadPage("../../areaServidor/sistema/login.php");
}


