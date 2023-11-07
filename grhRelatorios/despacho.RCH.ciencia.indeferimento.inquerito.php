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

    # Começa uma nova página
    $page = new Page();
    $page->set_title("Despacho Ciência do Servidor");
    $page->iniciaPagina();
    
    # Pega quem assina
    $assina = get('assina', post('assina', $intra->get_idServidor($idUsuario)));

    # despacho
    $despacho = new Despacho();
    $despacho->set_destino("Prezado(a) servidor(a) {$pessoal->get_nome($idServidorPesquisado)}");
    $despacho->set_texto("Informamos o INDEFERIMENTO na solicitação por existir instaurado Inquérito Administrativo em fase de tramitação.");
    $despacho->set_texto("Solicitamos <b>ciência do(a) servidor(a)</b> para a devida conclusão do presente processo.");
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
            'linha' => 1)));

    $despacho->set_formFocus('assina');
    $despacho->set_formLink('?');
    $despacho->show();

    # Grava o log da visualização do relatório
    $data = date("Y-m-d H:i:s");
    $atividades = "Visualizou o Despacho de RCH: Ciência do Indeferimento por Inquérito";
    $tipoLog = 4;
    $intra->registraLog($idUsuario, $data, $atividades, "tbreducao", null, $tipoLog, $idServidorPesquisado);

    $page->terminaPagina();
}