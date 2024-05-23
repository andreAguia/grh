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
    $page->iniciaPagina();

    # Cabeçalho da Página
    if (substr($fase, 0, 9) <> "relatorio") {
        AreaServidor::cabecalho();
    }

    ########################################################
    # Limita o tamanho da tela
    $grid = new Grid();
    $grid->abreColuna(12);

    # Define a mensagem do relatorio
    $mensagemRelatorio = "Atenção, esta é uma previsão da posentadoria e as informações aqui contidas podem variar com o tempo.";

    # Cria um menu
    if (substr($fase, 0, 9) <> "relatorio") {
        $menu = new MenuBar();

        # Verifica a rotina e define o link
        if ($fase == "tabs") {
            $linkvoltar = 'servidorMenu.php';
        } elseif ($fase == "voluntaria" OR $fase == "compulsoria" OR $fase == "incapacidade1" OR $fase == "incapacidade2") {
            $linkvoltar = '?fase=tabs&aba=4';
        } elseif ($fase == "pontosIntegral" OR $fase == "pontosMedia" OR $fase == "pedagioIntegral" OR $fase == "pedagioMedia" OR $fase == "pedagioReducao") {
            $linkvoltar = '?fase=tabs&aba=4';
        } elseif ($fase == "direitoAdquirido1" OR $fase == "direitoAdquirido2" OR $fase == "direitoAdquirido3" OR $fase == "direitoAdquirido4") {
            $linkvoltar = '?fase=tabs&aba=4';
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

        if ($fase == "voluntaria"
                OR $fase == "compulsoria"
                OR $fase == "pontosIntegral"
                OR $fase == "pontosMedia"
                OR $fase == "pedagioIntegral"
                OR $fase == "pedagioMedia"
                OR $fase == "pedagioReducao"
                OR $fase == "direitoAdquirido1"
                OR $fase == "direitoAdquirido2") {

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
            $grid1->abreColuna(12);

            # Exibe os afastamentos que interrompem o tempo            
            $afast1 = new ListaAfastamentosServidor($idServidorPesquisado, "Afastamentos Sem Contribuição");
            $afast1->set_interrompe(true);
            $afast1->exibeTabela();

            # exibe todos os afastamentos
            $afast2 = new ListaAfastamentosServidor($idServidorPesquisado, "Todos os Afastamentos");
            $afast2->exibeTabela();

            $grid1->fechaColuna();
            $grid1->fechaGrid();

            $tab->fechaConteudo();

            ####################################################

            /*
             * Resumo das Regras
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

            tituloTable("Regras Permanentes", null, "clique no titulo da regra de aposentadoria para maiores detalhes");
            br();

            $grid2 = new Grid();
            $grid2->abreColuna(12, 12, 6);

            $aposentadoria = new AposentadoriaLC195Voluntaria($idServidorPesquisado);
            linkTituloTable($aposentadoria->get_descricao(), null, "?fase=voluntaria", $aposentadoria->get_legislacao());
            $aposentadoria->exibeAnaliseResumo();

            $grid2->fechaColuna();
            $grid2->abreColuna(12, 12, 6);

            $aposentadoria = new AposentadoriaLC195Compulsoria($idServidorPesquisado);
            linkTituloTable($aposentadoria->get_descricao(), null, "?fase=compulsoria", $aposentadoria->get_legislacao());
            $aposentadoria->exibeAnaliseResumo();

            $grid2->fechaColuna();
            $grid2->fechaGrid();

            tituloTable("Regras de Transição", null, "clique no titulo da regra de aposentadoria para maiores detalhes");
            br();

            $grid2 = new Grid();
            $grid2->abreColuna(12, 12, 6);

            $aposentadoria = new AposentadoriaTransicaoPontos1($idServidorPesquisado);
            linkTituloTable($aposentadoria->get_descricao(), null, "?fase=pontosIntegral", $aposentadoria->get_legislacao());
            $aposentadoria->exibeAnaliseResumo();

            $grid2->fechaColuna();
            $grid2->abreColuna(12, 12, 6);

            $aposentadoria = new AposentadoriaTransicaoPontos2($idServidorPesquisado);
            linkTituloTable($aposentadoria->get_descricao(), null, "?fase=pontosMedia", $aposentadoria->get_legislacao());
            $aposentadoria->exibeAnaliseResumo();

            $grid2->fechaColuna();
            $grid2->abreColuna(12, 12, 6);

            $aposentadoria = new AposentadoriaTransicaoPedagio1($idServidorPesquisado);
            linkTituloTable($aposentadoria->get_descricao(), null, "?fase=pedagioIntegral", $aposentadoria->get_legislacao());
            $aposentadoria->exibeAnaliseResumo();

            $grid2->fechaColuna();
            $grid2->abreColuna(12, 12, 6);

            $aposentadoria = new AposentadoriaTransicaoPedagio2($idServidorPesquisado);
            linkTituloTable($aposentadoria->get_descricao(), null, "?fase=pedagioMedia", $aposentadoria->get_legislacao());
            $aposentadoria->exibeAnaliseResumo();

            $grid2->fechaColuna();
            $grid2->abreColuna(12, 12, 6);

            $aposentadoria = new AposentadoriaTransicaoPedagio3($idServidorPesquisado);
            linkTituloTable($aposentadoria->get_descricao(), null, "?fase=pedagioReducao", $aposentadoria->get_legislacao());
            $aposentadoria->exibeAnaliseResumo();

            $grid2->fechaColuna();
            $grid2->fechaGrid();

            tituloTable("Direito Adquirido", null, "clique no titulo da regra de aposentadoria para maiores detalhes");
            br();

            $grid2 = new Grid();
            $grid2->abreColuna(12, 12, 6);

            $aposentadoria = new AposentadoriaDireitoAdquirido1($idServidorPesquisado);
            linkTituloTable($aposentadoria->get_descricao(), null, "?fase=direitoAdquirido1", $aposentadoria->get_legislacao());
            $aposentadoria->exibeAnaliseResumo();
            #$aposentadoria->exibeAnalise();

            $grid2->fechaColuna();
            $grid2->abreColuna(12, 12, 6);

            $aposentadoria = new AposentadoriaDireitoAdquirido2($idServidorPesquisado);
            linkTituloTable($aposentadoria->get_descricao(), null, "?fase=direitoAdquirido2", $aposentadoria->get_legislacao());
            $aposentadoria->exibeAnaliseResumo();
            #$aposentadoria->exibeAnalise();

            $grid2->fechaColuna();
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

            # Classes
            $permanente1 = new AposentadoriaLC195Voluntaria($idServidorPesquisado);
            $permanente2 = new AposentadoriaLC195Compulsoria($idServidorPesquisado);
            $transicao1 = new AposentadoriaTransicaoPontos1($idServidorPesquisado);
            $transicao2 = new AposentadoriaTransicaoPontos2($idServidorPesquisado);
            $transicao3 = new AposentadoriaTransicaoPedagio1($idServidorPesquisado);
            $transicao4 = new AposentadoriaTransicaoPedagio2($idServidorPesquisado);
            $transicao5 = new AposentadoriaTransicaoPedagio3($idServidorPesquisado);
            $direito1 = new AposentadoriaDireitoAdquirido1($idServidorPesquisado);
            $direito2 = new AposentadoriaDireitoAdquirido2($idServidorPesquisado);

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

            # Previsão
            $array = [
                ["Regras Permanentes", $permanente1->get_descricao() . "<p id='psubtituloRel'>{$permanente1->get_legislacao()}</p>", str_replace("<br/>", " ", $permanente1->exibeAnaliseResumo(true)), formataDiasFaltantes($permanente1->getDiasFaltantes())],
                ["Regras Permanentes", $permanente2->get_descricao() . "<p id='psubtituloRel'>{$permanente2->get_legislacao()}</p>", str_replace("<br/>", " ", $permanente2->exibeAnaliseResumo(true)), formataDiasFaltantes($permanente2->getDiasFaltantes())],
                ["Regras de Transição", $transicao1->get_descricao() . "<p id='psubtituloRel'>{$transicao1->get_legislacao()}</p>", str_replace("<br/>", " ", $transicao1->exibeAnaliseResumo(true)), formataDiasFaltantes($transicao1->getDiasFaltantes())],
                ["Regras de Transição", $transicao2->get_descricao() . "<p id='psubtituloRel'>{$transicao2->get_legislacao()}</p>", str_replace("<br/>", " ", $transicao2->exibeAnaliseResumo(true)), formataDiasFaltantes($transicao2->getDiasFaltantes())],
                ["Regras de Transição", $transicao3->get_descricao() . "<p id='psubtituloRel'>{$transicao3->get_legislacao()}</p>", str_replace("<br/>", " ", $transicao3->exibeAnaliseResumo(true)), formataDiasFaltantes($transicao3->getDiasFaltantes())],
                ["Regras de Transição", $transicao4->get_descricao() . "<p id='psubtituloRel'>{$transicao4->get_legislacao()}</p>", str_replace("<br/>", " ", $transicao4->exibeAnaliseResumo(true)), formataDiasFaltantes($transicao4->getDiasFaltantes())],
                ["Regras de Transição", $transicao5->get_descricao() . "<p id='psubtituloRel'>{$transicao5->get_legislacao()}</p>", str_replace("<br/>", " ", $transicao5->exibeAnaliseResumo(true)), formataDiasFaltantes($transicao5->getDiasFaltantes())],
                ["Direito Adquirido", $direito1->get_descricao() . "<p id='psubtituloRel'>{$direito1->get_legislacao()}</p>", str_replace("<br/>", " ", $direito1->exibeAnaliseResumo(true)), formataDiasFaltantes($direito1->getDiasFaltantes())],
                ["Direito Adquirido", $direito2->get_descricao() . "<p id='psubtituloRel'>{$direito2->get_legislacao()}</p>", str_replace("<br/>", " ", $direito2->exibeAnaliseResumo(true)), formataDiasFaltantes($direito2->getDiasFaltantes())],
            ];

            $relatorio = new Relatorio();
            $relatorio->set_cabecalhoRelatorio(false);
            $relatorio->set_menuRelatorio(false);
            $relatorio->set_totalRegistro(false);
            $relatorio->set_dataImpressao(false);
            $relatorio->set_bordaInterna(true);
            $relatorio->set_conteudo($array);
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

            # Inicia a classe
            $aposentadoria = new AposentadoriaLC195Voluntaria($idServidorPesquisado);

            # Grava no log a atividade
            $atividade = "Cadastro do servidor - Aposentadoria - Regras Permanentes<br/>{$aposentadoria->get_descricao()}";
            $intra->registraLog($idUsuario, date("Y-m-d H:i:s"), $atividade, null, null, 7, $idServidorPesquisado);

            $grid1 = new Grid();
            $grid1->abreColuna(12);

            # Exibe a regra
            tituloTable($aposentadoria->get_descricao(), null, $aposentadoria->get_legislacao());
            $aposentadoria->exibeAnaliseResumo();

            $grid1->fechaColuna();
            $grid1->abreColuna(12, 12, 8);

            $aposentadoria->exibeAnalise();

            $grid2 = new Grid();
            $grid2->abreColuna(12);

            tituloTable("Cartilha");

            $grid2->fechaColuna();
            $grid2->abreColuna(6);

            $aposentadoria->exibeResumoCartilha(1);

            $grid2->fechaColuna();
            $grid2->abreColuna(6);

            $aposentadoria->exibeResumoCartilha(2);

            $grid2->fechaColuna();
            $grid2->fechaGrid();
            br();

            $grid1->fechaColuna();
            $grid1->abreColuna(12, 12, 4);

            $aposentadoria->exibeRemuneração();
            $aposentadoria->exibeRegras();

            $grid1->fechaColuna();
            $grid1->fechaGrid();
            break;

        case "relatorio_voluntaria" :

            # Inicia a classe
            $aposentadoria = new AposentadoriaLC195Voluntaria($idServidorPesquisado);

            # Grava no log a atividade
            $atividade = "Visualizou o relatório de Aposentadoria - Regras Permanentes<br/>{$aposentadoria->get_descricao()}";
            $intra->registraLog($idUsuario, date("Y-m-d H:i:s"), $atividade, null, null, 4, $idServidorPesquisado);

            # Dados do Servidor
            Grh::listaDadosServidorRelatorio2(
                    $idServidorPesquisado,
                    $aposentadoria->get_descricao(),
                    $aposentadoria->get_legislacao() . "<br/>" . $aposentadoria->get_tipo(),
                    true,
                    $mensagemRelatorio
            );
            br();

            # Exibe a regra
            p($aposentadoria->exibeAnaliseResumo(true), "center");
            $aposentadoria->exibeAnalise(true);
            $aposentadoria->exibeRegras(true);
            $aposentadoria->exibeRemuneração(true);
            break;

        case "compulsoria" :

            # Inicia a classe
            $aposentadoria = new AposentadoriaLC195Compulsoria($idServidorPesquisado);

            # Grava no log a atividade
            $atividade = "Cadastro do servidor - Aposentadoria - Regras Permanentes<br/>{$aposentadoria->get_descricao()}";
            $intra->registraLog($idUsuario, date("Y-m-d H:i:s"), $atividade, null, null, 7, $idServidorPesquisado);

            $grid1 = new Grid();
            $grid1->abreColuna(12);

            # Exibe a regra
            tituloTable($aposentadoria->get_descricao(), null, $aposentadoria->get_legislacao());
            $aposentadoria->exibeAnaliseResumo();

            $grid1->fechaColuna();
            $grid1->abreColuna(12, 12, 8);

            $aposentadoria->exibeAnalise();

            $grid2 = new Grid();
            $grid2->abreColuna(12);

            tituloTable("Cartilha");

            $grid2->fechaColuna();
            $grid2->abreColuna(6);

            $aposentadoria->exibeResumoCartilha(1);

            $grid2->fechaColuna();
            $grid2->abreColuna(6);

            $aposentadoria->exibeResumoCartilha(2);
            $grid2->fechaColuna();
            $grid2->fechaGrid();

            $grid1->fechaColuna();
            $grid1->abreColuna(12, 12, 4);

            $aposentadoria->exibeRemuneração();
            $aposentadoria->exibeRegras();

            $grid1->fechaColuna();
            $grid1->fechaGrid();
            break;

        case "relatorio_compulsoria" :

            # Inicia a classe
            $aposentadoria = new AposentadoriaLC195Compulsoria($idServidorPesquisado);

            # Grava no log a atividade
            $atividade = "Visualizou o relatório de Aposentadoria - Regras Permanentes<br/>{$aposentadoria->get_descricao()}";
            $intra->registraLog($idUsuario, date("Y-m-d H:i:s"), $atividade, null, null, 4, $idServidorPesquisado);

            # Dados do Servidor
            Grh::listaDadosServidorRelatorio2(
                    $idServidorPesquisado,
                    $aposentadoria->get_descricao(),
                    $aposentadoria->get_legislacao() . "<br/>" . $aposentadoria->get_tipo()
            );
            br();

            # Exibe a regra
            p($aposentadoria->exibeAnaliseResumo(true), "center");
            $aposentadoria->exibeAnalise(true);
            $aposentadoria->exibeRegras(true);
            $aposentadoria->exibeRemuneração(true);
            break;

        case "incapacidade1" :
            $grid1 = new Grid();
            $grid1->abreColuna(12);

            tituloTable("Aposentadoria por Incapacidade Permanente<br/>Art. 2º, inciso I, combinado com o art. 7º, §4º da Lei Complementar nº 195/2021");

            $grid1->fechaColuna();
            $grid1->abreColuna(12, 6);

            $figura = new Imagem(PASTA_FIGURAS . "lc195incapacidadePermanente1.png", null, "100%", "100%");
            $figura->set_id('imgCasa');
            $figura->set_class('imagem');
            $figura->show();

            $grid1->fechaColuna();
            $grid1->abreColuna(12, 6);

            $figura = new Imagem(PASTA_FIGURAS . "lc195incapacidadePermanente2.png", null, "100%", "100%");
            $figura->set_id('imgCasa');
            $figura->set_class('imagem');
            $figura->show();

            $grid1->fechaColuna();
            $grid1->fechaGrid();
            br();
            break;

        case "incapacidade2" :
            $grid1 = new Grid();
            $grid1->abreColuna(12);

            tituloTable("Aposentadoria por Incapacidade Permanente<br/>Acidente de trabalho, doença profissional ou doença de trabalho<br/>Art. 2º, inciso I, combinado com o art. 7º, §5º da Lei Complementar nº 195/2021.");

            $grid1->fechaColuna();
            $grid1->abreColuna(12, 6);

            $figura = new Imagem(PASTA_FIGURAS . "lc195incapacidadePermanente3.png", null, "100%", "100%");
            $figura->set_id('imgCasa');
            $figura->set_class('imagem');
            $figura->show();

            $grid1->fechaColuna();
            $grid1->abreColuna(12, 6);

            $figura = new Imagem(PASTA_FIGURAS . "lc195incapacidadePermanente4.png", null, "100%", "100%");
            $figura->set_id('imgCasa');
            $figura->set_class('imagem');
            $figura->show();

            $grid1->fechaColuna();
            $grid1->fechaGrid();
            br(2);
            break;

        ########################################################

        /*
         * Regras Transição
         */

        case "pontosIntegral" :

            # Inicia a classe
            $aposentadoria = new AposentadoriaTransicaoPontos1($idServidorPesquisado);

            # Grava no log a atividade
            $atividade = "Cadastro do servidor - Aposentadoria - Regras de Transição<br/>{$aposentadoria->get_descricao()}";
            $intra->registraLog($idUsuario, date("Y-m-d H:i:s"), $atividade, null, null, 7, $idServidorPesquisado);

            $grid1 = new Grid();
            $grid1->abreColuna(12);

            # Exibe a regra
            tituloTable($aposentadoria->get_descricao(), null, $aposentadoria->get_legislacao());
            $aposentadoria->exibeAnaliseResumo();

            $grid1->fechaColuna();
            $grid1->abreColuna(12, 12, 8);

            $aposentadoria->exibeAnalise();
            $aposentadoria->exibeHistoricoPontuacao();

            $grid2 = new Grid();
            $grid2->abreColuna(12);

            tituloTable("Cartilha");

            $grid2->fechaColuna();
            $grid2->abreColuna(6);

            $aposentadoria->exibeResumoCartilha(1);

            $grid2->fechaColuna();
            $grid2->abreColuna(6);

            $aposentadoria->exibeResumoCartilha(2);
            $grid2->fechaColuna();
            $grid2->fechaGrid();

            $grid1->fechaColuna();
            $grid1->abreColuna(12, 12, 4);

            $aposentadoria->exibeRemuneração();
            $aposentadoria->exibeRegras();
            $aposentadoria->exibeTabelaRegras();

            $grid1->fechaColuna();
            break;

        case "relatorio_pontosIntegral" :

            # Inicia a classe
            $aposentadoria = new AposentadoriaTransicaoPontos1($idServidorPesquisado);

            # Grava no log a atividade
            $atividade = "Visualizou o relatório de Aposentadoria - Regras de Transição<br/>{$aposentadoria->get_descricao()}";
            $intra->registraLog($idUsuario, date("Y-m-d H:i:s"), $atividade, null, null, 4, $idServidorPesquisado);

            # Dados do Servidor
            Grh::listaDadosServidorRelatorio2(
                    $idServidorPesquisado,
                    $aposentadoria->get_descricao(),
                    $aposentadoria->get_legislacao() . "<br/>" . $aposentadoria->get_tipo(),
                    true,
                    $mensagemRelatorio
            );
            br();

            # Exibe a regra
            p($aposentadoria->exibeAnaliseResumo(true), "center");
            $aposentadoria->exibeAnalise(true);
            $aposentadoria->exibeHistoricoPontuacao(true);
            $aposentadoria->exibeRemuneração(true);
            $aposentadoria->exibeRegras(true);
            $aposentadoria->exibeTabelaRegras(true);
            break;

        case "pontosMedia" :

            # Inicia a classe
            $aposentadoria = new AposentadoriaTransicaoPontos2($idServidorPesquisado);

            # Grava no log a atividade
            $atividade = "Cadastro do servidor - Aposentadoria - Regras de Transição<br/>{$aposentadoria->get_descricao()}";
            $intra->registraLog($idUsuario, date("Y-m-d H:i:s"), $atividade, null, null, 7, $idServidorPesquisado);

            $grid1 = new Grid();
            $grid1->abreColuna(12);

            # Exibe a regra
            tituloTable($aposentadoria->get_descricao(), null, $aposentadoria->get_legislacao());
            $aposentadoria->exibeAnaliseResumo();

            $grid1->fechaColuna();
            $grid1->abreColuna(12, 12, 8);

            $aposentadoria->exibeAnalise();
            $aposentadoria->exibeHistoricoPontuacao();

            $grid2 = new Grid();
            $grid2->abreColuna(12);

            tituloTable("Cartilha");

            $grid2->fechaColuna();
            $grid2->abreColuna(6);

            $aposentadoria->exibeResumoCartilha(1);

            $grid2->fechaColuna();
            $grid2->abreColuna(6);

            $aposentadoria->exibeResumoCartilha(2);
            $grid2->fechaColuna();
            $grid2->fechaGrid();
            $grid1->fechaColuna();

            $grid1->abreColuna(12, 12, 4);

            $aposentadoria->exibeRemuneração();
            $aposentadoria->exibeRegras();
            $aposentadoria->exibeTabelaRegras();

            $grid1->fechaColuna();
            break;

        case "relatorio_pontosMedia" :

            # Inicia a classe
            $aposentadoria = new AposentadoriaTransicaoPontos2($idServidorPesquisado);

            # Grava no log a atividade
            $atividade = "Visualizou o relatório de Aposentadoria - Regras de Transição<br/>{$aposentadoria->get_descricao()}";
            $intra->registraLog($idUsuario, date("Y-m-d H:i:s"), $atividade, null, null, 4, $idServidorPesquisado);

            # Dados do Servidor
            Grh::listaDadosServidorRelatorio2(
                    $idServidorPesquisado,
                    $aposentadoria->get_descricao(),
                    $aposentadoria->get_legislacao() . "<br/>" . $aposentadoria->get_tipo(),
                    true,
                    $mensagemRelatorio
            );
            br();

            # Exibe a regra
            p($aposentadoria->exibeAnaliseResumo(true), "center");
            $aposentadoria->exibeAnalise(true);
            $aposentadoria->exibeHistoricoPontuacao(true);
            $aposentadoria->exibeRemuneração(true);
            $aposentadoria->exibeRegras(true);
            $aposentadoria->exibeTabelaRegras(true);
            break;

        case "pedagioIntegral" :
            # Inicia a classe
            $aposentadoria = new AposentadoriaTransicaoPedagio1($idServidorPesquisado);

            # Grava no log a atividade
            $atividade = "Cadastro do servidor - Aposentadoria - Regras de Transição<br/>{$aposentadoria->get_descricao()}";
            $intra->registraLog($idUsuario, date("Y-m-d H:i:s"), $atividade, null, null, 7, $idServidorPesquisado);

            $grid1 = new Grid();
            $grid1->abreColuna(12);

            # Exibe a regra
            tituloTable($aposentadoria->get_descricao(), null, $aposentadoria->get_legislacao());
            $aposentadoria->exibeAnaliseResumo();

            $grid1->fechaColuna();
            $grid1->abreColuna(12, 12, 8);

            $aposentadoria->exibeAnalise();

            $grid2 = new Grid();
            $grid2->abreColuna(12, 6);

            $aposentadoria->exibeTempoAntes31_12_21();

            $grid2->fechaColuna();
            $grid2->abreColuna(12, 6);

            $aposentadoria->exibeCalculoPedagio();

            $grid2->fechaColuna();
            $grid2->abreColuna(12);

            tituloTable("Cartilha");

            $grid2->fechaColuna();
            $grid2->abreColuna(12, 6);

            $aposentadoria->exibeResumoCartilha(1);

            $grid2->fechaColuna();
            $grid2->abreColuna(12, 6);

            $aposentadoria->exibeResumoCartilha(2);
            $grid2->fechaColuna();
            $grid2->fechaGrid();
            $grid1->fechaColuna();

            $grid1->abreColuna(12, 12, 4);

            $aposentadoria->exibeRemuneração();
            $aposentadoria->exibeRegras();

            $grid1->fechaColuna();
            break;

        case "relatorio_pedagioIntegral" :

            # Inicia a classe
            $aposentadoria = new AposentadoriaTransicaoPedagio1($idServidorPesquisado);

            # Grava no log a atividade
            $atividade = "Visualizou o relatório de Aposentadoria - Regras de Transição<br/>{$aposentadoria->get_descricao()}";
            $intra->registraLog($idUsuario, date("Y-m-d H:i:s"), $atividade, null, null, 4, $idServidorPesquisado);

            # Dados do Servidor
            Grh::listaDadosServidorRelatorio2(
                    $idServidorPesquisado,
                    $aposentadoria->get_descricao(),
                    $aposentadoria->get_legislacao() . "<br/>" . $aposentadoria->get_tipo(),
                    true,
                    $mensagemRelatorio
            );
            br();

            # Exibe a regra
            p($aposentadoria->exibeAnaliseResumo(true), "center");
            $aposentadoria->exibeAnalise(true);

            $grid2 = new Grid();
            $grid2->abreColuna(6);

            $aposentadoria->exibeTempoAntes31_12_21(true);

            $grid2->fechaColuna();
            $grid2->abreColuna(6);

            $aposentadoria->exibeCalculoPedagio(true);

            $grid2->fechaColuna();
            $grid2->fechaGrid();

            $aposentadoria->exibeRemuneração(true);
            $aposentadoria->exibeRegras(true);
            break;

        case "pedagioMedia" :
            # Inicia a classe
            $aposentadoria = new AposentadoriaTransicaoPedagio2($idServidorPesquisado);

            # Grava no log a atividade
            $atividade = "Cadastro do servidor - Aposentadoria - Regras de Transição<br/>{$aposentadoria->get_descricao()}";
            $intra->registraLog($idUsuario, date("Y-m-d H:i:s"), $atividade, null, null, 7, $idServidorPesquisado);

            $grid1 = new Grid();
            $grid1->abreColuna(12);

            # Exibe a regra
            tituloTable($aposentadoria->get_descricao(), null, $aposentadoria->get_legislacao());
            $aposentadoria->exibeAnaliseResumo();

            $grid1->fechaColuna();
            $grid1->abreColuna(12, 12, 8);

            $aposentadoria->exibeAnalise();

            $grid2 = new Grid();
            $grid2->abreColuna(12, 6);

            $aposentadoria->exibeTempoAntes31_12_21();

            $grid2->fechaColuna();
            $grid2->abreColuna(12, 6);

            $aposentadoria->exibeCalculoPedagio();

            $grid2->fechaColuna();
            $grid2->abreColuna(12);

            tituloTable("Cartilha");

            $grid2->fechaColuna();
            $grid2->abreColuna(12, 6);

            $aposentadoria->exibeResumoCartilha(1);

            $grid2->fechaColuna();
            $grid2->abreColuna(12, 6);

            $aposentadoria->exibeResumoCartilha(2);
            $grid2->fechaColuna();
            $grid2->fechaGrid();
            $grid1->fechaColuna();

            $grid1->abreColuna(12, 12, 4);

            $aposentadoria->exibeRemuneração();
            $aposentadoria->exibeRegras();

            $grid1->fechaColuna();
            break;

        case "relatorio_pedagioMedia" :

            # Inicia a classe
            $aposentadoria = new AposentadoriaTransicaoPedagio1($idServidorPesquisado);

            # Grava no log a atividade
            $atividade = "Visualizou o relatório de Aposentadoria - Regras de Transição<br/>{$aposentadoria->get_descricao()}";
            $intra->registraLog($idUsuario, date("Y-m-d H:i:s"), $atividade, null, null, 4, $idServidorPesquisado);

            # Dados do Servidor
            Grh::listaDadosServidorRelatorio2(
                    $idServidorPesquisado,
                    $aposentadoria->get_descricao(),
                    $aposentadoria->get_legislacao() . "<br/>" . $aposentadoria->get_tipo(),
                    true,
                    $mensagemRelatorio
            );
            br();

            # Exibe a regra
            p($aposentadoria->exibeAnaliseResumo(true), "center");
            $aposentadoria->exibeAnalise(true);

            $grid2 = new Grid();
            $grid2->abreColuna(6);

            $aposentadoria->exibeTempoAntes31_12_21(true);

            $grid2->fechaColuna();
            $grid2->abreColuna(6);

            $aposentadoria->exibeCalculoPedagio(true);

            $grid2->fechaColuna();
            $grid2->fechaGrid();

            $aposentadoria->exibeRemuneração(true);
            $aposentadoria->exibeRegras(true);
            break;

        case "pedagioReducao" :

            # Inicia a classe
            $aposentadoria = new AposentadoriaTransicaoPedagio3($idServidorPesquisado);

            # Grava no log a atividade
            $atividade = "Cadastro do servidor - Aposentadoria - Regras de Transição<br/>{$aposentadoria->get_descricao()}";
            $intra->registraLog($idUsuario, date("Y-m-d H:i:s"), $atividade, null, null, 7, $idServidorPesquisado);

            $grid1 = new Grid();
            $grid1->abreColuna(12);

            # Exibe a regra
            tituloTable($aposentadoria->get_descricao(), null, $aposentadoria->get_legislacao());
            $aposentadoria->exibeAnaliseResumo();

            $grid1->fechaColuna();
            $grid1->abreColuna(12, 12, 8);

//            $painel = new Callout("alert");
//            $painel->abre();
//            p("Atenção! Esta rotina não está pronta ainda!</br>Os valores ainda estão incorretos.", "center");
//            $painel->fecha();

            $aposentadoria->exibeAnalise();

            $grid2 = new Grid();
            $grid2->abreColuna(12, 6);

            $aposentadoria->exibeTempoAntes31_12_21();

            $grid2->fechaColuna();
            $grid2->abreColuna(12, 6);

            $aposentadoria->exibeCalculoPedagio();

            $grid2->fechaColuna();
            $grid2->fechaGrid();

            $grid1->fechaColuna();
            $grid1->abreColuna(12, 12, 4);

            $aposentadoria->exibeRemuneração();
            $aposentadoria->exibeRegras();

            $grid1->fechaColuna();
            $grid1->abreColuna(12, 12, 5);

            $aposentadoria->exibeCalculoRedutor();

            $grid1->fechaColuna();
            $grid1->abreColuna(12, 12, 7);

            $aposentadoria->exibeCalculoRedutorDetalhado();

            $grid1->fechaColuna();
            $grid1->fechaGrid();

            tituloTable("Cartilha");

            $grid2->fechaColuna();
            $grid2->abreColuna(12, 6);

            $aposentadoria->exibeResumoCartilha(1);

            $grid2->fechaColuna();
            $grid2->abreColuna(12, 6);

            $aposentadoria->exibeResumoCartilha(2);
            $grid2->fechaColuna();
            $grid2->fechaGrid();
            break;

        case "relatorio_pedagioReducao" :

            # Inicia a classe
            $aposentadoria = new AposentadoriaTransicaoPedagio3($idServidorPesquisado);

            # Grava no log a atividade
            $atividade = "Visualizou o relatório de Aposentadoria - Regras de Transição<br/>{$aposentadoria->get_descricao()}";
            $intra->registraLog($idUsuario, date("Y-m-d H:i:s"), $atividade, null, null, 4, $idServidorPesquisado);

            # Dados do Servidor
            Grh::listaDadosServidorRelatorio2(
                    $idServidorPesquisado,
                    $aposentadoria->get_descricao(),
                    $aposentadoria->get_legislacao() . "<br/>" . $aposentadoria->get_tipo(),
                    true,
                    $mensagemRelatorio
            );
            br();

            # Exibe a regra
            p($aposentadoria->exibeAnaliseResumo(true), "center");
            $aposentadoria->exibeAnalise(true);

            $grid2 = new Grid();
            $grid2->abreColuna(6);

            $aposentadoria->exibeTempoAntes31_12_21(true);
            $aposentadoria->exibeCalculoRedutor(true);

            $grid2->fechaColuna();
            $grid2->abreColuna(6);

            $aposentadoria->exibeCalculoPedagio(true);

            $grid2->fechaColuna();
            $grid2->fechaGrid();

            $aposentadoria->exibeRemuneração(true);
            $aposentadoria->exibeRegras(true);
            break;
        ########################################################

        /*
         * Direito Adquirido
         */

        case "direitoAdquirido1" :
            # C.F. Art. 40, §1º, III, alínea a
            # Inicia a classe
            $aposentadoria = new AposentadoriaDireitoAdquirido1($idServidorPesquisado);

            # Grava no log a atividade
            $atividade = "Cadastro do servidor - Aposentadoria - Direito Adquirido<br/>{$aposentadoria->get_descricao()}";
            $intra->registraLog($idUsuario, date("Y-m-d H:i:s"), $atividade, null, null, 7, $idServidorPesquisado);

            $grid1 = new Grid();
            $grid1->abreColuna(12);

            # Exibe a regra
            tituloTable($aposentadoria->get_descricao(), null, $aposentadoria->get_legislacao());
            $aposentadoria->exibeAnaliseResumo();

            $grid1->fechaColuna();
            $grid1->abreColuna(12, 12, 8);

            $aposentadoria->exibeAnalise();

            $grid1->fechaColuna();
            $grid1->abreColuna(12, 6, 4);

            $aposentadoria->exibeRemuneração();

            $grid1->fechaColuna();
            $grid1->abreColuna(12, 6, 8);

            $aposentadoria->exibeRegras();

            $grid1->fechaColuna();
            break;

        case "relatorio_direitoAdquirido1" :

            # Inicia a classe
            $aposentadoria = new AposentadoriaDireitoAdquirido1($idServidorPesquisado);

            # Grava no log a atividade
            $atividade = "Visualizou o relatório de Aposentadoria - Direito Adquirido<br/>{$aposentadoria->get_descricao()}";
            $intra->registraLog($idUsuario, date("Y-m-d H:i:s"), $atividade, null, null, 4, $idServidorPesquisado);

            # Dados do Servidor
            Grh::listaDadosServidorRelatorio2(
                    $idServidorPesquisado,
                    $aposentadoria->get_descricao(),
                    $aposentadoria->get_legislacao() . "<br/>" . $aposentadoria->get_tipo(),
                    true,
                    $mensagemRelatorio
            );
            br();

            # Exibe a regra
            p($aposentadoria->exibeAnaliseResumo(true), "center");
            $aposentadoria->exibeAnalise(true);
            $aposentadoria->exibeRemuneração(true);
            $aposentadoria->exibeRegras(true);
            break;

        case "direitoAdquirido2" :
            # C.F. Art. 40, §1º, III, alínea b
            # Inicia a classe
            $aposentadoria = new AposentadoriaDireitoAdquirido2($idServidorPesquisado);

            # Grava no log a atividade
            $atividade = "Cadastro do servidor - Aposentadoria - Direito Adquirido<br/>{$aposentadoria->get_descricao()}";
            $intra->registraLog($idUsuario, date("Y-m-d H:i:s"), $atividade, null, null, 7, $idServidorPesquisado);

            $grid1 = new Grid();
            $grid1->abreColuna(12);

            # Exibe a regra
            tituloTable($aposentadoria->get_descricao(), null, $aposentadoria->get_legislacao());
            $aposentadoria->exibeAnaliseResumo();

            $grid1->fechaColuna();
            $grid1->abreColuna(12, 12, 8);

            $aposentadoria->exibeAnalise();

            $grid1->fechaColuna();
            $grid1->abreColuna(12, 6, 4);

            $aposentadoria->exibeRemuneração();

            $grid1->fechaColuna();
            $grid1->abreColuna(12, 6, 8);

            $aposentadoria->exibeRegras();

            $grid1->fechaColuna();
            break;

        case "relatorio_direitoAdquirido2" :

            # Inicia a classe
            $aposentadoria = new AposentadoriaDireitoAdquirido2($idServidorPesquisado);

            # Grava no log a atividade
            $atividade = "Visualizou o relatório de Aposentadoria - Direito Adquirido<br/>{$aposentadoria->get_descricao()}";
            $intra->registraLog($idUsuario, date("Y-m-d H:i:s"), $atividade, null, null, 4, $idServidorPesquisado);

            # Dados do Servidor
            Grh::listaDadosServidorRelatorio2(
                    $idServidorPesquisado,
                    $aposentadoria->get_descricao(),
                    $aposentadoria->get_legislacao() . "<br/>" . $aposentadoria->get_tipo(),
                    true,
                    $mensagemRelatorio
            );
            br();

            # Exibe a regra
            p($aposentadoria->exibeAnaliseResumo(true), "center");
            $aposentadoria->exibeAnalise(true);
            $aposentadoria->exibeRemuneração(true);
            $aposentadoria->exibeRegras(true);
            break;

        case "direitoAdquirido3" :
            # Art. 6º DA EC Nº 41/2003
            # Inicia a classe
            $aposentadoria = new AposentadoriaDireitoAdquirido3($idServidorPesquisado);

            # Grava no log a atividade
            $atividade = "Cadastro do servidor - Aposentadoria - Direito Adquirido<br/>{$aposentadoria->get_descricao()}";
            $intra->registraLog($idUsuario, date("Y-m-d H:i:s"), $atividade, null, null, 7, $idServidorPesquisado);

            $grid1 = new Grid();
            $grid1->abreColuna(12);

            # Exibe a regra
            tituloTable($aposentadoria->get_descricao());
            $aposentadoria->exibeAnaliseResumo();

            $grid1->fechaColuna();
            $grid1->abreColuna(12, 12, 8);

            $aposentadoria->exibeAnalise();

            $grid1->fechaColuna();
            $grid1->abreColuna(12, 6, 4);

            $aposentadoria->exibeRemuneração();

            $grid1->fechaColuna();
            $grid1->abreColuna(12, 6, 8);

            $aposentadoria->exibeRegras();

            $grid1->fechaColuna();
            break;

        case "direitoAdquirido4" :
            # Artigo 3º da EC nº 47/2005

            $grid1->abreColuna(9);
            #titulo("Direito Adquirido");
            #br();

            $aposentadoria = new AposentadoriaDireitoAdquirido4($idServidorPesquisado);
            tituloTable($aposentadoria->get_descricao());
            emConstrucao("Em breve esta área estará disponível.");
//            $aposentadoria->exibeAnaliseResumo();
//            $aposentadoria->exibeAnalise();
//
//            $grid2 = new Grid();
//            $grid2->abreColuna(6);
//
//            $aposentadoria->exibeRegras();
//
//            $grid2->fechaColuna();
//            $grid2->abreColuna(6);
//
//            $aposentadoria->exibeRemuneração();
//
//            $grid2->fechaColuna();
//            $grid2->fechaGrid();
            $grid1->fechaColuna();
            break;

        case "direitoAdquirido5" :
            # Outras Regras

            $grid1->abreColuna(9);
            #titulo("Direito Adquirido");
            #br();

            tituloTable("Outras Regras");
            br();

            $grid2 = new Grid();

            for ($i = 1; $i <= 12; $i++) {

                $grid2->abreColuna(6);

                $figura = new Imagem(PASTA_FIGURAS . "aposentadoria direito adquirido{$i}.jpg", null, "100%", "100%");
                $figura->set_id('imgCasa');
                $figura->set_class('imagem');
                $figura->show();

                $grid2->fechaColuna();

                if (epar($i)) {
                    $grid2->abreColuna(12);
                    br();
                    hr("grosso");
                    br();
                    $grid2->fechaColuna();
                }
            }

            $grid2->fechaGrid();

            $grid1->fechaColuna();
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
            Grh::listaDadosServidorRelatorio2($idServidorPesquisado, "Dados do Servidor", "Para Aposentadoria");
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