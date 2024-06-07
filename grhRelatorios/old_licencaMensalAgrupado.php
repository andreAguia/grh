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
    $relatorioLicenca = post('licenca', 800);

    ######

    $data = $relatorioAno . '-' . $relatorioMes . '-01';

    $relatorio = new Relatorio();

    if ($relatorioLicenca <> 6) {

        $select = 'SELECT tbservidor.idfuncional,
                      tbpessoa.nome,
                      tbperfil.nome,
                      idServidor,
                      CONCAT(tbtipolicenca.nome," ",IFnull(tbtipolicenca.lei,"")),
                      tblicenca.dtInicial,
                      tblicenca.numDias,
                      ADDDATE(tblicenca.dtInicial,tblicenca.numDias-1)
                 FROM tbservidor LEFT JOIN tbpessoa USING (idPessoa)
                                 LEFT JOIN tblicenca USING (idServidor)
                                 LEFT JOIN tbtipolicenca USING (idTpLicenca)
                                 LEFT JOIN tbperfil USING (idPerfil)
                WHERE tbservidor.situacao = 1
                  AND tbtipolicenca.idTpLicenca = ' . $relatorioLicenca . ' 
                  AND (("' . $data . '" BETWEEN dtInicial AND ADDDATE(tblicenca.dtInicial,tblicenca.numDias-1))
                   OR  (LAST_DAY("' . $data . '") BETWEEN dtInicial AND ADDDATE(tblicenca.dtInicial,tblicenca.numDias-1))
                   OR  ("' . $data . '" < dtInicial AND LAST_DAY("' . $data . '") > ADDDATE(tblicenca.dtInicial,tblicenca.numDias-1))) 
             ORDER BY tblicenca.dtInicial';
    } else {
        $select = 'SELECT tbservidor.idfuncional,
                     tbpessoa.nome,
                     tbperfil.nome,
                     idServidor,
                     (SELECT CONCAT(tbtipolicenca.nome," ",IFnull(tbtipolicenca.lei,"")) FROM tbtipolicenca WHERE idTpLicenca = 6),
                     tblicencapremio.dtInicial,
                     tblicencapremio.numDias,
                     ADDDATE(tblicencapremio.dtInicial,tblicencapremio.numDias-1)
                FROM tbtipolicenca,tbservidor LEFT JOIN tbpessoa USING (idPessoa)
                                              LEFT JOIN tblicencapremio USING (idServidor)
                                              LEFT JOIN tbperfil USING (idPerfil)
                WHERE tbtipolicenca.idTpLicenca = 6 AND tbservidor.situacao = 1
                  AND (("' . $data . '" BETWEEN tblicencapremio.dtInicial AND ADDDATE(tblicencapremio.dtInicial,tblicencapremio.numDias-1))
                   OR  (LAST_DAY("' . $data . '") BETWEEN tblicencapremio.dtInicial AND ADDDATE(tblicencapremio.dtInicial,tblicencapremio.numDias-1))
                   OR  ("' . $data . '" < tblicencapremio.dtInicial AND LAST_DAY("' . $data . '") > ADDDATE(tblicencapremio.dtInicial,tblicencapremio.numDias-1)))
                 ORDER BY tblicencapremio.dtInicial';
    }

    $result = $pessoal->select($select);

    #$nomeLicenca = $pessoal->get_licencaNome($relatorioLicenca);
    #$leiLicenca = $pessoal->get_licencaLei($relatorioLicenca);

    $relatorio->set_titulo('Relatório Mensal de Servidores em Licença e/ou Afastamanto');
    #$relatorio->set_tituloLinha2($nomeLicenca);
    $relatorio->set_tituloLinha2(get_nomeMes($relatorioMes) . ' / ' . $relatorioAno);
    $relatorio->set_subtitulo('Ordem Decrescente de Data Inicial da Licença');
    $relatorio->set_label(array('IdFuncional', 'Nome', 'Perfil', 'Lotaçao', 'Licença', 'Data Inicial', 'Dias', 'Data Final'));

    $relatorio->set_classe(array(null, null, null, "pessoal"));
    $relatorio->set_metodo(array(null, null, null, "get_LotacaoRel"));

    $relatorio->set_align(array('center', 'left', 'center', 'left', 'left'));
    $relatorio->set_funcao(array(null, null, null, null, null, "date_to_php", null, "date_to_php"));

    $relatorio->set_conteudo($result);
    $relatorio->set_numGrupo(4);
    $relatorio->set_botaoVoltar(false);

    # Dados da combo licena
    $licenca = $pessoal->select('SELECT idTpLicenca,
                                         CONCAT(tbtipolicenca.nome," ",IFnull(tbtipolicenca.lei,"")) as licenca
                                    FROM tbtipolicenca
                                ORDER BY 2');
    array_unshift($licenca, array('800', 'Escolha um tipo de Licença ou Afastamento'));

    $relatorio->set_formCampos(array(
        array('nome' => 'ano',
            'label' => 'Ano:',
            'tipo' => 'texto',
            'size' => 4,
            'title' => 'Ano',
            'col' => 3,
            'padrao' => $relatorioAno,
            'onChange' => 'formPadrao.submit();',
            'linha' => 1),
        array('nome' => 'mes',
            'label' => 'Mês',
            'tipo' => 'combo',
            'array' => $mes,
            'col' => 3,
            'size' => 10,
            'padrao' => $relatorioMes,
            'title' => 'Mês do Ano.',
            'onChange' => 'formPadrao.submit();',
            'linha' => 1),
        array('nome' => 'licenca',
            'label' => 'Licença/Afastamento',
            'tipo' => 'combo',
            'array' => $licenca,
            'col' => 6,
            'size' => 50,
            'padrao' => $relatorioLicenca,
            'title' => 'Filtra por Licenca ou Afastamento.',
            'onChange' => 'formPadrao.submit();',
            'linha' => 1)));

    $relatorio->set_formFocus('ano');
    
    $relatorio->show();

    $page->terminaPagina();
}