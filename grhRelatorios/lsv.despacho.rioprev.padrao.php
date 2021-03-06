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
$acesso = Verifica::acesso($idUsuario, 2);

if ($acesso) {

    # Conecta ao Banco de Dados
    $pessoal = new Pessoal();
    $lsv = new LicencaSemVencimentos();

    # Pega o id
    $id = get('id');

    # Começa uma nova página
    $page = new Page();
    $page->iniciaPagina();

    # Pega os Dados
    $dados = $lsv->get_dados($id);

    # Da Licença
    $idTpLicenca = $dados['idTpLicenca'];
    $nomeLicenca = $pessoal->get_nomeTipoLicenca($idTpLicenca);

    # Servidor
    $nomeServidor = $pessoal->get_nome($idServidorPesquisado);
    $idFuncional = $pessoal->get_idFuncional($idServidorPesquisado);
    $cargoServidor = $pessoal->get_cargoCompleto($idServidorPesquisado);
    $idLotacao = $pessoal->get_idLotacao($idServidorPesquisado);
    $lotacao = $pessoal->get_nomeLotacao($idLotacao);

    # Pega o idServidor do gerente GRH
    $idGerente = $pessoal->get_gerente(66);
    $gerente = $pessoal->get_nome($idGerente);
    $cargo = $pessoal->get_cargoComissaoDescricao($idGerente);
    $idFuncional = $pessoal->get_idFuncional($idGerente);

    # Monta a CI
    $despacho = new Despacho();

    $despacho->set_origemNome($gerente);
    $despacho->set_origemDescricao($cargo);
    $despacho->set_origemIdFuncional($idFuncional);

    $despacho->set_destino("Ao RIOPREVIDÊNCIA,");
    $despacho->set_texto('Para as devidas providências administrativas.');
    $despacho->set_saltoRodape(3);
    $despacho->show();

    # Grava o log da visualização do relatório
    $data = date("Y-m-d H:i:s");
    $atividades = 'Visualizou despacho ao RIOPREVIDÊNCIA (padrão) da licença sem vencimentos.';
    $tipoLog = 4;
    $intra->registraLog($idUsuario, $data, $atividades, "tblicencasemvencimentos", $id, $tipoLog, $idServidorPesquisado);

    $page->terminaPagina();
}