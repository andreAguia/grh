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

    # Começa uma nova página
    $page = new Page();
    $page->set_title("Despacho Perícia");
    $page->iniciaPagina();

    # Pega quem assina
    $assina = get('assina', post('assina', $intra->get_idServidor($idUsuario)));

    $destino = "À SE/SPM,";
    $texto = "Em devolução para arquivamento no AIMAQ, conforme solicitado.";

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
    $despacho->set_origemDescricao($cargo);
    $despacho->set_origemIdFuncional($idFuncional);
    
    $despacho->set_destino($destino);
    $despacho->set_texto($texto);
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
    $atividades = "Visualizou o despacho para a perícia paea arquivamento";
    $tipoLog = 4;
    $intra->registraLog($idUsuario, $data, $atividades, "tbreducao", null, $tipoLog, $idServidorPesquisado);

    $page->terminaPagina();
}