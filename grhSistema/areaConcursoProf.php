<?php

/**
 * Área concurso Professores
 *  
 * By Alat
 */
# Reservado para o servidor logado
$idUsuario = null;

# Configuração
include ("_config.php");

# Limpa as sessões
set_session('idConcurso');
set_session('parametroCargo');
set_session('origem', basename(__FILE__));

# Permissão de Acesso
$acesso = Verifica::acesso($idUsuario, 2);

if ($acesso) {
    # Conecta ao Banco de Dados
    $intra = new Intra();
    $pessoal = new Pessoal();

    # Verifica a fase do programa
    $fase = get('fase', 'listar');
    $idConcurso = get('idConcurso');

    # Verifica se veio menu grh e registra o acesso no log
    $grh = get('grh', false);
    if ($grh) {
        # Grava no log a atividade
        $atividade = "Visualizou a área de concurso de professores";
        $data = date("Y-m-d H:i:s");
        $intra->registraLog($idUsuario, $data, $atividade, null, null, 7);
    }

    # pega o id (se tiver)
    $id = soNumeros(get('id'));

    # Pega os parâmetros
    $parametroAno = post('parametroAno', get_session('parametroAno', "*"));

    # Joga os parâmetros par as sessions
    set_session('parametroAno', $parametroAno);

    # Começa uma nova página
    $page = new Page();
    $page->iniciaPagina();

    # Cabeçalho da Página
    AreaServidor::cabecalho();

    $grid = new Grid();
    $grid->abreColuna(12);

################################################################

    switch ($fase) {
        case "listar" :

            # Cria um menu
            $menu1 = new MenuBar();

            # Voltar
            $botaoVoltar = new Link("Voltar", "grh.php");
            $botaoVoltar->set_class('button');
            $botaoVoltar->set_title('Voltar a página anterior');
            $botaoVoltar->set_accessKey('V');
            $menu1->add_link($botaoVoltar, "left");

            # Vagas
            $botaoVoltar = new Link("Vagas", "areaVagasDocentes.php");
            $botaoVoltar->set_class('button');
            $botaoVoltar->set_title('Exibe as vagas dos concursos');
            $menu1->add_link($botaoVoltar, "right");

            # Novo Concurso
            $botaoVoltar = new Link("Novo Concurso", "cadastroConcurso.php?fase=editar&tipo=2");
            $botaoVoltar->set_class('button');
            $menu1->add_link($botaoVoltar, "right");

            $menu1->show();

            $form = new Form('?');

            # Ano
            $anoBase = $pessoal->select('SELECT DISTINCT anoBase, anoBase
                                            FROM tbconcurso
                                           WHERE tipo = 2
                                          ORDER BY anoBase DESC');

            array_unshift($anoBase, array("*", 'Todas'));

            $controle = new Input('parametroAno', 'combo', 'Ano:', 1);
            $controle->set_size(8);
            $controle->set_title('Filtra por Ano');
            $controle->set_array($anoBase);
            $controle->set_valor($parametroAno);
            $controle->set_onChange('formPadrao.submit();');
            $controle->set_linha(1);
            $controle->set_col(2);
            $form->add_item($controle);

            $form->show();

            # Monta a tabala
            $select = "SELECT idConcurso,
                      anobase,
                      dtPublicacaoEdital,
                      regime,
                      CASE tipo
                        WHEN 1 THEN 'Adm & Tec'
                        WHEN 2 THEN 'Professor'
                        ELSE '--'
                      END,
                      orgExecutor,                      
                      tbplano.numDecreto,
                      idConcurso,
                      idConcurso,
                      idConcurso,
                      idConcurso
                 FROM tbconcurso LEFT JOIN tbplano USING (idPlano)
                WHERE tipo = 2 ";

            # Ano Base
            if ($parametroAno <> "*") {
                $select .= "AND anoBase = '{$parametroAno}'";
            }

            $select .= " ORDER BY anobase desc, dtPublicacaoEdital desc";

            $resumo = $pessoal->select($select);

            # Monta a tabela
            $tabela = new Tabela();
            $tabela->set_conteudo($resumo);
            $tabela->set_titulo("Concursos para Servidores Professores");
            $tabela->set_label(["id", "Ano Base", "Publicação <br/>do Edital", "Regime", "Tipo", "Executor", "Plano de Cargos", "Ativos", "Inativos", "Total", "Acessar"]);
            $tabela->set_align(["center"]);
            $tabela->set_width([5, 8, 10, 10, 10, 10, 17, 5, 5, 5, 5, 5]);
            $tabela->set_funcao([null, null, 'date_to_php']);
            $tabela->set_classe([null, null, null, null, null, null, null, "Pessoal", "Pessoal", "Pessoal"]);
            $tabela->set_excluirCondicional('cadastroConcurso.php?fase=excluir', 0, 9, "==");
            $tabela->set_metodo([null, null, null, null, null, null, null, "get_servidoresAtivosConcurso", "get_servidoresInativosConcurso", "get_servidoresConcurso"]);
            $tabela->set_rowspan(1);
            $tabela->set_grupoCorColuna(1);

            $botao = new Link(null, '?fase=acessaConcurso&idConcurso=', 'Acessa a página do concurso');
            $botao->set_imagem(PASTA_FIGURAS . 'bullet_edit.png', 20, 20);
            $tabela->set_link([null, null, null, null, null, null, null, null, null, null, $botao]);
            $tabela->show();
            break;

        ################################################################
        # Chama o menu do Servidor que se quer editar
        case "acessaConcurso" :
            set_session('idConcurso', $idConcurso);
            loadPage('cadastroConcursoPublicacao.php');
            break;

        ################################################################
    }
    $grid->fechaColuna();
    $grid->fechaGrid();

    $page->terminaPagina();
} else {
    loadPage("../../areaServidor/sistema/login.php");
}
