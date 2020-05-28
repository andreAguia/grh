<?php

/**
 * Relatório
 *    
 * By Alat
 */
# Inicia as variáveis que receberão as sessions
$idUsuario = NULL;              # Servidor logado
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

    # Pega os parâmetros dos relatórios
    $relatorioParametro = post('relatorioParametro');

    ######
    $select = 'SELECT tbservidor.idfuncional,
                      tbpessoa.nome,
                      tbservidor.idServidor,
                      tbservidor.idServidor,
                      tbescolaridade.escolaridade,
                      tbformacao.habilitacao
                 FROM tbservidor JOIN tbpessoa USING (idPessoa)
                                 JOIN tbformacao USING (idPessoa)
                                 JOIN tbescolaridade USING (idEscolaridade)
                 WHERE situacao = 1 AND idPerfil = 1
                   AND tbformacao.habilitacao LIKE "%' . $relatorioParametro . '%" 
                 ORDER BY tbpessoa.nome';

    $result = $pessoal->select($select);

    $relatorio = new Relatorio();
    $relatorio->set_titulo('Relatório Geral de Formação Servidores');
    $relatorio->set_subtitulo('Filtro: ' . $relatorioParametro);
    $relatorio->set_label(array("IdFuncional", "Nome", "Cargo", "Lotação", "Escolaridade", "Curso"));
    #$relatorio->set_width(array(10,80));
    $relatorio->set_align(array("center", "left", "left", "left", "left", "left"));
    $relatorio->set_classe(array(NULL, NULL, "pessoal", "pessoal"));
    $relatorio->set_metodo(array(NULL, NULL, "get_CargoRel", "get_LotacaoRel"));


    $relatorio->set_conteudo($result);
    #$relatorio->set_numGrupo(2);
    $relatorio->set_botaoVoltar(FALSE);
    $relatorio->set_logServidor($idServidorPesquisado);
    $relatorio->set_logDetalhe("Visualizou o Relatório Geral de Formação Servidores");

    $relatorio->set_formCampos(array(
        array('nome' => 'relatorioParametro',
            'label' => 'Formação:',
            'tipo' => 'texto',
            'size' => 200,
            'title' => 'Formação do Servidor',
            'onChange' => 'formPadrao.submit();',
            'padrao' => $relatorioParametro,
            'col' => 12,
            'linha' => 1)));

    $relatorio->set_formFocus('ano');
    $relatorio->set_formLink('?');
    $relatorio->show();

    $page->terminaPagina();
}