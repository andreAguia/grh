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
    $servidor = new Pessoal();

    # Começa uma nova página
    $page = new Page();
    $page->iniciaPagina();

    # Pega os parâmetros dos relatórios
    $anoBase = post('anoBase', date('Y'));

    ######

    $select = 'SELECT tbservidor.idfuncional,
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
                  AND ((YEAR(tblicenca.dtInicial)=' . $anoBase . ') OR (YEAR(ADDDATE(tblicenca.dtInicial,tblicenca.numDias-1))=' . $anoBase . '))
                  AND tblicenca.idTpLicenca = 25
                  AND tbhistlot.data = (select max(data) from tbhistlot where tbhistlot.idServidor = tbservidor.idServidor)
             ORDER BY dtInicial desc';

    $result = $servidor->select($select);

    $relatorio = new Relatorio();
    $relatorio->set_titulo('Relatório Anual de Faltas de Servidores Ativos');
    $relatorio->set_tituloLinha2($anoBase);
    $relatorio->set_subtitulo('Ordem Decrescente de Data Inicial da Falta');

    $relatorio->set_label(array('IdFuncional', 'Nome', 'Perfil', 'Lotação', 'Data Inicial', 'Dias', 'Data Final'));
    #$relatorio->set_width(array(10,40,20,10,10,10));
    $relatorio->set_align(array("center", "left", "center", "left"));
    $relatorio->set_funcao(array(null, null, null, null, "date_to_php", null, "date_to_php"));

    $relatorio->set_conteudo($result);
    $relatorio->set_formCampos(array(
        array('nome' => 'anoBase',
            'label' => 'Ano:',
            'tipo' => 'texto',
            'size' => 4,
            'title' => 'Ano',
            'padrao' => $anoBase,
            'onChange' => 'formPadrao.submit();',
            'col' => 3,
            'linha' => 1)));

    $relatorio->set_formFocus('anoBase');
    
    $relatorio->show();

    $page->terminaPagina();
}
