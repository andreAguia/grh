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
    
    # Pega o número da CI
    $ci = post('ci');

    # Começa uma nova página
    $page = new Page();			
    $page->iniciaPagina();

    # pega os dados
    $dados = $reducao->get_dadosCiInicio($id);
    
    # Da Redução
    $numCi = $dados[0];
    $dtCiInicio = date_to_php($dados[1]);
    $dtInicio = date_to_php($dados[2]);
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
    
    # Lotação
    $idLotacao = $pessoal->get_idLotacao($idServidorPesquisado);                    // Pega o id da lotação do servidor
    $lotacaoDestino = $pessoal->get_nomeLotacao($idLotacao);                        // Pega o nome da lotação
    
    # Servidor
    $nomeServidor = $pessoal->get_nome($idServidorPesquisado);
    $idFuncional = $pessoal->get_idFuncional($idServidorPesquisado);
    
    # Assunto
    $assunto = "Redução de Carga Horária de ".$nomeServidor;

    # Monta a CI
    $ci = new Ci();
    $ci->set_ciNumero($numCi);
    $ci->set_ciData($dtCiInicio);
    $ci->set_destinoNome($nomeGerenteDestino);
    $ci->set_destinoSetor($gerenciaImediataDescricao);
    $ci->set_ciAssunto($assunto);
    $ci->set_ciTexto("Vimos informar a concessão de <b>Redução de Carga Horária</b> do(a) servidor(a) <b>".strtoupper($nomeServidor)."</b>,"
    . " ID $idFuncional, por um período de $periodo meses, a contar <b>em $dtInicio</b>, "
    . "atendendo processo $processo, publicado no DOERJ de $publicacao,"
    . " em anexo.");
    $ci->show();
    
    $page->terminaPagina();
}