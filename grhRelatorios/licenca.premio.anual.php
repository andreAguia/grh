<?php

/**
 * Sistema GRH
 * 
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
    $servidor = new Pessoal();

    # Começa uma nova página
    $page = new Page();
    $page->iniciaPagina();

    # Pega os parâmetros dos relatórios
    $relatorioAno = post('ano', date('Y'));

    ######

    $select = 'SELECT tbservidor.idFuncional,
                      tbpessoa.nome,
                      dtInicial,
                      tblicencapremio.numdias,
                      ADDDATE(dtInicial,tblicencapremio.numDias-1),
                      IFNULL(CONCAT(DATE_FORMAT(dtInicioPeriodo, "%d/%m/%Y")," - ",DATE_FORMAT(dtFimPeriodo, "%d/%m/%Y")),"---"),
                      tbpublicacaopremio.dtPublicacao,
                      idLicencaPremio
                 FROM tbservidor JOIN tbpessoa USING (idPessoa)
                                 LEFT JOIN tblicencapremio  USING (idServidor)
                                 LEFT JOIN tbpublicacaopremio USING (idPublicacaoPremio)
                                 JOIN tbperfil USING (idPerfil)
                WHERE tbperfil.tipo <> "Outros"
                  AND situacao = 1
                  AND (((YEAR(tblicencapremio.dtInicial) =  ' . $relatorioAno . ') OR (YEAR(ADDDATE(tblicencapremio.dtInicial,tblicencapremio.numDias-1)) = ' . $relatorioAno . '))
                   OR ((YEAR(tblicencapremio.dtInicial) <  ' . $relatorioAno . ') AND (YEAR(ADDDATE(tblicencapremio.dtInicial,tblicencapremio.numDias-1)) > ' . $relatorioAno . ')))
             ORDER BY tbpessoa.nome';

    $result = $servidor->select($select);

    $relatorio = new Relatorio();
    $relatorio->set_titulo('Relatório Anual de Fruição de Licença Prêmio');
    $relatorio->set_tituloLinha2($relatorioAno);
    $relatorio->set_subtitulo('Ordenados pelo Nome');
    $relatorio->set_width([10, 30, 10, 10, 5, 25, 10]);
    $relatorio->set_label(['IdFuncional', 'Nome', 'Data Inicial', 'Dias', 'Data Final', 'Período', 'Publicação']);
    $relatorio->set_align(["center", "left"]);
    $relatorio->set_funcao([null, null, "date_to_php", null, "date_to_php", null, "date_to_php"]);
    $relatorio->set_bordaInterna(true);
    $relatorio->set_conteudo($result);

    # Cria um array com os anos possíveis
    $anoInicial = 1999;
    $anoAtual = date('Y');
    $anoExercicio = arrayPreenche($anoInicial, $anoAtual + 2, "d");

    $relatorio->set_formCampos(array(
        array('nome' => 'ano',
            'label' => 'Ano:',
            'tipo' => 'combo',
            'array' => $anoExercicio,
            'size' => 4,
            'title' => 'Ano',
            'col' => 3,
            'padrao' => $relatorioAno,
            'onChange' => 'formPadrao.submit();',
            'linha' => 1)));

    $relatorio->show();
    $page->terminaPagina();
}