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
        $atividade = "Visualizou a área de controle de frequência de cedidos da Uenf para outros órgãos";
        $data = date("Y-m-d H:i:s");
        $intra->registraLog($idUsuario, $data, $atividade, null, null, 7);
    }

    # Pega os parâmetros
    $parametroAgora = post('parametroAgora', get_session('parametroAgora', "Atualmente Cedidos"));
    $parametroNome = post('parametroNome', get_session('parametroNome'));

    # Joga os parâmetros par as sessions
    set_session('parametroAgora', $parametroAgora);
    set_session('parametroNome', $parametroNome);

    # pega o id (se tiver)
    $id = soNumeros(get('id'));

    # Começa uma nova página
    $page = new Page();
    $page->iniciaPagina();

    # Cabeçalho da Página
    AreaServidor::cabecalho();

################################################################

    switch ($fase) {
        case "" :
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

            # Relatórios
            $imagem = new Imagem(PASTA_FIGURAS . 'print.png', null, 15, 15);
            $botaoRel = new Button();
            $botaoRel->set_title("Relatório dessa pesquisa");
            $botaoRel->set_url("?fase=relatorio");
            $botaoRel->set_target("_blank");
            $botaoRel->set_imagem($imagem);
            #$menu1->add_link($botaoRel, "right");

            $menu1->show();

            ##############

            tituloTable("Área de Servidores Cedidos da Uenf");
            br();

            $grid->fechaColuna();
            $grid->abreColuna(3, 2);

            /*
             * Menu lateral
             */

            $menu = new Menu("menuProcedimentos");
            $menu->add_item('titulo', 'Relatórios');

            $menu->add_item('titulo1', 'Atualmente Cedidos');
            $menu->add_item('linkWindow', 'Geral', '../grhRelatorios/estatutarios.cedidos.geral.php');
            $menu->add_item('linkWindow', 'Geral Lotação Anterior', '../grhRelatorios/estatutarios.cedidos.lotacao.anterior.php');
            $menu->add_item('linkWindow', 'por Órgão', '../grhRelatorios/estatutarios.cedidos.porOrgao.php');
            $menu->add_item('linkWindow', 'por Ano da Cessão', '../grhRelatorios/estatutarios.cedidos.anoCessao.php');
            $menu->add_item('linkWindow', 'Admin e Tecnicos', '../grhRelatorios/estatutarios.cedidos.admin.php');
            $menu->add_item('linkWindow', 'Profesores', '../grhRelatorios/estatutarios.cedidos.professores.php');
            $menu->add_item('titulo1', 'Histórico Ativos');
            $menu->add_item('linkWindow', 'por Ano da Cessão', '../grhRelatorios/estatutarios.cedidos.ativos.historico.php');
            $menu->add_item('titulo1', 'Histórico Todos');
            $menu->add_item('linkWindow', 'por Ano da Cessão', '../grhRelatorios/estatutarios.cedidos.historico.php');
            $menu->show();

            $grid->fechaColuna();
            $grid->abreColuna(9, 10);

            /*
             * Formulário de Pesquisa
             */

            $form = new Form('?');

            # Servidor
            $controle = new Input('parametroNome', 'texto', 'Nome:', 1);
            $controle->set_size(100);
            $controle->set_title('Nome do servidor');
            $controle->set_valor($parametroNome);
            $controle->set_onChange('formPadrao.submit();');
            $controle->set_linha(1);
            $controle->set_col(9);
            $controle->set_autofocus(true);
            $form->add_item($controle);

            $controle = new Input('parametroAgora', 'combo', 'Cedidos:', 1);
            $controle->set_size(8);
            $controle->set_title('Filtra por Centro');
            $controle->set_array(["Atualmente Cedidos", "Em Qualquer Tempo"]);
            $controle->set_valor($parametroAgora);
            $controle->set_onChange('formPadrao.submit();');
            $controle->set_linha(1);
            $controle->set_col(3);
            $form->add_item($controle);

            $form->show();

            ##############

            if ($parametroAgora == "Atualmente Cedidos") {

                /*
                 * Exibe Servidores que terminaram a cessão mas ainda estão erradamente lotados na Reitoria Cedidos
                 */

                $select = "SELECT tbservidor.idFuncional,  
                                  tbservidor.idServidor,
                                  tbhistcessao.orgao,
                                  tbhistcessao.dtInicio,
                                  tbhistcessao.dtFim,
                          tbservidor.idServidor,
                          tbservidor.idServidor
                    FROM tbhistcessao LEFT JOIN tbservidor USING (idServidor)
                                           JOIN tbpessoa USING (idPessoa)
                                           JOIN tbhistlot ON (tbservidor.idServidor = tbhistlot.idServidor)
                                           JOIN tblotacao ON (tbhistlot.lotacao=tblotacao.idLotacao)
                    WHERE current_date() > tbhistcessao.dtFim
                      AND tbhistlot.data = (select max(data) from tbhistlot where tbhistlot.idServidor = tbservidor.idServidor)
                      AND tbhistcessao.dtInicio = (select max(dtInicio) from tbhistcessao where tbhistcessao.idServidor = tbservidor.idServidor)
                      AND situacao = 1
                      AND tbhistlot.lotacao = 113";

                # Nome
                if (!is_null($parametroNome)) {

                    # Verifica se tem espaços
                    if (strpos($parametroNome, ' ') !== false) {
                        # Separa as palavras
                        $palavras = explode(' ', $parametroNome);

                        # Percorre as palavras
                        foreach ($palavras as $item) {
                            $select .= " AND (tbpessoa.nome LIKE '%{$item}%')";
                        }
                    } else {
                        $select .= " AND (tbpessoa.nome LIKE '%{$parametroNome}%')";
                    }
                }

                $select .= " ORDER BY tbpessoa.nome";

                $result = $pessoal->select($select);
                $count = $pessoal->count($select);

                # Exibe a tabela
                $tabela = new Tabela();
                $tabela->set_conteudo($result);
                $tabela->set_label(['IdFuncional', 'Nome', 'Órgão', 'Início', 'Término', 'Lotação']);
                $tabela->set_align(['center', 'left', 'left', 'center', 'center', 'left']);
                $tabela->set_titulo("Servidor(es) que já terminaram o período de cessão mas ainda estão lotados na Reitoria - Cedidos");
                $tabela->set_classe([null, "Pessoal", null, null, null, "Pessoal"]);
                $tabela->set_metodo([null, "get_nomeECargo", null, null, null, "get_lotacao"]);
                $tabela->set_funcao([null, null, null, "date_to_php", "date_to_php"]);
                $tabela->set_editar('?fase=editaServidor');
                $tabela->set_idCampo('idServidor');

                if ($count > 0) {
                    label("Problema Encontrado", "alert");
                    $tabela->show();
                }

                /*
                 * Exibe Servidores com mais de um lançamento de cessão vigente
                 */

                $select = "SELECT DISTINCT tbservidor.idFuncional,
                              tbservidor.idServidor,
                              tbhistcessao.dtInicio,
                              tbhistcessao.dtFim,
                              tbhistcessao.orgao,
                              idServidor
                         FROM tbhistcessao LEFT JOIN tbservidor USING (idServidor)
                                                JOIN tbpessoa USING (idPessoa)
                        WHERE tbservidor.situacao = 1
                          AND idPerfil = 1
                          AND (tbhistcessao.dtFim IS NULL OR (now() BETWEEN tbhistcessao.dtInicio AND tbhistcessao.dtFim))";

                # Nome
                if (!is_null($parametroNome)) {

                    # Verifica se tem espaços
                    if (strpos($parametroNome, ' ') !== false) {
                        # Separa as palavras
                        $palavras = explode(' ', $parametroNome);

                        # Percorre as palavras
                        foreach ($palavras as $item) {
                            $select .= " AND (tbpessoa.nome LIKE '%{$item}%')";
                        }
                    } else {
                        $select .= " AND (tbpessoa.nome LIKE '%{$parametroNome}%')";
                    }
                }

                $select .= " GROUP BY tbservidor.idFuncional HAVING COUNT(idFuncional) > 1";

                $result = $pessoal->select($select);
                $count = $pessoal->count($select);

                # Exibe a tabela
                $tabela = new Tabela();
                $tabela->set_conteudo($result);
                $tabela->set_label(['IdFuncional', 'Nome', 'Início', 'Término', 'Órgão']);
                $tabela->set_align(['center', 'left', 'center', 'center', 'left']);
                $tabela->set_titulo("Servidor(es) Com Mais de uma Cessão Vigente Cadastrada");
                $tabela->set_classe([null, "Pessoal", null, null, null, "Pessoal"]);
                $tabela->set_metodo([null, "get_nomeECargo", null, null, null, "get_lotacao"]);
                $tabela->set_funcao([null, null, "date_to_php", "date_to_php"]);
                $tabela->set_editar('?fase=editaServidor');
                $tabela->set_idCampo('idServidor');

                if ($count > 0) {
                    label("Problema Encontrado", "alert");
                    $tabela->show();
                }

                /*
                 * Exibe os servidores atualmente cedidos
                 */

                $select = "SELECT year(tbhistcessao.dtInicio),
                              tbservidor.idFuncional,
                              idServidor,
                              tbhistcessao.dtInicio,
                              tbhistcessao.dtFim,
                              tbhistcessao.orgao,
                              idServidor,
                              idServidor
                         FROM tbhistcessao LEFT JOIN tbservidor USING (idServidor)
                                                JOIN tbpessoa USING (idPessoa)
                        WHERE tbservidor.situacao = 1
                          AND idPerfil = 1
                          AND (tbhistcessao.dtFim IS NULL OR (now() BETWEEN tbhistcessao.dtInicio AND tbhistcessao.dtFim))";

                # Nome
                if (!is_null($parametroNome)) {

                    # Verifica se tem espaços
                    if (strpos($parametroNome, ' ') !== false) {
                        # Separa as palavras
                        $palavras = explode(' ', $parametroNome);

                        # Percorre as palavras
                        foreach ($palavras as $item) {
                            $select .= " AND (tbpessoa.nome LIKE '%{$item}%')";
                        }
                    } else {
                        $select .= " AND (tbpessoa.nome LIKE '%{$parametroNome}%')";
                    }
                }

                $select .= " ORDER BY dtInicio desc";

                $result = $pessoal->select($select);

                # Monta a tabela
                $tabela = new Tabela();
                $tabela->set_conteudo($result);
                $tabela->set_label(["Ano", "IdFuncional", "Nome", "Início", "Término", "Órgão", "Lotação<br/>Correta?"]);
                $tabela->set_titulo("Servidores Atualmente Cedidos");
                $tabela->set_align(["center", "center", "left", "center", "center", "left"]);
                $tabela->set_funcao([null, null, null, "date_to_php", "date_to_php"]);
                $tabela->set_width([5, 10, 20, 10, 10, 30, 5]);

                $tabela->set_classe([null, null, "Pessoal", null, null, null, "Cessao"]);
                $tabela->set_metodo([null, null, "get_nomeECargo", null, null, null, "lotacaoCorreta"]);

                $tabela->set_rowspan(0);
                $tabela->set_grupoCorColuna(0);

                $tabela->set_idCampo('idServidor');
                $tabela->set_editar('?fase=editaServidor');
                $tabela->show();
            } else {

                /*
                 * Exibe o histórico de servidores cedidos da Uenf
                 */

                $select = "SELECT year(tbhistcessao.dtInicio),
                              tbservidor.idFuncional,
                              tbpessoa.nome,
                              tbhistcessao.dtInicio,
                              tbhistcessao.dtFim,
                              tbhistcessao.orgao,
                              idServidor
                         FROM tbhistcessao LEFT JOIN tbservidor USING (idServidor)
                                                JOIN tbpessoa USING (idPessoa)
                        WHERE tbservidor.situacao = 1
                          AND idPerfil = 1";

                # Nome
                if (!is_null($parametroNome)) {

                    # Verifica se tem espaços
                    if (strpos($parametroNome, ' ') !== false) {
                        # Separa as palavras
                        $palavras = explode(' ', $parametroNome);

                        # Percorre as palavras
                        foreach ($palavras as $item) {
                            $select .= " AND (tbpessoa.nome LIKE '%{$item}%')";
                        }
                    } else {
                        $select .= " AND (tbpessoa.nome LIKE '%{$parametroNome}%')";
                    }
                }

                $select .= " ORDER BY dtInicio desc";

                $result = $pessoal->select($select);

                # Monta a tabela
                $tabela = new Tabela();
                $tabela->set_conteudo($result);
                $tabela->set_label(["Ano", "IdFuncional", "Nome", "Início", "Término", "Órgão"]);
                $tabela->set_titulo("Servidores da Uenf que Foram Cedidos em Algum Momento para Outro Órgão");
                $tabela->set_align(["center", "center", "left", "center", "center", "left"]);
                $tabela->set_funcao([null, null, null, "date_to_php", "date_to_php"]);
                #$tabela->set_width([30, 10, 20, 15, 15]);

                $tabela->set_rowspan(0);
                $tabela->set_grupoCorColuna(0);

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
            set_session('origem', 'areaCedidos.php');

            # Carrega a página específica
            loadPage('servidorCessao.php');
            break;

        ################################################################
    }

    $page->terminaPagina();
} else {
    loadPage("../../areaServidor/sistema/login.php");
}


