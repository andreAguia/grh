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

if($acesso){
    
    # Conecta ao Banco de Dados
    $pessoal = new Pessoal();
    $readaptacao = new Readaptacao();
	
    # Pega o id
    $id = get('id');
    
    # Pega o nome e cargo do chefe
    $array = unserialize(get('array'));
    $chefe = $array[0];
    $cargo = $array[1];

    # Começa uma nova página
    $page = new Page();			
    $page->iniciaPagina();

    # Pega os Dados
    $dados = $readaptacao->get_dados($id);

    # Da Readaptação
    $numCiInicio = $dados['numCiInicio'];
    $dtCiInicio = date_to_php($dados['dtCiInicio']);
    $dtInicio = date_to_php($dados['dtInicio']);
    $dtPublicacao = date_to_php($dados['dtPublicacao']);
    $pgPublicacao = $dados['pgPublicacao'];
    $periodo = $dados['periodo'];
    $processo = $dados['processo'];
    $parecer = $dados['parecer'];
    $textoCi = $dados['textoCi'];
    
    # Trata a publicação
    if(vazio($pgPublicacao)){
        $publicacao = $dtPublicacao;
    }else{
        $publicacao = "$dtPublicacao, pág. $pgPublicacao";
    }
    
    # Servidor
    $nomeServidor = $pessoal->get_nome($idServidorPesquisado);
    $idFuncional = $pessoal->get_idFuncional($idServidorPesquisado);
    
    # Assunto
    $assunto = "Renovação da Readaptação de ".$nomeServidor;

    # Monta a CI
    $ci = new Ci($numCiInicio,$dtCiInicio,$assunto);
    $ci->set_destinoNome($chefe);
    $ci->set_destinoSetor($cargo);
    $ci->set_texto('Vimos informar a concessão da renovação da <b>Readaptação</b> do(a) servidor(a) <b>'.strtoupper($nomeServidor).'</b>,'
    . ' ID '.$idFuncional.', por um período de $periodo meses, a contar de $dtInicio, "<i>'.$textoCi.'</i>", conforme publicação no DOERJ em $publicacao'
    . ' em anexo.');
    $ci->set_saltoRodape(3);
    $ci->show();
    
    # Grava o log da visualização do relatório
    $data = date("Y-m-d H:i:s");
    $atividades = 'Visualizou a Ci de renovação de readaptacao.';
    $tipoLog = 4;
    $intra->registraLog($idUsuario,$data,$atividades,"tbreadaptacao",$id,$tipoLog,$idServidorPesquisado);
    
    $page->terminaPagina();
}