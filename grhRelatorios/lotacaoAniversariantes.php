<?php

/**
 * Sistema GRH
 * 
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
    $servidor = new Pessoal();

    # Começa uma nova página
    $page = new Page();
    $page->iniciaPagina();

    # Pega os parâmetros dos relatórios
    $lotacao = get('lotacao', post('lotacao'));

    # Verifica, pelo get, qual rotina chamou o relatório
    if (is_null(get('lotacao'))) {
        $exibeCombo = TRUE;
    } else {
        $exibeCombo = FALSE;
    }

    ######

    $select = 'SELECT DATE_FORMAT(tbpessoa.dtNasc,"%d/%m"),
                     tbpessoa.nome,
                     month(tbpessoa.dtNasc)
                FROM tbpessoa LEFT JOIN tbservidor ON (tbpessoa.idPessoa = tbservidor.idPessoa)
                                   JOIN tbhistlot ON (tbservidor.idServidor = tbhistlot.idServidor)
                                   JOIN tblotacao ON (tbhistlot.lotacao=tblotacao.idLotacao)
               WHERE tbservidor.situacao = 1
                 AND tbhistlot.data = (select max(data) from tbhistlot where tbhistlot.idServidor = tbservidor.idServidor)
                 AND idlotacao="' . $lotacao . '"
            ORDER BY month(tbpessoa.dtNasc), day(tbpessoa.dtNasc)';

    $result = $servidor->select($select);

    $relatorio = new Relatorio();
    $relatorio->set_titulo('Relatório de Aniversariantes');
    $relatorio->set_subtitulo($servidor->get_nomeCompletoLotacao($lotacao));
    $relatorio->set_tituloLinha2($servidor->get_nomeLotacao($lotacao));
    $relatorio->set_label(array('Data', 'Nome'));
    $relatorio->set_width(array(10, 90));
    $relatorio->set_align(array("center", "left"));
    $relatorio->set_funcao(array(NULL, NULL, "get_nomeMes"));

    $relatorio->set_conteudo($result);
    $relatorio->set_numGrupo(2);

    if ($exibeCombo) {
        $listaLotacao = $servidor->select('SELECT idlotacao, concat(IFNULL(tblotacao.UADM,"")," - ",IFNULL(tblotacao.DIR,"")," - ",IFNULL(tblotacao.GER,"")," - ",IFNULL(tblotacao.nome,"")) lotacao
                                              FROM tblotacao
                                           WHERE tblotacao.ativo  
                                          ORDER BY ativo desc,lotacao');
        array_unshift($listaLotacao, array('*', '-- Selecione a Lotação --'));

        $relatorio->set_formCampos(array(
            array('nome' => 'lotacao',
                'label' => 'Lotação:',
                'tipo' => 'combo',
                'array' => $listaLotacao,
                'size' => 30,
                'padrao' => $lotacao,
                'title' => 'Mês',
                'onChange' => 'formPadrao.submit();',
                'linha' => 1)));

        $relatorio->set_formFocus('lotacao');
        $relatorio->set_formLink('?');
    }

    $relatorio->show();

    $page->terminaPagina();
}