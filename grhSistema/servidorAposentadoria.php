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
$acesso = Verifica::acesso($idUsuario, 2);

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

    # Começa uma nova página
    $page = new Page();
    $page->set_bodyOnLoad('$(document).foundation();');
    $page->iniciaPagina();

    # Cabeçalho da Página
    AreaServidor::cabecalho();

##############################################################################################################################################
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

    $tab = new Tab(["Dados do Servidor", "Regras Permanentes", "Regras de Transição"]);

    ###

    $tab->abreConteudo();

    $grid1 = new Grid();
    $grid1->abreColuna(12, 4);

    /*
     *  Dados do Servidor
     */
    $array = [
        ["Idade", $pessoal->get_idade($idServidorPesquisado)],
        ["Cargo Efetivo - Uenf", $aposentadoria->get_tempoServicoUenf($idServidorPesquisado) . " dias"],
        ["Data de Ingresso", $aposentadoria->get_dtIngresso($idServidorPesquisado)],
    ];

    # Tabela
    $tabela = new Tabela();
    $tabela->set_titulo("Dados do Servidor");
    $tabela->set_conteudo($array);
    $tabela->set_label(array("Descrição", "Valor"));
    $tabela->set_width(array(60, 40));
    $tabela->set_align(array("left", "center"));
    $tabela->set_totalRegistro(false);
    $tabela->show();

    $grid1->fechaColuna();
    $grid1->abreColuna(12, 4);

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
    $tabela->set_label(array("Descrição", "Dias"));
    $tabela->set_width(array(60, 40));
    $tabela->set_align(array("left", "center"));
    $tabela->set_totalRegistro(false);
    $tabela->set_colunaSomatorio(1);
    $tabela->show();

    $grid1->fechaColuna();
    $grid1->abreColuna(12, 4);

    /*
     *  Tempo Público
     */
    $array = [
        ["Total (averbado + Uenf)", $aposentadoria->get_tempoServicoUenf($idServidorPesquisado) + $averbacao->get_tempoAverbadoPublico($idServidorPesquisado)],
        ["Ininterrupto", $aposentadoria->get_tempoPublicoIninterrupto($idServidorPesquisado)],
    ];

    # Tabela
    $tabela = new Tabela();
    $tabela->set_titulo("Tempo Público");
    $tabela->set_conteudo($array);
    $tabela->set_label(array("Descrição", "Dias"));
    $tabela->set_width(array(60, 40));
    $tabela->set_align(array("left", "center"));
    $tabela->set_totalRegistro(false);
    $tabela->show();

    $grid1->fechaColuna();
    $grid1->abreColuna(12);

    /*
     *  Resumo do Tempo Averbado
     */
    $select = 'SELECT dtInicial,
                      dtFinal,
                      dias,
                      idAverbacao,
                      empresa,
                      CASE empresaTipo
                         WHEN 1 THEN "Pública"
                         WHEN 2 THEN "Privada"
                      END,
                      CASE regime
                         WHEN 1 THEN "Celetista"
                         WHEN 2 THEN "Estatutário"
                         WHEN 3 THEN "Próprio"
                      END,
                      cargo,
                      dtPublicacao,
                      processo
                 FROM tbaverbacao
                WHERE idServidor = ' . $idServidorPesquisado . '
             ORDER BY dtInicial desc';

    $result = $pessoal->select($select);

    # Tabela
    $tabela = new Tabela();
    $tabela->set_titulo("Tempo Averbado - Detalhado");
    $tabela->set_conteudo($result);
    $tabela->set_label(["Data Inicial", "Data Final", "Dias Digitados", "Dias Calculados", "Empresa", "Tipo", "Regime", "Cargo", "Publicação", "Processo"]);
    #$tabela->set_width(array(60, 40));
    $tabela->set_align(["center", "center", "center", "center", "left"]);
    $tabela->set_funcao(["date_to_php", "date_to_php", null, null, null, null, null, null, "date_to_php"]);

    $tabela->set_classe(array(null, null, null, "Averbacao"));
    $tabela->set_metodo(array(null, null, null, "getNumDias"));

    $tabela->set_totalRegistro(false);
    $tabela->set_colunaSomatorio([2, 3]);
    $tabela->show();

    /*
     *  Vinculos do servidor
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

    ###

    $tab->abreConteudo();

    $grid1 = new Grid();
    $grid1->abreColuna(12);

    $previsao1 = new AposentadoriaPermanente1($idServidorPesquisado);
    $previsao1->exibeAnalise();

    $previsao2 = new AposentadoriaPermanente2($idServidorPesquisado);
    $previsao2->exibeAnalise();

    $previsao3 = new AposentadoriaCompulsoria($idServidorPesquisado);
    $previsao3->exibeAnalise();

    $grid1->fechaColuna();
    $grid1->fechaGrid();

    $tab->fechaConteudo();

    ###

    $tab->abreConteudo();

    $grid1 = new Grid();
    $grid1->abreColuna(12);

    $previsao4 = new AposentadoriaTransicao1($idServidorPesquisado);
    $previsao4->exibeAnalise();

    $previsao5 = new AposentadoriaTransicao2($idServidorPesquisado);
    $previsao5->exibeAnalise();

    $grid1->fechaColuna();
    $grid1->fechaGrid();

    $tab->fechaConteudo();

    ###

    $tab->show();

    $grid->fechaColuna();
    $grid->fechaGrid();
    br();

    $page->terminaPagina();
} else {
    loadPage("../../areaServidor/sistema/login.php");
}