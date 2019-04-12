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
	
    # Pega o id da diaria
    $id = get('id');
    
    # Pega o número da CI
    $ci = post('ci');

    # Começa uma nova página
    $page = new Page();			
    $page->iniciaPagina();

    # pega os dados
    $dados = $reducao->get_dadosCiInicio($id);

    # Número da Ci
    $numCi = $dados[0];
    
    # Data da CI
    $dtCiInicio = date_to_php($dados[1]);
    if(vazio($dtCiInicio)){
        $dtCiInicio = date("d/m/YYYY");
    }
    
    # Da Redução
    $dtInicio = date_to_php($dados[2]);
    $dtPublicacao = date_to_php($dados[3]);
    $pgPublicacao = $dados[4];
    $periodo = $dados[5];
    $processo = $reducao->get_numProcesso($idServidorPesquisado);
    
    # Gerente do GRH (id 66)
    $idGerenteGrh = $pessoal->get_gerente(66);
    $nomeGerente = $pessoal->get_nome($idGerenteGrh);
    $lotacaoOrigem = "Gerência de Recursos Humanos - GRH";
    $idFuncionalGerente = $pessoal->get_idFuncional($idGerenteGrh);
    
    # Lotação Destino
    $idLotacao = $pessoal->get_idLotacao($idServidorPesquisado);
    $idGerenteDestino = $pessoal->get_gerente($idLotacao);
    $nomeGerenteDestino = $pessoal->get_nome($idGerenteDestino);
    $lotacaoDestino = $pessoal->get_nomeLotacao($idLotacao);
    
    # Servidor
    $nomeServidor = $pessoal->get_nome($idServidorPesquisado);
    $idFuncional = $pessoal->get_idFuncional($idServidorPesquisado);
    
    # Assunto
    $assunto = "Redução de Carga Horária de ".$nomeServidor;

    ## Monta o Relatório 
    # Menu
    $menuRelatorio = new menuRelatorio();
    $menuRelatorio->set_botaoVoltar(NULL);
    
    $menuRelatorio->set_formCampos(array(
              array ('nome' => 'ci',
                     'label' => 'CI',
                     'tipo' => 'texto',
                     'valor' => $numCi,
                     'size' => 5,
                     'title' => 'Número da Ci',
                     'onChange' => 'formPadrao.submit();',
                     'col' => 3,
                     'linha' => 1)
        ));

    $menuRelatorio->set_formFocus('contatos');		
    $menuRelatorio->set_formLink('?');
    
    $menuRelatorio->show();
    
    # Cabeçalho do Relatório (com o logotipo)
    $relatorio = new Relatorio();
    $relatorio->exibeCabecalho();
    
    hr();
    
    # Limita o tamanho da tela
    $grid = new Grid("center");
    $grid->abreColuna(11);
    
    $grid = new Grid();
    $grid->abreColuna(6);
    
    # CI
    p('C.I.UENF/DGA/GRH Nº '.$numCi,'pCi');
    
    $grid->fechaColuna();
    $grid->abreColuna(6);
    
    # Data
    p('Campos dos Goytacazes,'.dataExtenso($dtCiInicio),'pCiData');
    
    $grid->fechaColuna();
    $grid->fechaGrid();
    
    # Origem
    p('De: '.$nomeGerente.'<br/>&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp'.$lotacaoOrigem,'pCi');
    br();
    
    # Destino
    p('Para: '.$nomeGerenteDestino.'<br/'.$lotacaoDestino,'pCi');
    br(2);
    
    # Assunto
    p("Assunto: ".$assunto,'pCi');
    
    # Texto
    p("&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbspVimos informar a concessão de <b>Redução de Carga Horária</b> do(a) servidor(a) <b>".strtoupper($nomeServidor)."</b>, ID $idFuncional, por um período de $periodo meses, a contar <b>em $dtInicio</b>, atendendo processo $processo, publicado no DOERJ de $dtPublicacao, pág. $pgPublicacao, em anexo.",'pCi');
    br(3);
    
    # Atenciosamente
    p('Atenciosamente','pCi');
    br(4);
    
    # Assinatura
    #p('____________________________________________________','pCiAssinatura');
    p($nomeGerente.'<br/>'.$lotacaoOrigem.'<br/>Id Funcional n° '.$idFuncionalGerente,'pCiAssinatura');

    $grid->fechaColuna();
    $grid->fechaGrid();
    $page->terminaPagina();
}