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

    # Pega os parâmetros dos relatórios
    #$relatorioMes = post('mes',date('m'));
    $relatorioAno = post('ano', date('Y'));

    # Começa uma nova página
    $page = new Page();
    $page->iniciaPagina();

    ######

    $select = 'SELECT tbservidor.idfuncional,
                      tbpessoa.nome,
                      concat(IFnull(tblotacao.UADM,"")," - ",IFnull(tblotacao.DIR,"")," - ",IFnull(tblotacao.GER,"")) lotacao,
                      tbatestado.dtInicio,
                      tbatestado.numDias,
                      ADDDATE(tbatestado.dtInicio,tbatestado.numDias-1),
                      tbatestado.nome_medico,
                      tbatestado.especi_medico,
                      tbatestado.tipo
                 FROM tbservidor LEFT JOIN tbpessoa ON (tbservidor.idPessoa = tbpessoa.idPessoa)
                                      JOIN tbhistlot ON (tbservidor.idServidor = tbhistlot.idServidor)
                                      JOIN tblotacao ON (tbhistlot.lotacao = tblotacao.idLotacao)
                                 LEFT JOIN tbatestado ON (tbservidor.idServidor = tbatestado.idServidor) 
                WHERE tbservidor.situacao = 1
                  AND YEAR(tbatestado.dtInicio) = "' . $relatorioAno . '"
                  AND tbhistlot.data = (select max(data) from tbhistlot where tbhistlot.idServidor = tbservidor.idServidor)    
             ORDER BY tbatestado.dtInicio';


    $result = $pessoal->select($select);

    $relatorio = new Relatorio();
    $relatorio->set_titulo('Relatório Anual de Servidores com Atestado');
    $relatorio->set_tituloLinha2($relatorioAno);
    $relatorio->set_subtitulo('Ordenado pela Data Inicial do Atestado');

    $relatorio->set_label(array('Id', 'Nome', 'Lotação', 'Data Inicial', 'Dias', 'Data Final', 'Médico', 'Especialidade', 'Tipo'));
    #$relatorio->set_width(array(5,20,10,10,5,10,15,15,10));
    $relatorio->set_align(array('center', 'left', 'left', 'center', 'center', 'center', 'left', 'left'));
    $relatorio->set_funcao(array(null, null, null, "date_to_php", null, "date_to_php"));

    $relatorio->set_conteudo($result);
    #$relatorio->set_numGrupo(2);
    $relatorio->set_botaoVoltar(false);
    #$relatorio->set_bordaInterna(true);
    #$relatorio->set_cabecalho(false);
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
?>
