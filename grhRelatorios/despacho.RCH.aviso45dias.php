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
    $reducao = new ReducaoCargaHoraria();

    # Começa uma nova página
    $page = new Page();
    $page->set_title("Despacho ao Servidor");
    $page->iniciaPagina();

    # Pega os Dados
    $id = get('id');
    $dados = $reducao->get_dados($id);
    
    # Pega quem assina
    $assina = get('assina', post('assina', $intra->get_idServidor($idUsuario)));

    $dtInicio = date_to_php($dados['dtInicio']);
    $dtPublicacao = date_to_php($dados['dtPublicacao']);
    $pgPublicacao = $dados["pgPublicacao"];
    $tipo = $dados["tipo"];
    $periodo = $dados["periodo"];
    $processo = $reducao->get_numProcesso($idServidorPesquisado);
    $dtTermino = date_to_php($dados["dtTermino"]);
    
    $hoje = date("d/m/Y");
    $dias = dataDif($hoje, $dtTermino);

    # Trata a publicação
    if (vazio($pgPublicacao)) {
        $publicacao = $dtPublicacao;
    } else {
        $publicacao = "$dtPublicacao, pág. $pgPublicacao";
    }

    # despacho
    $despacho = new Despacho();
    $despacho->set_destino("Ao Servidor(a) {$pessoal->get_nome($idServidorPesquisado)}");

    $despacho->set_texto("Comunicamos que faltam <b>{$dias} dias</b> para o <b>TÉRMINO da Redução de Carga Horária</b> do(a) servidor(a) "
                . "<b>{$pessoal->get_nome($idServidorPesquisado)}</b>, ID {$pessoal->get_idFuncional($idServidorPesquisado)}, "
                . "em {$dtTermino}, conforme Ato do Reitor, publicado no DOERJ de {$dtPublicacao}, "
                . "concedendo o benefício pelo prazo de {$periodo} meses.");
    $despacho->set_texto("Atenciosamente,");
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

    $despacho->set_formFocus('assina');
    $despacho->set_formLink("?id={$id}");
    $despacho->show();
    
    # Grava o log da visualização do relatório
    $data = date("Y-m-d H:i:s");
    $atividades = "Visualizou o Despacho de RCH: Aviso 45 dias";
    $tipoLog = 4;
    $intra->registraLog($idUsuario, $data, $atividades, "tbreducao", null, $tipoLog, $idServidorPesquisado);

    $page->terminaPagina();
}