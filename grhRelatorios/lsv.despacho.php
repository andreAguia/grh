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
    $lsv = new LicencaSemVencimentos();
	
    # Pega o id
    $id = get('id');
   
    # Começa uma nova página
    $page = new Page();			
    $page->iniciaPagina();

    # Pega os Dados
    $dados = $lsv->get_dados($id);

    # Da Licença
    $idTpLicenca = $dados['idTpLicenca'];
    $nomeLicenca = $pessoal->get_nomeTipoLicenca($idTpLicenca);
    
    # Servidor
    $nomeServidor = $pessoal->get_nome($idServidorPesquisado);
    $idFuncional = $pessoal->get_idFuncional($idServidorPesquisado);
    $cargo = $pessoal->get_cargoCompleto($idServidorPesquisado);
    $idLotacao = $pessoal->get_idLotacao($idServidorPesquisado);
    $lotacao = $pessoal->get_nomeLotacao($idLotacao);
    
    # Pega o idServidor do gerente GRH
    $idGerente = $pessoal->get_gerente(66);
    $gerente = $pessoal->get_nome($idGerente);
    $cargo = $pessoal->get_cargoComissaoDescricao($idGerente);
    $idFuncional = $pessoal->get_idFuncional($idGerente);
    
    # Monta a CI
    $despacho = new Despacho();
    
    $despacho->set_origemNome($gerente);
    $despacho->set_origemDescricao($cargo);
    $despacho->set_origemIdFuncional($idFuncional);
    
    $despacho->set_destino("A Reitoria");
    $despacho->set_texto('Trata o presente processo de solicitação de '.strtoupper($nomeLicenca).', do(a) servidor(a) <b>'.strtoupper($nomeServidor).'</b>, '
                    .$cargo.', ID '.$idFuncional.', lotado na '.$lotacao.', para o qual solicitamos o "NADA A OPOR" dessa Reitoria');
    $despacho->set_saltoRodape(3);
    $despacho->show();
    
    # Grava o log da visualização do relatório
    $data = date("Y-m-d H:i:s");
    $atividades = 'Visualizou a Ci de início de readaptacao.';
    $tipoLog = 4;
    $intra->registraLog($idUsuario,$data,$atividades,"tbreadaptacao",$id,$tipoLog,$idServidorPesquisado);
    
    $page->terminaPagina();
}