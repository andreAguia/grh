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

    # Começa uma nova página
    $page = new Page();
    $page->set_title("Declaração Inquérito");
    $page->iniciaPagina();

    # Verifica se tem PAD
    $penalidades = new Penalidade();
    
    # Inicia a variável de avisos
    $aviso = null;

    if ($penalidades->temPADFaltas($idServidorPesquisado)) {
        # Cabeçalho da Página
        AreaServidor::cabecalho();
        br(5);

        # Limita o tamanho da tela
        $grid = new Grid("center");
        $grid->abreColuna(8);

        $mensagem = "Este servidor tem PADs (Processo Administrativo Disciplinar) (penalidades) referentes a faltas cadastradas no sistema.";
        $mensagem .= "<br/><br/>Portanto, não é possível emitir esta Declaração.";
        calloutAlert($mensagem, " Atenção !");

        $grid->fechaColuna();
        $grid->fechaGrid();
    } else {
        
        # Monta a Declaração
        $dec = new Declaracao();

        # Pega o número de faltas
        $faltas = new Faltas();
        $numfaltas = $faltas->getNumFaltasServidor($idServidorPesquisado);
        $numduasfaltas = $faltas->getNumDiasFaltasServidor($idServidorPesquisado);

        # Exibe alereta de faltas
        if ($numfaltas > 0) {
            $aviso .= "ATENÇÃO !!<br/>Este Servidor TEM {$numduasfaltas} dias de FALTA(S) cadastrada(s) no sistema!";
            alert("ATENÇÃO !! Este Servidor TEM {$numduasfaltas} dias de FALTA(S) cadastrada(s) no sistema!");
        }

        # Pega os afastamentos
        $verifica = new VerificaAfastamentos($idServidorPesquisado);

        if ($verifica->verifica()) {
            $aviso .= "ATENÇÃO !!<br/>Este Servidor está em {$verifica->getAfastamento()} ({$verifica->getDetalhe()})";
            alert("ATENÇÃO !! Este Servidor está em {$verifica->getAfastamento()} ({$verifica->getDetalhe()})");
        }

        # Servidor
        $nomeServidor = $pessoal->get_nome($idServidorPesquisado);
        $idFuncional = $pessoal->get_idFuncional($idServidorPesquisado);
        $lotacao = $pessoal->get_lotacao($idServidorPesquisado);
        $dtAdmin = $pessoal->get_dtAdmissao($idServidorPesquisado);
        $cargoEfetivo = $pessoal->get_cargoCompletoDeclaracao($idServidorPesquisado);
        $sexo = $pessoal->get_sexo($idServidorPesquisado);
        $idPerfil = $pessoal->get_idPerfil($idServidorPesquisado);

        if ($idPerfil == 1) {
            $dtAdmin = "{$dtAdmin}, através de Concurso Público";
            $cargoEfetivo = "para o cargo de {$cargoEfetivo}";
        }

        if (!empty($cargoEfetivo)) {
            $cargoEfetivo .= ",";
        }

        # Altera parte do texto de acordo com o sexo (gênero) do servidor
        if ($pessoal->get_perfilTipo($idPerfil) == "Concursados") {
            if ($sexo == "Masculino") {
                $texto1 = "o servidor";
            } else {
                $texto1 = "a servidora";
            }
        } else {
            $texto1 = null;
        }

        # Altera o texto de acordo com o perfil do servidor
        $textoExtra = null;
        if ($idPerfil == 2) {
            $textoExtra = "cedido do(a) {$pessoal->get_orgaoCedidoFora($idServidorPesquisado)} a esta Universidade, ";
        }

        $identificacao = "Declaro, para os devidos fins, que {$texto1}"
                . " <b>" . strtoupper($nomeServidor) . "</b>,"
                . " ID funcional nº {$idFuncional}, "
                . " admitido(a) em {$dtAdmin},"
                . " {$textoExtra}{$cargoEfetivo} "
                . "lotado(a) no(a) {$lotacao}, ";

        # Monta a Declaração
        $dec->set_carimboCnpj(true);
        $dec->set_assinatura(true);
        $dec->set_data(date("d/m/Y"));
        $dec->set_texto("{$identificacao} não está respondendo a inquérito administrativo por"
                . " comunicação de faltas nesta Universidade Estadual do Norte Fluminense Darcy Ribeiro.");

        $dec->set_saltoRodape(10);
        
        $dec->set_aviso($aviso);

        $dec->show();

        # Grava o log da visualização do relatório
        $data = date("Y-m-d H:i:s");
        $atividades = 'Visualizou a declaração que NÃO responde a inquérito administrativo';
        $tipoLog = 4;
        $intra->registraLog($idUsuario, $data, $atividades, null, null, $tipoLog, $idServidorPesquisado);
    }

    $page->terminaPagina();
} else {
    echo "Ocorreu um erro !!";
}