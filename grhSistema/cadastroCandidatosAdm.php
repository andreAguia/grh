<?php

/**
 * Cadastro de Concursos
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

    # Verifica se veio menu grh e registra o acesso no log
    $grh = get('grh', false);
    if ($grh) {
        # Grava no log a atividade
        $atividade = "Visualizou o cadastro de candidatos";
        $data = date("Y-m-d H:i:s");
        $intra->registraLog($idUsuario, $data, $atividade, null, null, 7);
    }

    # Pega a fase
    $fase = get('fase', 'aguardaLista');

    # Pega o idConcurso
    $idConcurso = get_session("idConcurso");

    # Volta quando não temos o idconcurso
    if (empty($idConcurso)) {
        $fase = "nenhum";
        loadPage("areaConcursoAdm.php");
    } else {
        # Pega as variáveis
        $idServidorPesquisado = get('idServidorPesquisado');
        $concurso = new Concurso($idConcurso);

        $parametroCargoCandidato = post('parametroCargoCandidato', get_session('parametroCargoCandidato', '*'));

        set_session('parametroCargoCandidato', $parametroCargoCandidato);
    }

    # Começa uma nova página
    $page = new Page();
    $page->iniciaPagina();

    # Cabeçalho da Página
    if ($fase <> "relatorio") {
        AreaServidor::cabecalho();
    }

    $grid = new Grid();
    $grid->abreColuna(12);

################################################################

    switch ($fase) {
        case "":
        case "aguardaLista" :

            # Cria um menu
            $menu1 = new MenuBar();

            # Voltar
            $botaoVoltar = new Link("Voltar", "areaConcursoAdm.php");
            $botaoVoltar->set_class('button');
            $botaoVoltar->set_title('Voltar a página anterior');
            $botaoVoltar->set_accessKey('V');
            $menu1->add_link($botaoVoltar, "left");

            $menu1->show();

            $grid->fechaColuna();

            #######################################################

            $grid->abreColuna(3);

            # Exibe os dados do Concurso
            $concurso->exibeDadosConcurso($idConcurso, true);

            # menu
            $concurso->exibeMenu($idConcurso, "Candidatos");

            # Exibe os servidores deste concurso
            $concurso->exibeQuadroServidoresConcursoPorCargo($idConcurso);

            $grid->fechaColuna();

            #######################################################3

            $grid->abreColuna(9);

            br(4);
            aguarde();
            br();

            # Limita a tela
            $grid1 = new Grid("center");
            $grid1->abreColuna(5);
            p("Aguarde...", "center");
            $grid1->fechaColuna();
            $grid1->fechaGrid();

            loadPage('?fase=listaCandidatos');
            break;

            $grid->fechaColuna();
            $grid->fechaGrid();
            break;

        ################################################################

        case "listaCandidatos" :

            # Cria um menu
            $menu1 = new MenuBar();

            # Voltar
            $botaoVoltar = new Link("Voltar", "areaConcursoAdm.php");
            $botaoVoltar->set_class('button');
            $botaoVoltar->set_title('Voltar a página anterior');
            $botaoVoltar->set_accessKey('V');
            $menu1->add_link($botaoVoltar, "left");

            # Importar
            $botaoImportar = new Link("Importar", "importaCandidatos.php");
            $botaoImportar->set_class('success button');
            $botaoImportar->set_title('Faz a importação do petec');
            if (Verifica::acesso($idUsuario, 1)) {
                $menu1->add_link($botaoImportar, "right");
            }

            # Relatório
            $imagem2 = new Imagem(PASTA_FIGURAS . 'print.png', null, 15, 15);
            $botaoRel = new Button();
            $botaoRel->set_title("Relatório dos Servidores");
            $botaoRel->set_target("_blank");
            $botaoRel->set_url("?fase=relatorioClassificacao");
            $botaoRel->set_imagem($imagem2);
            #$menu1->add_link($botaoRel, "right");

            $menu1->show();

            $grid->fechaColuna();

            #######################################################
            # Menu

            $grid->abreColuna(3);

            # Exibe os dados do Concurso
            $concurso->exibeDadosConcurso($idConcurso, true);

            # menu
            $concurso->exibeMenu($idConcurso, "Candidatos");

            # Exibe os servidores deste concurso
            $concurso->exibeQuadroServidoresConcursoPorCargo($idConcurso);

            $grid->fechaColuna();

            #######################################################3

            $grid->abreColuna(9);

            # Formulário
            $form = new Form('?');

            # cargos por nivel
            $result = $pessoal->select('SELECT DISTINCT cargo,
                                               cargo
                                          FROM tbcandidato
                                       ORDER BY cargo');

            # acrescenta todos
            array_unshift($result, ['*', '-- Todos --']);

            $controle = new Input('parametroCargoCandidato', 'combo', 'Cargo:', 1);
            $controle->set_size(30);
            $controle->set_title('Filtra por Cargo');
            $controle->set_array($result);
            $controle->set_valor($parametroCargoCandidato);
            $controle->set_onChange('formPadrao.submit();');
            $controle->set_autofocus(true);
            $controle->set_linha(1);
            $controle->set_col(12);
            $form->add_item($controle);

            $form->show();

            # Monta o select
            $select = "SELECT inscricao,
                              nome,
                              cpf,
                              cargo,
                              CONVERT(notaFinal, DECIMAL(10,2))
                         FROM tbcandidato
                        WHERE idConcurso = {$idConcurso}";

            # cargo
            if ($parametroCargoCandidato <> "*") {
                $select .= " AND cargo = '{$parametroCargoCandidato}'";
            }

            $select .= " ORDER BY cargo, 5 desc";

            # Pega os dados
            $row = $pessoal->select($select);

            # tabela
            $tabela = new Tabela();
            $tabela->set_titulo("Cadastro de Candidatos Aprovados");
            if ($parametroCargoCandidato <> "*") {
                $tabela->set_subtitulo($parametroCargoCandidato);
            } else {
                $tabela->set_subtitulo("Todos os Cargos");
            }
            $tabela->set_conteudo($row);
            $tabela->set_label(["Inscrição", "Nome", "Cpf", "Cargo", "Nota Final"]);
            #$tabela->set_width([15, 5, 5, 20, 15, 18, 18]);
            $tabela->set_align(["center", "left", "center", "left"]);
            $tabela->set_numeroOrdem(true);

//            $tabela->set_classe([null, "Concurso", null, "pessoal", "Concurso", "Concurso", "Concurso", "Concurso"]);
//            $tabela->set_metodo([null, "exibeClassificacaoServidor", null, "get_nomeELotacaoESituacaoEAdmissao", "exibePublicacoesServidor", "exibeOcupanteAnterior", "exibeOcupantePosterior", "exibeObs"]);
//            $tabela->set_funcao([null, null, "trataNulo"]);

            $tabela->show();

            $grid->fechaColuna();
            $grid->fechaGrid();

            # Grava no log a atividade            
            $data = date("Y-m-d H:i:s");
            $atividade = "Visualizou o cadastro de candidatos do cargo {$parametroCargoCandidato}";
            $intra->registraLog($idUsuario, $data, $atividade, null, null, 7);
            break;

        ################################################################
    }
    $grid->fechaColuna();
    $grid->fechaGrid();

    $page->terminaPagina();
} else {
    loadPage("../../areaServidor/sistema/login.php");
}
