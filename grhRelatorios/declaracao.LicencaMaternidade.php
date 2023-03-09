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
    $dtAdmin = $pessoal->get_dtAdmissao($idServidorPesquisado);
    $lotacao = $pessoal->get_lotacao($idServidorPesquisado);
    $situacao = $pessoal->get_idSituacao($idServidorPesquisado);
    $idPerfil = $pessoal->get_idPerfil($idServidorPesquisado);
    $idLotacao = $pessoal->get_idLotacao($idServidorPesquisado);

    # Pega os dados da última licença maternidade
    $licencaMaternidade = new LicencaMaternidade();
    $dados = $licencaMaternidade->getUltima($idServidorPesquisado);
    
    $dtInicial = date_to_php($dados["dtInicial"]);    
    $dtTermino = addDias($dtInicial, $dados['numDias']);

    # Começa uma nova página
    $page = new Page();
    $page->iniciaPagina();

    # Monta a Declaração
    $dec = new Declaracao();
    #$dec->set_carimboCnpj(true);
    $dec->set_assinatura(true);
    $dec->set_data(date("d/m/Y"));

    $texto = "Declaro para os devidor fins que o(a) servidor(a) <b>" . strtoupper($nomeServidor) . "</b>,
              ID funcional nº {$idFuncional}, admitido(a) em {$dtAdmin},";

    # Somente se for estatutário
    if ($idPerfil == 1) {
        $texto .= " através de Concurso Público,";
    }

    # Se não estiver cedido para outro orgão
    if ($idLotacao <> 113) {
        $texto .= " lotado(a) no(a) {$lotacao}, ";
    }

    $texto .= " esteve afastada de suas atividades para licença de repouso à gestante no período de {$dtInicial} a {$dtTermino}.";
    $dec->set_texto($texto);

    if ($situacao == 1) {
        $dec->show();
    } else {
        # Limita o tamanho da tela
        $grid = new Grid("center");
        $grid->abreColuna(8);
        br(6);

        callout("ATENÇÃO !!! <br/>Este servidor não está ativo. A declaração não poderá ser emitida.");

        $grid->fechaColuna();
        $grid->fechaGrid();
    }

    # Grava o log da visualização do relatório
    $data = date("Y-m-d H:i:s");
    $atividades = 'Visualizou a declaração de licença maternidade';
    $tipoLog = 4;
    $intra->registraLog($idUsuario, $data, $atividades, null, null, $tipoLog, $idServidorPesquisado);

    $page->terminaPagina();
}    