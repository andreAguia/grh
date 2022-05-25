<?php

/**
 * Cadastro de Estado Civil
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
    $concurso = new Concurso();

    # Verifica a fase do programa
    $fase = get('fase', 'listar');
    set_session('origem', basename(__FILE__) . "?fase={$fase}");
    $idConcurso = get_session('idConcurso');

    # Pega o tipo do concurso
    $concurso = new Concurso($idConcurso);
    $tipo = $concurso->get_tipo($idConcurso);

    # pega o id (se tiver)
    $id = soNumeros(get('id'));

    # Começa uma nova página
    $page = new Page();
    $page->iniciaPagina();

    # Cabeçalho da Página
    AreaServidor::cabecalho();

    # Limita a tela
    $grid = new Grid();
    $grid->abreColuna(12);

    # Cria um menu
    $menu1 = new MenuBar();

    # Voltar
    $botaoVoltar = new Link("Voltar", "areaConcursoProf.php");
    $botaoVoltar->set_class('button');
    $botaoVoltar->set_title('Voltar a página anterior');
    $botaoVoltar->set_accessKey('V');
    $menu1->add_link($botaoVoltar, "left");

    # Relatórios
    $imagem = new Imagem(PASTA_FIGURAS . 'print.png', null, 15, 15);
    $botaoRel = new Button();
    $botaoRel->set_title("Relatório de vagas desse concurso");
    $botaoRel->set_url("../grhRelatorios/concurso.vagas.docentes.php?id=" . $idConcurso);
    $botaoRel->set_target("_blank");
    $botaoRel->set_imagem($imagem);
    $menu1->add_link($botaoRel, "right");

    $menu1->show();

    $grid->fechaColuna();

    #######################################################

    $grid->abreColuna(3);

    # Exibe os dados do Concurso
    $concurso->exibeDadosConcurso($idConcurso, true);

    # menu
    $concurso->exibeMenu($idConcurso, "Vagas");

    # Exibe os servidores deste concurso
    $concurso->exibeQuadroServidoresConcursoPorCargo($idConcurso);

    $grid->fechaColuna();

    #######################################################3

    $grid->abreColuna(9);

    # Exibe as vagas de Docente
    $select = 'SELECT tblotacao.DIR,
                      tblotacao.GER,
                      tbcargo.nome,
                      area,
                      idServidor,
                      tbvagahistorico.obs,
                      idVagaHistorico
                 FROM tbvagahistorico JOIN tbconcurso USING (idConcurso)
                                      JOIN tblotacao USING (idLotacao)
                                      JOIN tbvaga USING (idVaga)
                                      JOIN tbcargo USING (idCargo)
                WHERE idConcurso = ' . $idConcurso . ' ORDER BY tblotacao.DIR, tblotacao.GER desc';

    $conteudo = $pessoal->select($select);
    $numConteudo = $pessoal->count($select);

    if ($numConteudo > 0) {
        # Monta a tabela
        $tabela = new Tabela();
        $tabela->set_conteudo($conteudo);
        $tabela->set_align(["center", "center", "center", "left", "left"]);
        $tabela->set_label(["Centro", "Laboratório", "Cargo", "Área", "Servidor", "Obs"]);
        #$tabela->set_width([10, 10, 20, 20, 20, 20]);
        $tabela->set_titulo("Vagas de Professores");
        $tabela->set_classe([null, null, null, null, "Vaga"]);
        $tabela->set_metodo([null, null, null, null, "get_Nome"]);
        $tabela->set_numeroOrdem(true);

        $tabela->set_rowspan(0);
        $tabela->set_grupoCorColuna(0);

        $tabela->show();
    } else {
        tituloTable("Vagas de Professores");
        callout("Nenhuma vaga cadastrada", "secondary");
    }

    $grid->fechaColuna();
    $grid->fechaGrid();

    ################################################################

    $page->terminaPagina();
} else {
    loadPage("../../areaServidor/sistema/login.php");
}