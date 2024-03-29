<?php

/**
 * Sistema GRH
 * 
 * Ato de Nomeaçao
 *   
 * By Alat
 */

# Inicia as variáveis que receberão as sessions
$idUsuario = null;
$idServidorPesquisado = null;

# Configuração
include ("../grhSistema/_config.php");

# Pega o idComissao 
$idComissao = get('id');

# Permissão de Acesso
$acesso = $acesso = Verifica::acesso($idUsuario,[1, 2, 12]);

if ($acesso) {

    # Começa uma nova página
    $page = new Page();
    $page->iniciaPagina();

    # Conecta ao Banco de Dados    
    $pessoal = new Pessoal();
    $cargoComissao = new CargoComissao();

    # pega os dados da comissao
    $dadosComissao = $cargoComissao->get_dados($idComissao); 
    $idTipoComissao = $dadosComissao['idTipoComissao'];
    $tipoComissao = $pessoal->get_dadosTipoComissao($idTipoComissao);
    
    # Preenche as variaveis da comissao
    $nome = strtoupper($pessoal->get_nome($dadosComissao['idServidor'])); // Nome do servidor
    $idFuncional = $pessoal->get_idFuncional($dadosComissao['idServidor']);  // idFuncional
    $dtInicial = dataExtenso(date_to_php($dadosComissao['dtNom']));
    $tipo = $dadosComissao['tipo'];
    $publicacao = date_to_php($dadosComissao['dtPublicNom']);
    $dtAtoNom = date_to_php($dadosComissao['dtAtoNom']);
    $descricao = $cargoComissao->get_descricaoCargo($idComissao);
    
    # Ocupante anterior    
    $idComissaoAnterior = $dadosComissao['idAnterior'];
    $ocupanteAnteriorDados = $cargoComissao->get_dados($idComissaoAnterior); 
    $ocupanteAnterior = $pessoal->get_nome($ocupanteAnteriorDados["idServidor"]);

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

    # Inicia o texto
    $textoInicio = null;
    $verbo = "exercer";

    # inclui o status
    if ($tipo == 1) {
        $textoInicio = "<b>NOMEIA</b>, <i>pro-tempore</i>, <b>" . $nome . "</b>";
    } elseif ($tipo == 2) {
        $textoInicio = "<b>DESIGNA</b> <b>" . $nome . "</b>";
        $verbo = "exercer";
    } else {
        $textoInicio = "<b>NOMEIA $nome</b>";
    }

    # Cargos que so tem uma vaga na universidade
    if ($vagas == 1) {
        # Se tem uma unica vaga nao e necessario informar a o local pois e de toda a universidade
        $principal = "<b>NOMEIA $nome</b>, ID Funcional n° $idFuncional, para exercer, com validade a contar de $dtInicial,"
                . " o cargo em comissao de $cargo, simbolo $simbolo, da Universidade Estadual do Norte Fluminense"
                . " - Darcy Ribeiro - UENF, da Secretaria de Estado de Ciencia, Tecnologia e Inovaçao - SECTI,"
                . " do Quadro Permanente de Pessoal Civil do Poder Executivo do Estado do Rio de Janeiro";
    } else {
        # Se tem uma mais de uma vaga e necessario informar o nome do Laboratório, do Curso, da Gerência, da Diretoria ou da Pró Reitoria
        $principal = "<b>NOMEIA $nome</b>, ID Funcional n° $idFuncional, para exercer, com validade a contar de $dtInicial,"
                . " o cargo em comissao, simbolo $simbolo, $descricao da Universidade Estadual do Norte Fluminense"
                . " - Darcy Ribeiro - UENF, da Secretaria de Estado de Ciencia, Tecnologia e Inovaçao - SECTI,"
                . " do Quadro Permanente de Pessoal Civil do Poder Executivo do Estado do Rio de Janeiro";
    }

    # Preenche o ocupante anterior
    if (empty($ocupanteAnterior)) {
        $principal .= ".";
    } else {
        $principal .= ", em vaga anteriormente ocupada por $ocupanteAnterior.";
    }

    p($principal, "principal");
    br(2);

    # Data
    p("Campos dos Goytacazes, " . dataExtenso($dtAtoNom) . ".", "principal");
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
    if (!empty($publicacao)) {
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