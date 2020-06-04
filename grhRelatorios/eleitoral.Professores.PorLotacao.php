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
$acesso = Verifica::acesso($idUsuario, 2);

if ($acesso) {
    # Conecta ao Banco de Dados
    $servidor = new Pessoal();

    # Começa uma nova página
    $page = new Page();
    $page->iniciaPagina();

    # Pega os parâmetros dos relatórios
    $relatorioLotacao = post('lotacao', 'CBB');

    ######

    $select = 'SELECT tbservidor.idFuncional,
                     tbpessoa.nome,
                     concat(IFnull(tblotacao.DIR,"")," - ",IFnull(tblotacao.GER,"")) lotacao,                     
                     tbservidor.idServidor
                FROM tbservidor LEFT JOIN tbpessoa USING (idPessoa)
                                     JOIN tbhistlot USING (idServidor)
                                     JOIN tblotacao ON (tbhistlot.lotacao=tblotacao.idLotacao)
                                LEFT JOIN tbcargo USING (idCargo)
                                     JOIN tbtipocargo USING (idTipoCargo) 
               WHERE tbservidor.situacao = 1
                 AND (idPerfil = 1 OR idPerfil = 4)
                 AND tbtipocargo.tipo = "Professor"
                 AND tbhistlot.data = (select max(data) from tbhistlot where tbhistlot.idServidor = tbservidor.idServidor)';
    # Lotação
    if (!is_null($relatorioLotacao)) {
        # Verifica se o que veio é numérico
        if (is_numeric($relatorioLotacao)) {
            $select .= ' AND (tblotacao.idlotacao = "' . $relatorioLotacao . '")';
            $titulo = $servidor->get_nomeLotacao($relatorioLotacao);
        } else { # senão é uma diretoria genérica
            $select .= ' AND (tblotacao.DIR = "' . $relatorioLotacao . '")';
            $titulo = "Lotação: " . $relatorioLotacao . "<br/>";
        }
    }

    $select .= ' ORDER BY tblotacao.GER, tbpessoa.nome';

    $result = $servidor->select($select);

    $relatorio = new Relatorio();
    $relatorio->set_titulo('Relatório de Professores Ativos');
    $relatorio->set_tituloLinha2($titulo);
    $relatorio->set_subtitulo('Ordenados pela Lotação e Nome');
    $relatorio->set_label(array('IdFuncional', 'Nome', 'Lotação', 'Cargo'));
    #$relatorio->set_width(array(10,30,30,0,10,10,10));
    $relatorio->set_align(array("center", "left", "left", "left"));
    #$relatorio->set_funcao(array(null,null,null,null,null,"date_to_php"));

    $relatorio->set_classe(array(null, null, null, "pessoal"));
    $relatorio->set_metodo(array(null, null, null, "get_CargoRel"));

    $relatorio->set_conteudo($result);

    # Dados da combo lotacao
    $lotacao = $servidor->select('(SELECT idlotacao, concat(IFnull(tblotacao.DIR,"")," - ",IFnull(tblotacao.GER,"")," - ",IFnull(tblotacao.nome,"")) lotacao
                                              FROM tblotacao
                                             WHERE ativo) UNION (SELECT distinct DIR, DIR
                                              FROM tblotacao
                                             WHERE ativo)
                                          ORDER BY 2');

    $relatorio->set_formCampos(array(
        array('nome' => 'lotacao',
            'label' => 'Lotação',
            'tipo' => 'combo',
            'array' => $lotacao,
            'col' => 12,
            'size' => 10,
            'padrao' => $relatorioLotacao,
            'title' => 'Filtra por Lotação.',
            'onChange' => 'formPadrao.submit();',
            'linha' => 1)));

    $relatorio->set_formFocus('ano');
    $relatorio->set_formLink('?');
    $relatorio->show();

    $page->terminaPagina();
}