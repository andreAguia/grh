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
    $relatorioMes = post('mes', date('m'));
    $relatorioAno = post('ano', date('Y'));

    ######

    $data = $relatorioAno . '-' . $relatorioMes . '-01';

    $select = 'SELECT tbservidor.idfuncional,
                      tbpessoa.nome,
                      concat(IFNULL(tblotacao.UADM,"")," - ",IFNULL(tblotacao.DIR,"")," - ",IFNULL(tblotacao.GER,"")) lotacao,
                      tbfolga.data,                                    
                      tbfolga.dias,
                      ADDDATE(tbfolga.data,tbfolga.dias-1)
                 FROM tbservidor LEFT JOIN tbpessoa ON (tbservidor.idPessoa = tbpessoa.idPessoa)
                                 LEFT JOIN tbfolga ON (tbservidor.idServidor = tbfolga.idServidor)                                
                                      JOIN tbhistlot ON (tbservidor.idServidor = tbhistlot.idServidor)
                                      JOIN tblotacao ON (tbhistlot.lotacao = tblotacao.idLotacao)
                WHERE tbservidor.situacao = 1
                  AND (("' . $data . '" BETWEEN tbfolga.data AND ADDDATE(tbfolga.data,tbfolga.dias-1))
                   OR  (LAST_DAY("' . $data . '") BETWEEN tbfolga.data AND ADDDATE(tbfolga.data,tbfolga.dias-1))
                   OR  ("' . $data . '" < tbfolga.data AND LAST_DAY("' . $data . '") > ADDDATE(tbfolga.data,tbfolga.dias-1)))
                  AND tbhistlot.data = (select max(data) from tbhistlot where tbhistlot.idServidor = tbservidor.idServidor)      
             ORDER BY tbfolga.data';


    $result = $pessoal->select($select);

    $relatorio = new Relatorio();
    $relatorio->set_titulo('Relatório Mensal de Folgas Fruídas do TRE');
    $relatorio->set_tituloLinha2(get_nomeMes($relatorioMes) . ' / ' . $relatorioAno);
    $relatorio->set_subtitulo('Ordem de Data Inicial da Folga');

    $relatorio->set_label(array('IdFuncional', 'Nome', 'Lotação', 'Data Inicial', 'Dias', 'Data Final'));
    #$relatorio->set_width(array(10,30,20,10,10,10));
    $relatorio->set_align(array('center', 'left', 'left'));
    $relatorio->set_funcao(array(NULL, NULL, NULL, "date_to_php", NULL, "date_to_php"));

    $relatorio->set_conteudo($result);
    #$relatorio->set_numGrupo(2);
    $relatorio->set_botaoVoltar(FALSE);
    $relatorio->set_formCampos(array(
        array('nome' => 'ano',
            'label' => 'Ano:',
            'tipo' => 'texto',
            'size' => 4,
            'title' => 'Ano',
            'onChange' => 'formPadrao.submit();',
            'padrao' => $relatorioAno,
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

    $relatorio->set_formFocus('ano');
    $relatorio->set_formLink('?');
    $relatorio->show();

    $page->terminaPagina();
}