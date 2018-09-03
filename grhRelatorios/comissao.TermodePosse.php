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
$postData = post('dataEmissao',date("Y-m-d"));

# Permissão de Acesso
$acesso = Verifica::acesso($idUsuario);

if($acesso){  
    
    # Começa uma nova página
    $page = new Page();
    $page->iniciaPagina();
    
    # Conecta ao Banco de Dados    
    $pessoal = new Pessoal();
     
    # pega os dados ta comissao
    $comissao = $pessoal->get_dadosComissao($idComissao);           // dados da comissao
    $idTipoComissao = $comissao['idTipoComissao'];
    $tipoComissao = $pessoal->get_dadosTipoComissao($idTipoComissao);   // dados do tipo de comissao
   
    # Preenche as variaveis da comissao
    $nome = strtoupper($pessoal->get_nome($comissao['idServidor'])); // Nome do servidor
    $idFuncional = $pessoal->get_idFuncional($comissao['idServidor']);  // idFuncional
    $dtNom = dataExtenso(date_to_php($comissao['dtNom']));
    $descricao = $comissao['descricao'];
    $publicacao = date_to_php($comissao['dtPublicExo']);
    
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
    $ato->set_totalRegistro(FALSE);
    $ato->set_dataImpressao(FALSE);
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

    #$ato->set_formFocus('dataEmissao');		
    #$ato->set_formLink('?id='.$idComissao);
    #$ato->set_logServidor($idFicha);
    #$ato->set_logDetalhe("Visualizou a Ficha Cadastral");
    $ato->show();
    
    # Preambulo
    $principal = "Aos $dtNom ,na Universidade Estadual do Norte Fluminense Darcy Ribeiro - UENF, "
               . "$nome, identidade Funcional n° $idFuncional, nomeado(a), de acordo com o Inciso VII "
               . "do artigo 20° do Decreto Estadual n° 30.672, de 18 de fevereiro de 2002,"
               . "para exercer, o Cargo em Comissão de $cargo, simbolo $simbolo,"
               . "da Universidade Estadual do Norte Fluminense Darcy Ribeiro - UENF,"
               . "com validade de $dtNom, por Ato de Investidura do Magnífico Reitor, de [12 de maio de 2016], "
               . "publicado no Diário Oficial de [13 de maio de 2016], "
               . "compareceu perante o Magnífico Reitor da Universidade Estadual do Norte Fluminense Darcy Ribeiro e, "
               . "tendo exibido o título de provimento, prestado a declaração de bens e valores "
               . "e a declaração de acumulação de cargos e/ou emprego público, foi empossado(a) e "
               . "entrou em efetivo exercício no referido cargo mediante promessa de fiel cumprimento "
               . "dos deveres da função pública. E, para constar, lavrou-se o presente termo que é assinado pelo "
               . "Magnífico Reitor e pelo(a) empossado(a).";
    
    $grid->fechaColuna();
    $grid->abreColuna(12);
    br(2);
    
    p($principal,"termodePosse");
    br(4);
    
    # Data
    p("Campos dos Goytacazes, ".$dtNom.".","termodePosse");
    br(3);
    
    # Reitor
    p("<b>".$reitor."<br/>REITOR</b>","reitor");
    br(3);
    
    # Servidor
    p("<b>".$nome."<br/>REITOR</b>","reitor");
    $grid->fechaColuna();
    
    # Rodapé
    $grid->abreColuna(12);
    hr();
    
    $texto1 = "Av. Alberto Lamego 2000 - Predio E-1 Sala 217 - Parque California - Campos dos Goytacazes RJ - 28013-602";
    $texto2 = "Tel:(22) 2739-7064 - Email: grh@uenf.br";
    
    p($texto1,"rodape");
    p($texto2,"rodape");
   
    $grid->fechaColuna();
    $grid->fechaGrid();
    
    $page->terminaPagina();
}