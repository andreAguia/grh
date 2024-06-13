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
    $lotacao = new Lotacao();

    # Começa uma nova página
    $page = new Page();
    $page->iniciaPagina();

    # Pega os parâmetros dos relatórios
    $relatorioLotacao = get('relatorioLotacao', post('relatorioLotacao'));

    if ($relatorioLotacao == "*") {
        $relatorioLotacao = null;
    }

    $subTitulo = null;

    ######

    $select = 'SELECT tbservidor.idfuncional,
                     tbpessoa.nome,
                     tbdocumentacao.cpf,
                     CONCAT(tbdocumentacao.identidade," - ",IFNULL(tbdocumentacao.orgaoId,"")),
                     tbpessoa.dtNasc,
                     tbpessoa.sexo
                FROM tbservidor JOIN tbpessoa USING (idpessoa)
                           LEFT JOIN tbcargo USING (idCargo)
                                JOIN tbperfil USING (idPerfil)  
                                JOIN tbtipocargo USING (idTipoCargo)
                                JOIN tbdocumentacao USING (idPessoa)
                                JOIN tbhistlot ON (tbservidor.idServidor = tbhistlot.idServidor)
                                JOIN tblotacao ON (tbhistlot.lotacao=tblotacao.idLotacao)
               WHERE tbservidor.situacao = 1
                 AND tbtipocargo.tipo = "Professor"
                 AND tbperfil.tipo <> "Outros"
                 AND tbhistlot.data = (select max(data) from tbhistlot where tbhistlot.idServidor = tbservidor.idServidor)';

    if (!is_null($relatorioLotacao)) {
        # Verifica se o que veio é numérico
        if (is_numeric($relatorioLotacao)) {
            $select .= ' AND (tblotacao.idlotacao = "' . $relatorioLotacao . '")';
            $subTitulo = $servidor->get_nomeLotacao2($relatorioLotacao);
        } else { # senão é uma diretoria genérica
            $select .= ' AND (tblotacao.DIR = "' . $relatorioLotacao . '")';
            $subTitulo = $lotacao->get_nomeDiretoriaSigla($relatorioLotacao);
        }
    }

    $select .= ' ORDER BY tbpessoa.nome';

    $result = $servidor->select($select);

    $relatorio = new Relatorio();
    $relatorio->set_titulo('Relatório de Servidores Ativos');
    $relatorio->set_tituloLinha2("Professoress<br/>$subTitulo");
    $relatorio->set_subtitulo('Ordenados pelo Nome');

    $relatorio->set_label(['IdFuncional', 'Nome', 'CPF', 'Identidade', 'Nascimento', 'Sexo']);
    $relatorio->set_align(["center", "left"]);
    $relatorio->set_funcao([null, null, null, null, "date_to_php"]);
    $relatorio->set_conteudo($result);

    $listaLotacao = $servidor->select('(SELECT idlotacao, concat(IFnull(tblotacao.DIR,"")," - ",IFnull(tblotacao.GER,"")," - ",IFnull(tblotacao.nome,"")) lotacao
                                              FROM tblotacao
                                             WHERE ativo) UNION (SELECT distinct DIR, DIR
                                              FROM tblotacao
                                             WHERE ativo)
                                          ORDER BY 2');

    array_unshift($listaLotacao, array('*', '-- Todos --'));

    $relatorio->set_formCampos(array(
        array('nome' => 'relatorioLotacao',
            'label' => 'Lotação:',
            'tipo' => 'combo',
            'array' => $listaLotacao,
            'size' => 30,
            'padrao' => $relatorioLotacao,
            'onChange' => 'formPadrao.submit();',
            'linha' => 1)));

    $relatorio->show();
    $page->terminaPagina();
}