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

    # Pega os parâmetros
    $parametroAno = post("parametroAno", date('Y') + 1);
    $parametroData = post("parametroData", date_to_bd($dataDev));
    $parametroLotacao = post("parametroLotacao", "*");

    $intra->set_variavel("dataDevolucaoGrh", date_to_php($parametroData));

    ######

    $select = 'SELECT tbservidor.idfuncional,
                     tbpessoa.nome,
                     concat(IFnull(tblotacao.UADM,"")," - ",IFnull(tblotacao.DIR,"")," - ",IFnull(tblotacao.GER,"")) lotacao,
                     tbservidor.dtAdmissao,
                     concat(tbservidor.idServidor,"&",' . $parametroAno . '),
                     "___/___/___ (___)&nbsp;&nbsp;&nbsp;___/___/___ (___)&nbsp;&nbsp;&nbsp;___/___/___ (___)",
                     concat(tbservidor.idServidor,"&",' . $parametroAno . ')
                FROM tbservidor LEFT JOIN tbpessoa USING (idPessoa)
                                     JOIN tbhistlot USING (idServidor)
                                     JOIN tblotacao ON (tbhistlot.lotacao=tblotacao.idLotacao)
                                     JOIN tbcargo USING (idCargo)
                                     JOIN tbtipocargo USING (idTipoCargo)
                                     JOIN tbcomissao USING (idServidor)
               WHERE tbservidor.situacao = 1
                 AND (idPerfil = 1 OR idPerfil = 2 OR idPerfil = 4)
                 AND tbhistlot.data = (select max(data) from tbhistlot where tbhistlot.idServidor = tbservidor.idServidor)
                 AND tbtipocargo.tipo = "Professor"
                 AND ((CURRENT_DATE BETWEEN tbcomissao.dtNom AND tbcomissao.dtExo) OR (tbcomissao.dtExo is null))';

    if ($parametroLotacao <> "*") {
        $select .= ' AND idLotacao = ' . $parametroLotacao;
    }

    $select .= ' ORDER BY 3,tbpessoa.nome';

    $result = $servidor->select($select);

    $relatorio = new Relatorio();
    $relatorio->set_titulo('Escala Anual de Férias de Docentes Estatutarios com Cargo de Comissao - Ano Exercício: ' . $parametroAno);
    #$relatorio->set_tituloLinha2('Ano Exercicio:'.$anoBase);

    $relatorio->set_label(['Id', 'Nome', 'Lotação', 'Admissão', 'Prazo para<br/>o Gozo', 'Início Previsto (Dias)', 'Observação']);
    $relatorio->set_width([6, 25, 0, 10, 10, 35, 25]);
    $relatorio->set_align(["center", "left", "center", "center", "center", "center", "right"]);
    $relatorio->set_funcao([null, null, null, "date_to_php", "exibePrazoParaGozoEscalaFerias", null, "exibeFeriasPendentes"]);
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
            'col' => 5,
            'linha' => 1),
        array('nome' => 'Salva',
            'label' => '&nbsp;',
            'valor' => 'Muda',
            'tipo' => 'submit',
            'size' => 10,
            'col' => 2,
            'linha' => 1),
    ));

    $relatorio->show();
    $page->terminaPagina();
}
