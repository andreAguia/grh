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
$acesso = $acesso = Verifica::acesso($idUsuario,[1, 2, 12]);

if ($acesso) {

    # Começa uma nova página
    $page = new Page();
    $page->iniciaPagina();

    # Conecta ao Banco de Dados    
    $pessoal = new Pessoal();
    $cargoComissao = new CargoComissao();

    # pega os dados ta comissao
    $dadosComissao = $cargoComissao->get_dados($idComissao);           // dados da comissao
    $idTipoComissao = $dadosComissao['idTipoComissao'];

    $tipoComissao = $pessoal->get_dadosTipoComissao($idTipoComissao);   // dados do tipo de comissao
    # Preenche as variaveis da comissao
    $nome = mb_strtolower($pessoal->get_nome($dadosComissao['idServidor'])); // Nome do servidor
    $idFuncional = $pessoal->get_idFuncional($dadosComissao['idServidor']);  // idFuncional
    $inicio = dataExtenso2(date_to_php($dadosComissao['dtNom']));
    $dtNom = dataExtenso(date_to_php($dadosComissao['dtNom']));
    $publicacao = mb_strtolower(dataExtenso(date_to_php($dadosComissao['dtPublicNom'])));
    $dtAtoNom = mb_strtolower(dataExtenso(date_to_php($dadosComissao['dtAtoNom'])));

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
    $ato->set_titulo("TERMO DE POSSE");
    $ato->set_totalRegistro(false);
    $ato->set_dataImpressao(false);
    #$ato->set_formCampos(array(
    #          array ('nome' => 'dataEmissao',
    #                 'label' => 'Data do Documento',
    #                 'tipo' => 'date',
    #                 'valor' => $postData,
    #                 'size' => 5,
    #                 'title' => 'Data do Documento',
    #                 'onChange' => 'formPadrao.submit();',
    #                 'col' => 4,
    #                 'linha' => 1)));		
    #$ato->set_formLink('?id='.$idComissao);
    #$ato->set_logServidor($idFicha);
    #$ato->set_logDetalhe("Visualizou a Ficha Cadastral");
    $ato->show();

    # Preambulo
    $principal = "$inicio, na Universidade Estadual do Norte Fluminense Darcy Ribeiro - UENF, "
            . plm($nome) . ", identidade Funcional n° $idFuncional, nomeado(a), de acordo com o Inciso VII "
            . "do artigo 20° do Decreto Estadual n° 30.672, de 18 de fevereiro de 2002, "
            . "para exercer, o Cargo em Comissão de $cargo, simbolo $simbolo, "
            . "da Universidade Estadual do Norte Fluminense Darcy Ribeiro - UENF, "
            . "com validade de $dtNom, por Ato de Investidura do Magnífico Reitor, de $dtAtoNom, "
            . "publicado no Diário Oficial de $publicacao, "
            . "compareceu perante o Magnífico Reitor da Universidade Estadual do Norte Fluminense Darcy Ribeiro e, "
            . "tendo exibido o título de provimento, prestado a declaração de bens e valores "
            . "e a declaração de acumulação de cargos e/ou emprego público, foi empossado(a) e "
            . "entrou em efetivo exercício no referido cargo mediante promessa de fiel cumprimento "
            . "dos deveres da função pública. E, para constar, lavrou-se o presente termo que é assinado pelo "
            . "Magnífico Reitor e pelo(a) empossado(a).";

    $grid->fechaColuna();
    $grid->abreColuna(12);
    br(2);

    p($principal, "termodePosse");
    br(3);

    # Data
    p("Campos dos Goytacazes, " . $dtNom . ".", "termodePosse");
    br(3);

    # Reitor
    p("______________________________________<br/><b>" . $reitor . "<br/>REITOR</b>", "reitor");
    br(3);

    # Servidor
    p("______________________________________<br/><b>" . plm($nome) . "</b>", "reitor");
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