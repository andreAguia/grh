<?php

/**
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

    # Começa uma nova página
    $page = new Page();
    $page->set_title("relatório de Formação");
    $page->iniciaPagina();

    # Pega o parâmetro (se tiver)
    $parametro = retiraAspas(get_session('sessionParametro'));

    if (!empty($parametro)) {
        $subTitulo = "Filtro: {$parametro}";
    }

    ######
    # Dados do Servidor
    Grh::listaDadosServidorRelatorio($idServidorPesquisado, 'Relatório de Formação');
    br();

    # Pega o idPessoa
    $idPessoa = $pessoal->get_idPessoa($idServidorPesquisado);

    $selectFormacao = "SELECT anoTerm,
                              escolaridade,
                              idFormacao,
                              CONCAT(habilitacao,'<br/>', instEnsino),
                              horas,
                              idFormacao,
                              idFormacao,
                              idFormacao
                         FROM tbformacao LEFT JOIN tbescolaridade USING (idEscolaridade)
                        WHERE idPessoa={$idPessoa}";

    if (!empty($parametro)) {
        $selectFormacao .= " AND (escolaridade LIKE '%{$parametro}%' 
                              OR habilitacao LIKE '%{$parametro}%'
                              OR instEnsino LIKE '%{$parametro}%'
                              OR anoTerm LIKE '%{$parametro}%'
                              OR horas LIKE '%{$parametro}%')";
    }

    $selectFormacao .= " ORDER BY anoTerm desc";

    $result = $pessoal->select($selectFormacao);

    $relatorio = new Relatorio();
    $relatorio->set_cabecalhoRelatorio(false);
    $relatorio->set_menuRelatorio(false);
    $relatorio->set_subTotal(true);
    $relatorio->set_totalRegistro(false);
    $relatorio->set_label(["Ano de Término", "Nível do Curso", "Marcadores", "Curso / Instituição", "Carga Horária"]);
    $relatorio->set_width([10, 15, 20, 45, 10]);
    $relatorio->set_align(["center", "center", "center", "left"]);
    $relatorio->set_classe([null, null, "Formacao"]);
    $relatorio->set_metodo([null, null, "exibeMarcador"]);

    $relatorio->set_conteudo($result);
    $relatorio->set_botaoVoltar(false);
    $relatorio->set_bordaInterna(true);
    $relatorio->set_logServidor($idServidorPesquisado);
    $relatorio->set_logDetalhe("Visualizou o Relatório da Lista de Contatos");
    $relatorio->show();

    $page->terminaPagina();
}