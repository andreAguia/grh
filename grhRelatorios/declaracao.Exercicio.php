<?php

/**
 * Sistema GRH
 * 
 * Relatório
 *   
 * By Alat
 */
# Inicia as variáveis que receberão as sessions
$idUsuario = null;
$idServidorPesquisado = null;

# Configuração
include ("../grhSistema/_config.php");

# Permissão de Acesso
$acesso = Verifica::acesso($idUsuario, [1, 2, 12]);

if ($acesso) {
    # Conecta ao Banco de Dados
    $pessoal = new Pessoal();

    # Servidor
    $nomeServidor = $pessoal->get_nome($idServidorPesquisado);
    $idFuncional = $pessoal->get_idFuncional($idServidorPesquisado);
    $cargoEfetivo = $pessoal->get_cargoCompleto($idServidorPesquisado, false);
    $sexo = $pessoal->get_sexo($idServidorPesquisado);
    $idPerfil = $pessoal->get_idPerfil($idServidorPesquisado);
    $dtAdmissao = dataExtenso($pessoal->get_dtAdmissao($idServidorPesquisado));
    $lotacao = $pessoal->get_lotacaoSimples($idServidorPesquisado);
    $idLotacao = $pessoal->get_idLotacao($idServidorPesquisado);
    $nomeLotacao = $pessoal->get_nomeLotacao2($idLotacao);
    
    if($idPerfil == 2){
        $cargoEfetivo = "exercendo a função equivalente ao {$cargoEfetivo}";
    }

    # Altera parte do texto de acordo com o sexo (gênero) do servidor
    if ($sexo == "Masculino") {
        $texto1 = "o servidor";
    } else {
        $texto1 = "a servidora";
    }

    # Começa uma nova página
    $page = new Page();
    $page->iniciaPagina();

    # Monta a Declaração
    $dec = new Declaracao();
    $dec->set_carimboCnpj(true);
    $dec->set_assinatura(true);
    
    $dec->set_data(date("d/m/Y"));

    $dec->set_texto("Declaro para os devidos fins, que {$texto1} <b>" . strtoupper($nomeServidor) . "</b>,"
            . " ID funcional nº {$idFuncional}, é servidor(a) desta Universidade admitido através de concurso"
            . " público na data de {$dtAdmissao} para o cargo de {$cargoEfetivo}, desempenhando suas"
            . " atribuições no(a) {$nomeLotacao}.");

    $dec->set_saltoRodape(10);    
    $dec->show();

    # Grava o log da visualização do relatório
    $data = date("Y-m-d H:i:s");
    $atividades = 'Visualizou a declaração de efetivo exercício';
    $tipoLog = 4;
    $intra->registraLog($idUsuario, $data, $atividades, null, null, $tipoLog, $idServidorPesquisado);

    $page->terminaPagina();
}else{
    echo "Ocorreu um erro !!";
}