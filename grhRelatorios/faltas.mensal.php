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

    ######
    # Pega os parâmetros dos relatórios
    $relatorioMes = post('mes', date('m'));
    $relatorioAno = post('ano', date('Y'));

    $data = $relatorioAno . '-' . $relatorioMes . '-01';

    $select = 'SELECT tbservidor.idFuncional,
                      tbpessoa.nome,
                      tbperfil.nome,
                      concat(IFnull(tblotacao.UADM,"")," - ",IFnull(tblotacao.DIR,"")," - ",IFnull(tblotacao.GER,"")) lotacao,
                      tblicenca.dtInicial,
                      tblicenca.numDias,
                      ADDDATE(dtInicial,numDias-1)
                 FROM tbservidor LEFT JOIN tbpessoa USING (idPessoa)
                                      JOIN tbhistlot ON (tbservidor.idServidor = tbhistlot.idServidor)
                                      JOIN tblotacao ON (tbhistlot.lotacao = tblotacao.idLotacao)
                                 LEFT JOIN tblicenca ON (tbservidor.idServidor = tblicenca.idServidor)                             
                                JOIN tbperfil USING (idPerfil)
                WHERE tbservidor.situacao = 1
                  AND tbperfil.tipo <> "Outros"
                  AND tbhistlot.data = (select max(data) from tbhistlot where tbhistlot.idServidor = tbservidor.idServidor)
                  AND tblicenca.idTpLicenca = 25   
                  AND (("' . $data . '" BETWEEN dtInicial AND ADDDATE(dtInicial,numDias-1))
                   OR  (LAST_DAY("' . $data . '") BETWEEN dtInicial AND ADDDATE(dtInicial,numDias-1))
                   OR  ("' . $data . '" < dtInicial AND LAST_DAY("' . $data . '") > ADDDATE(dtInicial,numDias-1)))                       
             ORDER BY dtInicial desc';

    $result = $pessoal->select($select);

    $relatorio = new Relatorio();
    $relatorio->set_titulo('Relatório Mensal de Faltas de Servidores');
    $relatorio->set_tituloLinha2(get_nomeMes($relatorioMes) . ' / ' . $relatorioAno);
    $relatorio->set_subtitulo('Ordem Decrescente de Data Inicial da Falta');
    $relatorio->set_label(['IdFuncional', 'Nome', 'Perfil', 'Lotação', 'Data Inicial', 'Dias', 'Data Final']);
    $relatorio->set_align(["center", "left", "center", "left"]);
    $relatorio->set_funcao([null, null, null, null, "date_to_php", null, "date_to_php"]);
    $relatorio->set_conteudo($result);
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