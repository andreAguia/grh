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
    $page->iniciaPagina();

    # Verifica se tem PAD
    $penalidades = new Penalidade();

    if ($penalidades->temPADFaltas($idServidorPesquisado)) {
        # Cabeçalho da Página
        AreaServidor::cabecalho();
        br(5);

        # Limita o tamanho da tela
        $grid = new Grid("center");
        $grid->abreColuna(8);

        p("Atenção!!", "vermelho", "center");
        br(3);
        p("Este servidor tem PADs (Processo Administrativo Disciplinar) (penalidades) referentes a faltas cadastradas no sistema.", "center", "f16");
        br();
        p("Portanto, não é possível emitir esta Declaração.", "center", "f16");

        $grid->fechaColuna();
        $grid->fechaGrid();
    } else {

        # Pega o número de faltas
        $faltas = new Faltas();
        $numfaltas = $faltas->getNumFaltasServidor($idServidorPesquisado);

        # Exibe alereta de faltas
        if ($numfaltas > 0) {
            alert("ATENÇÃO !! Este Servidor TEM {$numfaltas} FALTA(S) cadastrada(s) no sistema!");
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
        $dec = new Declaracao();
        $dec->set_carimboCnpj(true);
        $dec->set_assinatura(true);
        $dec->set_data(date("d/m/Y"));
        $dec->set_texto("{$identificacao} não está respondendo a inquérito administrativo por"
                . " comunicação de faltas nesta Universidade Estadual do Norte Fluminense Darcy Ribeiro.");

        $dec->set_saltoRodape(10);

        # Exibe aviso de que tem faltas
        if ($numfaltas > 0) {
            $dec->set_aviso("ATENÇÃO !! Este Servidor TEM {$numfaltas} FALTA(S) cadastrada(s) no sistema!");
        }

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