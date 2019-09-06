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
    
    # Trata a publicação
    if(vazio($pgPublicacao)){
        $publicacao = $dtPublicacao;
    }else{
        $publicacao = "$dtPublicacao, pág. $pgPublicacao";
    }       
            
    # Chefia imediata
    $idChefiaImediataDestino = $pessoal->get_chefiaImediata($idServidorPesquisado);             // Pega o idServidor da chefia imediata desse servidor
    
    $nomeGerenteDestino = $pessoal->get_nome($idChefiaImediataDestino);                         // Pega o nome da chefia
    
    $gerenciaImediataDescricao = $pessoal->get_chefiaImediataDescricao($idChefiaImediataDestino);  // Pega a descrição da chefia imediata
    echo $idChefiaImediataDestino;
    # Servidor
    $nomeServidor = $pessoal->get_nome($idServidorPesquisado);
    $idFuncional = $pessoal->get_idFuncional($idServidorPesquisado);
    
    # Assunto
    $assunto = "Readaptação de ".$nomeServidor;

    # Monta a CI
    $ci = new Ci($numCiInicio,$dtCiInicio,$assunto);
    $ci->set_destinoNome($nomeGerenteDestino);
    $ci->set_destinoSetor($gerenciaImediataDescricao);
    $ci->set_texto("Vimos informar a concessão de <b>Readaptação</b> do(a) servidor(a) <b>".strtoupper($nomeServidor)."</b>,"
    . " ID $idFuncional, pelo prazo de $periodo meses, '<i>$parecer</i>', conforme publicação no DOERJ em $publicacao"
    . " em anexo, para fins de cumprimento.");
    $ci->set_saltoRodape(5);
    $ci->show();
    
    # Grava o log da visualização do relatório
    $data = date("Y-m-d H:i:s");
    $atividades = 'Visualizou a Ci de início de readaptacao.';
    $tipoLog = 4;
    $intra->registraLog($idUsuario,$data,$atividades,"tbreadaptacao",$id,$tipoLog,$idServidorPesquisado);
    
    $page->terminaPagina();
}