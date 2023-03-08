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
        $atividade = "Visualizou a área de readaptação";
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

    # Origem
    $origensPossiveis = array(
        array(0, "-- Todos --"),
        array(1, "Ex-Ofício"),
        array(2, "Solicitada")
    );

################################################################

    switch ($fase) {

        case "" :
        case "listaReadaptacao" :
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

            $linkBotao3 = new Link("Procedimentos", "servidorReducao.php?fase=procedimentos");
            $linkBotao3->set_class('button');
            $linkBotao3->set_title('Regras da readaptação');
            $linkBotao3->set_target("_blank");
            $menu1->add_link($linkBotao3, "right");

            # Site
            $botaoSite = new Button("Site da GRH");
            $botaoSite->set_target('_blank');
            $botaoSite->set_title("Pagina da GRH");
            $botaoSite->set_url("https://uenf.br/dga/grh/gerencia-de-recursos-humanos/readaptacao/");
            $menu1->add_link($botaoSite, "right");

            # Relatórios
            $imagem = new Imagem(PASTA_FIGURAS . 'print.png', null, 15, 15);
            $botaoRel = new Button();
            $botaoRel->set_title("Relatório dessa pesquisa");
            $botaoRel->set_url("../grhRelatorios/readaptacao.geral.php");
            $botaoRel->set_target("_blank");
            $botaoRel->set_imagem($imagem);
            $menu1->add_link($botaoRel, "right");

            # Redução da Carga Horária
            $botaoRel = new Button('Redução da Carga Horária');
            $botaoRel->set_url("?fase=listaReducao");
            #$menu1->add_link($botaoRel,"right");

            $menu1->show();

            ###
            # Formulário de Pesquisa
            $form = new Form('?fase=listaReadaptacao');

            # Nome    
            $controle = new Input('parametroNomeMat', 'texto', 'Servidor:', 1);
            $controle->set_size(100);
            $controle->set_title('Filtra por Nome');
            $controle->set_valor($parametroNomeMat);
            $controle->set_onChange('formPadrao.submit();');
            $controle->set_linha(1);
            $controle->set_col(6);
            $controle->set_autofocus(true);
            $form->add_item($controle);

            # Origem    
            $controle = new Input('parametroOrigem', 'combo', 'Origem:', 1);
            $controle->set_size(30);
            $controle->set_title('Filtra por Origem');
            $controle->set_array($origensPossiveis);
            $controle->set_valor($parametroOrigem);
            $controle->set_onChange('formPadrao.submit();');
            $controle->set_linha(1);
            $controle->set_col(3);
            $form->add_item($controle);

            # Status    
            $controle = new Input('parametroStatus', 'combo', 'Status:', 1);
            $controle->set_size(30);
            $controle->set_title('Filtra por Status');
            $controle->set_array($statusPossiveis);
            $controle->set_valor($parametroStatus);
            $controle->set_onChange('formPadrao.submit();');
            $controle->set_linha(1);
            $controle->set_col(3);
            $form->add_item($controle);

            $form->show();

            ###
            # Pega o time inicial
            $time_start = microtime(true);

            # Pega os dados
            $select = "SELECT CASE origem
                                WHEN 1 THEN 'Ex-Ofício'
                                WHEN 2 THEN 'Solicitada'
                                ELSE '--'
                              END,
                              CASE tipo
                                WHEN 1 THEN 'Inicial'
                                WHEN 2 THEN 'Renovação'
                                ELSE '--'
                              END,
                              idReadaptacao,
                              idServidor,
                              processo,
                              idReadaptacao,
                              idReadaptacao,
                              idReadaptacao,
                              idReadaptacao,
                              idReadaptacao,                                   
                              idReadaptacao,
                              ADDDATE(dtInicio,INTERVAL periodo MONTH) as dtTermino,
                              idServidor
                         FROM tbservidor JOIN tbpessoa USING (idPessoa)
                                         JOIN tbreadaptacao USING (idServidor)
                        WHERE tbservidor.idPerfil <> 10";

            # status
            if ($parametroStatus <> 0) {
                $select .= " AND status = " . $parametroStatus;
            }

            # origem
            if ($parametroOrigem <> 0) {
                $select .= " AND origem = " . $parametroOrigem;
            }

            # nome
            if (!is_null($parametroNomeMat)) {
                $select .= " AND tbpessoa.nome LIKE '%$parametroNomeMat%'";
            }

            $select .= " ORDER BY status, 
                    CASE WHEN status = 3 THEN dtTermino END DESC,
                    CASE WHEN status <> 3 THEN dtTermino END ASC,
                    dtInicio";

            $resumo = $pessoal->select($select);

            # Monta a tabela
            $tabela = new Tabela();
            $tabela->set_conteudo($resumo);
            $tabela->set_label(["Origem", "Tipo", "Status", "Servidor", "Processo", "Resultado", "Publicação", "Período"]);
            $tabela->set_align(["center", "center", "center", "left", "center", "center", "center", "left"]);
            $tabela->set_classe([null, null, "Readaptacao", "Pessoal", null, "Readaptacao", "Readaptacao", "Readaptacao"]);
            $tabela->set_metodo([null, null, "exibeStatus", "get_nomeEidFuncional", null, "exibeResultado", "exibePublicacao", "exibePeriodo"]);
            $tabela->set_titulo("Readaptação");
            $tabela->set_idCampo('idServidor');
            $tabela->set_editar('?fase=editaServidor');

            $tabela->set_formatacaoCondicional(array(
                array('coluna' => 2,
                    'valor' => 'Em Aberto',
                    'operador' => '=',
                    'id' => 'emAberto'),
                array('coluna' => 2,
                    'valor' => 'Arquivado',
                    'operador' => '=',
                    'id' => 'arquivado'),
                array('coluna' => 2,
                    'valor' => 'Vigente',
                    'operador' => '=',
                    'id' => 'vigenteReducao'),
                array('coluna' => 2,
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
            set_session('origem', 'areaReadaptacao.php');

            # Carrega a página específica
            loadPage('servidorReadaptacao.php');
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

            $grid = new Grid();
            $grid->abreColuna(12);
            br();

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

            $grid->fechaColuna();
            $grid->fechaGrid();
            break;

        ################################################################

        case "insere" :
            br(8);
            aguarde();

            # Informa o $id Servidor
            set_session('idServidorPesquisado', $id);

            # Informa a origem
            set_session('origem', 'areaReadaptacao.php');

            # Carrega a página específica
            loadPage('servidorReadaptacao.php');
            break;

        ################################################################
    }

    $page->terminaPagina();
} else {
    loadPage("../../areaServidor/sistema/login.php");
}


