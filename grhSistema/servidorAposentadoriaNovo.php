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
    $fase = get('fase', 'permanente');
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

    # Botão voltar
    $linkBotaoVoltar = new Button('Voltar', 'servidorMenu.php');
    $linkBotaoVoltar->set_title('Volta para a página anterior');
    $linkBotaoVoltar->set_accessKey('V');
    $menu->add_link($linkBotaoVoltar, "left");
    $menu->show();

    # Exibe os dados do servidor
    get_DadosServidor($idServidorPesquisado);

    ########################################################
    # Menu de Abas
    $tab = new Tab([
        "Dados do Servidor",
        "Tempo Averbado",
        "Vínculos Anteriores",
        "Afastamentos",
        "Regras"
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
        ["Data de Ingresso no Serviço Público", $aposentadoria->get_dtIngresso($idServidorPesquisado)]
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
        ["Privado", $averbacao->get_tempoAverbadoPrivado($idServidorPesquisado)],
        ["Público", $averbacao->get_tempoAverbadoPublico($idServidorPesquisado)],
    ];

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
    $grid1->fechaGrid();

    $tab->fechaConteudo();

    ####################################################
    /*
     *  Tempo Averbado Detalhado
     */

    $tab->abreConteudo();

    $grid1 = new Grid();
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

    $grid1->fechaColuna();
    $grid1->fechaGrid();

    $tab->fechaConteudo();

    ####################################################
    /*
     *  Vinculos Anteriores do servidor
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

    $afast = new ListaAfastamentosServidor($idServidorPesquisado);
    $afast->exibeTabela();

    $tab->fechaConteudo();

    ####################################################

    /*
     * Regras Aposentadoria
     */

    $tab->abreConteudo();

    $grid1 = new Grid();
    $grid1->abreColuna(3);

    # Monta o array do menu
    $arrayMenu = [
        ["link", "Resumão", "resumao"],
        ["titulo", "Regras Permanentes", "permanente"],
        ["link", "Aposentadoria Voluntária", "voluntaria"],
        ["link", "Aposentadoria Compulsória", "compulsoria"],
        ["link", "Incapacidade Permanente", "incapacidade"],
        ["titulo", "Regras de Transição", "transicao"],
        ["link", "Pontos - Integral. e Paridade", "pontosIntegral"],
        ["link", "Pontos - Média", "pontosMedia"],
        ["link", "Pedágio - Integral. e Paridade", "pedagioIntegral"],
        ["link", "Pedágio - Média", "pedagioMedia"],
        ["link", "Pedágio - Redutor de Idade", "pedagioRedutor"],
        ["titulo", "Direito Adquirido", "direitoAdquirido"],
        ["link", "C.F. Art. 40, §1º, III, alínea a", "direitoAdquirido1"],
        ["link", "C.F. Art. 40, §1º, III, alínea b", "direitoAdquirido2"],
        ["link", "Art. 6º DA EC Nº 41/2003", "direitoAdquirido3"],
        ["link", "Artigo 3º da EC nº 47/2005", "direitoAdquirido4"],
        ["link", "Outras regras", "direitoAdquirido5"]
    ];

    # Menu
    $menu = new Menu("menuAposentadoria", array_search($fase, array_column($arrayMenu, 2)));

    foreach ($arrayMenu as $item) {
        $menu->add_item($item[0], $item[1], "?aba=5&fase={$item[2]}");
    }

    # Documentação
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

    switch ($fase) {

        /*
         * Regras Permanentes
         */

        case "resumao" :
            
            $grid1->abreColuna(9);
            titulo("Regras Permanentes");
            br();
            
            $grid2 = new Grid();
            $grid2->abreColuna(6);
            
            $aposentadoria = new AposentadoriaLC195Voluntaria($idServidorPesquisado);
            tituloTable($aposentadoria->get_descricao());
            $aposentadoria->exibeAnaliseResumo();
            
            $grid2->fechaColuna();
            $grid2->abreColuna(6);
            
            $aposentadoria = new AposentadoriaLC195Compulsoria($idServidorPesquisado);
            tituloTable($aposentadoria->get_descricao());
            $aposentadoria->exibeAnaliseResumo();
            
            $grid2->fechaColuna();
            $grid2->fechaGrid();
            
            titulo("Regras de Transição");
            br();
            
            $grid2 = new Grid();
            $grid2->abreColuna(6);

            $aposentadoria = new AposentadoriaTransicaoPontos1($idServidorPesquisado);
            tituloTable($aposentadoria->get_descricao());
            $aposentadoria->exibeAnaliseResumo();

            $grid2->fechaColuna();
            $grid2->abreColuna(6);

            $aposentadoria = new AposentadoriaTransicaoPontos2($idServidorPesquisado);
            tituloTable($aposentadoria->get_descricao());
            $aposentadoria->exibeAnaliseResumo();

            $grid2->fechaColuna();
            $grid2->fechaGrid();            

            $grid1->fechaColuna();
            break;
        /*
         * Regras Permanentes
         */

        case "permanente" :
            $grid1->abreColuna(9);
            titulo("Regras Permanentes");
            br();

            $aposentadoria = new AposentadoriaLC195Voluntaria($idServidorPesquisado);
            tituloTable($aposentadoria->get_descricao());
            $aposentadoria->exibeAnaliseResumo();

            hr("grosso");
            br();

            $aposentadoria = new AposentadoriaLC195Compulsoria($idServidorPesquisado);
            tituloTable($aposentadoria->get_descricao());
            $aposentadoria->exibeAnaliseResumo();
            $grid1->fechaColuna();
            break;

        case "voluntaria" :
            $grid1->abreColuna(9);
            titulo("Regras Permanentes");
            br();

            $aposentadoria = new AposentadoriaLC195Voluntaria($idServidorPesquisado);
            tituloTable($aposentadoria->get_descricao());
            $aposentadoria->exibeAnaliseResumo();
            $aposentadoria->exibeAnalise();

            $grid2 = new Grid();
            $grid2->abreColuna(6);

            $aposentadoria->exibeRegras();

            $grid2->fechaColuna();
            $grid2->abreColuna(6);

            $aposentadoria->exibeRemuneração();

            $grid2->fechaColuna();
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
            break;

        case "compulsoria" :
            $grid1->abreColuna(9);
            titulo("Regras Permanentes");
            br();

            $aposentadoria = new AposentadoriaLC195Compulsoria($idServidorPesquisado);
            tituloTable($aposentadoria->get_descricao());
            $aposentadoria->exibeAnaliseResumo();
            $aposentadoria->exibeAnalise();

            $grid2 = new Grid();
            $grid2->abreColuna(6);

            $aposentadoria->exibeRegras();

            $grid2->fechaColuna();
            $grid2->abreColuna(6);

            $aposentadoria->exibeRemuneração();

            $grid2->fechaColuna();
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
            break;

        case "incapacidade" :
            $grid1->abreColuna(9);
            titulo("Regras Permanentes");
            br();

            tituloTable("Aposentadoria por Incapacidade Permanente");

            $grid2 = new Grid();
            $grid2->abreColuna(6);

            $figura = new Imagem(PASTA_FIGURAS . "lc195incapacidadePermanente1.png", null, "100%", "100%");
            $figura->set_id('imgCasa');
            $figura->set_class('imagem');
            $figura->show();

            $grid2->fechaColuna();
            $grid2->abreColuna(6);

            $figura = new Imagem(PASTA_FIGURAS . "lc195incapacidadePermanente2.png", null, "100%", "100%");
            $figura->set_id('imgCasa');
            $figura->set_class('imagem');
            $figura->show();

            $grid2->fechaColuna();
            $grid2->fechaGrid();

            br();
            hr("grosso");
            br();

            tituloTable("Aposentadoria por Incapacidade Permanente<br/>Acidente de trabalho, doença profissional ou doença de trabalho");

            $grid2 = new Grid();
            $grid2->abreColuna(6);

            $figura = new Imagem(PASTA_FIGURAS . "lc195incapacidadePermanente3.png", null, "100%", "100%");
            $figura->set_id('imgCasa');
            $figura->set_class('imagem');
            $figura->show();

            $grid2->fechaColuna();
            $grid2->abreColuna(6);

            $figura = new Imagem(PASTA_FIGURAS . "lc195incapacidadePermanente4.png", null, "100%", "100%");
            $figura->set_id('imgCasa');
            $figura->set_class('imagem');
            $figura->show();

            $grid2->fechaColuna();
            $grid2->fechaGrid();
            $grid1->fechaColuna();
            break;

        case "transicao" :
            $grid1->abreColuna(9);
            titulo("Regras de Transição");
            br();

            $aposentadoria = new AposentadoriaTransicaoPontos1($idServidorPesquisado);
            tituloTable($aposentadoria->get_descricao());
            $aposentadoria->exibeAnaliseResumo();

            hr("grosso");
            br();

            $aposentadoria = new AposentadoriaTransicaoPontos2($idServidorPesquisado);
            tituloTable($aposentadoria->get_descricao());
            $aposentadoria->exibeAnaliseResumo();

            hr("grosso");
            br();

            $grid1->fechaColuna();
            break;

        case "pontosIntegral" :
            $grid1->abreColuna(9);
            titulo("Regras de Transição");
            br();

            $aposentadoria = new AposentadoriaTransicaoPontos1($idServidorPesquisado);
            tituloTable($aposentadoria->get_descricao());
            $aposentadoria->exibeAnaliseResumo();
            $aposentadoria->exibeAnalise();

            $grid2 = new Grid();
            $grid2->abreColuna(6);

            $aposentadoria->exibeRegras();

            $grid2->fechaColuna();
            $grid2->abreColuna(6);

            $aposentadoria->exibeRemuneração();

            $grid2->fechaColuna();
            $grid2->abreColuna(12);

            $aposentadoria->exibeHistoricoPontuacao();

            tituloTable("Cartilha");
            br();
            $aposentadoria->exibeResumoCartilha(3);
            br();

            $grid2->fechaColuna();
            $grid2->abreColuna(6);

            $aposentadoria->exibeResumoCartilha(1);

            $grid2->fechaColuna();
            $grid2->abreColuna(6);

            $aposentadoria->exibeResumoCartilha(2);
            $grid2->fechaColuna();
            $grid2->fechaGrid();
            $grid1->fechaColuna();
            break;

        case "pontosMedia" :
            $grid1->abreColuna(9);
            titulo("Regras de Transição");
            br();

            $aposentadoria = new AposentadoriaTransicaoPontos2($idServidorPesquisado);
            tituloTable($aposentadoria->get_descricao());
            $aposentadoria->exibeAnaliseResumo();
            $aposentadoria->exibeAnalise();

            $grid2 = new Grid();
            $grid2->abreColuna(6);

            $aposentadoria->exibeRegras();

            $grid2->fechaColuna();
            $grid2->abreColuna(6);

            $aposentadoria->exibeRemuneração();

            $grid2->fechaColuna();
            $grid2->abreColuna(12);

            $aposentadoria->exibeHistoricoPontuacao();
            br();

            tituloTable("Cartilha");
            br();

            $grid2->fechaColuna();
            $grid2->abreColuna(6);

            $aposentadoria->exibeResumoCartilha(1);

            $grid2->fechaColuna();
            $grid2->abreColuna(6);

            $aposentadoria->exibeResumoCartilha(2);
            $grid2->fechaColuna();
            $grid2->fechaGrid();
            $grid1->fechaColuna();
            break;

        case "pedagioIntegral" :
            $grid1->abreColuna(9);
            titulo("Regras de Transição");
            br();

            $aposentadoria = new AposentadoriaTransicaoPedagio1($idServidorPesquisado);
            tituloTable($aposentadoria->get_descricao());
            $aposentadoria->exibeAnaliseResumo();
            $aposentadoria->exibeAnalise();

            $grid2 = new Grid();
            $grid2->abreColuna(6);

            $aposentadoria->exibeRegras();

            $grid2->fechaColuna();
            $grid2->abreColuna(6);

            $aposentadoria->exibeRemuneração();

            $grid2->fechaColuna();
            $grid2->abreColuna(12);

            tituloTable("Cartilha");
            br();

            $aposentadoria->exibeResumoCartilha(3);
            br(2);

            $grid2->fechaColuna();
            $grid2->abreColuna(6);

            $aposentadoria->exibeResumoCartilha(1);

            $grid2->fechaColuna();
            $grid2->abreColuna(6);

            $aposentadoria->exibeResumoCartilha(2);
            $grid2->fechaColuna();
            $grid2->fechaGrid();
            $grid1->fechaColuna();
            break;

        case "pedagioMedia" :
            $grid1->abreColuna(9);
            titulo("Regras de Transição");
            br();
            tituloTable("Regras do Pedágio");
            br();

            tituloTable("Por Idade e Tempo de Contribuição<br/>Média da Lei Federal nº 10.887/2004");
            emConstrucao("Em breve esta área estará disponível.");
            $grid1->fechaColuna();
            break;

        case "pedagioRedutor" :
            $grid1->abreColuna(9);
            titulo("Regras de Transição");
            br();
            tituloTable("Regras do Pedágio");
            br();

            tituloTable("Com redutor de idade<br/>Integralidade e Paridade");
            emConstrucao("Em breve esta área estará disponível.");
            $grid1->fechaColuna();
            break;

        case "direitoAdquirido" :
            $grid1->abreColuna(9);
            titulo("Direito Adquirido");
            br();

            $grid1->fechaColuna();
            break;

        case "direitoAdquirido1" :
            # C.F. Art. 40, §1º, III, alínea a

            $grid1->abreColuna(9);
            titulo("Direito Adquirido");
            br();

            $aposentadoria = new AposentadoriaDiretoAdquirido1($idServidorPesquisado);
            tituloTable($aposentadoria->get_descricao());
            $aposentadoria->exibeAnaliseResumo();
            $aposentadoria->exibeAnalise();

            $grid2 = new Grid();
            $grid2->abreColuna(6);

            $aposentadoria->exibeRegras();

            $grid2->fechaColuna();
            $grid2->abreColuna(6);

            $aposentadoria->exibeRemuneração();

            $grid2->fechaColuna();
            $grid2->fechaGrid();
            $grid1->fechaColuna();
            break;

        case "direitoAdquirido2" :
            # C.F. Art. 40, §1º, III, alínea b

            $grid1->abreColuna(9);
            titulo("Direito Adquirido");
            br();

            $aposentadoria = new AposentadoriaDiretoAdquirido2($idServidorPesquisado);
            tituloTable($aposentadoria->get_descricao());
            $aposentadoria->exibeAnaliseResumo();
            $aposentadoria->exibeAnalise();

            $grid2 = new Grid();
            $grid2->abreColuna(6);

            $aposentadoria->exibeRegras();

            $grid2->fechaColuna();
            $grid2->abreColuna(6);

            $aposentadoria->exibeRemuneração();

            $grid2->fechaColuna();
            $grid2->fechaGrid();
            $grid1->fechaColuna();
            break;

        case "direitoAdquirido3" :
            # Art. 6º DA EC Nº 41/2003

            $grid1->abreColuna(9);
            titulo("Direito Adquirido");
            br();

            $aposentadoria = new AposentadoriaDiretoAdquirido3($idServidorPesquisado);
            tituloTable($aposentadoria->get_descricao());
            $aposentadoria->exibeAnaliseResumo();
            $aposentadoria->exibeAnalise();

            $grid2 = new Grid();
            $grid2->abreColuna(6);

            $aposentadoria->exibeRegras();

            $grid2->fechaColuna();
            $grid2->abreColuna(6);

            $aposentadoria->exibeRemuneração();

            $grid2->fechaColuna();
            $grid2->fechaGrid();
            $grid1->fechaColuna();
            break;

        case "direitoAdquirido4" :
            # Artigo 3º da EC nº 47/2005

            $grid1->abreColuna(9);
            titulo("Direito Adquirido");
            br();

            $aposentadoria = new AposentadoriaDiretoAdquirido4($idServidorPesquisado);
            tituloTable($aposentadoria->get_descricao());
            $aposentadoria->exibeAnaliseResumo();
            $aposentadoria->exibeAnalise();

            $grid2 = new Grid();
            $grid2->abreColuna(6);

            $aposentadoria->exibeRegras();

            $grid2->fechaColuna();
            $grid2->abreColuna(6);

            $aposentadoria->exibeRemuneração();

            $grid2->fechaColuna();
            $grid2->fechaGrid();
            $grid1->fechaColuna();
            break;

        case "direitoAdquirido5" :
            # Outras Regras

            $grid1->abreColuna(9);
            titulo("Direito Adquirido");
            br();
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

    $grid1->fechaGrid();
    $tab->fechaConteudo();

    ########################################################


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

########################################################

    $tab->show();
    br();

    $grid->fechaColuna();
    $grid->fechaGrid();

    $page->terminaPagina();
} else {
    loadPage("../../areaServidor/sistema/login.php");
}    