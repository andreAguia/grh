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
$acesso = Verifica::acesso($idUsuario, 2);

if ($acesso) {

    # Conecta ao Banco de Dados
    $servidor = new Pessoal();

    # Começa uma nova página
    $page = new Page();
    $page->iniciaPagina();

    # Pega os parâmetros dos relatórios
    $lotacao = get_session('parametroLotacao');
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
    $parametro = array($nomeLotacao, $ci, $chefia, $textoCi);

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

            p("<b>De: $gerenteGrh<br/>Gerente de Recursos Humanos - GRH/UENF</b>", "left");

            p("Para: $chefia<br/>$nomeLotacao", "left");

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

        $figura = new Imagem(PASTA_FIGURAS . 'assinatura.png', 'Assinatura do Gerente', 120, 140);
        $figura->show();

        $servidor = new Pessoal();
        $gerenteGrh = $servidor->get_Nome($servidor->get_gerente(66));
        $idGerente = $servidor->get_idFuncional($servidor->get_gerente(66));
        p("$gerenteGrh<br/>Gerente de Recursos Humanos<br/>Id Funcional: $idGerente", "center", "f12");

        $grid->fechaColuna();
        $grid->abreColuna(3);
        $grid->fechaColuna();
        $grid->fechaGrid();

        p("_______________________________________________________________________________<br/>Av. Alberto Lamego 2000 - Parque California - Campos dos Goytacazes/RJ - 28013-602<br/>Tel.: (22) 2739-7064 - correio eletronico: grh@uenf.br", "center", "f12");
    }

    ######
    # Inicia a Classe
    $sispatri = new Sispatri();
    $sispatri->set_lotacao($lotacao);

    $result = $sispatri->get_servidoresRelatorio();

    $relatorio = new Relatorio();
    $relatorio->set_funcaoAntesTitulo('exibeTextoSispatri');
    $relatorio->set_funcaoAntesTituloParametro($parametro);

    $relatorio->set_funcaoFinalRelatorio('exibeTextoFinal');

    #$relatorio->set_titulo('Relatório de Servidores Que nao Entregaram o Sispatri');

    $relatorio->set_label(array('idFuncional', 'Nome'));
    $relatorio->set_width(array(20, 80));
    $relatorio->set_align(array("center", "left"));
    $relatorio->set_subTotal(false);
    $relatorio->set_totalRegistro(false);
    $relatorio->set_dataImpressao(false);
    $relatorio->set_linhaNomeColuna(false);
    $relatorio->set_conteudo($result);
    $relatorio->show();

    $grid->fechaColuna();
    $grid->abreColuna(1);
    $grid->fechaColuna();
    $grid->fechaGrid();

    $page->terminaPagina();
}