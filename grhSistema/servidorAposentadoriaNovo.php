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

    # Começa uma nova página
    $page = new Page();
    $page->iniciaPagina();

    # Cabeçalho da Página
    AreaServidor::cabecalho();

    ########################################################
    # Limita o tamanho da tela
    $grid = new Grid();
    $grid->abreColuna(12);

    # Cria um menu
    $menu = new MenuBar();

    # Verifica a rotina e define o link
    if ($fase == "tabs") {
        $linkvoltar = 'servidorMenu.php';
    } elseif ($fase == "voluntaria" OR $fase == "compulsoria" OR $fase == "incapacidade1" OR $fase == "incapacidade2") {
        $linkvoltar = '?fase=tabs&aba=3';
    } elseif ($fase == "pontosIntegral" OR $fase == "pontosMedia" OR $fase == "pedagioIntegral" OR $fase == "pedagioMedia" OR $fase == "pedagioRedutor") {
        $linkvoltar = '?fase=tabs&aba=4';
    } elseif ($fase == "direitoAdquirido1" OR $fase == "direitoAdquirido2" OR $fase == "direitoAdquirido3" OR $fase == "direitoAdquirido4") {
        $linkvoltar = '?fase=tabs&aba=5';
    }

    # Botão voltar    
    $linkBotaoVoltar = new Button('Voltar', $linkvoltar);
    $linkBotaoVoltar->set_title('Volta para a página anterior');
    $linkBotaoVoltar->set_accessKey('V');
    $menu->add_link($linkBotaoVoltar, "left");
    $menu->show();

    # Exibe os dados do servidor
    get_DadosServidor($idServidorPesquisado);

    calloutAlert("ATENÇÃO!!<br/>Esta rotina AINDA está em teste!! Os cálculos podem não estar corretos!!<br/>Use-a por sua própria conta e risco!");

    switch ($fase) {
        case "tabs" :

            # Menu de Abas
            $tab = new Tab([
                "Dados do Servidor",
                "Afastamentos",
                "Regras Permanentes",
                "Regras de Transição",
                "Direito Adquirido",
                "Documentação"
                    ], $aba);

            ####################################################

            /*
             *  Dados do Servidor
             */

            $tab->abreConteudo();

            $grid1 = new Grid();
            $grid1->abreColuna(12, 6, 3);

            $array = [
                ["Idade", $pessoal->get_idade($idServidorPesquisado)],
                ["Data de Nascimento", $pessoal->get_dataNascimento($idServidorPesquisado)],
                ["Data de Admissão", $pessoal->get_dtadmissao($idServidorPesquisado)],
                ["Data de Ingresso<br/><p id='psubtitulo'>no Serviço Público</p>", $aposentadoria->get_dtIngresso($idServidorPesquisado)]
            ];

            # Tabela
            $tabela = new Tabela();
            $tabela->set_titulo("Dados do Servidor");
            $tabela->set_conteudo($array);
            $tabela->set_label(["Descrição", "Valor"]);
            $tabela->set_width([60, 40]);
            $tabela->set_align(["left", "center"]);
            $tabela->set_totalRegistro(false);
            $tabela->show();

            $grid1->fechaColuna();
            $grid1->abreColuna(12, 6, 3);

            /*
             *  Tempo Geral
             */

            $array = [
                ["Cargo Efetivo - Uenf", $aposentadoria->get_tempoServicoUenf($idServidorPesquisado)],
                ["Tempo Averbado", $averbacao->get_tempoAverbadoTotal($idServidorPesquisado)]
            ];

            # Tabela Tempo Geral
            $tabela = new Tabela();
            $tabela->set_titulo("Tempo Geral");
            $tabela->set_conteudo($array);
            $tabela->set_label(["Descrição", "Dias"]);
            $tabela->set_width([60, 40]);
            $tabela->set_align(["left", "center"]);
            $tabela->set_totalRegistro(false);
            $tabela->set_colunaSomatorio(1);
            $tabela->show();

            $array = [
                ["Cargo Efetivo - Uenf", $aposentadoria->get_tempoServicoUenfAntes31_12_21($idServidorPesquisado)],
                ["Tempo Averbado", $averbacao->getTempoAverbadoAntes31_12_21($idServidorPesquisado)]
            ];

            # Tabela Tempo até 31/12/2021
            $tabela = new Tabela();
            $tabela->set_titulo("Tempo até 31/12/2021");
            $tabela->set_conteudo($array);
            $tabela->set_label(["Descrição", "Dias"]);
            $tabela->set_width([60, 40]);
            $tabela->set_align(["left", "center"]);
            $tabela->set_totalRegistro(false);
            $tabela->set_colunaSomatorio(1);
            $tabela->show();

            $grid1->fechaColuna();
            $grid1->abreColuna(12, 6, 3);

            /*
             *  Tempo Averbado
             */

            $array = [
                ["Uenf Celetista<br/><p id='psubtitulo'>Antes de 09/09/2003</p>", $aposentadoria->get_tempoServicoUenfCeletista($idServidorPesquisado)],
                ["Uenf Estatutária<br/><p id='psubtitulo'>Depois de 09/09/2003</p>", $aposentadoria->get_tempoServicoUenfEstatutario($idServidorPesquisado)],
            ];

            # Tabela
            $tabela = new Tabela();
            $tabela->set_titulo("Tempo Uenf");
            $tabela->set_conteudo($array);
            $tabela->set_label(["Descrição", "Dias"]);
            $tabela->set_width([60, 40]);
            $tabela->set_align(["left", "center"]);
            $tabela->set_totalRegistro(false);
            $tabela->set_colunaSomatorio(1);
            $tabela->show();

            /*
             *  Tempo Averbado
             */
            $array = [
                ["Privado", $averbacao->get_tempoAverbadoPrivado($idServidorPesquisado)]];

            foreach ($regime as $item) {
                if ($averbacao->get_tempoAverbadoPublicoRegime($idServidorPesquisado, $item[0]) > 0) {
                    array_unshift($array, array("Público<br/><p id='psubtitulo'>Regime {$item[1]}</p>", $averbacao->get_tempoAverbadoPublicoRegime($idServidorPesquisado, $item[0])));
                }
            }

            # Tabela
            $tabela = new Tabela();
            $tabela->set_titulo("Tempo Averbado");
            $tabela->set_conteudo($array);
            $tabela->set_label(["Descrição", "Dias"]);
            $tabela->set_width([60, 40]);
            $tabela->set_align(["left", "center"]);
            $tabela->set_totalRegistro(false);
            $tabela->set_colunaSomatorio(1);
            $tabela->show();

            $grid1->fechaColuna();
            $grid1->abreColuna(12, 6, 3);

            /*
             *  Tempo Público
             */
            $array = [
                ["Uenf", $aposentadoria->get_tempoServicoUenf($idServidorPesquisado)],
                ["Averbado Público", $averbacao->get_tempoAverbadoPublico($idServidorPesquisado)]
            ];

            # Tabela
            $tabela = new Tabela();
            $tabela->set_titulo("Tempo Público");
            $tabela->set_conteudo($array);
            $tabela->set_label(["Descrição", "Dias"]);
            $tabela->set_width([60, 40]);
            $tabela->set_align(["left", "center"]);
            $tabela->set_totalRegistro(false);
            $tabela->set_colunaSomatorio(1);
            $tabela->show();

            $array = [
                ["Tempo Ininterrupto", $aposentadoria->get_tempoPublicoIninterrupto($idServidorPesquisado)]
            ];

            $tabela = new Tabela();
            #$tabela->set_titulo("Tempo Público");
            $tabela->set_conteudo($array);
            $tabela->set_label(["", ""]);
            $tabela->set_width([60, 40]);
            $tabela->set_align(["left", "center"]);
            $tabela->set_totalRegistro(false);
            $tabela->show();

            $grid1->fechaColuna();

            /*
             *  Tempo Averbado Detalhado
             */

            $grid1->abreColuna(12);

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

            $tabela->set_formatacaoCondicional(array(
                array('coluna' => 4,
                    'valor' => 0,
                    'operador' => '<>',
                    'id' => 'diasAntes'),
                array('coluna' => 4,
                    'valor' => 0,
                    'operador' => '=',
                    'id' => 'normal')
            ));

            $tabela->set_totalRegistro(false);
            $tabela->set_colunaSomatorio([2, 3]);
            $tabela->show();

            /*
             *  Vinculos Anteriores do servidor
             */


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

            $afast = new ListaAfastamentosServidor($idServidorPesquisado);
            $afast->exibeTabela();

            $grid1->fechaColuna();
            $grid1->fechaGrid();

            $tab->fechaConteudo();

            ####################################################

            /*
             * Resumo das Regras Permanentes
             */

            $tab->abreConteudo();

            $grid1 = new Grid();
            $grid1->abreColuna(12);

            tituloTable("Regras Permanentes");
            br();

            $grid2 = new Grid();
            $grid2->abreColuna(12, 12, 6);

            $aposentadoria = new AposentadoriaLC195Voluntaria($idServidorPesquisado);
            linkTituloTable($aposentadoria->get_descricao(), null, "?fase=voluntaria", "(clique no texto acima para maiores detalhes)");
            $aposentadoria->exibeAnaliseResumo();
            #$aposentadoria->exibeAnalise();

            $grid2->fechaColuna();
            $grid2->abreColuna(12, 12, 6);

            $aposentadoria = new AposentadoriaLC195Compulsoria($idServidorPesquisado);
            linkTituloTable($aposentadoria->get_descricao(), null, "?fase=compulsoria", "(clique no texto acima para maiores detalhes)");
            $aposentadoria->exibeAnaliseResumo();
            #$aposentadoria->exibeAnalise();

            $grid2->fechaColuna();
            $grid2->abreColuna(12, 12, 6);

            $texto = "Aposentadoria por Incapacidade Permanente<br/>Art. 2º, inciso I, combinado com o art. 7º, §4º da Lei Complementar nº 195/2021";
            linkTituloTable($texto, null, "?fase=incapacidade1", "(clique no texto acima para maiores detalhes)");

            $painel = new Callout("warning");
            $painel->abre();

            p("Esta Regra não cabe previsão", "center");

            $painel->fecha();

            $grid2->fechaColuna();
            $grid2->abreColuna(12, 12, 6);

            $texto = "Aposentadoria por Incapacidade Permanente<br/>Acidente de trabalho, doença profissional ou doença de trabalho<br/>Art. 2º, inciso I, combinado com o art. 7º, §5º da Lei Complementar nº 195/2021.";
            linkTituloTable($texto, null, "?fase=incapacidade2", "(clique no texto acima para maiores detalhes)");

            $painel = new Callout("warning");
            $painel->abre();

            p("Esta Regra não cabe previsão", "center");

            $painel->fecha();

            $grid2->fechaColuna();
            $grid2->fechaGrid();

            $grid1->fechaColuna();
            $grid1->fechaGrid();

            $tab->fechaConteudo();

            ####################################################

            /*
             * Resumo das Regras de Transição
             */

            $tab->abreConteudo();

            $grid1 = new Grid();
            $grid1->abreColuna(12);

            tituloTable("Regras de Transição");
            br();

            $grid2 = new Grid();
            $grid2->abreColuna(12, 12, 6);

            $aposentadoria = new AposentadoriaTransicaoPontos1($idServidorPesquisado);
            linkTituloTable($aposentadoria->get_descricao(), null, "?fase=pontosIntegral", "(clique no texto acima para maiores detalhes)");
            $aposentadoria->exibeAnaliseResumo();
            #$aposentadoria->exibeAnalise();

            $grid2->fechaColuna();
            $grid2->abreColuna(12, 12, 6);

            $aposentadoria = new AposentadoriaTransicaoPontos2($idServidorPesquisado);
            linkTituloTable($aposentadoria->get_descricao(), null, "?fase=pontosMedia", "(clique no texto acima para maiores detalhes)");
            $aposentadoria->exibeAnaliseResumo();
            #$aposentadoria->exibeAnalise();

            $grid2->fechaColuna();
            $grid2->abreColuna(12, 12, 6);

            $aposentadoria = new AposentadoriaTransicaoPedagio1($idServidorPesquisado);
            linkTituloTable($aposentadoria->get_descricao(), null, "?fase=pedagioIntegral", "(clique no texto acima para maiores detalhes)");
            $aposentadoria->exibeAnaliseResumo();
            #$aposentadoria->exibeAnalise();

            $grid2->fechaColuna();
            $grid2->fechaGrid();

            $grid1->fechaColuna();
            $grid1->fechaGrid();

            $tab->fechaConteudo();

            ####################################################

            /*
             * Resumo das Regras de Direito Adquirido
             */

            $tab->abreConteudo();

            $grid1 = new Grid();
            $grid1->abreColuna(12);

            tituloTable("Direito Adquirido");
            br();

            $grid2 = new Grid();
            $grid2->abreColuna(12, 12, 6);

            $aposentadoria = new AposentadoriaDiretoAdquirido1($idServidorPesquisado);
            linkTituloTable($aposentadoria->get_descricao(), null, "?aba=8&fase=direitoAdquirido1", "(clique no texto acima para maiores detalhes)");
            $aposentadoria->exibeAnaliseResumo();
            #$aposentadoria->exibeAnalise();

            $grid2->fechaColuna();
            $grid2->abreColuna(6);

            $aposentadoria = new AposentadoriaDiretoAdquirido2($idServidorPesquisado);
            linkTituloTable($aposentadoria->get_descricao(), null, "?aba=8&fase=direitoAdquirido2", "(clique no texto acima para maiores detalhes)");
            $aposentadoria->exibeAnaliseResumo();
            #$aposentadoria->exibeAnalise();

            $grid2->fechaColuna();
            $grid2->abreColuna(12, 12, 6);

            $aposentadoria = new AposentadoriaDiretoAdquirido3($idServidorPesquisado);
            linkTituloTable($aposentadoria->get_descricao(), null, "?aba=8&fase=direitoAdquirido3", "(clique no texto acima para maiores detalhes)");
            $aposentadoria->exibeAnaliseResumo();
            #$aposentadoria->exibeAnalise();

            $grid2->fechaColuna();
//            $grid2->abreColuna(6);
//
//            $aposentadoria = new AposentadoriaDiretoAdquirido4($idServidorPesquisado);
//            tituloTable($aposentadoria->get_descricao());
//            $aposentadoria->exibeAnaliseResumo();
//            
//            $grid2->fechaColuna();
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
            tituloTable($aposentadoria->get_descricao());
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
            tituloTable($aposentadoria->get_descricao());
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
            tituloTable($aposentadoria->get_descricao());
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

        case "pontosMedia" :

            # Inicia a classe
            $aposentadoria = new AposentadoriaTransicaoPontos2($idServidorPesquisado);

            # Grava no log a atividade
            $atividade = "Cadastro do servidor - Aposentadoria - Regras de Transição<br/>{$aposentadoria->get_descricao()}";
            $intra->registraLog($idUsuario, date("Y-m-d H:i:s"), $atividade, null, null, 7, $idServidorPesquisado);

            $grid1 = new Grid();
            $grid1->abreColuna(12);

            # Exibe a regra
            tituloTable($aposentadoria->get_descricao());
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

        case "pedagioIntegral" :
            # Inicia a classe
            $aposentadoria = new AposentadoriaTransicaoPedagio1($idServidorPesquisado);

            # Grava no log a atividade
            $atividade = "Cadastro do servidor - Aposentadoria - Regras de Transição<br/>{$aposentadoria->get_descricao()}";
            $intra->registraLog($idUsuario, date("Y-m-d H:i:s"), $atividade, null, null, 7, $idServidorPesquisado);

            $grid1 = new Grid();
            $grid1->abreColuna(12);

            # Exibe a regra
            tituloTable($aposentadoria->get_descricao());
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

        case "pedagioMedia" :
            $grid1->abreColuna(9);
            tituloTable("Regra do Pedágio<br/>Por Idade e Tempo de Contribuição<br/>Média da Lei Federal nº 10.887/2004");
            emConstrucao("Em breve esta área estará disponível.");
            $grid1->fechaColuna();
            break;

        case "pedagioRedutor" :
            $grid1->abreColuna(9);

            tituloTable("Regra do Pedágio<br/>Com redutor de idade<br/>Integralidade e Paridade");
            emConstrucao("Em breve esta área estará disponível.");
            $grid1->fechaColuna();
            break;

        ########################################################

        /*
         * Direito Adquirido
         */

        case "direitoAdquirido1" :
            # C.F. Art. 40, §1º, III, alínea a
            # Inicia a classe
            $aposentadoria = new AposentadoriaDiretoAdquirido1($idServidorPesquisado);

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

        case "direitoAdquirido2" :
            # C.F. Art. 40, §1º, III, alínea b
            # Inicia a classe
            $aposentadoria = new AposentadoriaDiretoAdquirido2($idServidorPesquisado);

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

        case "direitoAdquirido3" :
            # Art. 6º DA EC Nº 41/2003
            # Inicia a classe
            $aposentadoria = new AposentadoriaDiretoAdquirido3($idServidorPesquisado);

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

            $aposentadoria = new AposentadoriaDiretoAdquirido4($idServidorPesquisado);
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
    }

    $grid->fechaColuna();
    $grid->fechaGrid();

    $page->terminaPagina();
} else {
    loadPage("../../areaServidor/sistema/login.php");
}    