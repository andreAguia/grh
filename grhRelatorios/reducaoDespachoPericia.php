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
    $reducao = new ReducaoCargaHoraria();
	
    # Pega o id
    $id = get('id');
    
    # Pega a folha
    $folha = get('folha');

    # Começa uma nova página
    $page = new Page();			
    $page->iniciaPagina();

    # Pega os dados
    $dados = $reducao->get_dados($id);
    
    # da Redução
    $tipo = $dados["tipo"];
    $dtTermino = date_to_php($dados["dtTermino"]);
    $dtPublicacao = date_to_php($dados["dtPublicacao"]);
    
    # do Servidor
    $nomeServidor = $pessoal->get_nome($idServidorPesquisado);
    $idFuncional = $pessoal->get_idFuncional($idServidorPesquisado);
    $cargoEfetivo = $pessoal->get_cargoCompleto($idServidorPesquisado, FALSE);
    
    $destino = "À SE/SPM,";
    $data = date("d/m/Y");
    
    # Tipo
    if($tipo == 2){
        $tipo = " de Renovação";
    }else{
        $tipo = NULL;
    }
    
    # Sexo
    $sexo = $pessoal->get_sexo($idServidorPesquisado);
    if($sexo == "Masculino"){
        $detalhe = "do servidor";
    }else{
        $detalhe = "da servidora";
    }
        
    $texto[] = "Encaminhamos a solicitação$tipo da Redução de Carga Horária $detalhe <b>".strtoupper($nomeServidor)."</b>,"
                 . " ID nº $idFuncional, $cargoEfetivo, enquanto responsável por pessoa portadora de necessidades especiais com base na Resolução n° 3.004 de 20/05/2003.";
    
    $texto[] = "Ressaltamos a devida antecedência do pedido, uma vez que a concessão do benefício finda em $dtTermino, conforme publicação no DOERJ de $dtPublicacao, anexada às fls. $folha do p.p.";
    
    $texto[] = "Desta forma, encaminhamos o presente para providências cabíveis.";
    
    # despacho
    $despacho = new Despacho();
    $despacho->set_destino($destino);
    $despacho->set_data($data);
    $despacho->set_texto($texto);
    
    # Pega o idServidor do gerente GRH
    $idGerente = $pessoal->get_gerente(66);
    $gerente = $pessoal->get_nome($idGerente);
    $cargo = $pessoal->get_cargoComissaoDescricao($idGerente);
    $idFuncional = $pessoal->get_idFuncional($idGerente);
    
    $despacho->set_origemNome($gerente);
    $despacho->set_origemDescricao($cargo);
    $despacho->set_origemIdFuncional($idFuncional);
    
    $despacho->set_saltoRodape(1);
    $despacho->show();
                
    # Grava o log da visualização do relatório
    $data = date("Y-m-d H:i:s");
    $atividades = 'Visualizou O Despacho para a Perícia de redução da carga horária: ';
    $tipoLog = 4;
    $intra->registraLog($idUsuario,$data,$atividades,"tbreducao",$id,$tipoLog,$idServidorPesquisado);                
    
    $page->terminaPagina();
}