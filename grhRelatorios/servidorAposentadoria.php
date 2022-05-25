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
    $aposentadoria = new Aposentadoria();

    # Começa uma nova página
    $page = new Page();
    $page->iniciaPagina();

    ######
    # Abre o Grid
    $grid = new Grid();
    $grid->abreColuna(12);

    # Dados do Servidor
    Grh::listaDadosServidorRelatorio($idServidorPesquisado, 'Previsão de Aposentadoria');
    br();

    # Regras Gerais    
    $aposentadoria->exibeRegras(true);
    hr();

    # Previsão
    $aposentadoria->exibePrevisao($idServidorPesquisado, true);

    # Tempo de Serviço Detalhado
    $aposentadoria->exibeTempo($idServidorPesquisado, true);
    br();

    $select = "SELECT dtInicial,
                      dtFinal,
                      dias,
                      empresa,
                      CASE empresaTipo
                         WHEN 1 THEN 'Pública'
                         WHEN 2 THEN 'Privada'
                      END,
                      CASE regime
                         WHEN 1 THEN 'Celetista'
                         WHEN 2 THEN 'Estatutário'
                      END,
                      cargo,
                      dtPublicacao,
                      processo,
                      idAverbacao
                 FROM tbaverbacao
                WHERE idServidor = $idServidorPesquisado
             ORDER BY 1 desc";

    $result = $pessoal->select($select);

    $relatorio = new Relatorio();
    $relatorio->set_subtitulo('Tempo de Serviço Averbado');
    $relatorio->set_cabecalhoRelatorio(false);
    $relatorio->set_menuRelatorio(false);
    $relatorio->set_subTotal(true);
    $relatorio->set_totalRegistro(false);
    $relatorio->set_label(array("Data Inicial", "Data Final", "Dias", "Empresa", "Tipo", "Regime", "Cargo", "Publicação", "Processo"));
    $relatorio->set_colunaSomatorio(2);
    $relatorio->set_textoSomatorio("Total de Dias Averbados:");
    $relatorio->set_exibeSomatorioGeral(false);
    $relatorio->set_align(array('center', 'center', 'center', 'left'));
    $relatorio->set_funcao(array("date_to_php", "date_to_php", null, null, null, null, null, "date_to_php"));

    $relatorio->set_conteudo($result);
    #$relatorio->set_numGrupo(2);
    $relatorio->set_botaoVoltar(false);
    $relatorio->set_logServidor($idServidorPesquisado);
    $relatorio->set_logDetalhe("Visualizou o Relatório de Tempo de Serviço Averbado");
    $relatorio->show();

    $grid->fechaColuna();
    $grid->fechaGrid();

    $page->terminaPagina();
}