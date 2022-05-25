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
    Grh::listaDadosServidorRelatorio($idServidorPesquisado, 'Relatório de Histórico de Solicitação de Readaptação');

    # Pega o idPessoa
    $idPessoa = $pessoal->get_idPessoa($idServidorPesquisado);

    # Pega o número do processo (Quando tem)
    $reducao = new ReducaoCargaHoraria($idServidorPesquisado);
    $processo = trataNulo($reducao->get_numProcesso());

    br();
    $select = "SELECT CASE origem
                            WHEN 1 THEN 'Ex-Ofício'
                            WHEN 2 THEN 'Solicitada'
                            ELSE '--'
                        END,
                        CASE tipo
                            WHEN 1 THEN 'Inicial'
                            WHEN 2 THEN 'Renovação'
                            ELSE '--'
                        END,
                        idReadaptacao,
                        processo,
                        idReadaptacao,                                     
                        idReadaptacao,
                        idReadaptacao,
                        idReadaptacao,
                        idReadaptacao,
                        idReadaptacao
                   FROM tbreadaptacao
                  WHERE idServidor = $idServidorPesquisado
               ORDER BY status, dtInicio desc";

    $result = $pessoal->select($select);

    $relatorio = new Relatorio();
    $relatorio->set_cabecalhoRelatorio(false);
    $relatorio->set_menuRelatorio(false);
    $relatorio->set_subTotal(true);
    $relatorio->set_totalRegistro(false);
    $relatorio->set_label(array("Origem", "Tipo", "Status", "Processo", "Solicitado em:", "Pericia", "Resultado", "Publicação", "Período"));
    $relatorio->set_align(array("center", "center", "center", "center", "center", "left", "center", "center", "left", "left"));
    #$relatorio->set_funcao(array(null,"date_to_php"));

    $relatorio->set_classe(array(null, null, "Readaptacao", null, "Readaptacao", "Readaptacao", "Readaptacao", "Readaptacao", "Readaptacao"));
    $relatorio->set_metodo(array(null, null, "exibeStatus", null, "exibeSolicitacao", "exibeDadosPericia", "exibeResultado", "exibePublicacao", "exibePeriodo"));

    $relatorio->set_conteudo($result);
    #$relatorio->set_numGrupo(2);
    $relatorio->set_botaoVoltar(false);
    $relatorio->set_logServidor($idServidorPesquisado);
    $relatorio->set_logDetalhe("Visualizou o Relatório de Histórico de Solicitação de Readaptação");
    $relatorio->show();

    $page->terminaPagina();
}