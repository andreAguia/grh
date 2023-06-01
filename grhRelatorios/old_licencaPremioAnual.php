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

    $select = 'SELECT tbservidor.idfuncional,
                      tbpessoa.nome,
                      tblicencapremio.dtInicial,
                      tblicencapremio.numDias,
                      ADDDATE(tblicencapremio.dtInicial,tblicencapremio.numDias-1),
                      tbservidor.processoPremio,
                      tbpublicacaopremio.dtPublicacao,
                      tbpublicacaopremio.dtInicioPeriodo,
                      tbpublicacaopremio.dtFimPeriodo,
                      MONTH(tblicencapremio.dtInicial)
                 FROM tbservidor LEFT JOIN tbpessoa USING (idPessoa)
                                 LEFT JOIN tblicencapremio USING (idServidor)
                                 LEFT JOIN tbpublicacaopremio USING (idPublicacaoPremio)
                WHERE tbservidor.situacao = 1
                  AND YEAR(tblicencapremio.dtInicial) = ' . $relatorioAno . '   
             ORDER BY 3';

    $result = $pessoal->select($select);

    $relatorio = new Relatorio();
    $relatorio->set_titulo('Relatório Anual de ' . $pessoal->get_licencaNome(6));
    $relatorio->set_subtitulo("Servidores com a Data Inicial da Licença em " . $relatorioAno);
    #$relatorio->set_tituloLinha3($relatorioAno);
    #$relatorio->set_subtitulo('Ordem de Data Inicial da Licença');

    $relatorio->set_label(array('IdFuncional', 'Nome', 'Data Inicial', 'Dias', 'Data Final', 'Poocesso', 'Publicação', 'Início do Período', 'Fim do Período', 'Mês'));
    #$relatorio->set_width(array(15,40,15,5,15,0));
    $relatorio->set_align(array('center', 'left'));
    $relatorio->set_funcao(array(null, null, "date_to_php", null, "date_to_php", null, "date_to_php", "date_to_php", "date_to_php", "get_nomeMes"));

    $relatorio->set_conteudo($result);
    $relatorio->set_numGrupo(9);
    $relatorio->set_botaoVoltar(false);
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