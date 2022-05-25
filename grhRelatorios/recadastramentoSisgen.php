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

    ######

    # Pega o sisgen
    $sisgen = get('sisgen', 1);

    if ($sisgen == 1) {
        $titulo = "Relatório De Docentes Ativos que Responderam REALIZEI no Anexo III do Recadastramento";
    }

    if ($sisgen == 0) {
        $titulo = "Relatório De Docentes Ativos que Responderam NÃO REALIZEI no Anexo III do Recadastramento";
    }

    if ($sisgen == 2) {
        $titulo = "Relatório De Docentes Ativos que NÃO RESPONDERAM o Anexo III do Recadastramento";
    }

    ######

    $select = 'SELECT tbservidor.idFuncional,
                    tbpessoa.nome,
                    tbservidor.idServidor,
                    concat(IFnull(tblotacao.UADM,"")," - ",IFnull(tblotacao.DIR,"")," - ",IFnull(tblotacao.GER,"")," - ",IFnull(tblotacao.nome,"")) lotacao,
                    tbrecadastramento.dataAtualizacao
               FROM tbservidor LEFT JOIN tbpessoa USING (idPessoa)
                               LEFT JOIN tbrecadastramento USING (idServidor)
                               LEFT JOIN tbperfil USING (idPerfil)
                               JOIN tbhistlot USING (idServidor)
                               JOIN tblotacao ON (tbhistlot.lotacao = tblotacao.idLotacao)
                               JOIN tbcargo USING (idCargo)
                               JOIN tbtipocargo USING (idTipoCargo)
             WHERE tbhistlot.data = (select max(data) from tbhistlot where tbhistlot.idServidor = tbservidor.idServidor)
               AND tbservidor.situacao = 1
               AND tbrecadastramento.dataAtualizacao is NOT null
               AND tbrecadastramento.sisgen = '.$sisgen;

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

    $relatorio->set_titulo($titulo);
    $relatorio->set_subtitulo('Agrupada por Lotaçao - Ordenados pelo Nome');
    $relatorio->set_label(array('IdFuncional', 'Nome', 'Cargo', 'Lotação', 'Atualizado em:'));
    $relatorio->set_align(array("center", "left", "left", "left"));
    $relatorio->set_funcao(array(null, null, null, null, "date_to_php"));

    $relatorio->set_classe(array(null, null, "pessoal"));
    $relatorio->set_metodo(array(null, null, "get_CargoRel"));

    $relatorio->set_conteudo($result);
    $relatorio->set_numGrupo(3);
    
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
            'title' => 'Mês',
            'onChange' => 'formPadrao.submit();',
            'linha' => 1)));

    $relatorio->set_formFocus('lotacao');
    $relatorio->set_formLink('?sisgen='.$sisgen);
    $relatorio->show();

    $page->terminaPagina();
}