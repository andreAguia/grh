<?php

/**
 * Sistema GRH
 * 
 * Relatório
 *   
 * By Alat
 */
# Inicia as variáveis que receberão as sessions
$idUsuario = null;              # Servidor logado
$idServidorPesquisado = null; # Servidor Editado na pesquisa do sistema do GRH
# Configuração
include ("../grhSistema/_config.php");

# Permissão de Acesso
$acesso = Verifica::acesso($idUsuario, [1, 2, 12]);

if ($acesso) {

    # Conecta ao Banco de Dados
    $pessoal = new Pessoal();
    $lsv = new LicencaSemVencimentos();

    # Pega o id
    $id = get('id');

    # Pega quem assina
    $assina = get('assina', post('assina', $intra->get_idServidor($idUsuario)));

    # Começa uma nova página
    $page = new Page();
    $page->set_title("Despacho Rioprev");
    $page->iniciaPagina();

    # Pega os Dados
    $dados = $lsv->get_dados($id);

    # Da Licença
    $idTpLicenca = $dados['idTpLicenca'];
    $dtRetorno = $dados['dtRetorno'];
    $dtInicial = date_to_php($dados['dtInicial']);
    $dtPublicacao = date_to_php($dados['dtPublicacao']);
    $nomeLicenca = $pessoal->get_nomeTipoLicenca($idTpLicenca);
    $pgPublicacao = $dados['pgPublicacao'];

    # Servidor
    $nomeServidor = $pessoal->get_nome($idServidorPesquisado);
    $idFuncionalServidor = $pessoal->get_idFuncional($idServidorPesquisado);
    $cargoServidor = $pessoal->get_cargoCompleto($idServidorPesquisado);
    $idLotacao = $pessoal->get_idLotacao($idServidorPesquisado);
    $lotacao = $pessoal->get_nomeLotacao($idLotacao);

    # Pega o idServidor do gerente GRH
    $idGerente = $pessoal->get_gerente(66);
    $gerente = $pessoal->get_nome($idGerente);
    $cargo = $pessoal->get_cargoComissaoDescricao($idGerente);
    $idFuncional = $pessoal->get_idFuncional($idGerente);

    # Pega o idServidor do gerente GRH
    $idGerente = $pessoal->get_gerente(66);

    if ($assina == $idGerente) {
        $nome = $pessoal->get_nome($idGerente);
        $cargo = $pessoal->get_cargoComissaoDescricao($idGerente);
        $idFuncional = $pessoal->get_idFuncional($idGerente);
    } else {
        $nome = $pessoal->get_nome($assina);
        $cargo = $pessoal->get_cargoSimples($assina);
        $idFuncional = $pessoal->get_idFuncional($assina);
    }

    # Monta o despacho
    $despacho = new Despacho();

    $despacho->set_origemNome($nome);
    if (!empty($cargo)) {
        $despacho->set_origemDescricao($cargo);
    }
    $despacho->set_origemIdFuncional($idFuncional);

    # Trata parte do texto
    if (vazio($dtRetorno)) {
        $retorno = "conforme previsto";
    } else {
        $retorno = "antecipando";
    }

    $texto1 = "Informamos que o(a) servidor(a) $nomeServidor, $cargoServidor, ID $idFuncionalServidor, <b>reassumiu</b> o exercício de suas atividades em $lotacao "
            . ", $retorno na licença sem vencimentos que vinha fruindo desde $dtInicial, publicada no DOERJ de $dtPublicacao";

    if (!vazio($pgPublicacao)) {
        $texto1 .= ', página ' . $pgPublicacao . '.';
    } else {
        $texto1 .= '.';
    }

    $texto2 = "Sendo assim, emcaminhamos o p.p. para fins de emissão de Certidão de Situação Previdenciária (CSP) / Certidão de Regularidade Previdenciária (CRP)";

    $despacho->set_destino("Ao RIOPREVIDÊNCIA,");
    $despacho->set_texto($texto1);
    $despacho->set_texto($texto2);
    $despacho->set_saltoRodape(3);

    $listaServidor = $pessoal->select('SELECT tbservidor.idServidor,
                                              tbpessoa.nome
                                         FROM tbservidor LEFT JOIN tbpessoa ON (tbservidor.idPessoa = tbpessoa.idPessoa)
                                                         LEFT JOIN tbhistlot ON (tbservidor.idServidor = tbhistlot.idServidor)
                                                         LEFT JOIN tblotacao ON (tbhistlot.lotacao=tblotacao.idLotacao)
                                        WHERE situacao = 1
                                          AND tbhistlot.data = (select max(data) from tbhistlot where tbhistlot.idServidor = tbservidor.idServidor)
                                          AND tblotacao.idlotacao = 66
                                          ORDER BY tbpessoa.nome');

    $despacho->set_formCampos(array(
        array('nome' => 'assina',
            'label' => 'Assinatura:',
            'tipo' => 'combo',
            'array' => $listaServidor,
            'size' => 30,
            'padrao' => $assina,
            'title' => 'Quem assina o documento',
            'onChange' => 'formPadrao.submit();',
            'linha' => 1)));

    $despacho->set_formLink('?id=' . $id);

    $despacho->show();

    # Grava o log da visualização do relatório
    $data = date("Y-m-d H:i:s");
    $atividades = 'Visualizou despacho ao RIOPREVIDÊNCIA (CSP/CRP) da licença sem vencimentos.';
    $tipoLog = 4;
    $intra->registraLog($idUsuario, $data, $atividades, "tblicencasemvencimentos", $id, $tipoLog, $idServidorPesquisado);

    $page->terminaPagina();
}