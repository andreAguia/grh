<?php

/**
 * Histórico de Progressões
 *  
 * By Alat
 */
# Inicia as variáveis que receberão as sessions
$idUsuario = null;
$idServidorPesquisado = null;

# Configuração
include ("_config.php");

# Permissão de Acesso
$acesso = Verifica::acesso($idUsuario, [1, 2, 12]);

if ($acesso) {

    # Verifica a fase do programa
    $fase = get('fase', 'exibePlanos');

    # Conecta ao Banco de Dados
    $intra = new Intra();
    $pessoal = new Pessoal();
    $plano = new PlanoCargos();

    # pega o id (se tiver)
    $id = soNumeros(get('id'));

    # Começa uma nova página
    $page = new Page();
    $page->iniciaPagina();

    # Cabeçalho da Página
    AreaServidor::cabecalho();

    ################################################################

    switch ($fase) {

        case "exibePlanos" :

            # Limita o tamanho da tela
            $grid = new Grid();
            $grid->abreColuna(12);

            $select = "SELECT idPlano,
                              numDecreto,
                              servidores,
                              dtVigencia,
                              CASE planoAtual
                                   WHEN 1 THEN 'Vigente'
                                   ELSE 'Antigo'
                              end,
                              idPlano
                         FROM tbplano
                     ORDER BY planoAtual desc, dtPublicacao desc, numDecreto desc";

            $row = $pessoal->select($select);
            br();

            # Monta a tabela
            $tabela = new Tabela();
            $tabela->set_titulo("Planos Cadastrados no Sistema");
            $tabela->set_conteudo($row);
            $tabela->set_align(["center", "left"]);
            $tabela->set_label(["id", "Decreto / Lei", "Servidores", "Vigência", "Plano Atual", "Tabela"]);
            $tabela->set_funcao([null, null, null, "date_to_php"]);
            $tabela->set_classe([null, null, null, null, null, 'PlanoCargos']);
            $tabela->set_metodo([null, null, null, null, null, 'exibeBotaoTabela2']);
            $tabela->set_formatacaoCondicional(array(
                array('coluna' => 4,
                    'valor' => "Antigo",
                    'operador' => '=',
                    'id' => 'inativo')));
            $tabela->show();

            $grid->fechaColuna();
            $grid->fechaGrid();
            break;

        ################################################################

        case "exibeTabela" :
            # Limita o tamanho da tela
            $grid = new Grid();
            $grid->abreColuna(12);

            # Cria um menu
            $menu1 = new MenuBar();

            # Voltar
            $botaoVoltar = new Link("Voltar", "?fase=exibePlanos");
            $botaoVoltar->set_class('button');
            $menu1->add_link($botaoVoltar, "left");

            $menu1->show();

            $plano->exibeTabela($id, false);

            if ($plano->get_numDadosPlano($id) == 0) {
                $painel = new Callout();
                $painel->abre();

                p("Não há dados a serem exibidos", "center");

                $painel->fecha();
            }

            $grid->fechaColuna();
            $grid->fechaGrid();
            break;

        ################################################################
    }
    $page->terminaPagina();
} else {
    loadPage("../../areaServidor/sistema/login.php");
}
