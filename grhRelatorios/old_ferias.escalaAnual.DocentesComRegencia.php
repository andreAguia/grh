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

    # Pega o valor arquivado
    $intra = new Intra();
    $dataDev = $intra->get_variavel("dataDevolucaoGrh");

    # Pega o ano exercicio
    $parametroAno = post("parametroAno", date('Y') + 1);
    $parametroPrazoInicio = post("parametroPrazoInicio");
    $parametroPrazoTermino = post("parametroPrazoTermino");
    $parametroData = post("parametroData", date_to_bd($dataDev));
    $parametroLotacao = post("parametroLotacao", "*");

    $intra->set_variavel("dataDevolucaoGrh", date_to_php($parametroData));

    ######

    $select = 'SELECT distinct tbservidor.idfuncional,
                     tbpessoa.nome,
                     concat(IFnull(tblotacao.UADM,"")," - ",IFnull(tblotacao.DIR,"")," - ",IFnull(tblotacao.GER,"")) lotacao,
                     tbservidor.dtAdmissao,
                     "' . $parametroPrazoInicio . ' - ' . $parametroPrazoTermino . '",
                     "___/___/___ (___)",
                     concat(tbservidor.idServidor,"&",' . $parametroAno . ')
                FROM tbservidor LEFT JOIN tbpessoa USING (idPessoa)
                                     JOIN tbhistlot USING (idServidor)
                                     JOIN tblotacao ON (tbhistlot.lotacao=tblotacao.idLotacao)
                                     JOIN tbcargo USING (idCargo)
                                     JOIN tbtipocargo USING (idTipoCargo)
                                LEFT JOIN tbcomissao USING (idServidor)
               WHERE tbservidor.situacao = 1
                 AND (idPerfil = 1 OR idPerfil = 2 OR idPerfil = 4)
                 AND tbhistlot.data = (select max(data) from tbhistlot where tbhistlot.idServidor = tbservidor.idServidor)
                 AND tbtipocargo.tipo = "Professor"
                 AND tbservidor.idServidor NOT IN(SELECT tbservidor.idServidor
                                                    FROM tbservidor LEFT JOIN tbpessoa USING (idPessoa)
                                                                         JOIN tbhistlot USING (idServidor)
                                                                         JOIN tblotacao ON (tbhistlot.lotacao=tblotacao.idLotacao)
                                                                         JOIN tbcargo USING (idCargo)
                                                                         JOIN tbtipocargo USING (idTipoCargo)
                                                                         JOIN tbcomissao USING (idServidor)
                                                   WHERE tbservidor.situacao = 1
                                                     AND idPerfil = 1
                                                     AND tbhistlot.data = (select max(data) from tbhistlot where tbhistlot.idServidor = tbservidor.idServidor)
                                                     AND tbtipocargo.tipo = "Professor"
                                                     AND ((CURRENT_DATE BETWEEN tbcomissao.dtNom AND tbcomissao.dtExo) OR (tbcomissao.dtExo is null)))';

    if ($parametroLotacao <> "*") {
        $select .= ' AND idLotacao = ' . $parametroLotacao;
    }

    $select .= ' ORDER BY 3,tbpessoa.nome';

    $result = $servidor->select($select);

    $relatorio = new Relatorio();
    $relatorio->set_titulo('Escala Anual de Férias de Docentes com Regência de Turma - Ano Exercício: ' . $parametroAno);
    #$relatorio->set_tituloLinha2('Ano Exercicio:'.$anoBase);

    $relatorio->set_label(['Id', 'Nome', 'Lotação', 'Admissão', 'Prazo para<br/>o Gozo', 'Início Previsto (Dias)', 'Observação']);
    $relatorio->set_width([6, 25, 0, 10, 20, 25, 25]);
    $relatorio->set_align(["center", "left", "center", "center", "center", "center", "right"]);
    $relatorio->set_funcao([null, null, null, "date_to_php", null, null, "exibeFeriasPendentes"]);
    #$relatorio->set_classe(array(null,null,null,null,null,null,"pessoal"));
    #$relatorio->set_metodo(array(null,null,null,null,null,null,"get_feriasPeriodo"));
    $relatorio->set_conteudo($result);
    $relatorio->set_numGrupo(2);
    $relatorio->set_saltoAposGrupo(true);
    $relatorio->set_bordaInterna(true);
    $relatorio->set_subTotal(false);
    $relatorio->set_totalRegistro(false);
    $relatorio->set_dataImpressao(false);
    $relatorio->set_funcaoFinalGrupo("textoEscalaFerias");
    $relatorio->set_funcaoFinalGrupoParametro(null);

    $listaLotacao = $servidor->select('SELECT idlotacao, concat(IFnull(tblotacao.DIR,"")," - ",IFnull(tblotacao.GER,"")," - ",IFnull(tblotacao.nome,"")) lotacao
                                              FROM tblotacao
                                           WHERE tblotacao.ativo  
                                          ORDER BY ativo desc,lotacao');
    array_unshift($listaLotacao, array('*', 'Todos'));

    $relatorio->set_formCampos(array(
        array('nome' => 'parametroAno',
            'label' => 'Ano:',
            'tipo' => 'texto',
            'size' => 10,
            'padrao' => $parametroAno,
            'title' => 'Ano',
            'col' => 2,
            'linha' => 1),
        array('nome' => 'parametroPrazoInicio',
            'label' => 'Inicio do Prazo:',
            'tipo' => 'texto',
            'size' => 30,
            'padrao' => $parametroPrazoInicio,
            'title' => 'Inicio do Prazo para Gozo',
            'col' => 2,
            'linha' => 1),
        array('nome' => 'parametroPrazoTermino',
            'label' => 'Término do Prazo:',
            'tipo' => 'texto',
            'size' => 30,
            'padrao' => $parametroPrazoTermino,
            'title' => 'Término do Prazo para Gozo',
            'col' => 2,
            'linha' => 1),
        array('nome' => 'parametroData',
            'label' => 'Data da Entrega:',
            'tipo' => 'data',
            'size' => 20,
            'linha' => 1,
            'col' => 3,
            'valor' => $parametroData,
            'title' => 'A data da entrega das Escalas de férias'),
        array('nome' => 'parametroLotacao',
            'label' => 'Lotação:',
            'tipo' => 'combo',
            'array' => $listaLotacao,
            'size' => 30,
            'padrao' => $parametroLotacao,
            'onChange' => 'formPadrao.submit();',
            'col' => 3,
            'linha' => 1),
        array('nome' => 'Salva',
            'label' => '&nbsp;',
            'valor' => 'Muda',
            'tipo' => 'submit',
            'size' => 10,
            'col' => 3,
            'linha' => 1),
    ));

    $relatorio->show();
    $page->terminaPagina();
}
