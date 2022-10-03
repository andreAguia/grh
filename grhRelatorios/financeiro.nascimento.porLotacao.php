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
    $lotacao = get('lotacao', post('lotacao', 66));

    ######

    $select = "SELECT idfuncional,
                      tbpessoa.nome,
                      tbpessoa.dtNasc,
                      tbservidor.idServidor,
                      tbservidor.idServidor,
                      concat(IFnull(tblotacao.UADM,''),' - ',IFnull(tblotacao.DIR,''),' - ',IFnull(tblotacao.GER,''),' - ',IFnull(tblotacao.nome,'')) lotacao
                 FROM tbservidor JOIN tbpessoa USING  (idPessoa)
                                 JOIN tbhistlot ON (tbservidor.idServidor = tbhistlot.idServidor)
                                 JOIN tblotacao ON (tbhistlot.lotacao=tblotacao.idLotacao)
                 WHERE tbservidor.situacao = 1
                   AND idPerfil = 1
                   AND tbhistlot.data = (select max(data) from tbhistlot where tbhistlot.idServidor = tbservidor.idServidor)";

    # lotacao
    if (!is_null($lotacao)) {
        # Verifica se o que veio é numérico
        if (is_numeric($lotacao)) {
            $select .= " AND tblotacao.idlotacao = {$lotacao}";
            $subTitulo = null;
        } else { # senão é uma diretoria genérica
            $select .= " AND tblotacao.DIR = '{$lotacao}'";
            $subTitulo = $lotacao;
        }
    }

    $select .= " ORDER BY tblotacao.GER, tbpessoa.nome";

    $result = $servidor->select($select);

    $relatorio = new Relatorio();
    $relatorio->set_titulo('Relatório de Servidores Estatutarios');
    $relatorio->set_subtitulo("Com Data de Nascimento, Faixa e Nivel do Plano de Cargos");
    if (!is_null($subTitulo)) {
        $lotacaoClasse = new Lotacao();
        $relatorio->set_subtitulo2($subTitulo." - ".$lotacaoClasse->get_nomeDiretoriaSigla($subTitulo));
    }
    $relatorio->set_label(['IdFuncional', 'Nome', 'Nascimento', 'Cargo', 'Nivel Faixa Padrao', 'Lotaçao']);
    #$relatorio->set_width([10, 90]);
    $relatorio->set_align(["center", "left", "center", "left", "left"]);
    $relatorio->set_classe([null, null, null, "Pessoal", "Progressao"]);
    $relatorio->set_metodo([null, null, null, "get_cargoSimples", "get_FaixaAtual"]);
    $relatorio->set_funcao([null, null, "date_to_php"]);
    $relatorio->set_conteudo($result);
    $relatorio->set_numGrupo(5);

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
    $relatorio->set_formLink('?');

    $relatorio->show();

    $page->terminaPagina();
}