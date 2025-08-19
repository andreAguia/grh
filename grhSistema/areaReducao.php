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
        $atividade = "Visualizou a área de redução de carga horária";
        $data = date("Y-m-d H:i:s");
        $intra->registraLog($idUsuario, $data, $atividade, null, null, 7);
    }

    # pega o id (se tiver)
    $id = soNumeros(get('id'));
    set_session('areaPremio', false);

    # Pega os parâmetros
    $parametroNomeMat = post('parametroNomeMat', get_session('parametroNomeMat'));
    $parametroStatus = post('parametroStatus', get_session('parametroStatus', 0));
    $parametroOrigem = post('parametroOrigem', get_session('parametroOrigem', 0));
    $parametroNome = retiraAspas(post('parametroNome', get_session('parametroNome')));

    # Joga os parâmetros par as sessions    
    set_session('parametroNomeMat', $parametroNomeMat);
    set_session('parametroStatus', $parametroStatus);
    set_session('parametroOrigem', $parametroOrigem);
    set_session('parametroNome', $parametroNome);

    # Começa uma nova página
    $page = new Page();
    $page->iniciaPagina();

    # Cabeçalho da Página
    if ($fase <> "relatorio") {
        AreaServidor::cabecalho();
    }

    # Status
    $statusPossiveis = array(
        array(0, "-- Todos --"),
        array(1, "Em Aberto"),
        array(2, "Vigente"),
        array(3, "Arquivado"),
        array(4, "Aguardando Publicação")
    );

################################################################

    switch ($fase) {

        case "":
        case "listaReducao" :
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

            # Incluir
            if (Verifica::acesso($idUsuario, [1, 2])) {
                $botaoInserir = new Button("Incluir", "?fase=incluir");
                $botaoInserir->set_title("Incluir um Servidor");
                $menu1->add_link($botaoInserir, "right");
            }

            # Calendário
            $botaoCalendario = new Link("Calendário", "calendario.php");
            $botaoCalendario->set_class('button');
            $botaoCalendario->set_title('Exibe o calendário');
            $botaoCalendario->set_target("_calendario");
            $menu1->add_link($botaoCalendario, "right");

            # Procedimentos
            $linkBotao3 = new Link("Procedimentos", "servidorReducao.php?fase=procedimentos");
            $linkBotao3->set_class('button');
            $linkBotao3->set_title('Regras da readaptação');
            $linkBotao3->set_target("_blank");
            $menu1->add_link($linkBotao3, "right");

            # Relatórios
            $imagem = new Imagem(PASTA_FIGURAS . 'print.png', null, 15, 15);
            $botaoRel = new Button();
            $botaoRel->set_title("Relatório dessa pesquisa");
            $botaoRel->set_url("../grhRelatorios/reducao.geral.php");
            $botaoRel->set_target("_blank");
            $botaoRel->set_imagem($imagem);
            $menu1->add_link($botaoRel, "right");

            # Redução da Carga Horária
            $botaoRel = new Button('Readaptação');
            $botaoRel->set_url("?fase=listaReadaptacao");
            #$menu1->add_link($botaoRel,"right");

            $menu1->show();

            ###
            # Formulário de Pesquisa
            $form = new Form('?fase=listaReducao');

            # Nome    
            $controle = new Input('parametroNomeMat', 'texto', 'Servidor:', 1);
            $controle->set_size(100);
            $controle->set_title('Filtra por Nome');
            $controle->set_valor($parametroNomeMat);
            $controle->set_onChange('formPadrao.submit();');
            $controle->set_linha(1);
            $controle->set_col(8);
            $controle->set_autofocus(true);
            $form->add_item($controle);

            # Status    
            $controle = new Input('parametroStatus', 'combo', 'Status:', 1);
            $controle->set_size(30);
            $controle->set_title('Filtra por Status');
            $controle->set_array($statusPossiveis);
            $controle->set_valor($parametroStatus);
            $controle->set_onChange('formPadrao.submit();');
            $controle->set_linha(1);
            $controle->set_col(4);
            $form->add_item($controle);

            $form->show();

            ###
            # Pega o time inicial
            $time_start = microtime(true);

            # Pega os dados
            $select = "SELECT CASE tbreducao.tipo
                                WHEN 1 THEN 'Inicial'
                                WHEN 2 THEN 'Renovação'
                                ELSE '--'
                              END,                              
                              idReducao,
                              tbservidor.idServidor,                              
                              idServidor,
                              idReducao,
                              idReducao,
                              idReducao,
                              idServidor,
                              ADDDATE(dtInicio,INTERVAL periodo MONTH) as dtTermino
                         FROM tbservidor JOIN tbpessoa USING (idPessoa)
                                         JOIN tbreducao USING (idServidor)
                                         JOIN tbperfil USING (idPerfil)
                        WHERE tbperfil.tipo <> 'Outros' ";

            # status
            if ($parametroStatus <> 0) {
                $select .= " AND status = " . $parametroStatus;
            }

            # Nome
            if (!is_null($parametroNomeMat)) {

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

            $select .= " ORDER BY status, 
                    CASE WHEN status = 3 THEN dtTermino END DESC,
                    CASE WHEN status <> 3 THEN dtTermino END ASC,
                    dtInicio";

            $resumo = $pessoal->select($select);

            # Monta a tabela
            $tabela = new Tabela();
            $tabela->set_conteudo($resumo);
            $tabela->set_label(["Tipo", "Status", "Servidor", "Processo", "Resultado", "Publicação", "Período"]);
            $tabela->set_align(["center", "center", "left", "center", "center", "center", "left"]);
            $tabela->set_classe([null, "ReducaoCargaHoraria", "Pessoal", "ReducaoCargaHoraria", "ReducaoCargaHoraria", "ReducaoCargaHoraria", "ReducaoCargaHoraria"]);
            $tabela->set_metodo([null, "exibeStatus", "get_nomeEidFuncional", "get_numProcesso", "exibeResultado", "exibePublicacao", "exibePeriodo"]);
            $tabela->set_titulo("Redução de Carga Horária");
            $tabela->set_idCampo('idServidor');
            $tabela->set_editar('?fase=editaServidor');

            $tabela->set_formatacaoCondicional(array(
                array('coluna' => 1,
                    'valor' => 'Em Aberto',
                    'operador' => '=',
                    'id' => 'emAberto'),
                array('coluna' => 1,
                    'valor' => 'Arquivado',
                    'operador' => '=',
                    'id' => 'arquivado'),
                array('coluna' => 1,
                    'valor' => 'Vigente',
                    'operador' => '=',
                    'id' => 'vigenteReducao'),
                array('coluna' => 1,
                    'valor' => 'Aguardando Publicação',
                    'operador' => '=',
                    'id' => 'aguardando')
            ));
            $tabela->show();

            # Pega o time final
            $time_end = microtime(true);
            $time = $time_end - $time_start;
            p(number_format($time, 4, '.', ',') . " segundos", "right", "f10");

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
            set_session('origem', 'areaReducao.php');

            # Carrega a página específica
            loadPage('servidorReducao.php');
            break;

        ################################################################

        case "incluir" :

            # Limita o tamanho da tela
            $grid = new Grid("center");
            $grid->abreColuna(12);
            br(6);

            tituloTable("Incluir Servidor");
            br(2);

            aguarde();
            br();

            $grid->fechaColuna();
            $grid->abreColuna(5);
            p("Aguarde...", "center");
            $grid->fechaColuna();
            $grid->fechaGrid();

            loadPage('?fase=incluir2');
            break;

        ################################################################

        case "incluir2" :

            # Cria um menu
            $menu = new MenuBar();

            # Voltar
            $botaoVoltar = new Link("Voltar", "?");
            $botaoVoltar->set_class('button');
            $botaoVoltar->set_title('Voltar a página anterior');
            $botaoVoltar->set_accessKey('V');
            $menu->add_link($botaoVoltar, "left");

            $menu->show();

            ###
            # Parâmetros
            $form = new Form('?fase=incluir');

            # Nome ou Matrícula
            $controle = new Input('parametroNome', 'texto', 'Nome do Servidor:', 1);
            $controle->set_size(100);
            $controle->set_title('Nome, matrícula ou ID:');
            $controle->set_valor($parametroNome);
            $controle->set_autofocus(true);
            $controle->set_onChange('formPadrao.submit();');
            $controle->set_linha(1);
            $controle->set_col(12);
            $form->add_item($controle);

            $form->show();

            ###

            $select = 'SELECT idFuncional,
                              tbpessoa.nome,
                              tbservidor.idServidor,
                              tbservidor.idServidor
                         FROM tbservidor LEFT JOIN tbpessoa USING (idPessoa) 
                         WHERE situacao = 1 AND idPerfil = 1';
            # nome
            if (!is_null($parametroNome)) {
                $select .= " AND tbpessoa.nome LIKE '%$parametroNome%'";
            }

            $select .= " ORDER BY tbpessoa.nome";

            # Pega os dados
            $conteudo = $pessoal->select($select);

            # Monta a tabela
            $tabela = new Tabela();

            $tabela->set_titulo("Escolha o Servidor");
            $tabela->set_conteudo($conteudo);
            $tabela->set_label(array("IdFuncional", "Servidor", "Cargo", "Lotação"));
            $tabela->set_align(array("center", "left", "left", "left"));
            $tabela->set_classe(array(null, null, "Pessoal", "Pessoal"));
            $tabela->set_metodo(array(null, null, "get_cargo", "get_lotacao"));
            $tabela->set_idCampo('idServidor');
            $tabela->set_editar('?fase=insere&id=');
            $tabela->set_nomeColunaEditar("Inserir");
            $tabela->set_textoRessaltado($parametroNome);
            $tabela->show();
            break;

        ################################################################

        case "insere" :
            br(8);
            aguarde();

            # Informa o $id Servidor
            set_session('idServidorPesquisado', $id);

            # Informa a origem
            set_session('origem', 'areaReducao.php');

            # Carrega a página específica
            loadPage('servidorReducao.php');
            break;

        ################################################################
    }

    $page->terminaPagina();
} else {
    loadPage("../../areaServidor/sistema/login.php");
}


