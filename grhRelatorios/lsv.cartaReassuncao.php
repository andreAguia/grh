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
    
    # Pega o nome e cargo do chefe
    $array = unserialize(get('array'));
    $chefe = $array[0];
    $cargo = $array[1];
   
    # Começa uma nova página
    $page = new Page();			
    $page->iniciaPagina();

    # Pega os Dados
    $dados = $lsv->get_dados($id);

    # Da Licença
    $dtRetorno = dataExtenso(date_to_php($dados['dtRetorno']));
    $dtTermino = dataExtenso(date_to_php($dados['dtTermino']));
    $dtPublicacao = dataExtenso(date_to_php($dados['dtPublicacao']));
    $pgPublicacao = $dados['pgPublicacao'];
    
    # Servidor
    $nomeServidor = $pessoal->get_nome($idServidorPesquisado);
    $idFuncional = $pessoal->get_idFuncional($idServidorPesquisado);
    $cargoServidor = $pessoal->get_cargoCompleto($idServidorPesquisado);
    $idLotacao = $pessoal->get_idLotacao($idServidorPesquisado);
    $lotacao = $pessoal->get_nomeLotacao($idLotacao);
    
    # Monta a Carta
    $carta = new Carta();
    
    $carta->set_nomeCarta("CARTA DE REASSUNÇÃO NO CARGO PÚBLICO");
    $carta->set_destinoNome($chefe);
    $carta->set_destinoSetor($cargo);
    
    $texto = 'Apresentamos a V.Sª. o(a) Sr(a) <b>'.strtoupper($nomeServidor).'</b>, cargo '
            .$cargoServidor.', para reassumir o exercício de suas atividades na '.$lotacao
            .', a contar de '.$dtRetorno.', antecipando o término do prazo da Licença Sem Vencimentos publicada no DOERJ de '.$dtPublicacao.'.';
    
    if (!vazio($pgPublicacao)){
        $texto .= ', página '.$pgPublicacao.'.';
    }else{
        $texto .= '.';
    }
    
    $carta->set_texto($texto);
    
    $carta->set_saltoRodape(3);
    $carta->show();
    
    # Grava o log da visualização do relatório
    $data = date("Y-m-d H:i:s");
    $atividades = 'Visualizou a Carta de reassunção.';
    $tipoLog = 4;
    $intra->registraLog($idUsuario,$data,$atividades,"tblicencasemvencimentos",$id,$tipoLog,$idServidorPesquisado);
    
    $page->terminaPagina();
}