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
    $lotacao = get('lotacao', post('lotacao', $servidor->get_idLotacao($intra->get_idServidor($idUsuario))));

    ######

    $select = 'SELECT distinct tbservidor.idFuncional,
                     tbservidor.idServidor,
                     concat(IFnull(tblotacao.UADM,"")," - ",IFnull(tblotacao.DIR,"")," - ",IFnull(tblotacao.GER,"")," - ",IFnull(tblotacao.nome,"")) lotacao,
                     tbservidor.dtAdmissao,
                     tbservidor.dtDemissao,
                     tbservidor.idServidor,
                     tbservidor.idServidor
                FROM tbservidor LEFT JOIN tbpessoa ON (tbservidor.idPessoa = tbpessoa.idPessoa)
                                     JOIN tbhistlot ON (tbservidor.idServidor = tbhistlot.idServidor)
                                     JOIN tblotacao ON (tbhistlot.lotacao=tblotacao.idLotacao)
                                     JOIN tbperfil USING (idPerfil)     
               WHERE tbservidor.situacao = 1
                 AND tbperfil.tipo <> "Outros" ';

    # lotacao
    if (!is_null($lotacao)) {
        # Verifica se o que veio é numérico
        if (is_numeric($lotacao)) {
            $select .= ' AND (tblotacao.idlotacao =  "' . $lotacao . '")';
        } else { # senão é uma diretoria genérica
            $select .= ' AND (tblotacao.DIR = "' . $lotacao . '")';
        }
    }

    $select .= ' ORDER BY lotacao, tbpessoa.nome';

    $result = $servidor->select($select);

    $relatorio = new Relatorio();
    $relatorio->set_titulo('Histórico de Servidores Ativos por Lotação');
    $relatorio->set_subtitulo('Ordenados pelo Nome');
    $relatorio->set_label(['IdFuncional', 'Servidor', 'Lotação', 'Admissão', 'Saída', 'Situação', 'Histórico']);
    $relatorio->set_width([10, 20, 0, 10, 10, 10, 40]);
    $relatorio->set_align(["center", "left", "left", "center", "center", "center", "left"]);
    $relatorio->set_funcao([null, null, null, "date_to_php", "date_to_php"]);

    $relatorio->set_classe([null, "pessoal", null, null, null, "pessoal", "Lotacao"]);
    $relatorio->set_metodo([null, "get_nomeECargoSimplesEPerfil", null, null, null, "get_Situacao", "exibeHistoricoLotacao"]);

    $relatorio->set_conteudo($result);
    $relatorio->set_numGrupo(2);

    $listaLotacao = $servidor->select('(SELECT idlotacao, concat(IFnull(tblotacao.DIR,"")," - ",IFnull(tblotacao.GER,"")," - ",IFnull(tblotacao.nome,"")) lotacao
                                              FROM tblotacao
                                             WHERE ativo) UNION (SELECT distinct DIR, DIR
                                              FROM tblotacao
                                             WHERE ativo)
                                          ORDER BY 2');
    array_unshift($listaLotacao, array('*', '-- Selecione a Lotação --'));

    $relatorio->set_formCampos(array(
        array('nome' => 'lotacao',
            'label' => 'Lotação:',
            'tipo' => 'combo',
            'array' => $listaLotacao,
            'size' => 30,
            'padrao' => $lotacao,
            'onChange' => 'formPadrao.submit();',
            'linha' => 1)));

    $relatorio->set_formFocus('lotacao');
    $relatorio->set_formLink('?');
    $relatorio->set_bordaInterna(true);
    $relatorio->show();

    $page->terminaPagina();
}