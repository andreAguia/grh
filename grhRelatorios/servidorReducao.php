<?php

/**
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
    $page->iniciaPagina();

    ######
    # Dados do Servidor
    Grh::listaDadosServidorRelatorio($idServidorPesquisado, 'Relatório de Histórico de Solicitação de Redução da Carga Horária');

    # Pega o idPessoa
    $idPessoa = $pessoal->get_idPessoa($idServidorPesquisado);

    # Pega o número do processo (Quando tem)
    $reducao = new ReducaoCargaHoraria($idServidorPesquisado);
    $processo = trataNulo($reducao->get_numProcesso());

    br();
    $select = "SELECT CASE tipo
                        WHEN 1 THEN 'Inicial'
                        WHEN 2 THEN 'Renovação'
                        ELSE '--'
                      END,
                      idReducao,
                      CASE resultado
                        WHEN 1 THEN 'Deferido'
                        WHEN 2 THEN 'Indeferido'
                        WHEN 3 THEN 'Interrompido'
                      ELSE '---'
                      END,
                      idReducao,
                      idReducao,
                      ADDDATE(dtInicio,INTERVAL periodo MONTH) as dtTermino
                 FROM tbreducao
                WHERE idServidor = {$idServidorPesquisado}
             ORDER BY status, dtTermino, dtInicio";

    $result = $pessoal->select($select);

    $relatorio = new Relatorio();
    $relatorio->set_cabecalhoRelatorio(false);
    $relatorio->set_menuRelatorio(false);
    $relatorio->set_subTotal(true);
    $relatorio->set_totalRegistro(false);
    $relatorio->set_label(["Tipo", "Status", "Resultado", "Publicação", "Período"]);
    $relatorio->set_subtitulo("Processo: " . $processo);
    $relatorio->set_align(["center", "center", "center", "center", "left", "left"]);
    $relatorio->set_classe([null, "ReducaoCargaHoraria", null, "ReducaoCargaHoraria", "ReducaoCargaHoraria", "ReducaoCargaHoraria"]);
    $relatorio->set_metodo([null, "exibeStatus", null, "exibePublicacao", "exibePeriodo", "exibeCi"]);

    $relatorio->set_conteudo($result);
    $relatorio->set_botaoVoltar(false);
    $relatorio->set_logServidor($idServidorPesquisado);
    $relatorio->set_logDetalhe("Visualizou o Relatório de Histórico de Solicitação de Redução da Carga Horária");
    $relatorio->show();

    $page->terminaPagina();
}