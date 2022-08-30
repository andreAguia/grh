<?php

/**
 * Área de Acumulação
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
    $acumul = new AcumulacaoDeclaracao();

    # Verifica a fase do programa
    $fase = get('fase');

    # Verifica se veio menu grh e registra o acesso no log
    $grh = get('grh', false);

    if ($grh) {
        # Grava no log a atividade
        $atividade = "Cadastro do servidor - Acumulações de cargos públicos";
        $data = date("Y-m-d H:i:s");
        $intra->registraLog($idUsuario, $data, $atividade, null, null, 7);
    }

    # pega o id (se tiver)
    $id = soNumeros(get('id'));

    # Pega os parâmetros
    $parametroAno = post('parametroAno', get_session('parametroAno',$acumul->getUltimoAnoDeclaracao()));
    $parametroLotacao = post('parametroLotacao', get_session('parametroLotacao', '*'));
    $parametroNome = retiraAspas(post('parametroNome', get_session('parametroNome')));

    # Joga os parâmetros par as sessions
    set_session('parametroAno', $parametroAno);
    set_session('parametroLotacao', $parametroLotacao);
    set_session('parametroNome', $parametroNome);

    # Começa uma nova página
    $page = new Page();
    $page->iniciaPagina();

    # Cabeçalho da Página
    AreaServidor::cabecalho();

    # Limita a Tela 
    $grid = new Grid();
    $grid->abreColuna(12);
    br();

################################################################

    switch ($fase) {
        case "":
            br(4);
            aguarde();
            br();

            # Limita a tela
            $grid1 = new Grid("center");
            $grid1->abreColuna(5);
            p("Aguarde...", "center");
            $grid1->fechaColuna();
            $grid1->fechaGrid();

            loadPage('?fase=listaAcumulacao');
            break;

################################################################

        case "listaAcumulacao" :

            /*
             *  Cria um menu
             */
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
            $botaoRel->set_url("../grhRelatorios/acumulacao.geral.php");
            $botaoRel->set_target("_blank");
            $botaoRel->set_imagem($imagem);
            #$menu1->add_link($botaoRel, "right");
            $menu1->show();

            /*
             *  Formulário de Pesquisa
             */
            $form = new Form('?');

            # AnoReferencia
            $comboAno = $pessoal->select('SELECT DISTINCT anoReferencia, anoReferencia
            FROM tbacumulacaodeclaracao
            ORDER BY anoReferencia');

            if (empty($comboAno)) {
                $comboAno[] = $acumul->getUltimoAnoDeclaracao();
            }

            # Ano
            $controle = new Input('parametroAno', 'combo', 'Referência:', 1);
            $controle->set_size(5);
            $controle->set_title('Ano do início do serviço');
            $controle->set_valor($parametroAno);
            $controle->set_onChange('formPadrao.submit();');
            $controle->set_linha(1);
            $controle->set_col(2);
            $controle->set_array($comboAno);
            #$controle->set_autofocus(true);
            $form->add_item($controle);

            # Nome    
            $controle = new Input('parametroNome', 'texto', 'Servidor:', 1);
            $controle->set_size(100);
            $controle->set_title('Filtra por Nome');
            $controle->set_valor($parametroNome);
            $controle->set_onChange('formPadrao.submit();');
            $controle->set_linha(1);
            $controle->set_col(4);
            $controle->set_autofocus(true);
            $form->add_item($controle);

            # Lotação
            $result = $pessoal->select('(SELECT idlotacao, concat(IFnull(tblotacao.DIR, ""), " - ", IFnull(tblotacao.GER, ""), " - ", IFnull(tblotacao.nome, "")) lotacao
                                           FROM tblotacao
                                          WHERE ativo) UNION(SELECT distinct DIR, DIR
                                           FROM tblotacao
                                          WHERE ativo)
                                       ORDER BY 2');
            array_unshift($result, array('*', '--Todos--'));

            $controle = new Input('parametroLotacao', 'combo', 'Lotação:', 1);
            $controle->set_size(30);
            $controle->set_title('Filtra por Lotação');
            $controle->set_array($result);
            $controle->set_valor($parametroLotacao);
            $controle->set_onChange('formPadrao.submit();');
            $controle->set_linha(1);
            $controle->set_col(6);
            $form->add_item($controle);

            $form->show();

            ######################################################

            $grid->fechaColuna();
            $grid->abreColuna(3);

            
            $acumul->showResumoGeral($parametroAno, $parametroLotacao, $parametroNome);
            $acumul->showResumoAcumula($parametroAno, $parametroLotacao, $parametroNome);

            ######################################################
            # Só exibe se não tiver pesquisa por nome
            if (empty($parametroNome)) {

                if ($parametroLotacao <> "*") {
                    tituloTable("Relatórios - {$pessoal->get_nomeLotacao($parametroLotacao)}");
                } else {
                    tituloTable("Relatórios");
                }
                $menu = new Menu("menuProcedimentos");

                ### Entregaram ###
                $menu->add_item('titulo', "Servidores que Entregaram");
                $menu->add_item('linkWindow', "Geral", '../grhRelatorios/acumulacao.entregaram.geral.php');
                $menu->add_item('linkWindow', 'Geral - Agrupado por Lotação', '../grhRelatorios/acumulacao.entregaram.lotacao.php');

                $menu->add_item('linkWindow', "Declararam Acumular", '../grhRelatorios/acumulacao.entregaram.acumulam.php');
                $menu->add_item('linkWindow', "Declararam Não Acumular", '../grhRelatorios/acumulacao.entregaram.nao.acumulam.php');

                ### Não Entregaram
                $menu->add_item('titulo', 'Servidores que Não Entregaram');
                $menu->add_item('linkWindow', 'Geral', '../grhRelatorios/acumulacao.nao.entregaram.geral.php');
                $menu->add_item('linkWindow', 'Geral - Agrupado por Lotação', '../grhRelatorios/acumulacao.nao.entregaram.lotacao.php');

                $menu->show();
            }

            $grid->fechaColuna();
            $grid->abreColuna(9);

            ######################################################
            # Pega o time inicial
            $time_start = microtime(true);

            # Lista de quem entregou
            $select = "SELECT dtEntrega,
                      IF(acumula,'SIM','Não'),
                      tbservidor.idServidor,
                      tbacumulacaodeclaracao.idServidor,
                      processo,
                      idAcumulacaoDeclaracao
                 FROM tbacumulacaodeclaracao LEFT JOIN tbservidor USING (idServidor)
                                             LEFT JOIN tbpessoa USING (idPessoa)
                                             LEFT JOIN tbhistlot USING (idServidor)
                                             LEFT JOIN tblotacao ON (tbhistlot.lotacao=tblotacao.idLotacao)
                WHERE situacao = 1
                  AND tbhistlot.data = (select max(data) from tbhistlot where tbhistlot.idServidor = tbservidor.idServidor)
                  AND anoReferencia = '{$parametroAno}'";

            # nome
            if (!empty($parametroNome)) {
                $select .= " AND tbpessoa.nome LIKE '%{$parametroNome}%'";
            }

            # lotacao
            if ($parametroLotacao <> "*") {
                # Verifica se o que veio é numérico
                if (is_numeric($parametroLotacao)) {
                    $select .= " AND (tblotacao.idlotacao = '{$parametroLotacao}')";
                } else { # senão é uma diretoria genérica
                    $select .= " AND (tblotacao.DIR = '{$parametroLotacao}')";
                }
            }

            $select .= " ORDER BY anoReferencia, tbpessoa.nome";

            $resumo = $pessoal->select($select);

            # Monta a tabela
            $tabela = new Tabela();
            $tabela->set_titulo("Servidores Ativos que Entregaram a Declaração do Ano {$parametroAno}");
            $tabela->set_conteudo($resumo);
            $tabela->set_label(array("Entregue em", "Acumula?", "Servidor", "Lotação", "Processo"));
            $tabela->set_align(array("center", "center", "left", "left", "left"));
            $tabela->set_funcao(array("date_to_php"));
            $tabela->set_classe(array(null, null, "Pessoal", "Pessoal"));
            $tabela->set_metodo(array(null, null, "get_nomeECargoEPerfil", "get_Lotacao"));
            $tabela->set_formatacaoCondicional(array(
                array('coluna'   => 1,
                    'valor'    => 'SIM',
                    'operador' => '=',
                    'id'       => 'problemas')));

            $tabela->set_idCampo('idServidor');
            $tabela->set_editar('?fase=editaServidor');
            $tabela->show();

            # Lista de quem NÃO entregou
            $select = "SELECT '-' as dtEntrega,
                           '-' as acumula,
                           tbservidor.idServidor,
                           tbservidor.idServidor,
                           '-'
                      FROM tbservidor LEFT JOIN tbpessoa USING (idPessoa)
                                      LEFT JOIN tbhistlot USING (idServidor)
                                      LEFT JOIN tblotacao ON (tbhistlot.lotacao=tblotacao.idLotacao)
                     WHERE situacao = 1
                     AND tbhistlot.data = (select max(data) from tbhistlot where tbhistlot.idServidor = tbservidor.idServidor)
                     AND tbservidor.idServidor NOT IN (SELECT tbacumulacaodeclaracao.idServidor FROM tbacumulacaodeclaracao LEFT JOIN tbservidor USING (idServidor)
                                             LEFT JOIN tbpessoa USING (idPessoa)
                                             LEFT JOIN tbhistlot USING (idServidor)
                                             LEFT JOIN tblotacao ON (tbhistlot.lotacao=tblotacao.idLotacao)
                WHERE situacao = 1
                  AND tbhistlot.data = (select max(data) from tbhistlot where tbhistlot.idServidor = tbservidor.idServidor)
                  AND anoReferencia = '{$parametroAno}'";

            # lotacao
            if ($parametroLotacao <> "*") {
                # Verifica se o que veio é numérico
                if (is_numeric($parametroLotacao)) {
                    $select .= " AND (tblotacao.idlotacao = '{$parametroLotacao}')";
                } else { # senão é uma diretoria genérica
                    $select .= " AND (tblotacao.DIR = '{$parametroLotacao}')";
                }
            }
            $select .= ") ";

            # lotacao
            if ($parametroLotacao <> "*") {
                # Verifica se o que veio é numérico
                if (is_numeric($parametroLotacao)) {
                    $select .= " AND (tblotacao.idlotacao = '{$parametroLotacao}')";
                } else { # senão é uma diretoria genérica
                    $select .= " AND (tblotacao.DIR = '{$parametroLotacao}')";
                }
            }

            # nome
            if (!empty($parametroNome)) {
                $select .= " AND tbpessoa.nome LIKE '%{$parametroNome}%'";
            }

            $select .= " ORDER BY tbpessoa.nome";


            $resumo = $pessoal->select($select);

            # Monta a tabela
            $tabela = new Tabela();
            $tabela->set_titulo("Servidores Ativos que NÃO Entregaram a Declaração do Ano {$parametroAno}");
            $tabela->set_conteudo($resumo);
            $tabela->set_label(["Entregue em", "Acumula?", "Servidor", "Lotação", "Processo"]);
            $tabela->set_align(["center", "center", "left", "left", "center"]);
            $tabela->set_classe([null, null, "Pessoal", "Pessoal"]);
            $tabela->set_metodo([null, null, "get_nomeECargoEPerfil", "get_Lotacao"]);
            $tabela->set_idCampo('idServidor');
            $tabela->set_editar('?fase=editaServidor');
            $tabela->show();

            # Pega o time final
            $time_end = microtime(true);
            $time = $time_end - $time_start;
            p(number_format($time, 4, '.', ',') . " segundos", "right", "f10");
            break;

        ################################################################

        case "editaServidor" :
            br(8);
            aguarde();

            # Informa o $id Servidor
            set_session('idServidorPesquisado', $id);

            # Informa a origem
            set_session('origem', 'areaAcumulacaoDeclaracao.php');

            # Carrega a página específica
            loadPage('servidorAcumulacaoDeclaracao.php');
            break;

        ################################################################
    }

    $grid->fechaColuna();
    $grid->fechaGrid();

    $page->terminaPagina();
} else {
    loadPage("../../areaServidor/sistema/login.php");
}


