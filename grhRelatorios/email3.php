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
    $lotacao = get('lotacao', post('lotacao'));
    if ($lotacao == "*") {
        $lotacao = null;
    }
    $subTitulo = null;

    ######

    $relatorio = new Relatorio();

    $select = 'SELECT tbservidor.idServidor,
                      CONCAT(tbtipocargo.sigla," - ",tbcargo.nome),
                      concat(IFnull(tblotacao.UADM,"")," - ",IFnull(tblotacao.DIR,"")," - ",IFnull(tblotacao.GER,"")) lotacao,
                      tbpessoa.emailUenf,
                      tbpessoa.emailPessoal,
                      tbpessoa.emailOutro
                 FROM tbservidor JOIN tbpessoa USING (idpessoa)
                                 JOIN tbcargo USING (idCargo)
                                 JOIN tbtipocargo USING (idTipoCargo)
                                 JOIN tbhistlot ON (tbservidor.idServidor = tbhistlot.idServidor)
                                 JOIN tblotacao ON (tbhistlot.lotacao=tblotacao.idLotacao)                                 
                WHERE tbservidor.situacao = 1
                  AND tbhistlot.data = (select max(data) from tbhistlot where tbhistlot.idServidor = tbservidor.idServidor)';

    if (!is_null($lotacao)) {
        # Verifica se o que veio é numérico
        if (is_numeric($lotacao)) {
            $select .= ' AND (tblotacao.idlotacao = "' . $lotacao . '")';
            $relatorio->set_numGrupo(2);
        } else { # senão é uma diretoria genérica
            $select .= ' AND (tblotacao.DIR = "' . $lotacao . '")';
            $subTitulo .= "Lotação: " . $lotacao . "<br/>";
            $relatorio->set_numGrupo(2);
        }
    }

    if (is_null($lotacao)) {
        $select .= ' ORDER BY idTipoCargo, tbtipocargo.sigla, tbcargo.nome, tbpessoa.nome';
    } else {
        $select .= ' ORDER BY DIR, GER, idTipoCargo, tbtipocargo.sigla, tbcargo.nome, tbpessoa.nome';
    }


    $result = $servidor->select($select);

    $relatorio->set_titulo('Relatório de Emails dos Servidores Ativos');
    $relatorio->set_subtitulo($subTitulo . 'Ordenados pelo Cargo');
    $relatorio->set_label(array('Servidor', 'Cargo', 'Lotação', 'E-mail UENF', 'E-mail Pessoal', 'Outro E-mail'));
    #$relatorio->set_width(array(20,20,20,20,20));
    $relatorio->set_align(array("left", "left", "left", "left", "left", "left"));
    $relatorio->set_conteudo($result);
    #$relatorio->set_bordaInterna(true);

    $relatorio->set_classe(array("pessoal"));
    $relatorio->set_metodo(array("get_nome"));

    $listaLotacao = $servidor->select('(SELECT idlotacao, concat(IFnull(tblotacao.DIR,"")," - ",IFnull(tblotacao.GER,"")," - ",IFnull(tblotacao.nome,"")) lotacao
                                              FROM tblotacao
                                             WHERE ativo) UNION (SELECT distinct DIR, DIR
                                              FROM tblotacao
                                             WHERE ativo)
                                          ORDER BY 2');

    array_unshift($listaLotacao, array('*', '-- Todos --'));

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
    $relatorio->show();

    $page->terminaPagina();
}