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
        $atividade = "Visualizou a área de controle de frequência de cedidos da Uenf para outros órgãos";
        $data = date("Y-m-d H:i:s");
        $intra->registraLog($idUsuario, $data, $atividade, null, null, 7);
    }

    # Pega os parâmetros
    $parametroAgora = post('parametroAgora', get_session('parametroAgora', "Atualmente Cedidos"));

    # Joga os parâmetros par as sessions
    set_session('parametroAgora', $parametroAgora);

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
            $grid->abreColuna(3,2);

            $menu = new Menu("menuProcedimentos");
            $menu->add_item('titulo', 'Relatórios');
            
            ###############################################   Parei aqui!!!
            
            $menu->add_item('titulo1', 'Atualmente Cedidos');
            $menu->add_item('linkWindow', 'Geral', '../grhRelatorios/estatutarios.cedidos.php');
            $menu->add_item('linkWindow', 'Geral Lotação Anterior', '../grhRelatorios/estatutarios.cedidos.lotacao.anterior.php');
            $menu->add_item('linkWindow', 'por Órgão', '../grhRelatorios/estatutarios.cedidos.orgao.php');
            $menu->add_item('linkWindow', 'por Ano da Cessão', '../grhRelatorios/estatutarios.cedidos.anoCessao.php');
            $menu->add_item('linkWindow', 'Admin e Tecnicos', '../grhRelatorios/estatutarios.cedidos.admin.php');
            $menu->add_item('linkWindow', 'Docentes', '../grhRelatorios/estatutarios.cedidos.professores.php');
            $menu->add_item('titulo1', 'Histórico Ativos');
            $menu->add_item('linkWindow', 'por Ano da Cessão', '../grhRelatorios/estatutarios.cedidos.ativos.historico.php');
            $menu->add_item('titulo1', 'Histórico Todos');
            $menu->add_item('linkWindow', 'por Ano da Cessão', '../grhRelatorios/estatutarios.cedidos.historico.php');
            $menu->show();

            $grid->fechaColuna();
            $grid->abreColuna(9,10);

            ##############

            $form = new Form('?');

            $controle = new Input('parametroAgora', 'combo', 'Cedidos:', 1);
            $controle->set_size(8);
            $controle->set_title('Filtra por Centro');
            $controle->set_array(["Atualmente Cedidos", "Em Qualquer Tempo"]);
            $controle->set_valor($parametroAgora);
            $controle->set_onChange('formPadrao.submit();');
            $controle->set_linha(1);
            $controle->set_autofocus(true);
            $controle->set_col(3);
            $form->add_item($controle);

            $form->show();

            ##############

            if ($parametroAgora == "Atualmente Cedidos") {

                ##############

                $select = 'SELECT tbservidor.idFuncional,  
                          tbpessoa.nome,
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
                      AND tbhistlot.lotacao = 113 ORDER BY tbpessoa.nome';

                $result = $pessoal->select($select);
                $count = $pessoal->count($select);

                # Exibe a tabela
                $tabela = new Tabela();
                $tabela->set_conteudo($result);
                $tabela->set_label(['IdFuncional', 'Nome', 'Órgão', 'Início', 'Término', 'Lotação']);
                $tabela->set_align(['center', 'left', 'left', 'center', 'center', 'left']);
                $tabela->set_titulo('Servidor(es) que já terminaram o período de cessão mas ainda estão lotados na Reitoria - Cedidos');
                $tabela->set_classe([null, null, null, null, null, "Pessoal"]);
                $tabela->set_metodo([null, null, null, null, null, "get_lotacao"]);
                $tabela->set_funcao([null, null, null, "date_to_php", "date_to_php"]);
                $tabela->set_editar('?fase=editaServidor');
                $tabela->set_idCampo('idServidor');

                if ($count > 0) {
                    $tabela->show();
                }

                ##############

                $select = "SELECT year(tbhistcessao.dtInicio),
                              tbservidor.idFuncional,
                              tbpessoa.nome,
                              tbhistcessao.dtInicio,
                              tbhistcessao.dtFim,
                              tbhistcessao.orgao,
                              idServidor,
                              idServidor
                         FROM tbhistcessao LEFT JOIN tbservidor USING (idServidor)
                                                JOIN tbpessoa USING (idPessoa)
                        WHERE tbservidor.situacao = 1
                          AND idPerfil = 1
                          AND (tbhistcessao.dtFim IS NULL OR (now() BETWEEN tbhistcessao.dtInicio AND tbhistcessao.dtFim)) 
                     ORDER BY dtInicio desc";

                $result = $pessoal->select($select);

                # Monta a tabela
                $tabela = new Tabela();
                $tabela->set_conteudo($result);
                $tabela->set_label(["Ano", "IdFuncional", "Nome", "Início", "Término", "Órgão", "Lotação<br/>Correta?"]);
                $tabela->set_titulo("Servidores Atualmente Cedidos");
                $tabela->set_align(["center", "center", "left", "center", "center", "left"]);
                $tabela->set_funcao([null, null, null, "date_to_php", "date_to_php"]);
                $tabela->set_width([5, 10, 20, 10, 10, 30, 5]);

                $tabela->set_classe([null, null, null, null, null, null, "Cessao"]);
                $tabela->set_metodo([null, null, null, null, null, null, "lotacaoCorreta"]);

                $tabela->set_rowspan(0);
                $tabela->set_grupoCorColuna(0);

                $tabela->set_idCampo('idServidor');
                $tabela->set_editar('?fase=editaServidor');
                $tabela->show();
            } else {
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
                          AND idPerfil = 1
                     ORDER BY dtInicio desc";

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


