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

    # Começa o texto
    $texto = "Declaro, para os devidos fins, que ";

    # O nome do servidor
    $texto .= " <b>" . strtoupper($nomeServidor) . "</b>,";

    # O id(se tiver)
    if (!empty($idFuncional)) {
        $texto .= " ID funcional nº {$idFuncional},";
    }

    # Altera parte do texto de acordo com o sexo (gênero) do servidor
    if ($sexo == "Masculino") {
        $texto .= " é servidor desta Universidade, admitido";
        $texto1 = "lotado";
        $texto2 = "O referido servidor";
        $texto3 = "ele";
    } else {
        $texto .= " é servidora desta Universidade, admitida";
        $texto1 = "lotada";
        $texto2 = "A referida servidora";
        $texto3 = "ela";
    }

    # Continua o texto
    $texto .= " através de concurso público,"
            . " na data de {$dtAdmissao}, para o cargo de {$cargoEfetivo},"
            . " com carga horária de 40 horas semanais,";

    # Verifica se está cedido para outro órgão        
    if ($idLotacao == 113) {
        $texto .= " {$texto1} no(a) {$nomeLotacao}.";
    } else {
        $texto .= " desempenhando suas atribuições no(a) {$nomeLotacao}.";
    }
    
    $texto .= " {$texto2} não se encontra em Estágio Probatório, não responde à Sindicância "
            . "e/ou Processo Administrativo Disciplinar.";
    
    $ferias = new Ferias();
    $ano = date("Y");
    $numferiasNFruidas = $ferias->get_diasNaoFruidos($idServidorPesquisado, $ano);
    if($numferiasNFruidas > 0){
        $texto .= " Informo, ainda, que {$texto3} possui {$numferiasNFruidas} dias de férias não fruídas do exercício {$ano}.";
    }

    # Começa uma nova página
    $page = new Page();
    $page->iniciaPagina();

    # Monta a Declaração
    $dec = new Declaracao();
    $dec->set_carimboCnpj(true);
    $dec->set_assinatura(true);
    $dec->set_data(date("d/m/Y"));
    $dec->set_texto($texto);
    $dec->set_saltoRodape(10);
    $dec->show();

    # Grava o log da visualização do relatório
    $data = date("Y-m-d H:i:s");
    $atividades = 'Visualizou a declaração de efetivo exercício';
    $tipoLog = 4;
    $intra->registraLog($idUsuario, $data, $atividades, null, null, $tipoLog, $idServidorPesquisado);

    $page->terminaPagina();
} else {
    echo "Ocorreu um erro !!";
}