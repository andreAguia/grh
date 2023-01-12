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

    # Pega as férias 
    $ferias = new Ferias();
    $numDiasFruidos = $ferias->get_diasSolicitadosFruidos($idServidorPesquisado, $parametroAno);
    $feriasSolicitadas = $ferias->get_feriasSolicitadas($idServidorPesquisado, $parametroAno);
    $numSolicitacoes = count($feriasSolicitadas);

    # Inicia a variável dos dados de férias
    $dadosFerias = "com referência ao ano exercício de {$parametroAno}, ";
    $contadorFruiu = 0;
    $contadorPossui = 0;

    if ($numDiasFruidos == 0) {
        $dadosFerias = "ainda não fruiu férias referente ao exercício {$parametroAno}. ";
    } else {

        # Analisa as férias 
        foreach ($feriasSolicitadas as $item) {

            # Define as datas
            $dtInicial = date_to_php($item['dtInicial']);
            $dtTermino = addDias($dtInicial, $item['numDias']);

            # se a data está solicitada (agendada)
            if ($item['status'] == "solicitada") {
                if ($contadorPossui == 0) {
                    $dadosFerias .= "possui {$item['numDias']} dias de férias agendadas a partir de {$dtInicial}";
                }else{
                    $dadosFerias .= "{$item['numDias']} dias de férias agendadas a partir de {$dtInicial}";
                }
                
                $contadorPossui++;
            }

            # Se já foi fruída ou está sendo fruída
            if ($item['status'] == "fruída") {

                # Verifica se o servidor está em férias no momento
                if (entre(date('d/m/Y'), $dtInicial, $dtTermino)) {
                    $dadosFerias .= "está fruindo {$item['numDias']} dias de férias de {$dtInicial} a {$dtTermino}";
                } else {
                    if ($contadorFruiu == 0) {
                        $dadosFerias .= "fruiu {$item['numDias']} dias de férias no período de {$dtInicial} a {$dtTermino}";
                    } else {
                        $dadosFerias .= "{$item['numDias']} dias de férias no período de {$dtInicial} a {$dtTermino}";
                    }

                    $contadorFruiu++;
                }
            }

            # Coloca a ligação entre os textos
            if ($numSolicitacoes == 3) {
                $dadosFerias .= ", ";
            }

            if ($numSolicitacoes == 2) {
                $dadosFerias .= " e ";
            }

            if ($numSolicitacoes == 1) {
                $dadosFerias .= ".";
            }

            $numSolicitacoes--;
        }
    }

    # Monta a Declaração
    $dec = new Declaracao();
    #$dec->set_carimboCnpj(true);
    $dec->set_declaracaoNome("DECLARAÇÃO DE FÉRIAS");
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
    } else {
        $texto = "Declaro para os devidor fins que o(a) servidor(a) <b>" . strtoupper($nomeServidor) . "</b>,"
                . " ID funcional nº {$idFuncional}, admitido(a) em {$dtAdmin}, lotado(a) no(a) {$lotacao}, ";
    }

    $dec->set_texto($texto . $dadosFerias);

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