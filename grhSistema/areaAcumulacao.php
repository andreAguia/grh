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
        $atividade = "Visualizou a área de acumulação";
        $data = date("Y-m-d H:i:s");
        $intra->registraLog($idUsuario, $data, $atividade, null, null, 7);
    }

    # pega o id (se tiver)
    $id = soNumeros(get('id'));

    # Pega os parâmetros
    $parametroNomeMat = post('parametroNomeMat', get_session('parametroNomeMat'));
    $parametroNome = retiraAspas(post('parametroNome', get_session('parametroNome')));

    # Joga os parâmetros par as sessions    
    set_session('parametroNomeMat', $parametroNomeMat);
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

        case "" :
        case "listaAcumulacao" :

            # Cria um menu
            $menu1 = new MenuBar();

            # Voltar
            $botaoVoltar = new Link("Voltar", "grh.php?fase=acumulacao");
            $botaoVoltar->set_class('button');
            $botaoVoltar->set_title('Voltar a página anterior');
            $botaoVoltar->set_accessKey('V');
            $menu1->add_link($botaoVoltar, "left");

            # Incluir
            $botaoInserir = new Button("Incluir", "?fase=incluir");
            $botaoInserir->set_title("Incluir um Servidor");
            $menu1->add_link($botaoInserir, "right");

            # Relatórios
            $imagem = new Imagem(PASTA_FIGURAS . 'print.png', null, 15, 15);
            $botaoRel = new Button();
            $botaoRel->set_title("Relatório dessa pesquisa");
            $botaoRel->set_url("../grhRelatorios/acumulacao.geral.php");
            $botaoRel->set_target("_blank");
            $botaoRel->set_imagem($imagem);
            $menu1->add_link($botaoRel, "right");

            # Normas
            $botao2 = new Button("Regras", "servidorAcumulacao.php?fase=regras");
            $botao2->set_title("Exibe as regras da acumulação");
            #$botao2->set_url("../grhRelatorios/servidorGratificacao.php");
            $botao2->set_target("_blank");
            $menu1->add_link($botao2, "right");

            $menu1->show();

            ###
            # Formulário de Pesquisa
            $form = new Form('?');

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

            $form->show();

            ###
            # Pega o time inicial
            $time_start = microtime(true);

            # Pega os dados
            $select = "SELECT CASE conclusao
                                WHEN 1 THEN 'Pendente'
                                WHEN 2 THEN 'Resolvido'
                                ELSE '--'
                              END,
                              idAcumulacao,
                              dtPublicacao,
                              tbservidor.idServidor,
                              idAcumulacao,
                              tbservidor.idServidor,
                              idAcumulacao,                         
                              tbservidor.idServidor
                         FROM tbacumulacao JOIN tbservidor USING (idServidor)
                                           JOIN tbpessoa USING (idPessoa)
                        WHERE tbservidor.idPerfil <> 10";

            # nome
            if (!is_null($parametroNomeMat)) {
                $select .= " AND tbpessoa.nome LIKE '%$parametroNomeMat%'";
            }

            $select .= " ORDER BY conclusao, tbpessoa.nome";

            $resumo = $pessoal->select($select);

            # Monta a tabela
            $tabela = new Tabela();
            $tabela->set_conteudo($resumo);
            $tabela->set_label(array("Conclusão", "Resultado", "Publicação", "Servidor", "Processo", "Vínculo da Uenf", "Outro Vínculo"));
            $tabela->set_align(array("center", "center", "center", "left", "center", "left", "left"));
            $tabela->set_funcao(array(null, null, "date_to_php"));
            $tabela->set_classe(array(null, "Acumulacao", null, "Pessoal", "Acumulacao", "Acumulacao", "Acumulacao"));
            $tabela->set_metodo(array(null, "get_resultado", null, "get_nomeEidFuncional", "exibeProcesso", "exibeDadosUenf", "exibeDadosOutroVinculo"));

            $tabela->set_titulo("Área de Acumulação");

            $tabela->set_formatacaoCondicional(array(array('coluna'   => 0,
                    'valor'    => 'Resolvido',
                    'operador' => '=',
                    'id'       => 'emAberto'),
                array('coluna'   => 0,
                    'valor'    => 'Pendente',
                    'operador' => '=',
                    'id'       => 'alerta')
            ));

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
            set_session('origem', 'areaAcumulacao.php');

            # Carrega a página específica
            loadPage('servidorAcumulacao.php');
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
            set_session('origem', 'areaAcumulacao.php');

            # Carrega a página específica
            loadPage('servidorAcumulacao.php');
            break;

        ################################################################
    }

    $grid->fechaColuna();
    $grid->fechaGrid();

    $page->terminaPagina();
} else {
    loadPage("../../areaServidor/sistema/login.php");
}


