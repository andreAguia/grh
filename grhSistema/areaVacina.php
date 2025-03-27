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
    $lotacao = new Lotacao();

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
    $parametroNomeMat = post('parametroNomeMat', get_session('parametroNomeMat'));
    $parametroLotacao = post('parametroLotacao', get_session('parametroLotacao', $pessoal->get_idLotacao($intra->get_idServidor($idUsuario))));
    $parametroApto = post('parametroApto', get_session('parametroApto', 'Sim'));
    $parametroJustificativa = post('parametroJustificativa', get_session('parametroJustificativa', 'Não'));

    # Joga os parâmetros par as sessions
    set_session('parametroNomeMat', $parametroNomeMat);
    set_session('parametroLotacao', $parametroLotacao);
    set_session('parametroApto', $parametroApto);
    set_session('parametroJustificativa', $parametroJustificativa);
    
    # Limite da data de admissão
    $aposDataAdmissao = date_to_bd("01/03/2023");

    # Começa uma nova página
    $page = new Page();
    $page->iniciaPagina();

    # Variáveis
    $portaria = "Portaria 161 de 28 de Julho de 2022";
    $dosesAptidao = 3;

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

            $controle = new Input('parametroNomeMat', 'texto', 'Nome, Matrícula ou id:', 1);
            $controle->set_size(100);
            $controle->set_title('Nome do servidor');
            $controle->set_valor($parametroNomeMat);
            $controle->set_autofocus(true);
            $controle->set_onChange('formPadrao.submit();');
            $controle->set_linha(1);
            $controle->set_col(3);
            $form->add_item($controle);

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
            $controle->set_col(5);
            $form->add_item($controle);

            # Vacinado
            $controle = new Input('parametroApto', 'combo', 'Apto acessar a Uenf?', 1);
            $controle->set_size(30);
            $controle->set_title('Filtra Vacinados /  não Vacinados');
            $controle->set_array([["Sim", "Sim"], ["Não", "Não"]]);
            $controle->set_valor($parametroApto);
            $controle->set_onChange('formPadrao.submit();');
            $controle->set_linha(1);
            $controle->set_col(2);
            $form->add_item($controle);

            if ($parametroApto == "Não") {
                # Com justificativa
                $controle = new Input('parametroJustificativa', 'combo', 'Isentado?', 1);
                $controle->set_size(30);
                $controle->set_title('Filtra Justificado /  não Justificado');
                $controle->set_array([["Sim", "Sim"], ["Não", "Não"]]);
                $controle->set_valor($parametroJustificativa);
                $controle->set_onChange('formPadrao.submit();');
                $controle->set_linha(1);
                $controle->set_col(2);
                $form->add_item($controle);
            }

            $form->show();

            $grid->fechaColuna();
            $grid->abreColuna(12, 4, 3);

            $vacina = new Vacina();
            $vacina->exibeQuadroAptidao($parametroLotacao);
            $vacina->exibeQuadroQuantidadeDoses($parametroLotacao, $dosesAptidao);
            $vacina->exibeQuadroVacinas($parametroLotacao);
            $vacina->exibeQuadroDosesPorVacina($parametroLotacao);

            $grid->fechaColuna();
            $grid->abreColuna(12, 8, 9);

            ##############
            /*
             * Aptos
             */

            if ($parametroApto == "Sim") {

                $select = "SELECT rr.idServidor,
                                  rr.idServidor
                             FROM tbservidor as rr JOIN tbpessoa USING (idPessoa)
                                                   JOIN tbperfil USING (idPerfil) 
                                                   JOIN tbhistlot USING (idServidor)
                                                   JOIN tblotacao ON (tbhistlot.lotacao=tblotacao.idLotacao)
                        WHERE situacao = 1
                          AND dtAdmissao < '{$aposDataAdmissao}'
                          AND tbperfil.tipo <> 'Outros'
                          AND tbhistlot.data = (select max(data) from tbhistlot where tbhistlot.idServidor = rr.idServidor)
                          AND (SELECT COUNT(idServidor) FROM tbvacina as tt WHERE tt.idServidor = rr.idServidor) >= {$dosesAptidao}
                          ";

                # Matrícula, nome ou id
                if (!empty($parametroNomeMat)) {
                    if (is_numeric($parametroNomeMat)) {
                        $select .= " AND ((tbpessoa.nome LIKE '%{$parametroNomeMat}%')";
                    } else {

                        # Verifica se tem espaços
                        if (strpos($parametroNomeMat, ' ') !== false) {
                            # Separa as palavras
                            $palavras = explode(' ', $parametroNomeMat);

                            # Percorre as palavras
                            foreach ($palavras as $item) {
                                $select .= " AND (tbpessoa.nome LIKE '%{$item}%')";
                            }
                        } else {
                            $select .= " AND (tbpessoa.nome LIKE '%{$parametroNomeMat}%')";
                        }
                    }

                    if (is_numeric($parametroNomeMat)) {
                        $select .= " OR (tbservidor.matricula LIKE '%{$parametroNomeMat}%')
                                 OR (tbservidor.idfuncional LIKE '%{$parametroNomeMat}%'))";
                    }
                }

                # Verifica se tem filtro por lotação
                if ($parametroLotacao <> "Todos") {
                    if (is_numeric($parametroLotacao)) {
                        $select .= " AND (tblotacao.idlotacao = {$parametroLotacao})";
                    } else { # senão é uma diretoria genérica
                        $select .= " AND (tblotacao.DIR = '{$parametroLotacao}')";
                    }
                }

                $select .= " ORDER BY tbpessoa.nome";

                $result = $pessoal->select($select);

                $tabela = new Tabela();
                $tabela->set_titulo('Servidores Aptos a Acessar os Campi da Uenf');
                if ($parametroLotacao <> "Todos") {
                    if (is_numeric($parametroLotacao)) {
                        $tabela->set_subtitulo($pessoal->get_nomeLotacao2($parametroLotacao));
                    } else {
                        $tabela->set_subtitulo($lotacao->get_nomeDiretoriaSigla($parametroLotacao));
                    }
                } else {
                    $tabela->set_titulo('Servidores Aptos a Acessar os Campi da Uenf');
                }

                $tabela->set_label(["Servidor", "Vacinas"]);
                $tabela->set_width([50, 45]);
                $tabela->set_conteudo($result);
                $tabela->set_align(["left", "left"]);
                $tabela->set_classe(["pessoal", "Vacina"]);
                $tabela->set_metodo(["get_nomeELotacaoEDtAdmissao", "exibeVacinas"]);
                $tabela->set_idCampo('idServidor');
                $tabela->set_editar('?fase=editaServidor');
                $tabela->show();
            } else {
                /*
                 * Não Aptos
                 */

                if ($parametroJustificativa == "Sim") {
                    # Título
                    $titulo = "Servidores Isentos da Apresentação do Comprovante de Vacinação";

                    # select
                    $select = "SELECT rr.idServidor,
                                  rr.idServidor,
                                  rr.idServidor,
                                  rr.idServidor,
                                  rr.justificativaVacina
                             FROM tbservidor as rr JOIN tbpessoa USING (idPessoa)
                                                   JOIN tbperfil USING (idPerfil)
                                                   JOIN tbhistlot USING (idServidor)
                                                   JOIN tblotacao ON (tbhistlot.lotacao = tblotacao.idLotacao)
                        WHERE situacao = 1
                          AND dtAdmissao < '{$aposDataAdmissao}'
                          AND tbperfil.tipo <> 'Outros'
                          AND tbhistlot.data = (select max(data) from tbhistlot where tbhistlot.idServidor = rr.idServidor)
                          AND (SELECT COUNT(idServidor) FROM tbvacina as tt WHERE tt.idServidor = rr.idServidor) < {$dosesAptidao}";
                } else {
                    # Título
                    $titulo = "Servidores NÃO Aptos a Acessar os Campi da Uenf";

                    # select
                    $select = "SELECT rr.idServidor,
                                      rr.idServidor,
                                      rr.idServidor,
                                      rr.idServidor
                                 FROM tbservidor as rr JOIN tbpessoa USING (idPessoa)
                                                       JOIN tbperfil USING (idPerfil)
                                                       JOIN tbhistlot USING (idServidor)
                                                       JOIN tblotacao ON (tbhistlot.lotacao = tblotacao.idLotacao)
                        WHERE situacao = 1
                          AND dtAdmissao < '{$aposDataAdmissao}'
                          AND tbperfil.tipo <> 'Outros'
                          AND tbhistlot.data = (select max(data) from tbhistlot where tbhistlot.idServidor = rr.idServidor)
                          AND (SELECT COUNT(idServidor) FROM tbvacina as tt WHERE tt.idServidor = rr.idServidor) < {$dosesAptidao}";
                }

                # Matrícula, nome ou id
                if (!empty($parametroNomeMat)) {
                    if (is_numeric($parametroNomeMat)) {
                        $select .= " AND ((tbpessoa.nome LIKE '%{$parametroNomeMat}%')";
                    } else {

                        # Verifica se tem espaços
                        if (strpos($parametroNomeMat, ' ') !== false) {
                            # Separa as palavras
                            $palavras = explode(' ', $parametroNomeMat);

                            # Percorre as palavras
                            foreach ($palavras as $item) {
                                $select .= " AND (tbpessoa.nome LIKE '%{$item}%')";
                            }
                        } else {
                            $select .= " AND (tbpessoa.nome LIKE '%{$parametroNomeMat}%')";
                        }
                    }

                    if (is_numeric($parametroNomeMat)) {
                        $select .= " OR (tbservidor.matricula LIKE '%{$parametroNomeMat}%')
                                 OR (tbservidor.idfuncional LIKE '%{$parametroNomeMat}%'))";
                    }
                }

                # Verifica se tem filtro por lotação
                if ($parametroLotacao <> "Todos") {
                    if (is_numeric($parametroLotacao)) {
                        $select .= " AND (tblotacao.idlotacao = {$parametroLotacao})";
                    } else { # senão é uma diretoria genérica
                        $select .= " AND (tblotacao.DIR = '{$parametroLotacao}')";
                    }
                }

                if ($parametroJustificativa == "Sim") {
                    $select .= " AND rr.justificativaVacina <> '' ";
                }

                if ($parametroJustificativa == "Não") {
                    $select .= " AND (rr.justificativaVacina = '' OR rr.justificativaVacina is null)";
                }

                $select .= "ORDER BY tbpessoa.nome";

                $result = $pessoal->select($select);
                
                $subtitulo = "";

                $tabela = new Tabela();
                if ($parametroLotacao <> "Todos") {
                    if (is_numeric($parametroLotacao)) {
                        $subtitulo = $pessoal->get_nomeLotacao($parametroLotacao);
                    } else {
                        $subtitulo = $lotacao->get_nomeDiretoriaSigla($parametroLotacao);
                    }
                }

                if ($parametroJustificativa == "Sim") {
                    $tabela->set_label(["Servidor", "Vacinas", "E-mail", "Situação", "Justificativa da Isenção da Entrega"]);
                    $tabela->set_width([30, 15, 20, 10, 25]);
                    $tabela->set_align(["left", "left", "center", "center", "left"]);
                }

                if ($parametroJustificativa == "Não") {
                    $tabela->set_label(["Servidor", "Vacinas", "E-mail", "Situação"]);
                    $tabela->set_width([40, 30, 25, 5]);
                    $tabela->set_align(["left", "left"]);
                }
                $tabela->set_titulo($titulo);
                $tabela->set_subtitulo($subtitulo);
                $tabela->set_mensagemPreTabela("Servidores admitidos antes de ".date_to_php($aposDataAdmissao));
                $tabela->set_conteudo($result);
                $tabela->set_classe(["pessoal", "vacina", "pessoal"]);
                $tabela->set_metodo(["get_nomeELotacaoEDtAdmissao", "exibeVacinas", "get_emails"]);
                $tabela->set_funcao([null, null, null, "get_situacao"]);
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
             * Aptos
             */

            # Verifica se é ou não apto
            if ($parametroApto == "Sim") {

                $select = "SELECT tbpessoa.nome,
                                  rr.idServidor,
                                  concat(IFnull(tblotacao.UADM,''),' - ',IFnull(tblotacao.DIR,''),' - ',IFnull(tblotacao.GER,''),' - ',IFnull(tblotacao.nome,'')) lotacao,
                                  rr.idServidor
                            FROM tbservidor as rr JOIN tbpessoa USING (idPessoa)
                                                  JOIN tbhistlot USING (idServidor)
                                                  JOIN tblotacao ON (tbhistlot.lotacao=tblotacao.idLotacao)
                        WHERE situacao = 1
                          AND dtAdmissao < '{$aposDataAdmissao}'
                          AND tbhistlot.data = (select max(data) from tbhistlot where tbhistlot.idServidor = rr.idServidor)
                          AND (SELECT COUNT(idServidor) FROM tbvacina as tt WHERE tt.idServidor = rr.idServidor) >= {$dosesAptidao}
                          ";

                # Verifica se tem filtro por lotação
                if ($parametroLotacao <> "Todos") {
                    if (is_numeric($parametroLotacao)) {
                        $select .= " AND (tblotacao.idlotacao = {$parametroLotacao})";
                    } else { # senão é uma diretoria genérica
                        $select .= " AND (tblotacao.DIR = '{$parametroLotacao}')";
                    }
                }

                # Matrícula, nome ou id
                if (!empty($parametroNomeMat)) {
                    if (is_numeric($parametroNomeMat)) {
                        $select .= " AND ((tbpessoa.nome LIKE '%{$parametroNomeMat}%')";
                    } else {

                        # Verifica se tem espaços
                        if (strpos($parametroNomeMat, ' ') !== false) {
                            # Separa as palavras
                            $palavras = explode(' ', $parametroNomeMat);

                            # Percorre as palavras
                            foreach ($palavras as $item) {
                                $select .= " AND (tbpessoa.nome LIKE '%{$item}%')";
                            }
                        } else {
                            $select .= " AND (tbpessoa.nome LIKE '%{$parametroNomeMat}%')";
                        }
                    }

                    if (is_numeric($parametroNomeMat)) {
                        $select .= " OR (tbservidor.matricula LIKE '%{$parametroNomeMat}%')
                                 OR (tbservidor.idfuncional LIKE '%{$parametroNomeMat}%'))";
                    }
                }

                $select .= " ORDER BY lotacao, tbpessoa.nome";

                $result = $pessoal->select($select);

                $relatorio = new Relatorio();
                $relatorio->set_titulo('Servidores Aptos a Acessar os Campi da Uenf');
                $relatorio->set_subtitulo("De Acordo com a {$portaria}");

                if (!is_numeric($parametroLotacao) AND $parametroLotacao <> "Todos") {
                    $relatorio->set_subtitulo2($parametroLotacao);
                }
                $relatorio->set_label(["Nome", "Cargo", "Lotação", "Vacinas"]);
                $relatorio->set_width([30, 30, 0, 40]);
                $relatorio->set_conteudo($result);
                $relatorio->set_align(["left", "left", "left", "left"]);
                $relatorio->set_classe([null, "pessoal", null, "Vacina"]);
                $relatorio->set_metodo([null, "get_cargoSimples", null, "exibeVacinas"]);
                $relatorio->set_bordaInterna(true);
                $relatorio->set_numGrupo(2);
                $relatorio->show();
            }

            ######

            /*
             * Não Aptos
             */

            if ($parametroApto == "Não") {
                $relatorio = new Relatorio();

                if ($parametroJustificativa == "Sim") {

                    $select = "SELECT tbpessoa.nome,
                                      rr.idServidor,
                                      concat(IFnull(tblotacao.UADM,''),' - ',IFnull(tblotacao.DIR,''),' - ',IFnull(tblotacao.GER,''),' - ',IFnull(tblotacao.nome,'')) lotacao,
                                      rr.justificativaVacina
                                 FROM tbservidor as rr JOIN tbpessoa USING (idPessoa)
                                                       JOIN tbhistlot USING (idServidor)
                                                       JOIN tblotacao ON (tbhistlot.lotacao=tblotacao.idLotacao)
                                 WHERE situacao = 1
                                   AND dtAdmissao < '{$aposDataAdmissao}'
                                   AND tbhistlot.data = (select max(data) from tbhistlot where tbhistlot.idServidor = rr.idServidor)
                                   AND (SELECT COUNT(idServidor) FROM tbvacina as tt WHERE tt.idServidor = rr.idServidor) < {$dosesAptidao}";
                } else {
                    $select = "SELECT tbpessoa.nome,
                                      rr.idServidor,
                                      concat(IFnull(tblotacao.UADM,''),' - ',IFnull(tblotacao.DIR,''),' - ',IFnull(tblotacao.GER,''),' - ',IFnull(tblotacao.nome,'')) lotacao,
                                      rr.idServidor
                                 FROM tbservidor as rr JOIN tbpessoa USING (idPessoa)
                                                       JOIN tbhistlot USING (idServidor)
                                                       JOIN tblotacao ON (tbhistlot.lotacao=tblotacao.idLotacao)
                                 WHERE situacao = 1
                                   AND dtAdmissao < '{$aposDataAdmissao}'
                                   AND tbhistlot.data = (select max(data) from tbhistlot where tbhistlot.idServidor = rr.idServidor)
                                   AND (SELECT COUNT(idServidor) FROM tbvacina as tt WHERE tt.idServidor = rr.idServidor) < {$dosesAptidao}";
                }

                # Verifica se tem filtro por lotação
                if ($parametroLotacao <> "Todos") {
                    if (is_numeric($parametroLotacao)) {
                        $select .= " AND (tblotacao.idlotacao = {$parametroLotacao})";
                    } else { # senão é uma diretoria genérica
                        $select .= " AND (tblotacao.DIR = '{$parametroLotacao}')";
                    }
                }

                # Matrícula, nome ou id
                if (!empty($parametroNomeMat)) {
                    if (is_numeric($parametroNomeMat)) {
                        $select .= " AND ((tbpessoa.nome LIKE '%{$parametroNomeMat}%')";
                    } else {

                        # Verifica se tem espaços
                        if (strpos($parametroNomeMat, ' ') !== false) {
                            # Separa as palavras
                            $palavras = explode(' ', $parametroNomeMat);

                            # Percorre as palavras
                            foreach ($palavras as $item) {
                                $select .= " AND (tbpessoa.nome LIKE '%{$item}%')";
                            }
                        } else {
                            $select .= " AND (tbpessoa.nome LIKE '%{$parametroNomeMat}%')";
                        }
                    }

                    if (is_numeric($parametroNomeMat)) {
                        $select .= " OR (tbservidor.matricula LIKE '%{$parametroNomeMat}%')
                                 OR (tbservidor.idfuncional LIKE '%{$parametroNomeMat}%'))";
                    }
                }

                if ($parametroJustificativa == "Sim") {
                    $select .= " AND rr.justificativaVacina <> '' ";
                    $relatorio->set_titulo("Servidores Isentos da Entrega dos Comprovantes de Vacina");
                }

                if ($parametroJustificativa == "Não") {
                    $select .= " AND (rr.justificativaVacina = '' OR rr.justificativaVacina is null)";
                    $relatorio->set_titulo('Servidores NÃO Aptos a Acessar os Campi da Uenf');
                    $relatorio->set_subtitulo("De Acordo com a {$portaria}");
                }

                $select .= "ORDER BY lotacao, tbpessoa.nome";

                $result = $pessoal->select($select);

                if (!is_numeric($parametroLotacao) AND $parametroLotacao <> "Todos") {
                    $relatorio->set_subtitulo2($parametroLotacao);
                }

                if ($parametroJustificativa == "Sim") {
                    $relatorio->set_label(["Servidor", "Cargo", "Lotação", "Motivo"]);
                    $relatorio->set_align(["left", "left", "left", "left", "left"]);
                    $relatorio->set_width([30, 30, 0, 40]);
                    $relatorio->set_classe([null, "pessoal"]);
                    $relatorio->set_metodo([null, "get_cargoSimples"]);
                } else {
                    $relatorio->set_label(["Servidor", "Cargo", "Lotação", "Vacinas"]);
                    $relatorio->set_align(["left", "left", "left", "left", "left"]);
                    $relatorio->set_width([30, 30, 0, 40]);
                    $relatorio->set_classe([null, "pessoal", null, "Vacina"]);
                    $relatorio->set_metodo([null, "get_cargoSimples", null, "exibeVacinas"]);
                    $relatorio->set_bordaInterna(true);
                }

                $relatorio->set_conteudo($result);

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


