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
    $relatorioMes = post('mes', date('m'));
    $relatorioAno = post('ano', date('Y'));

    ######

    $data = $relatorioAno . '-' . $relatorioMes . '-01';
    $select = '(SELECT tbservidor.idfuncional,
                       tbpessoa.nome,
                       tbperfil.nome,
                       CONCAT(tbtipolicenca.nome," ",IFnull(tbtipolicenca.lei,"")),
                       tblicenca.dtInicial,
                       tblicenca.numDias,
                       ADDDATE(tblicenca.dtInicial,tblicenca.numDias-1)
                  FROM tbservidor LEFT JOIN tbpessoa USING (idPessoa)
                                  LEFT JOIN tblicenca USING (idServidor)
                                  LEFT JOIN tbtipolicenca USING (idTpLicenca)
                                  LEFT JOIN tbperfil USING (idPerfil)
                WHERE tbservidor.situacao = 1
                  AND MONTH(ADDDATE(tblicenca.dtInicial,tblicenca.numDias-1)) = ' . $relatorioMes . '
                  AND YEAR(ADDDATE(tblicenca.dtInicial,tblicenca.numDias-1)) = ' . $relatorioAno . ')
             UNION (SELECT tbservidor.idfuncional,
                      tbpessoa.nome,
                      tbperfil.nome,
                      (SELECT CONCAT(tbtipolicenca.nome," ",IFnull(tbtipolicenca.lei,"")) FROM tbtipolicenca WHERE idTpLicenca = 6),
                      tblicencapremio.dtInicial,
                      tblicencapremio.numDias,
                      ADDDATE(tblicencapremio.dtInicial,tblicencapremio.numDias-1)
                 FROM tbtipolicenca, tbservidor LEFT JOIN tbpessoa USING (idPessoa)
                                 LEFT JOIN tblicencapremio USING (idServidor)
                                 LEFT JOIN tbperfil USING (idPerfil)
                WHERE tbtipolicenca.idTpLicenca = 6 AND tbservidor.situacao = 1
                  AND MONTH(ADDDATE(tblicencapremio.dtInicial,tblicencapremio.numDias-1)) = ' . $relatorioMes . '
                  AND YEAR(ADDDATE(tblicencapremio.dtInicial,tblicencapremio.numDias-1)) = ' . $relatorioAno . ')  
                  ORDER BY 7';

    $result = $pessoal->select($select);

    $relatorio = new Relatorio();
    $relatorio->set_titulo('Relatório Mensal de Término de Licença');
    $relatorio->set_tituloLinha2(get_nomeMes($relatorioMes) . ' / ' . $relatorioAno);
    $relatorio->set_subtitulo('Ordem de Data de Término da Licença');

    $relatorio->set_label(array('IdFuncional', 'Nome', 'Perfil', 'Licença', 'Data Inicial', 'Dias', 'Data Final'));
    $relatorio->set_width(array(10, 30, 10, 25, 10, 5, 10));
    $relatorio->set_align(array('center', 'left'));
    $relatorio->set_funcao(array(null, null, null, null, "date_to_php", null, "date_to_php"));

    $relatorio->set_conteudo($result);
    #$relatorio->set_numGrupo(2);
    $relatorio->set_botaoVoltar(false);
    $relatorio->set_formCampos(array(
        array('nome' => 'ano',
            'label' => 'Ano:',
            'tipo' => 'texto',
            'size' => 4,
            'title' => 'Ano',
            'padrao' => $relatorioAno,
            'onChange' => 'formPadrao.submit();',
            'col' => 3,
            'linha' => 1),
        array('nome' => 'mes',
            'label' => 'Mês',
            'tipo' => 'combo',
            'array' => $mes,
            'size' => 10,
            'padrao' => $relatorioMes,
            'title' => 'Mês do Ano.',
            'onChange' => 'formPadrao.submit();',
            'col' => 3,
            'linha' => 1)));

    $relatorio->show();
    $page->terminaPagina();
}