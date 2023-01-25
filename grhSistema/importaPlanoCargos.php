<?php

/**
 * Estatística
 *  
 * By Alat
 */
# Reservado para o servidor logado
$idUsuario = null;

# Configuração
include ("_config.php");

# Permissão de Acesso
$acesso = Verifica::acesso($idUsuario, 1);

if ($acesso) {
    # Conecta ao Banco de Dados
    $intra = new Intra();
    $pessoal = new Pessoal();
    $plano = new PlanoCargos();

    # Verifica a fase do programa
    $fase = get('fase');

    # pega o id 
    $id = get_session('idPlano');

    # Começa uma nova página
    $page = new Page();
    $page->iniciaPagina();

    # Cabeçalho da Página
    AreaServidor::cabecalho();

    # Limita o tamanho da tela
    $grid1 = new Grid();
    $grid1->abreColuna(12);

    # Cria um menu
    $menu1 = new MenuBar();

    # Voltar
    $linkVoltar = new Link("Voltar", "cadastroPlanoCargos.php?fase=gerenciaTabela");
    $linkVoltar->set_class('button');
    $linkVoltar->set_title('Voltar para página anterior');
    $linkVoltar->set_accessKey('V');
    $menu1->add_link($linkVoltar, "left");

    $menu1->show();

    # Exibe dados do plano
    $plano->exibeDadosPlano($id);
    br();

    switch ($fase) {
        case "":

            $grid = new Grid("center");
            $grid->abreColuna(8);

            # Verifica se existe algum servidor vinculado a essa tabela
            $select = "SELECT idProgressao 
                             FROM tbprogressao JOIN tbclasse USING (idClasse)
                            WHERE idPlano = {$id}";

            if ($pessoal->count($select) > 0) {
                tituloTable('Existem Servidores Vinculados');
                $painel = new Callout("warning");
                $painel->abre();
                br();

                p("Existem {$pessoal->count($select)} registros de servidores vinculados a esse plano de cargos.<br/>"
                        . "Para se importar servidores para essa tabela, é necessário que a mesma não tenha servidores vinculados a ela.", "center");
                br();

                $painel->fecha();
            } else {
                tituloTable('Atenção !');
                $painel = new Callout("warning");
                $painel->abre();
                br();

                p("Esta rotina atualiza o salário de TODOS OS SERVIDORES ESTATUTÁRIOS ATIVOS<br/>"
                        . "que estão cadastrados no PLANO ATUAL para a tabela: {$plano->get_numDecreto($id)},<br/>"
                        . "respeitando o nível / faixa / padrão correspondente.<br/>"
                        . "<br/>Esta atualização só irá ocorrer se o nível / faixa / padrão<br/>"
                        . "da nova tabela corresponder exatamente ao da tabela atual.<br/>"
                        . "<br/>Importante observer que só serão atualizados os servidores estatutários ativos que,<br/>"
                        . "estiverem com o cadastro regularizados, ou seja, com o salário vinculado ao plano atual.", "center");

                br();

                # Cria um menu
                $menu1 = new MenuBar();

                # Prosseguir
                $botaoVoltar = new Link("Prosseguir", "?fase=importa1");
                $botaoVoltar->set_class('button');
                $botaoVoltar->set_title('Confirma e pressegue');
                $menu1->add_link($botaoVoltar, "right");

                $menu1->show();

                $painel->fecha();
            }

            $grid->fechaColuna();
            $grid->fechaGrid();
            break;

        ################################################################

        case "importa1":

            $grid = new Grid("center");
            $grid->abreColuna(8);

            # Pega qual e a tabela atual
            $idPlanoAtual = $plano->get_planoAtual();

            # Pega quantos servidores ativos estão na tabela atual
            $select = "SELECT tbservidor.idServidor, 
                              idPlano
                        FROM tbservidor JOIN  tbprogressao USING (idServidor)
                                        JOIN tbclasse USING (idclasse)
                       WHERE tbprogressao.dtInicial = (SELECT MAX(dtInicial) FROM tbprogressao WHERE tbprogressao.idServidor = tbservidor.idservidor)
                         AND situacao = 1
                         AND idPerfil = 1
                          AND idPlano = {$idPlanoAtual}";

            $servidoresafetados = $pessoal->select($select);
            $numServidoresafetados = $pessoal->count($select);

            tituloTable('Atenção !');
            $painel = new Callout("warning");
            $painel->abre();
            br();

            p("{$numServidoresafetados} servidores ativos serão atualizados para a nova tabela", "center");

            # Pega quantos servidores ativos não estão atualizados
            $select = "SELECT tbservidor.idServidor, 
                              idPlano
                        FROM tbservidor JOIN  tbprogressao USING (idServidor)
                                        JOIN tbclasse USING (idclasse)
                       WHERE tbprogressao.dtInicial = (SELECT MAX(dtInicial) FROM tbprogressao WHERE tbprogressao.idServidor = tbservidor.idservidor)
                         AND situacao = 1
                         AND idPerfil = 1
                          AND idPlano <> {$idPlanoAtual}";

            $servidoresNafetados = $pessoal->select($select);
            $numServidoresNafetados = $pessoal->count($select);

            p("{$numServidoresNafetados} servidores ativos que NÃO serão atualizados para a nova tabela", "center");

            # Exemplo com mais itens
            $tabela = new Tabela();
            $tabela->set_titulo("Servidores Que Não Serão Atualizados");
            $tabela->set_subtitulo("Plano de Cargos Atual: {$plano->get_numDecreto($idPlanoAtual)}");
            $tabela->set_conteudo($servidoresNafetados);
            $tabela->set_label(["Servidor", "Plano Cadastrador"]);
            $tabela->set_classe(["Pessoal","PlanoCargos"]);
            $tabela->set_metodo(["get_nome","get_numDecreto"]);
            $tabela->set_width([60, 40]);
            $tabela->set_align(["left", "left"]);
            $tabela->show();

            $painel->fecha();

            $grid->fechaColuna();
            $grid->fechaGrid();
            break;

            ################################################################
            # Fecha o grid
            $grid1->fechaColuna();
            $grid1->fechaGrid();

            $page->terminaPagina();
    }
} else {
    loadPage("../../areaServidor/sistema/login.php");
}    