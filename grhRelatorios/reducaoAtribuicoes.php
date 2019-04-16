<?php
/**
 * Sistema GRH
 * 
 * Relatório
 *   
 * By Alat
 */

# Inicia as variáveis que receberão as sessions
$idUsuario = NULL;              # Servidor logado
$idServidorPesquisado = NULL;	# Servidor Editado na pesquisa do sistema do GRH

# Configuração
include ("../grhSistema/_config.php");

# Permissão de Acesso
$acesso = Verifica::acesso($idUsuario,2);

if($acesso)
{    
    # Conecta ao Banco de Dados
    $pessoal = new Pessoal();
    $reducao = new ReducaoCargaHoraria();
    
    # Pega o número da CI
    $ci = post('ci');

    # Começa uma nova página
    $page = new Page();			
    $page->iniciaPagina();
            
    # Gerente do GRH (id 66)
    $idGerenteGrh = $pessoal->get_gerente(66);
    $nomeGerente = $pessoal->get_nome($idGerenteGrh);
    $lotacaoOrigem = "Gerência de Recursos Humanos - GRH";
    $idFuncionalGerente = $pessoal->get_idFuncional($idGerenteGrh);
    
    # Servidor
    $nomeServidor = $pessoal->get_nome($idServidorPesquisado);
    $idFuncional = $pessoal->get_idFuncional($idServidorPesquisado);
    $cargoEfetivo = $pessoal->get_cargoCompleto($idServidorPesquisado, FALSE);
    $dtAdmissao = $pessoal->get_dtAdmissao($idServidorPesquisado);
    $lotacao = $pessoal->get_lotacao($idServidorPesquisado);
    $idCargo = $pessoal->get_idCargo($idServidorPesquisado);
    $atribuicoesCargo = $pessoal->get_cargoAtribuicoes($idCargo);
    $idArea = $pessoal->get_idAreaCargo($idCargo);
    $atribuicoesArea = $pessoal->get_areaDescricao($idArea);
    
    # Assunto
    $assunto = "Redução de Carga Horária de ".$nomeServidor;

    ## Monta o Relatório 
    # Menu
    $menuRelatorio = new menuRelatorio();
    $menuRelatorio->set_botaoVoltar(NULL);    
    $menuRelatorio->show();
    
    # Cabeçalho do Relatório (com o logotipo)
    $relatorio = new Relatorio();
    $relatorio->exibeCabecalho();
    
    hr();
    
    # Limita o tamanho da tela
    $grid = new Grid("center");
    $grid->abreColuna(11);
    br(2);
    
    # Declaração
    p('DECLARAÇÃO','pDeclaracaoTitulo');
    br(2);
    
    # Texto
    $texto = "&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp"
           . "Declaramos que o(a) Sr.(a) <b>".strtoupper($nomeServidor)."</b>, é servidor(a) desta"
           . "  Universidade, admitido(a) através de Concurso Público em $dtAdmissao, ID Funcional n° $idFuncional,"
           . " para ocupar o cargo de $cargoEfetivo, lotado(a) no(a) $lotacao. O(A) servidor(a) em tela cumpre a carga horária de 40 horas semanais.";
    
    p($texto,'pCi');
        
    $texto = "&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp"
           . "Conforme Lei Estadual 4.800/06 de 29/06/06, publicada DOERJ em 30/06/06"
           ." e Resolução CONSUNI 005/06 de 08/07/2006, publicada DOERJ em 19/10/2006,"
           . "o cargo de $cargoEfetivo possui as seguintes atribuições:";
    p($texto,'pCi');
    
    # Atribuições
    p("Atribuições da Área");
    p($atribuicoesArea,'pCi');
    
    p("Atribuições da Função");
    p(formataAtribuicao($atribuicoesCargo),'pCi');
    
    $texto = "&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp"
           . "Outrossim, declaramos para fins de comprovação junto à Superintendência de Perícia Médica e Saúde Ocupacional – SPMSO,"
           . " que esta Universidade Estadual do Norte Fluminense Darcy Ribeiro – UENF é portadora do CNPJ nº 04.809.688/0001-06, com sede na Av. Alberto"
           . " Lamego, 2.000, Parque Califórnia – Campos dos Goytacazes – RJ, CEP: 28.013-602.";
    p($texto,'pCi');
    
    $texto = "&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp"
           . "Sendo expressão da verdade, subscrevemo-nos.";
    p($texto,'pCi');
    
    # Data
    $texto = "&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp"
           . "Campos dos Goytacazes, ".dataExtenso(date('d/m/Y')).".";
    p($texto,'pCi');
    br(6);
    
    # Assinatura
    #p('____________________________________________________','pCiAssinatura');
    p($nomeGerente.'<br/>'.$lotacaoOrigem.'<br/>Id Funcional n° '.$idFuncionalGerente,'pCiAssinatura');

    $grid->fechaColuna();
    $grid->fechaGrid();
    $page->terminaPagina();
}