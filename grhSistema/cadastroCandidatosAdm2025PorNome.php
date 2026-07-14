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

# Cadastro de reserva
$cadReserva = 5;

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

    # pega o id (se tiver)
    $id = soNumeros(get('id'));

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

        $parametroNome = post('parametroNome', get_session('parametroNome'));
        set_session('parametroNome', $parametroNome);
    }

    # Começa uma nova página
    $page = new Page();
    $page->iniciaPagina();

    # Cabeçalho da Página
    if ($fase <> "relatorio1"
            AND $fase <> "relatorio2"
            AND $fase <> "relatorio3"
            AND $fase <> "relatorio4"
            AND $fase <> "relatorio5"
            AND $fase <> "relatorio6"
            AND $fase <> "relatorio7"
            AND $fase <> "relatorio8"
            AND $fase <> "relatorio9"
            AND $fase <> "relatorio10"
            . "") {
        AreaServidor::cabecalho();
    }

    # Define array de Cotas
    $concurso2025 = new ConcursoAdm2025();
    $arrayCotas = $concurso2025->get_arrayCotas();
    #$idConcurso - $concurso2025->get_idConcurso();

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
            $concurso->exibeMenu($idConcurso, "Por Nome");

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

        ###################################################################

        /*
         * Lista Candidatos
         */

        case "listaCandidatos" :

            # Cria um menu
            $menu1 = new MenuBar();

            # Voltar
            $botaoVoltar = new Link("Voltar", "areaConcursoAdm.php");
            $botaoVoltar->set_class('button');
            $botaoVoltar->set_title('Voltar a página anterior');
            $botaoVoltar->set_accessKey('V');
            $menu1->add_link($botaoVoltar, "left");

            # Vagas
            $botaoVagas = new Link("Vagas", "?fase=exibeVagas");
            $botaoVagas->set_class('button');
            $botaoVagas->set_title('Exibe a tabela de vagas');
            $botaoVagas->set_target("_blank");
            $menu1->add_link($botaoVagas, "right");

            # Importar
            $botaoImportar = new Link("Importar", "importaCandidatos.php");
            $botaoImportar->set_class('success button');
            $botaoImportar->set_title('Faz a importação do petec');
            if (Verifica::acesso($idUsuario, 1)) {
                $menu1->add_link($botaoImportar, "right");
            }

            $menu1->show();

            $grid->fechaColuna();

            ###################################################################                       

            $grid->abreColuna(3);

            # Exibe os dados do Concurso
            $concurso->exibeDadosConcurso($idConcurso, true);

            # menu
            $concurso->exibeMenu($idConcurso, "Por Nome");

            # Exibe os servidores deste concurso
            $concurso->exibeQuadroServidoresConcursoPorCargo($idConcurso);

            $grid->fechaColuna();

            ###################################################################
            # Campos de Pesquisa
            $grid->abreColuna(9);

            # Formulário
            $form = new Form('?');

            # Nome
            $controle = new Input('parametroNome', 'texto', 'Pesquisar por Nome:', 1);
            $controle->set_size(50);
            $controle->set_title('Filtra por Nome');
            $controle->set_autofocus(true);
            $controle->set_valor($parametroNome);
            $controle->set_onChange('formPadrao.submit();');
            $controle->set_linha(1);
            $controle->set_col(12);
            $form->add_item($controle);

            $form->show();

            ###################################################################
            # Monta o select
            $select = "SELECT inscricao,
                                  idCandidato,
                                  cargo,
                                  cargo,
                                  idCandidato,
                                  CONVERT(notaFinal, DECIMAL(10,2)),
                                  idCandidato,
                                  idCandidato,
                                  idCandidato
                             FROM tbcandidato
                           WHERE tbcandidato.idConcurso = {$idConcurso}";

            if (!is_null($parametroNome)) {
                # Verifica se tem espaços
                if (strpos($parametroNome, ' ') !== false) {
                    # Separa as palavras
                    $palavras = explode(' ', $parametroNome);

                    # Percorre as palavras
                    foreach ($palavras as $item) {
                        $select .= ' AND (nome LIKE "%' . $item . '%")';
                    }
                } else {
                    $select .= " AND nome LIKE '%{$parametroNome}%'";
                }
            }

            $select .= " ORDER BY nome";

            if (!is_null($parametroNome)) {
                # Pega os dados
                $row = $pessoal->select($select);

                # tabela
                $tabela = new Tabela();
                $tabela->set_titulo("Cadastro de Candidatos Aprovados");
                #$tabela->set_subtitulo($subtitulo);
                $tabela->set_conteudo($row);
                $tabela->set_label(["Inscrição", "Candidato", "Cargo", "Vagas do Cargo", "Classificação do Candidato", "Nota Final", "Documento", "Obs", "Editar"]);
                $tabela->set_width([10, 20, 25, 10, 10, 10, 5, 5, 5]);
                $tabela->set_align(["center", "left", "left"]);

                $tabela->set_classe([null, "CandidatoAdm2025", null, "ConcursoAdm2025", "CandidatoAdm2025", null, "concursoAdm2025", "CandidatoAdm2025"]);
                $tabela->set_metodo([null, "get_nomeECargoELotacaoESituacao", null, "get_vagasGeral", "exibeClassific", null, "exibeDeclaracao", "exibeObs"]);
                $tabela->set_funcao([null, "plm", "plm"]);

                # Botão Editar
                $botao = new Link(null, "?fase=editaCandidato&id=", 'Acessa os dados do Candidato');
                $botao->set_imagem(PASTA_FIGURAS . 'bullet_edit.png', 20, 20);
                $tabela->set_link([null, null, null, null, null, null, null, null, $botao]);

                $tabela->set_bordaInterna(true);
                $tabela->show();
            }

            $grid->fechaColuna();
            $grid->fechaGrid();

            # Grava no log a atividade            
            $data = date("Y-m-d H:i:s");
            $atividade = "Visualizou o cadastro de candidatos por nome {$parametroNome}";
            $intra->registraLog($idUsuario, $data, $atividade, null, null, 7);
            break;

        ################################################################    

        case "editaCandidato" :
            br(8);
            aguarde();

            # Informa o $id Servidor
            set_session('idCandidatoPesquisado', $id);

            # Informa a origem
            set_session('origem', "cadastroCandidatosAdm2025PorNome.php");

            # Carrega a página específica
            loadPage('cadastroCandidatosAdm2025Edita.php');
            break;

        ################################################################       
    }
    $grid->fechaColuna();
    $grid->fechaGrid();

    $page->terminaPagina();
} else {
    loadPage("../../areaServidor/sistema/login.php");
}
    