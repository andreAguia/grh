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
                      idAcumulacao,
                      idAcumulacao,
                      idAcumulacao
                 FROM tbacumulacao
                WHERE idServidor = $idServidorPesquisado
             ORDER BY dtProcesso";

    $result = $pessoal->select($select);

    $relatorio = new Relatorio();
    $relatorio->set_cabecalhoRelatorio(false);
    $relatorio->set_menuRelatorio(false);
    $relatorio->set_subTotal(true);
    $relatorio->set_totalRegistro(false);

    $relatorio->set_label(["Conclusão", "Resultado", "Publicação", "Processo", "Outro Vínculo"]);
    $relatorio->set_align(["center", "center", "center", "left", "left", "left"]);
    #$relatorio->set_funcao([null, null, "date_to_php"]);
    $relatorio->set_classe([null, "Acumulacao", "Acumulacao", "Acumulacao", "Acumulacao"]);
    $relatorio->set_metodo([null, "get_resultado","exibePublicacao","exibeProcesso", "exibeDadosOutroVinculo"]);

    $relatorio->set_conteudo($result);
    #$relatorio->set_numGrupo(2);
    $relatorio->set_botaoVoltar(false);
    $relatorio->set_logServidor($idServidorPesquisado);
    $relatorio->set_logDetalhe("Visualizou o Relatório de Acumulação de Cargos Publicos");
    $relatorio->show();

    $page->terminaPagina();
}