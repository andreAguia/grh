<?php

/**
 * Relatório
 *    
 * By Alat
 */
# Servidor logado 
$idUsuario = NULL;

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
    $relatorioAno = post('ano', date('Y'));

    ######

    $select = "SELECT tbservidor.idFuncional,
                      tbpessoa.nome,
                      tbservidor.idServidor,
                      tbtrabalhotre.data,
                      ADDDATE(tbtrabalhotre.data,tbtrabalhotre.dias-1),
                      tbtrabalhotre.dias,
                      tbtrabalhotre.folgas,
                      tbtrabalhotre.documento
                 FROM tbservidor LEFT JOIN tbpessoa ON (tbservidor.idPessoa = tbpessoa.idPessoa)
                                 LEFT JOIN tbtrabalhotre ON (tbservidor.idServidor = tbtrabalhotre.idServidor) 
                WHERE tbservidor.situacao = 1
                  AND YEAR(tbtrabalhotre.data) = $relatorioAno
             ORDER BY tbpessoa.nome, tbtrabalhotre.data";


    $result = $pessoal->select($select);

    $relatorio = new Relatorio();
    $relatorio->set_titulo('Relatório Anual de Afastamento para Serviço Eleitorais no TRE');
    $relatorio->set_tituloLinha2($relatorioAno);
    $relatorio->set_subtitulo('Ordenado pelo Nome do Servidor');

    $relatorio->set_label(array('IdFuncional', 'Nome', 'Lotação', 'Data Inicial', 'Data Final', 'Dias<br/>Trabalhados', 'Folgas<br/>Concedidas'));
    #$relatorio->set_width(array(10,30,20,10,10,10));
    $relatorio->set_align(array('center', 'left', 'left'));
    $relatorio->set_funcao(array(NULL, NULL, NULL, "date_to_php", "date_to_php"));
    $relatorio->set_classe(array(NULL, NULL, "pessoal"));
    $relatorio->set_metodo(array(NULL, NULL, "get_lotacao"));

    $relatorio->set_conteudo($result);
    $relatorio->set_formCampos(array(
        array('nome' => 'ano',
            'label' => 'Ano:',
            'tipo' => 'texto',
            'size' => 4,
            'col' => 3,
            'title' => 'Ano',
            'onChange' => 'formPadrao.submit();',
            'padrao' => $relatorioAno,
            'linha' => 1)));

    $relatorio->set_formFocus('ano');
    $relatorio->set_formLink('?');
    $relatorio->show();

    $page->terminaPagina();
}