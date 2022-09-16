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
    $pessoal = new Pessoal();

    # Começa uma nova página
    $page = new Page();
    $page->iniciaPagina();

    # Pega os parâmetros dos relatórios
    $comissao = post('comissao', get('comissao'));
    if ($comissao == '*') {
        $comissao = null;
    }

    ######

    $servidor = new Pessoal();
    $select = 'SELECT distinct tbservidor.idFuncional,
                     tbpessoa.nome,
                     tbservidor.idServidor,
                     tbcomissao.idComissao,
                     tbnacionalidade.nacionalidade,
                     tbdocumentacao.CPF,
                     CONCAT(tbdocumentacao.identidade," (",tbdocumentacao.orgaoId,")"),
                     concat(tbtipocomissao.simbolo," - ",tbtipocomissao.descricao," - ",tbtipocomissao.vagas," vaga(s)") comissao
                FROM tbservidor LEFT JOIN tbpessoa ON (tbservidor.idPessoa = tbpessoa.idPessoa) 
                                LEFT JOIN tbnacionalidade ON (tbpessoa.nacionalidade = tbnacionalidade.idNacionalidade)
                                LEFT JOIN tbdocumentacao ON (tbpessoa.idPessoa = tbdocumentacao.idPessoa)
                                LEFT JOIN tbcomissao ON(tbservidor.idServidor = tbcomissao.idServidor)
                                LEFT JOIN tbdescricaocomissao USING (idDescricaoComissao)
                                     JOIN tbtipocomissao ON(tbcomissao.idTipoComissao=tbtipocomissao.idTipoComissao)
              WHERE tbservidor.situacao = 1
                AND tbcomissao.tipo <> 3
                AND tbcomissao.dtExo is null';

    # cargo em comissão
    if (!is_null($comissao)) {
        $select .= ' AND tbtipocomissao.idTipoComissao = ' . $comissao;
    }

    $select .= ' ORDER BY 8, tbpessoa.nome';

    $result = $servidor->select($select);

    $relatorio = new Relatorio();
    $relatorio->set_titulo('Relatório de Servidores com Cargos em Comissão');
    $relatorio->set_subtitulo('Agrupados por Cargo - Ordenados pelo Nome');
    $relatorio->set_label(['IdFuncional', 'Nome', 'Lotação', 'Descrição', 'Nacionalidade', 'CPF', 'RG', ""]);
    $relatorio->set_align(["center", "left", "left", "left"]);
    $relatorio->set_classe([null, null, "Pessoal", "CargoComissao"]);
    $relatorio->set_metodo([null, null, "get_Lotacao", "get_descricaoCargo"]);
    $relatorio->set_conteudo($result);
    $relatorio->set_numGrupo(7);
    #$relatorio->set_botaoVoltar('../sistema/areaServidor.php');

    $result = $pessoal->select('SELECT tbtipocomissao.idTipoComissao,concat(tbtipocomissao.simbolo," - ",tbtipocomissao.descricao)
                                              FROM tbtipocomissao
                                              WHERE ativo
                                          ORDER BY tbtipocomissao.simbolo');
    array_unshift($result, array('*', '-- Todos --'));

    $relatorio->set_formCampos(array(
        array('nome' => 'comissao',
            'label' => 'Cargo em Comissão:',
            'tipo' => 'combo',
            'array' => $result,
            'size' => 30,
            'padrao' => $comissao,
            'title' => 'Filtra por Cargo em Comissão',
            'onChange' => 'formPadrao.submit();',
            'linha' => 1)));

    $relatorio->set_formFocus('comissao');
    $relatorio->set_formLink('?');

    $relatorio->show();

    $page->terminaPagina();
}