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
    $intra = new Intra();

    # Começa uma nova página
    $page = new Page();
    $page->iniciaPagina();

    # Pega os parâmetros dos relatórios
    $lotacao = get('lotacao', post('lotacao', $servidor->get_idLotacao($intra->get_idServidor($idUsuario))));

    ######

    $select = 'SELECT DAY(tbpessoa.dtNasc),
                      tbpessoa.nome,
                      MONTH(tbpessoa.dtNasc),
                      tbservidor.idServidor,
                      tbservidor.idServidor,
                      tbservidor.idServidor
                 FROM tbpessoa LEFT JOIN tbservidor ON (tbpessoa.idPessoa = tbservidor.idPessoa)
                                    JOIN tbhistlot ON (tbservidor.idServidor = tbhistlot.idServidor)
                                    JOIN tblotacao ON (tbhistlot.lotacao=tblotacao.idLotacao)
                WHERE tbservidor.situacao = 1
                  AND tbhistlot.data = (select max(data) from tbhistlot where tbhistlot.idServidor = tbservidor.idServidor)';

    # lotacao
    $titulo = null;
    if (!is_null($lotacao)) {
        # Verifica se o que veio é numérico
        if (is_numeric($lotacao)) {
            $select .= ' AND (tblotacao.idlotacao =  "' . $lotacao . '")';
            $titulo = $servidor->get_nomeCompletoLotacao($lotacao);
        } else { # senão é uma diretoria genérica
            $select .= ' AND (tblotacao.DIR = "' . $lotacao . '")';
            $titulo = $lotacao;
        }
    }

    $select .= ' ORDER BY month(tbpessoa.dtNasc), day(tbpessoa.dtNasc)';

    $result = $servidor->select($select);

    $relatorio = new Relatorio();
    $relatorio->set_titulo('Relatório de Aniversariantes');
    $relatorio->set_tituloLinha2($titulo);
    $relatorio->set_subtitulo('Agrupados por Mês - Ordenados pelo Dia');

    $relatorio->set_label(["Dia", "Nome", "", "Lotação", "Cargo", "Perfil"]);
    $relatorio->set_align(["center", "left", "left", "left", "left"]);
    $relatorio->set_classe([null, null, null, 'Pessoal', 'Pessoal', 'Pessoal']);
    $relatorio->set_metodo([null, null, null, 'get_lotacao', 'get_cargoSimples', 'get_perfilSimples']);
    $relatorio->set_funcao([null, null, 'get_nomeMes']);

    $relatorio->set_conteudo($result);
    $relatorio->set_numGrupo(2);

    $listaLotacao = $servidor->select('(SELECT idlotacao, concat(IFnull(tblotacao.DIR,"")," - ",IFnull(tblotacao.GER,"")," - ",IFnull(tblotacao.nome,"")) lotacao
                                              FROM tblotacao
                                             WHERE ativo) UNION (SELECT distinct DIR, DIR
                                              FROM tblotacao
                                             WHERE ativo)
                                          ORDER BY 2');
    array_unshift($listaLotacao, [null, '-- Selecione a Lotação --']);

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