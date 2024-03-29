<?php

/**
 * Sistema GRH
 * 
 * Ato de Nomeaçao
 *   
 * By Alat
 */
# Configuração
include ("../grhSistema/_config.php");

# Pega o idComissao 
$idComissao = get('id');

# Pega os parâmetros do relatório
$postData = post('dataEmissao', date("Y-m-d"));

# Permissão de Acesso
$acesso = Verifica::acesso($idUsuario, [1, 2, 12]);

if ($acesso) {

    # Começa uma nova página
    $page = new Page();
    $page->iniciaPagina();

    # Conecta ao Banco de Dados    
    $pessoal = new Pessoal();
    $cargoComissao = new CargoComissao();

    # Pega os dados da comissao
    $dadosComissao = $cargoComissao->get_dados($idComissao);
    $idTipoComissao = $dadosComissao['idTipoComissao'];
    $tipoComissao = $pessoal->get_dadosTipoComissao($idTipoComissao);

    # do(a) Reitor(a)
    $generoReitor = $pessoal->get_sexo($pessoal->get_reitor());

    # Preenche as variaveis da comissao
    $nome = strtoupper($pessoal->get_nome($dadosComissao['idServidor'])); // Nome do servidor
    $idFuncional = $pessoal->get_idFuncional($dadosComissao['idServidor']);  // idFuncional
    $dtExo = dataExtenso(date_to_php($dadosComissao['dtExo']));
    #$descricao = $dadosComissao['descricao'];
    $publicacao = date_to_php($dadosComissao['dtPublicExo']);
    $dtAtoExo = date_to_php($dadosComissao['dtAtoExo']);

    # Preenche as variaveis do tipo de comissao
    $cargo = $tipoComissao['descricao'];
    $simbolo = $tipoComissao['simbolo'];
    $vagas = $tipoComissao['vagas'];

    # Outras variaveis
    $reitor = $pessoal->get_nomeReitor();

    # Limita a página
    $grid = new Grid();
    $grid->abreColuna(12);

    # Inicia o Relatorio
    $ato = new Relatorio();
    $ato->set_titulo("ATO DO REITOR");
    $ato->set_totalRegistro(false);
    $ato->set_dataImpressao(false);
    #$ato->set_logServidor($idFicha);
    #$ato->set_logDetalhe("Visualizou a Ficha Cadastral");
    $ato->show();

    # Preambulo
    if ($generoReitor == "Masculino") {
        p("O REITOR DA UNIVERSIDADE ESTADUAL DO NORTE FLUMINENSE DARCY RIBEIRO,  no uso das atribuiçoes legais;", "preambulo");
    } else {
        p("A REITORA DA UNIVERSIDADE ESTADUAL DO NORTE FLUMINENSE DARCY RIBEIRO,  no uso das atribuiçoes legais;", "preambulo");
    }

    $grid->fechaColuna();
    $grid->abreColuna(12);
    br(3);

    # Cargos que so tem uma vaga na universidade
    if ($vagas == 1) {
        # Se tem uma unica vaga nao e necessario informar a o local pois e de toda a universidade
        $principal = "<b>EXONERA $nome</b>, ID Funcional n° $idFuncional, a contar de $dtExo,"
                . " o cargo em comissao de $cargo, simbolo $simbolo, da Universidade Estadual do Norte Fluminense"
                . " - Darcy Ribeiro - UENF, da Secretaria de Estado de Ciencia, Tecnologia e Inovaçao - SECTI,"
                . " do Quadro Permanente de Pessoal Civil do Poder Executivo do Estado do Rio de Janeiro.";
    } else {
        # Se tem uma mais de uma vaga e necessario informar o nome do Laboratório, do Curso, da Gerência, da Diretoria ou da Pró Reitoria
        $principal = "<b>EXONERA</b> $nome, ID Funcional n° $idFuncional, a contar de $dtExo,"
                . " o cargo em comissao de $cargo, simbolo $simbolo, do(a) $descricao da Universidade Estadual do Norte Fluminense"
                . " - Darcy Ribeiro - UENF, da Secretaria de Estado de Ciencia, Tecnologia e Inovaçao - SECTI,"
                . " do Quadro Permanente de Pessoal Civil do Poder Executivo do Estado do Rio de Janeiro.";
    }

    p($principal, "principal");
    br(2);

    # Data
    p("Campos dos Goytacazes, " . dataExtenso($dtAtoExo) . ".", "principal");
    br(4);

    # Reitor
    if ($generoReitor == "Masculino") {
        p("<b>" . $reitor . "<br/>REITOR</b>", "reitor");
    } else {
        p("<b>" . $reitor . "<br/>REITORA</b>", "reitor");
    }

    $grid->fechaColuna();
    $grid->abreColuna(8);
    $grid->fechaColuna();
    $grid->abreColuna(4);
    if (!vazio($publicacao)) {
        callout("Publicado no DOERJ<br/>" . dataExtenso($publicacao), "secondary");
    }
    $grid->fechaColuna();

    # Rodapé
    $grid->abreColuna(12);
    hr();

    $texto1 = "Av. Alberto Lamego 2000 - Predio E-1 Sala 217 - Parque California - Campos dos Goytacazes RJ - 28013-602";
    $texto2 = "Tel:(22) 2739-7064 - Email: grh@uenf.br";

    p($texto1, "rodape");
    p($texto2, "rodape");

    $grid->fechaColuna();
    $grid->fechaGrid();

    $page->terminaPagina();
}