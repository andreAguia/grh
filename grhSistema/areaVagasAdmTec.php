<?php

/**
 * Cadastro de Banco
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

    # pega o id (se tiver)
    $id = soNumeros(get('id'));

    # Verifica se veio menu grh e registra o acesso no log
    $grh = get('grh', false);
    if ($grh) {
        # Grava no log a atividade
        $atividade = "Visualizou o cadastro de vagas de Administrativo e Técnico";
        $data = date("Y-m-d H:i:s");
        $intra->registraLog($idUsuario, $data, $atividade, null, null, 7);
    }

    # Começa uma nova página
    $page = new Page();
    $page->iniciaPagina();

    # Cabeçalho da Página
    AreaServidor::cabecalho();

    $grid = new Grid();
    $grid->abreColuna(12);

    switch ($fase) {
        case "":
            /*
             * Menu
             */
            $menu = new MenuBar();

            # Voltar
            $botao = new Link("Voltar", "cadastroConcurso.php");
            $botao->set_class('button');
            $botao->set_title('Voltar a página anterior');
            $botao->set_accessKey('V');
            $menu->add_link($botao, "left");
            $menu->show();
            
            tituloTable("Área de Vagas de Concurso para Cargos Administrativos e Técnicos");
            br();
            
            $texto = "Observações Importantes:<br/>"
                    . " - Para essa análise serão consideradas somente as vagas novas(vagas reais) e descartadas as vagas de reposição.<br/>"
                    . " - Antes de aceitar estes dados com corretos, deve-se verificar no menu de vagas de cada concurso se não há problemas detectados.";
            callout($texto);

            $grid->fechaColuna();
            $grid->abreColuna(5);

            # Exibe as vagas 
            $select = "SELECT tbtipocargo.sigla,
                              idTipoCargo,
                              idTipoCargo,
                              idTipoCargo
                             FROM tbtipocargo
                              WHERE tipo = 'Adm/Tec'
                         ORDER BY 1 DESC";

            $conteudo = $pessoal->select($select);
            $numConteudo = $pessoal->count($select);

            # Monta a tabela
            $tabela = new Tabela();
            $tabela->set_conteudo($conteudo);
            $tabela->set_titulo("Geral");
            $tabela->set_label(array("Cargo", "Vagas Novas", "Servidores Ativos", "Vagas Disponíveis"));
            #$tabela->set_width(array(40, 15, 15, 15));
            $tabela->set_align(array("left"));

            $tabela->set_colunaSomatorio([1, 2, 3]);
            $tabela->set_textoSomatorio("Total:");
            $tabela->set_totalRegistro(false);

            $tabela->set_rowspan(0);
            $tabela->set_grupoCorColuna(0);

            $tabela->set_classe(array(null, "VagaAdm", "VagaAdm", "VagaAdm"));
            $tabela->set_metodo(array(null, "get_numReaisCargo", "get_numServidoresAtivosCargo", "get_vagasDisponiveisCargo"));

//            $tabela->set_editar('cadastroConcursoVaga.php?fase=editar&idConcurso=' . $id);
//            $tabela->set_excluir('cadastroConcursoVaga.php?fase=excluir&idConcurso=' . $id);
//            $tabela->set_idCampo('idConcursoVaga');

            $tabela->show();

            $grid->fechaColuna();
            $grid->abreColuna(7);

            # Exibe as vagas 
            $select = "SELECT tbconcurso.anobase,
                              tbtipocargo.sigla,
                                  vagasNovas,
                                  idConcursoVaga,
                                  idConcursoVaga
                             FROM tbconcursovaga JOIN tbtipocargo USING (idTipoCargo)
                                                 JOIN tbconcurso USING (idConcurso)
                            WHERE tbconcurso.tipo = 1
                         ORDER BY 1 DESC, 2 DESC";

            $conteudo = $pessoal->select($select);
            $numConteudo = $pessoal->count($select);

            # Monta a tabela
            $tabela = new Tabela();
            $tabela->set_conteudo($conteudo);
            $tabela->set_titulo("por Concurso");
            $tabela->set_label(array("Concurso", "Cargo", "Vagas Novas", "Servidores Ativos", "Vagas Disponíveis"));
            #$tabela->set_width(array(40, 15, 15, 15));
            $tabela->set_align(array("center", "left"));

            $tabela->set_colunaSomatorio([2, 3, 4]);
            $tabela->set_textoSomatorio("Total:");
            $tabela->set_totalRegistro(false);

            $tabela->set_rowspan(0);
            $tabela->set_grupoCorColuna(0);

            $tabela->set_classe(array(null, null, null, "VagaAdm", "VagaAdm"));
            $tabela->set_metodo(array(null, null, null, "get_numServidoresAtivosVaga", "get_vagasDisponiveis"));

//            $tabela->set_editar('cadastroConcursoVaga.php?fase=editar&idConcurso=' . $id);
//            $tabela->set_excluir('cadastroConcursoVaga.php?fase=excluir&idConcurso=' . $id);
//            $tabela->set_idCampo('idConcursoVaga');

            $tabela->show();
            break;
    }

    $grid->fechaColuna();
    $grid->fechaGrid();

    $page->terminaPagina();
} else {
    loadPage("../../areaServidor/sistema/login.php");
}