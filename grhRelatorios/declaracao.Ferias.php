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
    
    # Cria um array com os anos possíveis
    $anoInicial = 1999;
    $anoAtual = date('Y');
    $ano = arrayPreenche($anoInicial, $anoAtual, "d");

    # Servidor
    $nomeServidor = $pessoal->get_nome($idServidorPesquisado);
    $idFuncional = $pessoal->get_idFuncional($idServidorPesquisado);
    $dtAdmin = $pessoal->get_dtAdmissao($idServidorPesquisado);
    $lotacao = $pessoal->get_lotacao($idServidorPesquisado);
    $situacao = $pessoal->get_idSituacao($idServidorPesquisado);
    $idPerfil = $pessoal->get_idPerfil($idServidorPesquisado);

    # Começa uma nova página
    $page = new Page();
    $page->iniciaPagina();
    
    # Pega o ano exercicio
    $parametroAno = post("parametroAno", date('Y'));
    
    # Pega as férias fruídas
    $ferias = new Ferias();
    $numDiasFruidos = $ferias->get_feriasFruidasServidorExercicio($idServidorPesquisado,$parametroAno);
    if($numDiasFruidos == 0){
        $dadosFerias = "ainda não fruiu férias referente ao exercício {$parametroAno}. ";
    }else{
        $dadosFerias = "já fruiu {$numDiasFruidos} dias de férias referentes ao exercício {$parametroAno}. ";
    }
               
    # Monta a Declaração
    $dec = new Declaracao();
    #$dec->set_carimboCnpj(true);
    $dec->set_assinatura(true);
    $dec->set_data(date("d/m/Y"));
    
    # Formulario
    $dec->set_formCampos(array(
        array('nome' => 'parametroAno',
            'label' => 'Ano Exercício:',
            'tipo' => 'combo',
            'array' => $ano,
            'size' => 4,
            'title' => 'Ano',
            'onChange' => 'formPadrao.submit();',
            'padrao' => $parametroAno,
            'col' => 3,
            'linha' => 1)));

    $dec->set_formFocus('parametroAno');
    $dec->set_formLink('?');

    # Somente se for estatutário
    if ($idPerfil == 1) {
        $texto = "Declaro para os devidor fins que o(a) servidor(a) <b>" . strtoupper($nomeServidor) . "</b>,"
                . " ID funcional nº {$idFuncional}, admitido(a) em {$dtAdmin}, através de Concurso Público, lotado(a) no(a)"
                . " {$lotacao}, ";
    }else{
        $texto = "Declaro para os devidor fins que o(a) servidor(a) <b>" . strtoupper($nomeServidor) . "</b>,"
                . " ID funcional nº {$idFuncional}, admitido(a) em {$dtAdmin}, lotado(a) no(a) {$lotacao}, ";
    }
    
    $dec->set_texto($texto.$dadosFerias);

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
    $atividades = 'Visualizou a declaração de férias';
    $tipoLog = 4;
    $intra->registraLog($idUsuario, $data, $atividades, null, null, $tipoLog, $idServidorPesquisado);

    $page->terminaPagina();
}