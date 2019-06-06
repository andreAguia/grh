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
    
    # Começa uma nova página
    $page = new Page();			
    $page->iniciaPagina();
    
    # Monta a Declaração
    $dec = new Declaracao();
    $dec->set_data(date("d/m/Y"));
    
    $dec->set_texto("Declaramos que o(a) Sr.(a) <b>".strtoupper($nomeServidor)."</b>, é servidor(a) desta"
           . "  Universidade, admitido(a) através de Concurso Público em $dtAdmissao, ID Funcional n° $idFuncional,"
           . " para ocupar o cargo de $cargoEfetivo, lotado(a) no(a) $lotacao. O(A) servidor(a) em tela cumpre a carga horária de 40 horas semanais.");
    
    $dec->set_texto("Conforme Lei Estadual 4.800/06 de 29/06/06, publicada DOERJ em 30/06/06"
           ." e Resolução CONSUNI 005/06 de 08/07/2006, publicada DOERJ em 19/10/2006,"
           . "o cargo de $cargoEfetivo possui as seguintes atribuições:");
    
    $dec->set_texto("Atribuições da Área");
    
    $dec->set_texto($atribuicoesArea);
    
    $dec->set_texto("Atribuições da Função");
    
    $dec->set_texto(formataAtribuicao($atribuicoesCargo));
    
    $dec->set_texto("Outrossim, declaramos que esta Universidade Estadual do Norte Fluminense Darcy Ribeiro – UENF"
                  . " é portadora do CNPJ nº 04.809.688/0001-06, com sede na Av. Alberto"
                  . " Lamego, 2.000, Parque Califórnia – Campos dos Goytacazes – RJ, CEP: 28.013-602.");
    
    $dec->set_texto("Sendo expressão da verdade, subscrevemo-nos.");
    
    #$dec->set_saltoRodape(10);
    $dec->show();
    
    # Grava o log da visualização do relatório
    $data = date("Y-m-d H:i:s");    
    $atividades = 'Visualizou a Declaração de Atribuições do Cargo';
    $tipoLog = 4;
    $intra->registraLog($idUsuario,$data,$atividades,NULL,NULL,$tipoLog,$idServidorPesquisado);
    
    $page->terminaPagina();
}