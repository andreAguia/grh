<?php

/**
 * Relatório
 *    
 * By Alat
 */
# Servidor logado 
$idUsuario = null;

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

    # Pega os parâmetros dos relatórios
    $relatorioAno = post('ano', date('Y'));

    ######

    $select = "SELECT tbservidor.idfuncional,
                      tbpessoa.nome,
                      tbservidor.idServidor,
                      tbfolga.data,
                      ADDDATE(tbfolga.data,tbfolga.dias-1),
                      tbfolga.dias
                 FROM tbservidor LEFT JOIN tbpessoa ON (tbservidor.idPessoa = tbpessoa.idPessoa)
                                 LEFT JOIN tbfolga ON (tbservidor.idServidor = tbfolga.idServidor) 
                WHERE tbservidor.situacao = 1
                  AND YEAR(tbfolga.data) = $relatorioAno
             ORDER BY tbpessoa.nome, tbfolga.data";


    $result = $pessoal->select($select);

    $relatorio = new Relatorio();
    $relatorio->set_titulo('Relatório Anual de Folgas Fruídas do TRE');
    $relatorio->set_tituloLinha2($relatorioAno);
    $relatorio->set_subtitulo('Ordenado pelo Nome do Servidor');

    $relatorio->set_label(array('IdFuncional', 'Nome', 'Lotação', 'Data Inicial', 'Data Final', 'Dias'));
    #$relatorio->set_width(array(10,30,20,10,10,10));
    $relatorio->set_align(array('center', 'left', 'left'));
    $relatorio->set_funcao(array(null, null, null, "date_to_php", "date_to_php"));
    $relatorio->set_classe(array(null, null, "pessoal"));
    $relatorio->set_metodo(array(null, null, "get_lotacao"));

    $relatorio->set_conteudo($result);
    #$relatorio->set_numGrupo(2);
    $relatorio->set_botaoVoltar(false);
    $relatorio->set_formCampos(array(
        array('nome' => 'ano',
            'label' => 'Ano:',
            'tipo' => 'texto',
            'size' => 4,
            'title' => 'Ano',
            'onChange' => 'formPadrao.submit();',
            'padrao' => $relatorioAno,
            'col' => 3,
            'linha' => 1)));

    $relatorio->show();
    $page->terminaPagina();
}