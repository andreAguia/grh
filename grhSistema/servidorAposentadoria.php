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

    # Começa uma nova página
    $page = new Page();
    $page->set_bodyOnLoad('$(document).foundation();');
    $page->iniciaPagina();

    # Cabeçalho da Página
    AreaServidor::cabecalho();

####################################################
    # Limita o tamanho da tela
    $grid = new Grid();
    $grid->abreColuna(12);    
    
    # Verifica a data de saída
    $dtSaida = $pessoal->get_dtSaida($idServidorPesquisado);      # Data de Saída de servidor inativo
    $dtHoje = date("Y-m-d");                                      # Data de hoje
    $dtFinal = null;

    # Analisa a data
    if (!vazio($dtSaida)) {           // Se tem saída é a saída
        $dtFinal = date_to_bd($dtSaida);
        $disabled = true;
        $autofocus = false;
    } else {                          // Não tem saída então é hoje
        $dtFinal = $dtHoje;
    }

    # Finalmente define o valor
    $parametro = $dtFinal;

    # Cria um menu
    $menu = new MenuBar();

    # Botão voltar
    $linkBotaoVoltar = new Button('Voltar', 'servidorMenu.php');
    $linkBotaoVoltar->set_title('Volta para a página anterior');
    $linkBotaoVoltar->set_accessKey('V');
    $menu->add_link($linkBotaoVoltar, "left");

    # Regras
    $botaoVoltar = new Link("Regras", "areaAposentadoria.php?fase=regras");
    $botaoVoltar->set_class('button');
    $botaoVoltar->set_target('_blank');
    $botaoVoltar->set_title('Regras de aposentadoria');
    #$menu->add_link($botaoVoltar, "right");

    $menu->show();

    # Exibe os dados do servidor
    get_DadosServidor($idServidorPesquisado);

    tituloTable("Aposentadoria");
    br();

    ###

    $tab = new Tab(["Dados do Servidor", "Tempo Averbado", "Vínculos Anteriores", "Afastamentos", "Regras Permanentes", "Regras de Transição"]);

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

    # Tabela
    $tabela = new Tabela();
    $tabela->set_titulo("Tempo Geral");
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

    callout("Tempo Público Ininterrupto: " . $aposentadoria->get_tempoPublicoIninterrupto($idServidorPesquisado) . " dias","secondary");

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
    $tabela->set_metodo([null, null, null, "getNumDias", "getDiasAnterior151298"]);
    
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
     *  Regras Permanentes
     */

    $tab->abreConteudo();

    $grid1 = new Grid();
    $grid1->abreColuna(12);

    $previsao1 = new AposentadoriaPermanente1();
    $previsao1->exibeAnalise($idServidorPesquisado);
    hr();

    $previsao2 = new AposentadoriaPermanente2();
    $previsao2->exibeAnalise($idServidorPesquisado);
    hr();

    $previsao3 = new AposentadoriaCompulsoria();
    $previsao3->exibeAnalise($idServidorPesquisado);

    $grid1->fechaColuna();
    $grid1->fechaGrid();

    $tab->fechaConteudo();

    ####################################################
    /*
     *  Regras de Transição
     */

    $tab->abreConteudo();

    $grid1 = new Grid();
    $grid1->abreColuna(12);

    $previsao4 = new AposentadoriaTransicao1();
    $previsao4->exibeAnalise($idServidorPesquisado);
    hr();

    $previsao5 = new AposentadoriaTransicao2();
    $previsao5->exibeAnalise($idServidorPesquisado);

    $grid1->fechaColuna();
    $grid1->fechaGrid();

    $tab->fechaConteudo();

    ###

    $tab->show();
    br();

    $grid->fechaColuna();
    $grid->fechaGrid();

    $page->terminaPagina();
} else {
    loadPage("../../areaServidor/sistema/login.php");
}