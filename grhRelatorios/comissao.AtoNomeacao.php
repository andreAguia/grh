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
    $dtInicial = dataExtenso(date_to_php($comissao['dtNom']));
    $descricao = $comissao['descricao'];
    $ocupanteAnterior = $comissao['ocupanteAnterior'];
    $protempore = $comissao['protempore'];
    $publicacao = date_to_php($comissao['dtPublicNom']);
    
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
    $ato->set_totalRegistro(FALSE);
    $ato->set_dataImpressao(FALSE);
    $ato->set_formCampos(array(
              array ('nome' => 'dataEmissao',
                     'label' => 'Data do Documento',
                     'tipo' => 'date',
                     'valor' => $postData,
                     'size' => 5,
                     'title' => 'Data do Documento',
                     'onChange' => 'formPadrao.submit();',
                     'col' => 4,
                     'linha' => 1)));

    $ato->set_formFocus('dataEmissao');		
    $ato->set_formLink('?id='.$idComissao);
    #$ato->set_logServidor($idFicha);
    #$ato->set_logDetalhe("Visualizou a Ficha Cadastral");
    $ato->show();
    
    # Preambulo
    p("O REITOR DA UNIVERSIDADE ESTADUAL DO NORTE FLUMINENSE DARCY RIBEIRO,  no uso das atribuiçoes legais;","preambulo");
    
    $grid->fechaColuna();
    $grid->abreColuna(12);
    br(3);
    
    # inclui ou nao o protempore e junta no nome
    if($protempore){
        $nome = ", <i>pro-tempore</i>, <b>".$nome."</b>";
    }else{
        $nome = " <b>".$nome."</b>";
    }
    
    # Cargos que so tem uma vaga na universidade
    if($vagas == 1){
        $principal = "<b>NOMEIA</b>$nome, ID Funcional n° $idFuncional, para exercer, com validade a contar de $dtInicial,"
           . " o cargo em comissao de $cargo, simbolo $simbolo, da Universidade Estadual do Norte Fluminense"
           . " - Darcy Ribeiro - UENF, da Secretaria de Estado de Ciencia, Tecnologia e Inovaçao - SECTI,"
           . " do Quadro Permanente de Pessoal Civil do Poder Executivo do Estado do Rio de Janeiro";
    }else{
        $principal = "<b>NOMEIA</b>$nome, ID Funcional n° $idFuncional, para exercer, com validade a contar de $dtInicial,"
           . " o cargo em comissao de $cargo, simbolo $simbolo, do(a) $descricao da Universidade Estadual do Norte Fluminense"
           . " - Darcy Ribeiro - UENF, da Secretaria de Estado de Ciencia, Tecnologia e Inovaçao - SECTI,"
           . " do Quadro Permanente de Pessoal Civil do Poder Executivo do Estado do Rio de Janeiro";
    }
    
    # Preenche o ocupante anterior
    if(vazio($ocupanteAnterior)){
        $principal .= ".";
    }else{
        $principal .= ", em vaga anteriormente ocupada por $ocupanteAnterior.";
    }
    
    p($principal,"principal");
    br(2);
    
    # Data
    p("Campos dos Goytacazes, ".dataExtenso(date_to_php($postData)).".","principal");
    br(4);
    
    # Reitor
    p("<b>".$reitor."<br/>REITOR</b>","reitor");
    
    $grid->fechaColuna();
    $grid->abreColuna(8);
    $grid->fechaColuna();
    $grid->abreColuna(4);
        if(!vazio($publicacao)){
            callout("Publicado no DOERJ<br/>".dataExtenso($publicacao),"secondary");
        }
    $grid->fechaColuna();
    
    # Rodapé
    $grid->abreColuna(12);
    hr();
    
    $texto1 = "Av. Alberto Lamego 2000";
    
    p($texto1,"f10","center");
   
    $grid->fechaColuna();
    $grid->fechaGrid();
    
    $page->terminaPagina();
}