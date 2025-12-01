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
    $intra = new Intra();
    $reducao = new LicencaSemVencimentos();

    # Começa uma nova página
    $page = new Page();
    $page->set_title("Despacho ao Servidor");
    $page->iniciaPagina();

    # Pega os Dados
    $id = get('id');
    $dados = $reducao->get_dados($id);
    
    # Pega quem assina
    $assina = get('assina', post('assina', $intra->get_idServidor($idUsuario)));

    $dtInicio = date_to_php($dados['dtInicial']);
    $dtPublicacao = date_to_php($dados['dtPublicacao']);
    $pgPublicacao = $dados["pgPublicacao"];
    $tipo = $dados["tipo"];
    $processo = $reducao->get_numProcesso($id);
    $dtTermino = date_to_php($dados["dtTermino"]);
    $lotacao = $pessoal->get_lotacaoRel($idServidorPesquisado);
    $nomeServidor = $pessoal->get_nome($idServidorPesquisado);

    $hoje = date("d/m/Y");
    $dias = dataDif($hoje, $dtTermino);

    # Trata a publicação
    if (empty($pgPublicacao)) {
        $publicacao = $dtPublicacao;
    } else {
        $publicacao = "$dtPublicacao, pág. $pgPublicacao";
    }

    # despacho
    $despacho = new Despacho();
    $despacho->set_destino("Ao {$lotacao}<br/>A/C {$nomeServidor}");
    
    $despacho->set_texto("Prezado(a) Senhor(a),");    

    $despacho->set_texto("Vimos alertar que faltam <b>{$dias} dias</b> para o "
            . "<b>TÉRMINO da Licença Sem Vencimentos</b>, conforme publicação"
            . " no DOERJ de {$publicacao}, concedendo o benefício até {$dtTermino}.");
    $despacho->set_texto("Caso haja interesse em renovar, solicitamos manifestação o "
            . "quanto antes no processo {$processo}, para que os procedimentos "
            . "administrativos sejam providenciados com a devida antecedência.");
    $despacho->set_texto("Para tanto, segue o link com as orientações:");
    $despacho->set_texto('<a href="https://uenf.br/dga/grh/gerencia-de-recursos-humanos/licencas/licenca-sem-vencimentos-opcao-contribuicao/licenca-sem-vencimentos-para-acompanhar-conjuge-companheiroa/">'
            . 'Licença Sem Vencimentos para Acompanhar Cônjuge/Companheiro(a)</a>');
    $despacho->set_texto('<a href="https://uenf.br/dga/grh/gerencia-de-recursos-humanos/licencas/licenca-sem-vencimentos-opcao-contribuicao/licenca-sem-vencimentos-para-trato-de-interesse-particular/">'
            . 'Licença Sem Vencimentos para Trato de Interesses Particulares</a>');
    $despacho->set_texto("<br/>Atenciosamente,");
    $despacho->set_saltoRodape(3);

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

    $despacho->set_origemNome($nome);
    $despacho->set_origemDescricao($cargo);
    $despacho->set_origemIdFuncional($idFuncional);

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
            'col' => 6,
            'linha' => 1)
    ));

    $despacho->set_formLink("?id={$id}");
    $despacho->show();

    # Grava o log da visualização do relatório
    $data = date("Y-m-d H:i:s");
    $atividades = "Visualizou o Despacho de LSV: Aviso 45 dias";
    $tipoLog = 4;
    $intra->registraLog($idUsuario, $data, $atividades, "tbreducao", null, $tipoLog, $idServidorPesquisado);

    $page->terminaPagina();
}