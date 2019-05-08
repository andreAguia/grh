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
    
    # Servidor
    $nomeServidor = $pessoal->get_nome($idServidorPesquisado);
    $idFuncional = $pessoal->get_idFuncional($idServidorPesquisado);
    $cargoEfetivo = $pessoal->get_cargoCompleto($idServidorPesquisado, FALSE);
    $sexo = $pessoal->get_sexo($idServidorPesquisado);
    
    # Altera parte do texto de acordo com o sexo (gênero) do servidor
    if($sexo == "Masculino"){
        $texto1 = "o servidor";    
    }else{
        $texto1 = "a servidora";
    }

    # Começa uma nova página
    $page = new Page();			
    $page->iniciaPagina();
    
    # Monta a Declaração
    $dec = new Declaracao();
    $dec->set_data(date("d/m/Y"));
    $dec->set_texto("Declaro para os devidos fins, que $texto1 <b>".strtoupper($nomeServidor)."</b>,"
           . " ID funcional nº $idFuncional, $cargoEfetivo, não está respondendo a inquérito administrativo por"
           . " comunicação de faltas nesta Universidade Estadual do Norte Fluminense Darcy Ribeiro.");
    
    $dec->set_saltoRodape(10);
    $dec->set_aviso("teste");
    $dec->show();
    $page->terminaPagina();
}