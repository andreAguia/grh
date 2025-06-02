<?php

/**
 * Sistema GRH
 * 
 * Relatório
 *   
 * By Alat
 */
# Servidor logado 
$idUsuario = null;

# Configuração
include ("../grhSistema/_config.php");

# Permissão de Acesso
$acesso = Verifica::acesso($idUsuario, [1, 2, 12]);

if ($acesso) {

    # Conecta ao Banco de Dados
    $servidor = new Pessoal();

    # Começa uma nova página
    $page = new Page();
    $page->iniciaPagina();

    # Pega os parâmetros dos relatórios
    $lotacao = get_session('parametroLotacao');
    $parametroAfastamento = get_session('parametroAfastamento');
    $ci = post('ci');
    $chefia = post('chefia');
    $textoCi = post('textoCi');

    # Trata Lotação
    if ($lotacao == "*") {
        $nomeLotacao == null;
    } else {
        $nomeLotacao = $servidor->get_nomeLotacao2($lotacao);
    }

    # Grava o novo texto nas configurações do sispatri
    $sispatri = new Sispatri();
    $sispatri->set_textoCi($textoCi);

    # Parâmetro da função
    $parametro = array($nomeLotacao, $ci, $chefia, $textoCi, $lotacao);

    $grid = new Grid();
    $grid->abreColuna(1);
    $grid->fechaColuna();
    $grid->abreColuna(10);

    ####

    function exibeTextoSispatri($parametro) {

        # Pega os parametros
        $nomeLotacao = $parametro[0];
        $ci = $parametro[1];
        $chefia = $parametro[2];
        $texto = $parametro[3];
        $lotacao = $parametro[4];

        if (!is_null($nomeLotacao)) {

            $servidor = new Pessoal();
            $intra = new Intra();

            $grid = new Grid();
            $grid->abreColuna(5);
            p("CI GRH/DGA/UENF n° $ci/" . date('Y'), "left");
            $grid->fechaColuna();
            $grid->abreColuna(7);
            p("Campos dos Goytacazes, " . dataExtenso(date('d/m/Y')), "right");
            $grid->fechaColuna();
            $grid->fechaGrid();

            $gerenteGrh = $servidor->get_Nome($servidor->get_gerente(66));

            p("<b>De: Gerência de Recursos Humanos - GRH/UENF</b>", "left");

            if (is_numeric($lotacao)) {
                p("Para: {$chefia}<br/>{$nomeLotacao}", "left");
            } else {
                p("Para: {$chefia} - {$nomeLotacao}", "left");
            }

            p("Prezado(a) Senhor(a)", "left");

            echo $texto;
        }
    }

    ####

    function exibeTextoFinal() {

        $grid = new Grid();
        $grid->abreColuna(3);
        br();
        p("Atenciosamente,", "left");
        $grid->fechaColuna();
        $grid->abreColuna(6);

        # assinatura -> Retirado a pedido de gustavo pois vai assinar digitalmente pelo sei
        #$figura = new Imagem(PASTA_FIGURAS . 'assinatura.png', 'Assinatura do Gerente', 120, 140);
        #$figura->show();
//        $servidor = new Pessoal();
//        $gerenteGrh = $servidor->get_Nome($servidor->get_gerente(66));
//        $idGerente = $servidor->get_idFuncional($servidor->get_gerente(66));
//        p("$gerenteGrh<br/>Gerente de Recursos Humanos<br/>Id Funcional: $idGerente", "center", "f12");




        $grid->fechaColuna();
        $grid->abreColuna(3);
        $grid->fechaColuna();
        $grid->fechaGrid();
        p("Gerência de Recursos Humanos", "left");

        p("_______________________________________________________________________________<br/>Av. Alberto Lamego 2000 - Parque California - Campos dos Goytacazes/RJ - 28013-602<br/>Tel.: (22) 2739-7064 - correio eletronico: grh@uenf.br", "center", "f12");
    }

    ######
    # Inicia a Classe
    $sispatri = new Sispatri();
    $sispatri->set_lotacao($lotacao);

    # Exibe os servidores ativos que Não entregaram o sispatri
    if ($parametroAfastamento == "Todos") {
        $result = $sispatri->get_servidoresNaoEntregaramAtivos();
    }

    if ($parametroAfastamento == "Férias") {
        $result = $sispatri->get_servidoresNaoEntregaramAtivosFerias();
    }

    if ($parametroAfastamento == "Licença Prêmio") {
        $result = $sispatri->get_servidoresNaoEntregaramAtivosLicPremio();
    }

    if ($parametroAfastamento == "Licença Médica") {
        $result = $sispatri->get_servidoresNaoEntregaramAtivosLicMedica();
    }

    #$result = $sispatri->get_servidoresRelatorio();
    #$result = $sispatri->get_servidoresNaoEntregaramAtivos();

    $relatorio = new Relatorio();
    $relatorio->set_funcaoAntesTitulo('exibeTextoSispatri');
    $relatorio->set_funcaoAntesTituloParametro($parametro);

    $relatorio->set_funcaoFinalRelatorio('exibeTextoFinal');

    $relatorio->set_label(['idFuncional', 'Nome', 'Lotação']);
    $relatorio->set_width([20, 40, 40]);
    $relatorio->set_align(["center", "left", "left"]);
    $relatorio->set_subTotal(false);
    $relatorio->set_totalRegistro(false);
    $relatorio->set_dataImpressao(false);
    $relatorio->set_linhaNomeColuna(false);
    $relatorio->set_conteudo($result);
    $relatorio->set_classe([null, "pessoal"]);
    $relatorio->set_metodo([null, "get_nome"]);
    #$relatorio->set_numGrupo(2);
    $relatorio->show();

    $grid->fechaColuna();
    $grid->abreColuna(1);
    $grid->fechaColuna();
    $grid->fechaGrid();

    $page->terminaPagina();
}