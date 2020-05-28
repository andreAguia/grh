<?php

/**
 * Relatório
 *    
 * By Alat
 */
# Inicia as variáveis que receberão as sessions
$idUsuario = NULL;              # Servidor logado
$idServidorPesquisado = NULL; # Servidor Editado na pesquisa do sistema do GRH
# Configuração
include ("../grhSistema/_config.php");

# Permissão de Acesso
$acesso = Verifica::acesso($idUsuario, 2);

if ($acesso) {
    # Conecta ao Banco de Dados
    $pessoal = new Pessoal();

    # Começa uma nova página
    $page = new Page();
    $page->iniciaPagina();

    ######
    # Dados do Servidor
    Grh::listaDadosServidorRelatorio($idServidorPesquisado, 'Relatório de Acumulação de Cargos Públicos');

    # Pega o idPessoa
    $idPessoa = $pessoal->get_idPessoa($idServidorPesquisado);

    # Pega o número do processo (Quando tem)
    $reducao = new ReducaoCargaHoraria($idServidorPesquisado);
    $processo = trataNulo($reducao->get_numProcesso());

    br();
    $select = "SELECT CASE conclusao
                        WHEN 1 THEN 'Pendente'
                        WHEN 2 THEN 'Resolvido'
                        ELSE '--'
                      END,
                      idAcumulacao,
                      dtProcesso,
                      processo,
                      instituicao,
                      cargo,                                    
                      matricula
                 FROM tbacumulacao
                WHERE idServidor = $idServidorPesquisado
             ORDER BY dtProcesso";

    $result = $pessoal->select($select);

    $relatorio = new Relatorio();
    $relatorio->set_cabecalhoRelatorio(FALSE);
    $relatorio->set_menuRelatorio(FALSE);
    $relatorio->set_subTotal(TRUE);
    $relatorio->set_totalRegistro(FALSE);

    $relatorio->set_label(array("Conclusão", "Resultado", "Data", "Processo", "Instituição", "Cargo", "Matrícula"));
    $relatorio->set_align(array("center", "center", "center", "left", "left", "left"));
    $relatorio->set_funcao(array(NULL, NULL, "date_to_php"));
    $relatorio->set_classe(array(NULL, "Acumulacao"));
    $relatorio->set_metodo(array(NULL, "get_resultado"));

    $relatorio->set_conteudo($result);
    #$relatorio->set_numGrupo(2);
    $relatorio->set_botaoVoltar(FALSE);
    $relatorio->set_logServidor($idServidorPesquisado);
    $relatorio->set_logDetalhe("Visualizou o Relatório de Acumulação de Cargos Publicos");
    $relatorio->show();

    $page->terminaPagina();
}