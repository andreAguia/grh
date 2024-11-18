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

    # Verifica se veio menu grh e registra o acesso no log
    $grh = get('grh', false);
    if ($grh) {
        # Grava no log a atividade
        $atividade = "Cadastro do servidor - Aposentadoria";
        $data = date("Y-m-d H:i:s");
        $intra->registraLog($idUsuario, $data, $atividade, null, null, 7, $idServidorPesquisado);
    }

    # Pega a rotina
    # Verifica a fase do programa
    $fase = get('fase', 'tabs');
    $aba = get('aba', 1);

    # Verifica a origem 
    $origem = get_session("origem");

    # Começa uma nova página
    $page = new Page();

    # Cabeçalho da Página
    if (substr($fase, 0, 9) <> "relatorio") {
        AreaServidor::cabecalho();
    } else {
        $page->set_title("Previsão de Posentadoria - " . substr($fase, 10));
    }
    $page->iniciaPagina();

    ########################################################
    # Limita o tamanho da tela
    $grid = new Grid();
    $grid->abreColuna(12);

    # Define a mensagem do relatorio
    $mensagemRelatorio = "Atenção, esta é uma previsão da posentadoria"
            . " e as informações aqui contidas podem variar com o tempo.<br/>"
            . "As regras de aposentadoria estão diponíveis no site da GRH. https://uenf.br/dga/grh/";

    # Cria um menu
    if (substr($fase, 0, 9) <> "relatorio") {
        $menu = new MenuBar();

        # Verifica a rotina e define o link
        if ($fase == "tabs") {
            $linkvoltar = 'servidorMenu.php';
        } elseif (in_array($fase, $aposentadoria->get_modalidades())) {
            $linkvoltar = '?fase=tabs&aba=5';
        }

        # Verifica a origem
        if (!empty($origem)) {
            $linkvoltar = $origem;
        }

        # Botão voltar    
        $linkBotaoVoltar = new Button('Voltar', $linkvoltar);
        $linkBotaoVoltar->set_title('Volta para a página anterior');
        $linkBotaoVoltar->set_accessKey('V');
        $menu->add_link($linkBotaoVoltar, "left");

        if (in_array($fase, $aposentadoria->get_modalidades())) {

            # Relatório   
            $imagem = new Imagem(PASTA_FIGURAS . 'print.png', null, 15, 15);
            $botaoRel = new Button();
            $botaoRel->set_imagem($imagem);
            $botaoRel->set_title("Relatório da Previsão de Aposentadoria");
            $botaoRel->set_url("?fase=relatorio_{$fase}");
            $botaoRel->set_target("_blank");
            $menu->add_link($botaoRel, "right");
        }

        $menu->show();

        # Exibe os dados do servidor
        get_DadosServidor($idServidorPesquisado);
    }

    ########################################################

    switch ($fase) {
        case "tabs" :

            # Menu de Abas
            $tab = new Tab([
                "Dados do Servidor",
                "Tempo Averbado",
                "Vínculos Anteriores",
                "Afastamentos",
                "Previsão de Aposentadoria",
                "Documentação"
                    ], $aba);

            ####################################################

            /*
             *  Dados do Servidor
             */

            $tab->abreConteudo();

            # Relatório 
            $menu = new MenuBar();
            $imagem = new Imagem(PASTA_FIGURAS . 'print.png', null, 15, 15);
            $botaoRel = new Button();
            $botaoRel->set_imagem($imagem);
            $botaoRel->set_title("Relatório dos Dados de Aposentadoria");
            $botaoRel->set_url("?fase=relatorioDados");
            $botaoRel->set_target("_blank");
            $menu->add_link($botaoRel, "right");

            $menu->show();

            # Exibe os Dados
            $aposentadoria->exibeDadosServidor($idServidorPesquisado);

            $tab->fechaConteudo();

            ####################################################

            /*
             *  Tempo Averbado Detalhado
             */

            $tab->abreConteudo();

            # Relatório 
            $menu = new MenuBar();
            $imagem = new Imagem(PASTA_FIGURAS . 'print.png', null, 15, 15);
            $botaoRel = new Button();
            $botaoRel->set_imagem($imagem);
            $botaoRel->set_title("Relatório do Tempo Averbado");
            $botaoRel->set_url("?fase=relatorioAverbado");
            $botaoRel->set_target("_blank");
            $menu->add_link($botaoRel, "right");

            $menu->show();

            # Exibe os Dados
            $aposentadoria->exibeTempoAverbado($idServidorPesquisado);

            $tab->fechaConteudo();

            ####################################################

            /*
             *  Tempo Vinculos Anteriores
             */

            $tab->abreConteudo();

            $grid1 = new Grid();
            $grid1->abreColuna(12);

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

            $grid1->fechaColuna();
            $grid1->fechaGrid();

            $tab->fechaConteudo();

            ####################################################

            /*
             *  Afastamentos
             */

            $tab->abreConteudo();

            $grid1 = new Grid();
            $grid1->abreColuna(12, 12, 6);

            # Exibe os afastamentos que interrompem o tempo de contribuição
            $afast1 = new ListaAfastamentosServidor($idServidorPesquisado, "Afastamentos SEM Contribuição","Que Interrompes o Tempo de Serviço");
            $afast1->set_semTempoServico(true);
            $afast1->set_semTempoContribuicao(true);
            $afast1->exibeTabela();

            $grid1->fechaColuna();
            $grid1->abreColuna(12, 12, 6);

            # Exibe os afastamentos que interrompem o tempo de serviço     
            $afast2 = new ListaAfastamentosServidor($idServidorPesquisado, "Afastamentos COM Contribuição","Que Interrompes o Tempo de Serviço");         
            $afast2->set_semTempoServico(true);
            $afast2->set_semTempoContribuicao(false);
            $afast2->exibeTabela();

            $grid1->fechaColuna();
            $grid1->abreColuna(12);

            # exibe todos os afastamentos
            $afast3 = new ListaAfastamentosServidor($idServidorPesquisado, "Todos os Afastamentos");
            $afast3->exibeTabela();

            $grid1->fechaColuna();
            $grid1->fechaGrid();

            $tab->fechaConteudo();

            ####################################################

            /*
             * Previsão de Aposentadoria (resumo)
             */

            $tab->abreConteudo();

            $grid1 = new Grid();
            $grid1->abreColuna(12);

            # Relatório 
            $menu = new MenuBar();
            $imagem = new Imagem(PASTA_FIGURAS . 'print.png', null, 15, 15);
            $botaoRel = new Button();
            $botaoRel->set_imagem($imagem);
            $botaoRel->set_title("Relatório da Previsão de Aposentadoria");
            $botaoRel->set_url("?fase=relatorio_tabs");
            $botaoRel->set_target("_blank");
            $menu->add_link($botaoRel, "right");

            $menu->show();

            # Define as variáveis
            $subTitulo = "";

            # Percorre o array
            foreach ($aposentadoria->get_modalidades() as $item) {
                $previsaoAposentadoria = new PrevisaoAposentadoria($item, $idServidorPesquisado);

                # Verifica se mudou o subTitulo
                if ($previsaoAposentadoria->get_tipo() <> $subTitulo) {
                    # Verifica se é primeiro
                    if ($subTitulo <> "") {
                        $grid2->fechaGrid();
                    }

                    $subTitulo = $previsaoAposentadoria->get_tipo();

                    tituloTable($previsaoAposentadoria->get_tipo(), null, "clique no titulo da regra de aposentadoria para maiores detalhes");
                    br();

                    # Começa o grid
                    $grid2 = new Grid();
                }

                $grid2->abreColuna(12, 12, 6);
                $previsaoAposentadoria->exibe_analiseLink($idServidorPesquisado, "?fase={$item}", false);
                $grid2->fechaColuna();
            }

            $grid2->fechaGrid();

            $grid1->fechaColuna();
            $grid1->fechaGrid();

            $tab->fechaConteudo();

            ####################################################

            /*
             * Documentação
             */

            $tab->abreConteudo();

            $grid1 = new Grid();
            $grid1->abreColuna(12);

            $menu = new Menu("menuAposentadoria");

            $menu->add_item("titulo", "Documentação");

            # Banco de dados
            $pessoal = new Pessoal();

            # Pega os projetos cadastrados
            $select = 'SELECT idMenuDocumentos,
                          categoria,
                          texto,
                          title
                     FROM tbmenudocumentos
                     WHERE categoria = "Regras de Aposentadoria"
                  ORDER BY categoria, texto';

            $dados = $pessoal->select($select);
            $num = $pessoal->count($select);

            # Verifica se tem itens no menu
            if ($num > 0) {
                # Percorre o array 
                foreach ($dados as $valor) {

                    if (empty($valor["title"])) {
                        $title = $valor["texto"];
                    } else {
                        $title = $valor["title"];
                    }

                    # Verifica qual documento
                    $arquivoDocumento = PASTA_DOCUMENTOS . $valor["idMenuDocumentos"] . ".pdf";
                    if (file_exists($arquivoDocumento)) {
                        # Caso seja PDF abre uma janela com o pdf
                        $menu->add_item('linkWindow', $valor["texto"], PASTA_DOCUMENTOS . $valor["idMenuDocumentos"] . '.pdf', $title);
                    } else {
                        # Caso seja um .doc, somente faz o download
                        $menu->add_item('link', $valor["texto"], PASTA_DOCUMENTOS . $valor["idMenuDocumentos"] . '.doc', $title);
                    }
                }
            }

            $menu->add_item("linkWindow", "Regras Vigentes a partir de 01/01/2022", "https://www.rioprevidencia.rj.gov.br/PortalRP/Servicos/RegrasdeAposentadoria/apos2022/index.htm");
            $menu->add_item("linkWindow", "Regras Vigentes até 31/12/2021", "https://www.rioprevidencia.rj.gov.br/PortalRP/Servicos/RegrasdeAposentadoria/ate2021/index.htm");

            $menu->show();

            $grid1->fechaColuna();
            $grid1->fechaGrid();

            $tab->fechaConteudo();
            $tab->show();
            break;

        ########################################################

        /*
         * Relatório geral
         */

        case "relatorio_tabs" :

            # Dados do Servidor
            Grh::listaDadosServidorRelatorio2($idServidorPesquisado, "Previsão de Aposentadoria");
            br();

            # Define a função

            function formataDiasFaltantes($texto) {
                # Verifica se é numérico
                if (is_numeric($texto)) {
                    # Vefifica se é zero
                    if ($texto == 0) {
                        return "OK";
                    } else {
                        return "Faltam<br/>{$texto} Dias";
                    }
                } else {
                    return $texto;
                }
            }

            # Define o array do relatório
            foreach ($aposentadoria->get_modalidades() as $item) {
                $previsaoAposentadoria = new PrevisaoAposentadoria($item, $idServidorPesquisado);
                $arrayRelatorio[] = [
                    $previsaoAposentadoria->get_tipo(),
                    $previsaoAposentadoria->get_descricao() . "<p id='psubtituloRel'>{$previsaoAposentadoria->get_legislacao()}</p>",
                    str_replace("<br/>", " ", $previsaoAposentadoria->exibe_analiseRelatorio()),
                    formataDiasFaltantes($previsaoAposentadoria->get_diasFaltantes())];
            }

            $relatorio = new Relatorio();
            $relatorio->set_cabecalhoRelatorio(false);
            $relatorio->set_menuRelatorio(false);
            $relatorio->set_totalRegistro(false);
            $relatorio->set_dataImpressao(false);
            $relatorio->set_bordaInterna(true);
            $relatorio->set_conteudo($arrayRelatorio);
            $relatorio->set_numGrupo(0);
            $relatorio->set_label(["Regra", "Descrição", "Previsão", "Análise"]);
            $relatorio->set_align(["center", "left", "center", "center"]);
            $relatorio->set_width([0, 50, 35, 15]);
            $relatorio->set_subTotal(false);
            $relatorio->set_totalRegistro(false);
            $relatorio->set_rodape("");
            $relatorio->set_logServidor($idServidorPesquisado);
            $relatorio->set_logDetalhe("Visualizou relatório de previsão geral de aposentadoria");
            $relatorio->set_mensagemGeral($mensagemRelatorio);
            $relatorio->show($mensagemRelatorio);
            break;

        ########################################################

        /*
         * Regras Permanentes
         */

        case "voluntaria" :
            $aposentadoria = new PrevisaoAposentadoria("permanente1");
            $aposentadoria->exibe_telaServidor($idServidorPesquisado, $idUsuario);
            break;

        case "relatorio_voluntaria" :
            $aposentadoria = new PrevisaoAposentadoria("permanente1");
            $aposentadoria->exibe_relatorio($idServidorPesquisado, $idUsuario);
            break;

        case "compulsoria" :
            $aposentadoria = new PrevisaoAposentadoria("permanente2");
            $aposentadoria->exibe_telaServidor($idServidorPesquisado, $idUsuario);
            break;

        case "relatorio_compulsoria" :
            $aposentadoria = new PrevisaoAposentadoria("permanente2");
            $aposentadoria->exibe_relatorio($idServidorPesquisado, $idUsuario);
            break;

        ########################################################

        /*
         * Regras Transição
         */

        case "pontos1" :
            $aposentadoria = new PrevisaoAposentadoria("pontos1");
            $aposentadoria->exibe_telaServidor($idServidorPesquisado, $idUsuario);
            break;

        case "relatorio_pontos1" :
            $aposentadoria = new PrevisaoAposentadoria("pontos1");
            $aposentadoria->exibe_relatorio($idServidorPesquisado, $idUsuario);
            break;

        case "pontos2" :
            $aposentadoria = new PrevisaoAposentadoria("pontos2");
            $aposentadoria->exibe_telaServidor($idServidorPesquisado, $idUsuario);
            break;

        case "relatorio_pontos2" :
            $aposentadoria = new PrevisaoAposentadoria("pontos2");
            $aposentadoria->exibe_relatorio($idServidorPesquisado, $idUsuario);
            break;

        case "pedagio1" :
            $aposentadoria = new PrevisaoAposentadoria("pedagio1");
            $aposentadoria->exibe_telaServidor($idServidorPesquisado, $idUsuario);
            break;

        case "relatorio_pedagio1" :
            $aposentadoria = new PrevisaoAposentadoria("pedagio1");
            $aposentadoria->exibe_relatorio($idServidorPesquisado, $idUsuario);
            break;

        case "pedagio2" :
            $aposentadoria = new PrevisaoAposentadoria("pedagio2");
            $aposentadoria->exibe_telaServidor($idServidorPesquisado, $idUsuario);
            break;

        case "relatorio_pedagio2" :
            $aposentadoria = new PrevisaoAposentadoria("pedagio2");
            $aposentadoria->exibe_relatorio($idServidorPesquisado, $idUsuario);
            break;

        case "pedagio3" :
            $aposentadoria = new PrevisaoAposentadoria("pedagio3");
            $aposentadoria->exibe_telaServidor($idServidorPesquisado, $idUsuario);
            break;

        case "relatorio_pedagio3" :
            $aposentadoria = new PrevisaoAposentadoria("pedagio3");
            $aposentadoria->exibe_relatorio($idServidorPesquisado, $idUsuario);
            break;

        ########################################################

        /*
         * Direito Adquirido
         */

        case "adquirido1" :
            $aposentadoria = new PrevisaoAposentadoria("adquirido1");
            $aposentadoria->exibe_telaServidor($idServidorPesquisado, $idUsuario);
            break;

        case "relatorio_adquirido1" :
            $aposentadoria = new PrevisaoAposentadoria("adquirido1");
            $aposentadoria->exibe_relatorio($idServidorPesquisado, $idUsuario);
            break;

        case "adquirido2" :
            $aposentadoria = new PrevisaoAposentadoria("adquirido2");
            $aposentadoria->exibe_telaServidor($idServidorPesquisado, $idUsuario);
            break;

        case "relatorio_adquirido2" :
            $aposentadoria = new PrevisaoAposentadoria("adquirido2");
            $aposentadoria->exibe_relatorio($idServidorPesquisado, $idUsuario);
            break;

        case "adquirido3" :
            $aposentadoria = new PrevisaoAposentadoria("adquirido3");
            $aposentadoria->exibe_telaServidor($idServidorPesquisado, $idUsuario);
            break;

        case "relatorio_adquirido3" :
            $aposentadoria = new PrevisaoAposentadoria("adquirido3");
            $aposentadoria->exibe_relatorio($idServidorPesquisado, $idUsuario);
            break;

        case "adquirido4" :
            $aposentadoria = new PrevisaoAposentadoria("adquirido4");
            $aposentadoria->exibe_telaServidor($idServidorPesquisado, $idUsuario);
            break;

        case "relatorio_adquirido4" :
            $aposentadoria = new PrevisaoAposentadoria("adquirido4");
            $aposentadoria->exibe_relatorio($idServidorPesquisado, $idUsuario);
            break;

        ########################################################

        /*
         * Relatório Dados do Servidor
         */

        case "relatorioDados" :

            # Dados do Servidor
            Grh::listaDadosServidorRelatorio2($idServidorPesquisado, "Dados do Servidor", "Para Aposentadoria");
            br();

            $aposentadoria = new Aposentadoria();
            $aposentadoria->exibeDadosServidor($idServidorPesquisado, true);
            break;

        ########################################################

        /*
         * Relatório Averbação
         */

        case "relatorioAverbado" :

            # Dados do Servidor
            Grh::listaDadosServidorRelatorio2($idServidorPesquisado, "Tempo Averbado");
            br();

            $aposentadoria = new Aposentadoria();
            $aposentadoria->exibeTempoAverbado($idServidorPesquisado, true);
            break;

        ########################################################
    }

    $grid->fechaColuna();
    $grid->fechaGrid();

    $page->terminaPagina();
} else {
    loadPage("../../areaServidor/sistema/login.php");
}    