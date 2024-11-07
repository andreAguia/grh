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
    $readaptacao = new Readaptacao();

    # Pega o id
    $id = get('id');

    # Pega a folha
    $folha = get('folha');

    # Começa uma nova página
    $page = new Page();
    $page->set_title("Despacho Perícia");
    $page->iniciaPagina();

    # Pega quem assina
    $assina = get('assina', post('assina', $intra->get_idServidor($idUsuario)));

    # Pega o tipo
    $dados = $readaptacao->get_dados($id);
    $tipo = $dados["tipo"];

    # Pega os dados da readaptacao anterior quando for renovação
    if ($tipo == 2) {
        $idAnterior = $readaptacao->get_dadosAnterior($id);
        $dtTermino = date_to_php($idAnterior["dtTermino"]);
        $dtPublicacao = date_to_php($idAnterior["dtPublicacao"]);
    }

    # do Servidor
    $nomeServidor = strtoupper($pessoal->get_nome($idServidorPesquisado));
    $idFuncional = $pessoal->get_idFuncional($idServidorPesquisado);
    $cargoEfetivo = $pessoal->get_cargoCompleto($idServidorPesquisado, false);

    $destino = "À SE/SPM,";
    $data = date("d/m/Y");

    # Sexo
    $sexo = $pessoal->get_sexo($idServidorPesquisado);
    if ($sexo == "Masculino") {
        $detalhe = "do servidor";
    } else {
        $detalhe = "da servidora";
    }

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

    $despacho->set_destino($destino);
    $despacho->set_data($data);

    # Tipo
    if ($tipo == 2) {
        $despacho->set_texto("Encaminhamos a solicitação de Renovação da Readaptação {$detalhe} <b>{$nomeServidor}</b>, ID nº {$idFuncional}, {$cargoEfetivo}, por motivo de saúde.");
        $despacho->set_texto("Ressaltamos a devida antecedência do pedido, uma vez que a concessão do benefício finda em {$dtTermino}, conforme publicação no DOERJ de {$dtPublicacao}, anexada às fls. {$folha} do p.p.");
        $despacho->set_texto("Desta forma, encaminhamos o presente para providências cabíveis.");
    } else {
        $despacho->set_texto("Encaminhamos a solicitação de Readaptação {$detalhe} <b>{$nomeServidor}</b>, ID nº {$idFuncional}, {$cargoEfetivo}, por motivo de saúde.");
    }

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
    $dataLog = date("Y-m-d H:i:s");
    $atividades = 'Visualizou O Despacho para a Perícia de readaptação.';
    $tipoLog = 4;
    $intra->registraLog($idUsuario, $dataLog, $atividades, "tbreducao", $id, $tipoLog, $idServidorPesquisado);

    $page->terminaPagina();
}