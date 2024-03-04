<?php

/**
 * Cadastro de Tempo de Serviço
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
    # Conecta ao Banco de Dados
    $intra = new Intra();
    $pessoal = new Pessoal();
    $aposentadoria = new Aposentadoria();
    $averbacao = new Averbacao();

    # Verifica a fase do programa
    $fase = get('fase');

    # Verifica se veio menu grh e registra o acesso no log
    $grh = get('grh', false);
    if ($grh) {
        # Grava no log a atividade
        $atividade = "Cadastro do servidor - Aposentadoria";
        $data = date("Y-m-d H:i:s");
        $intra->registraLog($idUsuario, $data, $atividade, null, null, 7, $idServidorPesquisado);
    }

    # Começa uma nova página
    $page = new Page();
    $page->iniciaPagina();

    # Cabeçalho da Página
    AreaServidor::cabecalho();

########################################################
    # Limita o tamanho da tela
    $grid = new Grid();
    $grid->abreColuna(12);
    br();

    # Cria um menu
    $menu = new MenuBar();

    # Botão voltar
    $linkBotaoVoltar = new Button('Voltar', 'servidorMenu.php');
    $linkBotaoVoltar->set_title('Volta para a página anterior');
    $linkBotaoVoltar->set_accessKey('V');
    $menu->add_link($linkBotaoVoltar, "left");

    # Dados do Servidor
    $linkBotaoVoltar = new Button('Dados do Servidor', '?');
    $linkBotaoVoltar->set_title('Exibe os dados gerais do servidor');
    $menu->add_link($linkBotaoVoltar, "right");

    # Regras Permanentes
    $linkBotaoVoltar = new Button('Regras Permanentes', '?fase=regrasPermanentes');
    $linkBotaoVoltar->set_title('Exibe os dados gerais do servidor');
    $menu->add_link($linkBotaoVoltar, "right");

    # Regras de Transição
    $linkBotaoVoltar = new Button('Regras de Transição', '?');
    $linkBotaoVoltar->set_title('Exibe os dados gerais do servidor');
    $menu->add_link($linkBotaoVoltar, "right");

    # Direito Adquirido
    $linkBotaoVoltar = new Button('Direito Adquirido', '?');
    $linkBotaoVoltar->set_title('Exibe os dados gerais do servidor');
    $menu->add_link($linkBotaoVoltar, "right");

    $menu->show();

    # Exibe os dados do servidor
    get_DadosServidor($idServidorPesquisado);

########################################################

    switch ($fase) {
        /*
         *  Resumo Geral
         */
        case "":
            # Dados do Servidor
            $grid->fechaColuna();
            $grid->abreColuna(12, 3);

            $aposentadoria->exibeMenuServidor(1);

            $grid->fechaColuna();
            $grid->abreColuna(12, 9);

            tituloTable("Resumo Geral");
            br();

            $grid1 = new Grid();
            $grid1->abreColuna(12, 12, 5);

            $array = [
                ["Idade", $pessoal->get_idade($idServidorPesquisado)],
                ["Data de Admissão", $pessoal->get_dtAdmissao($idServidorPesquisado)],
                ["Data de Ingresso<br/>no Serviço Público", $aposentadoria->get_dtIngresso($idServidorPesquisado)],
                ["Tempo Público<br/>Ininterrupto (Dias)", $aposentadoria->get_tempoPublicoIninterrupto($idServidorPesquisado)]
            ];

            # Tabela
            $tabela = new Tabela();
            $tabela->set_titulo("Dados do Servidor");
            $tabela->set_conteudo($array);
            $tabela->set_label(["Descrição", "Valor"]);
            $tabela->set_width([50, 50]);
            $tabela->set_align(["left", "center"]);
            $tabela->set_totalRegistro(false);
            $tabela->show();

            $grid1->fechaColuna();
            $grid1->abreColuna(12, 12, 7);

            /*
             *  Tempo de Serviço
             */

            $array = [
                ["Público", $averbacao->get_tempoAverbadoPublico($idServidorPesquisado), $aposentadoria->get_tempoServicoUenf($idServidorPesquisado), $averbacao->get_tempoAverbadoPublico($idServidorPesquisado) + $aposentadoria->get_tempoServicoUenf($idServidorPesquisado)],
                ["Privado", $averbacao->get_tempoAverbadoPrivado($idServidorPesquisado), 0, $averbacao->get_tempoAverbadoPrivado($idServidorPesquisado)]
            ];

            # Tabela
            $tabela = new Tabela();
            $tabela->set_titulo("Tempo de Serviço (em dias)");
            $tabela->set_conteudo($array);
            $tabela->set_label(["", "Averbado", "Uenf", "Total"]);
            $tabela->set_width([25, 25, 25, 25]);
            $tabela->set_align(["left"]);
            $tabela->set_totalRegistro(false);
            $tabela->set_colunaSomatorio([1, 2, 3]);
            $tabela->show();

            $grid1->fechaColuna();
            $grid1->fechaGrid();
            break;

        ########################################################

        /*
         *  Tempo Averbado Detalhado
         */

        case "averbado":

            # Dados do Servidor
            $grid->fechaColuna();
            $grid->abreColuna(12, 3);

            $aposentadoria->exibeMenuServidor(2);

            $grid->fechaColuna();
            $grid->abreColuna(12, 9);

            # Variáveis
            $empresaTipo = [
                [1, "Pública"],
                [2, "Privada"]
            ];

            $regime = [
                [1, "Celetista"],
                [2, "Estatutário"],
                [3, "Próprio"],
                [4, "Militar"]
            ];

            $select = "SELECT dtInicial,
                      dtFinal,
                      dias,
                      idAverbacao,
                      idAverbacao,
                      empresa,
                      CASE empresaTipo ";

            foreach ($empresaTipo as $tipo) {
                $select .= " WHEN {$tipo[0]} THEN '{$tipo[1]}' ";
            }

            $select .= "      END,
                      CASE regime ";
            foreach ($regime as $tipo2) {
                $select .= " WHEN {$tipo2[0]} THEN '{$tipo2[1]}' ";
            }

            $select .= "      END,
                      cargo,
                      dtPublicacao,
                      processo
                 FROM tbaverbacao
                WHERE idServidor = {$idServidorPesquisado}
             ORDER BY dtInicial desc";

            $result = $pessoal->select($select);

            # Tabela
            $tabela = new Tabela();
            $tabela->set_titulo("Tempo Averbado - Detalhado");
            $tabela->set_conteudo($result);
            $tabela->set_label(["Data Inicial", "Data Final", "Dias Digitados", "Dias Calculados", "Dias Anteriores de 15/12/1998", "Empresa", "Tipo", "Regime", "Cargo", "Publicação", "Processo"]);
            #$tabela->set_width(array(60, 40));
            $tabela->set_align(["center", "center", "center", "center", "center", "left"]);
            $tabela->set_funcao(["date_to_php", "date_to_php", null, null, null, null, null, null, null, "date_to_php"]);

            $tabela->set_classe([null, null, null, "Averbacao", "Averbacao"]);
            $tabela->set_metodo([null, null, null, "getNumDias", "getDiasAnterior15_12_98"]);

            $tabela->set_formatacaoCondicional([
                ['coluna' => 4,
                    'valor' => 0,
                    'operador' => '<>',
                    'id' => 'diasAntes'],
                ['coluna' => 4,
                    'valor' => 0,
                    'operador' => '=',
                    'id' => 'normal']
            ]);

            $tabela->set_totalRegistro(false);
            $tabela->set_colunaSomatorio([2, 3]);
            $tabela->show();
            break;

        ########################################################

        /*
         * Vínculos Anteriores
         */

        case "vinculos":
            # Dados do Servidor
            $grid->fechaColuna();
            $grid->abreColuna(12, 3);

            $aposentadoria->exibeMenuServidor(3);

            $grid->fechaColuna();
            $grid->abreColuna(12, 9);

            # Pega o idPessoa desse idServidor
            $idPessoa = $pessoal->get_idPessoa($idServidorPesquisado);

            $select = "SELECT dtAdmissao,
                      dtDemissao,
                      idServidor,
                      idServidor,
                      idServidor,
                      idServidor
                 FROM tbservidor
                WHERE idPessoa = {$idPessoa}
                  AND idServidor <> {$idServidorPesquisado}  
             ORDER BY dtadmissao desc";

            $result = $pessoal->select($select);

            # Tabela
            $tabela = new Tabela();
            $tabela->set_titulo("Vínculos Anteriores");
            $tabela->set_conteudo($result);
            $tabela->set_label(["Admissão", "Saída", "Cargo", "Perfil", "Situação", "Motivo"]);
            #$tabela->set_width(array(60, 40));
            $tabela->set_align(["center", "center", "left"]);
            $tabela->set_funcao(["date_to_php", "date_to_php"]);

            $tabela->set_classe([null, null, "Pessoal", "Pessoal", "Pessoal", "Pessoal"]);
            $tabela->set_metodo([null, null, "get_cargo", "get_perfil", "get_situacao", "get_motivo"]);

            $tabela->show();
            break;

        ########################################################

        /*
         *  Afastamentos
         */

        case "afastamentos":
            # Dados do Servidor
            $grid->fechaColuna();
            $grid->abreColuna(12, 3);

            $aposentadoria->exibeMenuServidor(4);

            $grid->fechaColuna();
            $grid->abreColuna(12, 9);

            $afast = new ListaAfastamentosServidor($idServidorPesquisado);
            $afast->exibeTabela();
            break;

        ########################################################

        /*
         * Regras Premanentes
         */

        case "regrasPermanentes":

            titulo("Regras Permanentes");

            $tabPrincipal = new Tab(["Aposentadoria Voluntária", "Aposentadoria Compulsória", "Incapacidade Permanente"]);
            $tabPrincipal->abreConteudo();

            /*
             *  Aposentadoria Voluntária
             */

            $aposentadoria = new AposentadoriaLC195Voluntaria($idServidorPesquisado);
            tituloTable($aposentadoria->get_descricao());

            $aposentadoria->exibeAnaliseResumo();
            $aposentadoria->exibeAnalise();

            $grid1 = new Grid();
            $grid1->abreColuna(12, 6);

            $aposentadoria->exibeRegras();

            $grid1->fechaColuna();
            $grid1->abreColuna(12, 6);

            $aposentadoria->exibeRemuneração();

            $grid1->fechaColuna();
            $grid1->fechaGrid();
            
            br();

            $grid1 = new Grid();
            $grid1->abreColuna(12, 6);

            $aposentadoria->exibeResumoCartilha(1);

            $grid1->fechaColuna();
            $grid1->abreColuna(12, 6);

            $aposentadoria->exibeResumoCartilha(2);

            $grid1->fechaColuna();
            $grid1->fechaGrid();
            $tabPrincipal->fechaConteudo();

            /*
             *  Aposentadoria Compulsória
             */

            $tabPrincipal->abreConteudo();

            $aposentadoria = new AposentadoriaLC195Compulsoria($idServidorPesquisado);
            tituloTable($aposentadoria->get_descricao());

            $aposentadoria->exibeAnaliseResumo();
            $aposentadoria->exibeAnalise();

            $grid1 = new Grid();
            $grid1->abreColuna(12, 6);

            $aposentadoria->exibeRegras();

            $grid1->fechaColuna();
            $grid1->abreColuna(12, 6);

            $aposentadoria->exibeRemuneração();

            $grid1->fechaColuna();
            $grid1->fechaGrid();

            $aposentadoria->exibeResumoCartilha();
            $tabPrincipal->fechaConteudo();

            $tabPrincipal->abreConteudo();
            $tabPrincipal->fechaConteudo();

            $tabPrincipal->abreConteudo();
            $tabPrincipal->fechaConteudo();
            break;

        ########################################################

        /*
         * Aposentadoria Voluntária
         */

        case "permanenteVoluntaria":

            $grid->fechaColuna();
            $grid->abreColuna(12, 3);

            $aposentadoria->exibeMenuServidor(6);

            $grid->fechaColuna();
            $grid->abreColuna(12, 9);
            $aposentadoria = new AposentadoriaLC195Voluntaria($idServidorPesquisado);
            tituloTable($aposentadoria->get_descricao());

            $tab = new Tab(["Servidor", "Regras", "Cartilha"]);

            $tab->abreConteudo();
            $aposentadoria->exibeAnaliseResumo();
            $aposentadoria->exibeAnalise();

            $tab->fechaConteudo();

            ###

            $tab->abreConteudo();

            $grid1 = new Grid();
            $grid1->abreColuna(6);

            $aposentadoria->exibeRegras();

            $grid1->fechaColuna();
            $grid1->abreColuna(6);

            $aposentadoria->exibeRemuneração();

            $grid1->fechaColuna();
            $grid1->fechaGrid();

            $tab->fechaConteudo();

            ###

            $tab->abreConteudo();

            $aposentadoria->exibeResumoCartilha();

            $tab->fechaConteudo();

            ###

            $tab->show();
            break;

        ########################################################

        /*
         * Aposentadoria Compulsória
         */

        case "permanenteCompulsoria":

            $grid->fechaColuna();
            $grid->abreColuna(12, 3);

            $aposentadoria->exibeMenuServidor(7);

            $grid->fechaColuna();
            $grid->abreColuna(12, 9);

            $aposentadoria = new AposentadoriaLC195Compulsoria($idServidorPesquisado);
            tituloTable($aposentadoria->get_descricao());

            $tab = new Tab(["Servidor", "Regras", "Cartilha"]);

            $tab->abreConteudo();
            $aposentadoria->exibeAnaliseResumo();
            $aposentadoria->exibeAnalise();

            $tab->fechaConteudo();

            ###

            $tab->abreConteudo();

            $grid1 = new Grid();
            $grid1->abreColuna(6);

            $aposentadoria->exibeRegras();

            $grid1->fechaColuna();
            $grid1->abreColuna(6);

            $aposentadoria->exibeRemuneração();

            $grid1->fechaColuna();
            $grid1->fechaGrid();

            $tab->fechaConteudo();

            ###

            $tab->abreConteudo();

            $aposentadoria->exibeResumoCartilha();

            $tab->fechaConteudo();

            ###

            $tab->show();
            break;

        ########################################################

        /*
         * Incapacidade Permanente
         */

        case "incapacidadePermanente":

            $grid->fechaColuna();
            $grid->abreColuna(12, 3);

            $aposentadoria->exibeMenuServidor(8);

            $grid->fechaColuna();
            $grid->abreColuna(12, 9);

            $figura = new Imagem(PASTA_FIGURAS . 'lc195incapacidadePermanente.png', null, "100%", "100%");
            $figura->set_id('imgCasa');
            $figura->set_class('imagem');
            $figura->show();
            break;

        ########################################################

        /*
         * Incapacidade Permanente por Acidente de trabalho
         */

        case "incapacidadeAcidenteAT":

            $grid->fechaColuna();
            $grid->abreColuna(12, 3);

            $aposentadoria->exibeMenuServidor(9);

            $grid->fechaColuna();
            $grid->abreColuna(12, 9);

            $figura = new Imagem(PASTA_FIGURAS . 'lc195incapacidadePermanenteAT.png', null, "100%", "100%");
            $figura->set_id('imgCasa');
            $figura->set_class('imagem');
            $figura->show();
            break;

        ########################################################

        /*
         * Regras de Transição
         */

        ########################################################

        /*
         * Regra dos Pontos - Integralidade e Paridade
         */

        case "pontosIntegral":

            $grid->fechaColuna();
            $grid->abreColuna(12, 3);

            $aposentadoria->exibeMenuServidor(12);

            $grid->fechaColuna();
            $grid->abreColuna(12, 9);

            $figura = new Imagem(PASTA_FIGURAS . 'transicaoPontos1.png', null, "100%", "100%");
            $figura->set_id('imgCasa');
            $figura->set_class('imagem');
            $figura->show();
            break;

        ########################################################

        /*
         * Regra dos Pontos - Média da Lei Federal nº 10.887/2004
         */

        case "pontosMedia":

            $grid->fechaColuna();
            $grid->abreColuna(12, 3);

            $aposentadoria->exibeMenuServidor(13);

            $grid->fechaColuna();
            $grid->abreColuna(12, 9);

            $figura = new Imagem(PASTA_FIGURAS . 'transicaoPontos2.png', null, "100%", "100%");
            $figura->set_id('imgCasa');
            $figura->set_class('imagem');
            $figura->show();
            break;

        ########################################################

        /*
         * Regra do Pedágio - Integralidade e Paridade
         */

        case "pedagioIntegral":

            $grid->fechaColuna();
            $grid->abreColuna(12, 3);

            $aposentadoria->exibeMenuServidor(15);

            $grid->fechaColuna();
            $grid->abreColuna(12, 9);

            $figura = new Imagem(PASTA_FIGURAS . 'transicaoPedagio1.png', null, "100%", "100%");
            $figura->set_id('imgCasa');
            $figura->set_class('imagem');
            $figura->show();
            break;

        ########################################################

        /*
         * Regra do Pedágio - Redutor de idade
         */

        case "pedagioRedutor":

            $grid->fechaColuna();
            $grid->abreColuna(12, 3);

            $aposentadoria->exibeMenuServidor(16);

            $grid->fechaColuna();
            $grid->abreColuna(12, 9);

            $figura = new Imagem(PASTA_FIGURAS . 'transicaoPedagio2.png', null, "100%", "100%");
            $figura->set_id('imgCasa');
            $figura->set_class('imagem');
            $figura->show();
            break;

        ########################################################

        /*
         * Regra do Pedágio - Média
         */

        case "pedagioMedia":

            $grid->fechaColuna();
            $grid->abreColuna(12, 3);

            $aposentadoria->exibeMenuServidor(17);

            $grid->fechaColuna();
            $grid->abreColuna(12, 9);

            $figura = new Imagem(PASTA_FIGURAS . 'transicaoPedagio3.png', null, "100%", "100%");
            $figura->set_id('imgCasa');
            $figura->set_class('imagem');
            $figura->show();
            break;

        ########################################################

        /*
         * Direito Adquirido
         */

        ########################################################
        /*
         * Aposentadoria Permanente por Idade e Contribuição
         */

        case "idadeContribuicao":

            $grid->fechaColuna();
            $grid->abreColuna(12, 3);

            $aposentadoria->exibeMenuServidor(19);

            $grid->fechaColuna();
            $grid->abreColuna(12, 9);

            $aposentadoria = new AposentadoriaDiretoAdquirido1($idServidorPesquisado);
            tituloTable($aposentadoria->get_descricao());

            $tab = new Tab(["Servidor", "Regras"]);

            $tab->abreConteudo();
            $aposentadoria->exibeAnaliseResumo();
            $aposentadoria->exibeAnalise();

            $tab->fechaConteudo();

            ###

            $tab->abreConteudo();

            $grid1 = new Grid();
            $grid1->abreColuna(6);

            $aposentadoria->exibeRegras();

            $grid1->fechaColuna();
            $grid1->abreColuna(6);

            $aposentadoria->exibeRemuneração();

            $grid1->fechaColuna();
            $grid1->fechaGrid();

            $tab->fechaConteudo();

            $tab->show();
            break;

        ########################################################

        /*
         * Aposentadoria Permanente por Idade 
         */

        case "idade":

            $grid->fechaColuna();
            $grid->abreColuna(12, 3);

            $aposentadoria->exibeMenuServidor(20);

            $grid->fechaColuna();
            $grid->abreColuna(12, 9);

            $aposentadoria = new AposentadoriaDiretoAdquirido2($idServidorPesquisado);
            tituloTable($aposentadoria->get_descricao());

            $tab = new Tab(["Servidor", "Regras"]);

            $tab->abreConteudo();
            $aposentadoria->exibeAnaliseResumo();
            $aposentadoria->exibeAnalise();

            $tab->fechaConteudo();

            ###

            $tab->abreConteudo();

            $grid1 = new Grid();
            $grid1->abreColuna(6);

            $aposentadoria->exibeRegras();

            $grid1->fechaColuna();
            $grid1->abreColuna(6);

            $aposentadoria->exibeRemuneração();

            $grid1->fechaColuna();
            $grid1->fechaGrid();

            $tab->fechaConteudo();

            $tab->show();
            break;

        ########################################################

        /*
         * Regras de transição - Artigo 2º da EC nº 41/2003
         */

        case "41_2":

            # OBS regra retirada pelo sistema pois é desfavorável para o servidor

            $grid->fechaColuna();
            $grid->abreColuna(12, 3);

            $aposentadoria->exibeMenuServidor(21);

            $grid->fechaColuna();
            $grid->abreColuna(12, 9);

            $aposentadoria = new AposentadoriaDiretoAdquirido3($idServidorPesquisado);
            tituloTable($aposentadoria->get_descricao());

            $tab = new Tab(["Servidor", "Regras"]);

            $tab->abreConteudo();
            $aposentadoria->exibeAnaliseResumo();
            $aposentadoria->exibeAnalise();

            $tab->fechaConteudo();

            ###

            $tab->abreConteudo();

            $grid1 = new Grid();
            $grid1->abreColuna(6);

            $aposentadoria->exibeRegras();

            $grid1->fechaColuna();
            $grid1->abreColuna(6);

            $aposentadoria->exibeRemuneração();

            $grid1->fechaColuna();
            $grid1->fechaGrid();

            $tab->fechaConteudo();

            $tab->show();
            break;

        ########################################################

        /*
         * Regras de transição - Artigo 6º da EC nº 41/2003
         */

        case "41_6":

            $grid->fechaColuna();
            $grid->abreColuna(12, 3);

            $aposentadoria->exibeMenuServidor(21);

            $grid->fechaColuna();
            $grid->abreColuna(12, 9);

            $aposentadoria = new AposentadoriaDiretoAdquirido4($idServidorPesquisado);
            tituloTable($aposentadoria->get_descricao());

            $tab = new Tab(["Servidor", "Regras"]);

            $tab->abreConteudo();
            $aposentadoria->exibeAnaliseResumo();
            $aposentadoria->exibeAnalise();

            $tab->fechaConteudo();

            ###

            $tab->abreConteudo();

            $grid1 = new Grid();
            $grid1->abreColuna(6);

            $aposentadoria->exibeRegras();

            $grid1->fechaColuna();
            $grid1->abreColuna(6);

            $aposentadoria->exibeRemuneração();

            $grid1->fechaColuna();
            $grid1->fechaGrid();

            $tab->fechaConteudo();

            $tab->show();
            break;

        ########################################################


        /*
         * Regras de transição - Artigo 3º da EC nº 47/2003
         */

        case "47_3":

            $grid->fechaColuna();
            $grid->abreColuna(12, 3);

            $aposentadoria->exibeMenuServidor(22);

            $grid->fechaColuna();
            $grid->abreColuna(12, 9);

            emConstrucao("Em breve esta área estará disponível.");
            break;

        #######################################################################################

        /*
         * Regras de transição 1
         */

        case "transicao1":

            $grid->fechaColuna();
            $grid->abreColuna(12, 3);

            $aposentadoria->exibeMenuServidor(13);

            $grid->fechaColuna();
            $grid->abreColuna(12, 9);

            emConstrucao("Em breve esta área estará disponível.");
            break;

        #######################################################################################

        /*
         * Regras de transição 2
         */

        case "transicao2":

            $grid->fechaColuna();
            $grid->abreColuna(12, 3);

            $aposentadoria->exibeMenuServidor(14);

            $grid->fechaColuna();
            $grid->abreColuna(12, 9);

            emConstrucao("Em breve esta área estará disponível.");
            break;

        #######################################################################################

        /*
         * Aposentadoria Compulsória
         */

        case "compulsoria":

            $grid->fechaColuna();
            $grid->abreColuna(12, 3);

            $aposentadoria->exibeMenuServidor(16);

            $grid->fechaColuna();
            $grid->abreColuna(12, 9);

            emConstrucao("Em breve esta área estará disponível.");
            break;

        #######################################################################################
    }

#############################    

    $grid->fechaColuna();
    $grid->fechaGrid();

    $page->terminaPagina();
} else {
    loadPage("../../areaServidor/sistema/login.php");
}    