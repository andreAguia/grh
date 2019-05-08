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
	
    # Pega o id
    $id = get('id');

    # Começa uma nova página
    $page = new Page();			
    $page->iniciaPagina();

    # pega os dados
    $dados = $reducao->get_dadosCiTermino($id);
    
    # Da Redução
    $numCi = $dados[0];
    $dtCiTermino = date_to_php($dados[1]);
    $dtInicio = date_to_php($dados[2]);
    $dtTermino = date_to_php($dados[6]);
    $dtPublicacao = date_to_php($dados[3]);
    $pgPublicacao = $dados[4];
    $periodo = $dados[5];
    $processo = $reducao->get_numProcesso($idServidorPesquisado);
    
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
    
    # Servidor
    $nomeServidor = $pessoal->get_nome($idServidorPesquisado);
    $idFuncional = $pessoal->get_idFuncional($idServidorPesquisado);
    
    # Assunto
    $assunto = "Comunica <b>TÉRMINO</b> do prazo de Redução de Carga Horária de ".$nomeServidor;
    
    # Monta a CI
    $ci = new Ci($numCi,$dtCiTermino,$assunto);
    $ci->set_destinoNome($nomeGerenteDestino);
    $ci->set_destinoSetor($gerenciaImediataDescricao);
    
    $ci->set_texto("Vimos comunicar o <b>TÉRMINO</b> da"
    . " de <b>Redução de Carga Horária</b> do(a) servidor(a) <b>".strtoupper($nomeServidor)."</b>,"
    . " ID $idFuncional, em $dtTermino, conforme Ato do Reitor publicado no DOERJ de $publicacao,"
    . " concedendo o benefício pelo prazo de $periodo meses.");
    
    $ci->set_texto("Esclarecemos que o referido servidor deverá cumprir"
     . " a carga horária normal, enquanto aguarda o parecer da perícia médica oficial do Estado do RJ para concessão de"
     . " prorrogação, se for o caso.");
    
    $ci->set_texto("Sem mais para o momento, reiteramos votos de estima e consideração.");
    $ci->set_saltoRodape(1);
    $ci->show();
    
    $page->terminaPagina();
}