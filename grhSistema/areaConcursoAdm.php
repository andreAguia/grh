<?php

/**
 * Área de Férias
 * 
 * Por data de fruição
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
        $atividade = "Visualizou a área de concurso de adm e Tec";
        $data = date("Y-m-d H:i:s");
        $intra->registraLog($idUsuario, $data, $atividade, null, null, 7);
    }

    # pega o id (se tiver)
    $id = soNumeros(get('id'));

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
            $botaoVoltar = new Link("Vagas", "areaVagasAdm.php");
            $botaoVoltar->set_class('button');
            $botaoVoltar->set_title('Exibe as vagas dos concursos');
            $menu1->add_link($botaoVoltar, "right");

            # Planilha
            $botaoPlanilha = new Link("Planilha", "?fase=aguardaPlanilha");
            $botaoPlanilha->set_class('button');
            $botaoPlanilha->set_title('Exibe uma listagem para se copiar para uma planilha');
            $menu1->add_link($botaoPlanilha, "right");

            # Novo Concurso
            $botaoVoltar = new Link("Novo Concurso", "cadastroConcurso.php?fase=editar&tipo=1");
            $botaoVoltar->set_class('button');
            $menu1->add_link($botaoVoltar, "right");

            $menu1->show();

            # Monta a tabala
            $select = 'SELECT idConcurso,
                      anobase,
                      dtPublicacaoEdital,
                      regime,
                      CASE tipo
                        WHEN 1 THEN "Adm & Tec"
                        WHEN 2 THEN "Professor"
                        ELSE "--"
                      END,
                      orgExecutor,                      
                      tbplano.numDecreto,
                      idConcurso,
                      idConcurso,
                      idConcurso,
                      idConcurso
                 FROM tbconcurso LEFT JOIN tbplano USING (idPlano)
                WHERE true
                  AND tipo = 1 
             ORDER BY anobase desc, dtPublicacaoEdital desc';

            $resumo = $pessoal->select($select);

            # Monta a tabela
            $tabela = new Tabela();
            $tabela->set_conteudo($resumo);
            $tabela->set_titulo("Concursos para Servidores Administrativos & Técnicos");
            $tabela->set_label(["id", "Ano Base", "Publicação <br/>do Edital", "Regime", "Tipo", "Executor", "Plano de Cargos", "Ativos", "Inativos", "Total", "Acessar"]);
            $tabela->set_align(["center"]);
            $tabela->set_width([5, 8, 10, 10, 10, 10, 17, 5, 5, 5, 5, 5]);
            $tabela->set_funcao([null, null, 'date_to_php']);
            $tabela->set_classe([null, null, null, null, null, null, null, "Pessoal", "Pessoal", "Pessoal"]);
            $tabela->set_metodo([null, null, null, null, null, null, null, "get_servidoresAtivosConcurso", "get_servidoresInativosConcurso", "get_servidoresConcurso"]);
            $tabela->set_excluirCondicional('cadastroConcurso.php?fase=excluir', 0, 9, "==");
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
            loadPage('cadastroConcursoAdm.php');
            break;
################################################################
        case "aguardaPlanilha" :

            br(8);
            aguarde();
            br();

            # Limita a tela
            $grid1 = new Grid("center");
            $grid1->abreColuna(5);
            p("Aguarde...", "center");
            $grid1->fechaColuna();
            $grid1->fechaGrid();

            loadPage('?fase=planilha');
            break;

        ################################################################


        case "planilha" :

            # Cria um menu
            $menu1 = new MenuBar();

            # Voltar
            $botaoVoltar = new Link("Voltar", "?");
            $botaoVoltar->set_class('button');
            $botaoVoltar->set_title('Voltar a página anterior');
            $botaoVoltar->set_accessKey('V');
            $menu1->add_link($botaoVoltar, "left");

            $menu1->show();

            # Monta a tabala
            $select = 'SELECT idServidor,
                              tbpessoa.nome,
                              idServidor,
                              idServidor,
                              idServidor,
                              dtAdmissao,
                              dtDemissao,
                              idServidor,
                              idConcurso,
                              idServidor,
                              dtPublicAtoInvestidura,
                              dtPublicTermoPosse,
                              idServidor
                         FROM tbservidor LEFT JOIN tbpessoa USING (idPessoa)
                                         LEFT JOIN tbcargo USING (idCargo)
                                              JOIN tbtipocargo USING (idTipoCargo) 
                                         LEFT JOIN tbconcurso USING (idConcurso)
                        WHERE (idPerfil = 1 OR idPerfil = 4)
                          AND tbtipocargo.tipo = "Adm/Tec"
                     ORDER BY tbpessoa.nome';

            $resumo = $pessoal->select($select);

            # Monta a tabela
            $tabela = new Tabela();
            $tabela->set_conteudo($resumo);
            $tabela->set_titulo("Relação Geral de Concursados");
            $tabela->set_label(["id / Matrícula", "Servidor", "Cargo", "Lotação", "Perfil", "Admissão", "Saída", "Situação", "Concurso", "Vaga Anteriormente Ocupada por:", "Ato de Investidura", "Termo de Posse"]);
            $tabela->set_align(["center", "left", "left", "left"]);
            $tabela->set_funcao([null, null, null, null, null, 'date_to_php', 'date_to_php', null, null, null, 'date_to_php', 'date_to_php']);
            $tabela->set_classe(["Pessoal", null, "Pessoal", "Pessoal", "Pessoal", null, null, "Pessoal", "Concurso", "Concurso"]);
            $tabela->set_metodo(["get_idFuncionalEMatricula", null, "get_cargo", "get_lotacao", "get_perfil", null, null, "get_situacao", "get_nomeConcurso", "exibeOcupanteAnterior"]);
//            $tabela->set_rowspan(1);
//            $tabela->set_grupoCorColuna(1);
            $tabela->show();
            break;

################################################################
    }
    $grid->fechaColuna();
    $grid->fechaGrid();

    $page->terminaPagina();
} else {
    loadPage("../../areaServidor/sistema/login.php");
}
