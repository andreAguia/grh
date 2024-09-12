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
    $page->set_title("Servidores com Afastamento");
    $page->iniciaPagina();

    # Pega o ano exercicio
    $parametroAno = post("parametroAno", date('Y'));
    $parametroLotacao = post("parametroLotacao", "DGA");

    $relatorioDtInicial = "{$parametroAno}/01/01";
    $relatorioDtFinal = "{$parametroAno}/12/31";

    ######
    # Licença Geral
    $select = "(SELECT tbservidor.idFuncional,
                      tbservidor.idServidor,
                      tblicenca.dtInicial,
                      tblicenca.numDias,
                      ADDDATE(tblicenca.dtInicial,tblicenca.numDias-1),
                      CONCAT(tbtipolicenca.nome,IF(alta=1,' - Com Alta','')),
                      concat(IFnull(tblotacao.UADM,''),' - ',IFnull(tblotacao.DIR,''),' - ',IFnull(tblotacao.GER,''),' - ',IFnull(tblotacao.nome,'')) lotacao,
                      tbpessoa.nome
                 FROM tbservidor JOIN tbpessoa USING (idPessoa)
                                 JOIN tbhistlot USING (idServidor)
                                 JOIN tblotacao ON (tbhistlot.lotacao=tblotacao.idLotacao)
                            LEFT JOIN tblicenca USING (idServidor)
                            LEFT JOIN tbtipolicenca USING (idTpLicenca)
                WHERE situacao = 1
                  AND tbhistlot.data = (select max(data) from tbhistlot where tbhistlot.idServidor = tbservidor.idServidor)
                  AND ((tblicenca.dtInicial <= '{$relatorioDtInicial}' AND ADDDATE(tblicenca.dtInicial,tblicenca.numDias-1) >= '{$relatorioDtInicial}')
                   OR (tblicenca.dtInicial BETWEEN '{$relatorioDtInicial}' AND '{$relatorioDtFinal}')  
                   OR (ADDDATE(tblicenca.dtInicial,tblicenca.numDias-1) BETWEEN '{$relatorioDtInicial}' AND '{$relatorioDtFinal}'))";

    # lotacao
    if (!is_null($parametroLotacao)) {
        # Verifica se o que veio é numérico
        if (is_numeric($parametroLotacao)) {
            $select .= " AND (tblotacao.idlotacao = {$parametroLotacao})";
        } else { # senão é uma diretoria genérica
            $select .= " AND (tblotacao.DIR = '{$parametroLotacao}')";
        }
    }

    # Licença Prêmio               
    $select .= ") UNION (
               SELECT tbservidor.idFuncional,
                      tbservidor.idServidor,
                      tblicencapremio.dtInicial,
                      tblicencapremio.numDias,
                      ADDDATE(tblicencapremio.dtInicial,tblicencapremio.numDias-1),
                      (SELECT tbtipolicenca.nome FROM tbtipolicenca WHERE idTpLicenca = 6),
                      concat(IFnull(tblotacao.UADM,''),' - ',IFnull(tblotacao.DIR,''),' - ',IFnull(tblotacao.GER,''),' - ',IFnull(tblotacao.nome,'')) lotacao,
                      tbpessoa.nome
                 FROM tbservidor JOIN tbpessoa USING (idPessoa)
                                 JOIN tbhistlot USING (idServidor)
                                 JOIN tblotacao ON (tbhistlot.lotacao=tblotacao.idLotacao)
                            LEFT JOIN tblicencapremio  USING (idServidor)
                WHERE situacao = 1
                  AND tbhistlot.data = (select max(data) from tbhistlot where tbhistlot.idServidor = tbservidor.idServidor)
                  AND ((tblicencapremio.dtInicial <= '{$relatorioDtInicial}' AND ADDDATE(tblicencapremio.dtInicial,tblicencapremio.numDias-1) >= '{$relatorioDtInicial}')
                   OR (tblicencapremio.dtInicial BETWEEN '{$relatorioDtInicial}' AND '{$relatorioDtFinal}')  
                   OR (ADDDATE(tblicencapremio.dtInicial,tblicencapremio.numDias-1) BETWEEN '{$relatorioDtInicial}' AND '{$relatorioDtFinal}'))";

    # lotacao
    if (!is_null($parametroLotacao)) {
        # Verifica se o que veio é numérico
        if (is_numeric($parametroLotacao)) {
            $select .= " AND (tblotacao.idlotacao = {$parametroLotacao})";
        } else { # senão é uma diretoria genérica
            $select .= " AND (tblotacao.DIR = '{$parametroLotacao}')";
        }
    }

    # Férias
    $select .= ") UNION (
               SELECT tbservidor.idFuncional,
                      tbservidor.idServidor,
                      tbferias.dtInicial,
                      tbferias.numDias,
                      ADDDATE(tbferias.dtInicial,tbferias.numDias-1),
                      CONCAT('Férias ',tbferias.anoExercicio),
                      concat(IFnull(tblotacao.UADM,''),' - ',IFnull(tblotacao.DIR,''),' - ',IFnull(tblotacao.GER,''),' - ',IFnull(tblotacao.nome,'')) lotacao,
                      tbpessoa.nome
                 FROM tbservidor JOIN tbpessoa USING (idPessoa)
                                 JOIN tbhistlot USING (idServidor)
                                 JOIN tblotacao ON (tbhistlot.lotacao=tblotacao.idLotacao)
                            LEFT JOIN tbferias USING (idServidor)
                WHERE situacao = 1
                  AND tbhistlot.data = (select max(data) from tbhistlot where tbhistlot.idServidor = tbservidor.idServidor)
                  AND ((tbferias.dtInicial <= '{$relatorioDtInicial}' AND ADDDATE(tbferias.dtInicial,tbferias.numDias-1) >= '{$relatorioDtInicial}')
                   OR (tbferias.dtInicial BETWEEN '{$relatorioDtInicial}' AND '{$relatorioDtFinal}')  
                   OR (ADDDATE(tbferias.dtInicial,tbferias.numDias-1) BETWEEN '{$relatorioDtInicial}' AND '{$relatorioDtFinal}'))";

    # lotacao
    if (!is_null($parametroLotacao)) {
        # Verifica se o que veio é numérico
        if (is_numeric($parametroLotacao)) {
            $select .= " AND (tblotacao.idlotacao = {$parametroLotacao})";
        } else { # senão é uma diretoria genérica
            $select .= " AND (tblotacao.DIR = '{$parametroLotacao}')";
        }
    }

    # Faltas abonadas
    $select .= ") UNION (
               SELECT tbservidor.idFuncional,
                      tbservidor.idServidor,
                      tbatestado.dtInicio,
                      tbatestado.numDias,
                      ADDDATE(tbatestado.dtInicio,tbatestado.numDias-1),
                      'Falta Abonada',
                      concat(IFnull(tblotacao.UADM,''),' - ',IFnull(tblotacao.DIR,''),' - ',IFnull(tblotacao.GER,''),' - ',IFnull(tblotacao.nome,'')) lotacao,
                      tbpessoa.nome
                 FROM tbservidor JOIN tbpessoa USING (idPessoa)
                                 JOIN tbhistlot USING (idServidor)
                                 JOIN tblotacao ON (tbhistlot.lotacao=tblotacao.idLotacao)
                            LEFT JOIN tbatestado USING (idServidor)
                WHERE situacao = 1
                  AND tbhistlot.data = (select max(data) from tbhistlot where tbhistlot.idServidor = tbservidor.idServidor)
                  AND ((tbatestado.dtInicio <= '{$relatorioDtInicial}' AND ADDDATE(tbatestado.dtInicio,tbatestado.numDias-1) >= '{$relatorioDtInicial}')
                   OR (tbatestado.dtInicio BETWEEN '{$relatorioDtInicial}' AND '{$relatorioDtFinal}')  
                   OR (ADDDATE(tbatestado.dtInicio,tbatestado.numDias-1) BETWEEN '{$relatorioDtInicial}' AND '{$relatorioDtFinal}'))";

    # lotacao
    if (!is_null($parametroLotacao)) {
        # Verifica se o que veio é numérico
        if (is_numeric($parametroLotacao)) {
            $select .= " AND (tblotacao.idlotacao = {$parametroLotacao})";
        } else { # senão é uma diretoria genérica
            $select .= " AND (tblotacao.DIR = '{$parametroLotacao}')";
        }
    }

    # Trabalhando TRE
    $select .= ") UNION (
               SELECT tbservidor.idFuncional,
                      tbservidor.idServidor,
                      tbtrabalhotre.data,
                      tbtrabalhotre.dias,
                      ADDDATE(tbtrabalhotre.data,tbtrabalhotre.dias-1),
                      'Trabalhando no TRE',
                      concat(IFnull(tblotacao.UADM,''),' - ',IFnull(tblotacao.DIR,''),' - ',IFnull(tblotacao.GER,''),' - ',IFnull(tblotacao.nome,'')) lotacao,
                      tbpessoa.nome
                 FROM tbservidor JOIN tbpessoa USING (idPessoa)
                                 JOIN tbhistlot USING (idServidor)
                                 JOIN tblotacao ON (tbhistlot.lotacao=tblotacao.idLotacao)
                            LEFT JOIN tbtrabalhotre USING (idServidor)
                WHERE situacao = 1
                  AND tbhistlot.data = (select max(data) from tbhistlot where tbhistlot.idServidor = tbservidor.idServidor)
                  AND ((tbtrabalhotre.data <= '{$relatorioDtInicial}' AND ADDDATE(tbtrabalhotre.data,tbtrabalhotre.dias-1) >= '{$relatorioDtInicial}')
                   OR (tbtrabalhotre.data BETWEEN '{$relatorioDtInicial}' AND '{$relatorioDtFinal}')  
                   OR (ADDDATE(tbtrabalhotre.data,tbtrabalhotre.dias-1) BETWEEN '{$relatorioDtInicial}' AND '{$relatorioDtFinal}'))";

    # lotacao
    if (!is_null($parametroLotacao)) {
        # Verifica se o que veio é numérico
        if (is_numeric($parametroLotacao)) {
            $select .= " AND (tblotacao.idlotacao = {$parametroLotacao})";
        } else { # senão é uma diretoria genérica
            $select .= " AND (tblotacao.DIR = '{$parametroLotacao}')";
        }
    }

    # Folga TRE
    $select .= ") UNION (
               SELECT tbservidor.idFuncional,
                      tbservidor.idServidor,
                      tbfolga.data,
                      tbfolga.dias,
                      ADDDATE(tbfolga.data,tbfolga.dias-1),
                      'Folga TRE',
                      concat(IFnull(tblotacao.UADM,''),' - ',IFnull(tblotacao.DIR,''),' - ',IFnull(tblotacao.GER,''),' - ',IFnull(tblotacao.nome,'')) lotacao,
                      tbpessoa.nome
                 FROM tbservidor JOIN tbpessoa USING (idPessoa)
                                 JOIN tbhistlot USING (idServidor)
                                 JOIN tblotacao ON (tbhistlot.lotacao=tblotacao.idLotacao)
                            LEFT JOIN tbfolga USING (idServidor)
                WHERE situacao = 1
                  AND tbhistlot.data = (select max(data) from tbhistlot where tbhistlot.idServidor = tbservidor.idServidor)
                  AND ((tbfolga.data <= '{$relatorioDtInicial}' AND ADDDATE(tbfolga.data,tbfolga.dias-1) >= '{$relatorioDtInicial}')
                   OR (tbfolga.data BETWEEN '{$relatorioDtInicial}' AND '{$relatorioDtFinal}')  
                   OR (ADDDATE(tbfolga.data,tbfolga.dias-1) BETWEEN '{$relatorioDtInicial}' AND '{$relatorioDtFinal}'))";

    # lotacao
    if (!is_null($parametroLotacao)) {
        # Verifica se o que veio é numérico
        if (is_numeric($parametroLotacao)) {
            $select .= " AND (tblotacao.idlotacao = {$parametroLotacao})";
        } else { # senão é uma diretoria genérica
            $select .= " AND (tblotacao.DIR = '{$parametroLotacao}')";
        }
    }

    # Licença Sem Vencimentos
    $select .= ") UNION (
               SELECT tbservidor.idFuncional,
                      tbservidor.idServidor,
                      tblicencasemvencimentos.dtInicial,
                      tblicencasemvencimentos.numDias,
                      ADDDATE(tblicencasemvencimentos.dtInicial,tblicencasemvencimentos.numDias-1),
                      tbtipolicenca.nome,
                      concat(IFnull(tblotacao.UADM,''),' - ',IFnull(tblotacao.DIR,''),' - ',IFnull(tblotacao.GER,''),' - ',IFnull(tblotacao.nome,'')) lotacao,
                      tbpessoa.nome
                 FROM tbservidor JOIN tbpessoa USING (idPessoa)
                                 JOIN tbhistlot USING (idServidor)
                                 JOIN tblotacao ON (tbhistlot.lotacao=tblotacao.idLotacao)
                            LEFT JOIN tblicencasemvencimentos USING (idServidor)
                            JOIN tbtipolicenca ON (tblicencasemvencimentos.idTpLicenca = tbtipolicenca.idTpLicenca)
                WHERE situacao = 1
                  AND tbhistlot.data = (select max(data) from tbhistlot where tbhistlot.idServidor = tbservidor.idServidor)
                  AND ((tblicencasemvencimentos.dtInicial <= '{$relatorioDtInicial}' AND ADDDATE(tblicencasemvencimentos.dtInicial,tblicencasemvencimentos.numDias-1) >= '{$relatorioDtInicial}')
                   OR (tblicencasemvencimentos.dtInicial BETWEEN '{$relatorioDtInicial}' AND '{$relatorioDtFinal}')  
                   OR (ADDDATE(tblicencasemvencimentos.dtInicial,tblicencasemvencimentos.numDias-1) BETWEEN '{$relatorioDtInicial}' AND '{$relatorioDtFinal}'))";

    # lotacao
    if (!is_null($parametroLotacao)) {
        # Verifica se o que veio é numérico
        if (is_numeric($parametroLotacao)) {
            $select .= " AND (tblotacao.idlotacao = {$parametroLotacao})";
        } else { # senão é uma diretoria genérica
            $select .= " AND (tblotacao.DIR = '{$parametroLotacao}')";
        }
    }

    # Licença Médica Sem Alta
    $select .= ") UNION (
               SELECT T2.idFuncional,
                      T2.idServidor,
                      tblicenca.dtInicial,
                      tblicenca.numDias,
                      ADDDATE(tblicenca.dtInicial,tblicenca.numDias-1),
                      CONCAT(tbtipolicenca.nome,' - <b>(Em Aberto)</b>'),
                      concat(IFnull(tblotacao.UADM,''),' - ',IFnull(tblotacao.DIR,''),' - ',IFnull(tblotacao.GER,''),' - ',IFnull(tblotacao.nome,'')) lotacao,
                      tbpessoa.nome
                 FROM tbservidor AS T2 LEFT JOIN tbpessoa USING (idPessoa)
                                 JOIN tbhistlot USING (idServidor)
                                 JOIN tblotacao ON (tbhistlot.lotacao=tblotacao.idLotacao)
                                      LEFT JOIN tblicenca USING (idServidor)
                                      LEFT JOIN tbtipolicenca USING (idTpLicenca)
                 
                WHERE situacao = 1
                  AND tbhistlot.data = (select max(data) from tbhistlot where tbhistlot.idServidor = T2.idServidor)
                  AND (idTpLicenca = 1 OR idTpLicenca = 2 OR idTpLicenca = 30)
                  AND alta <> 1
                  AND (tblicenca.dtInicial <= '{$relatorioDtFinal}')
                  AND idLicenca = (SELECT idLicenca FROM tblicenca AS T1 WHERE T1.idServidor = T2.idServidor AND (T1.idTpLicenca = 1 OR T1.idTpLicenca = 2 OR T1.idTpLicenca = 30) ORDER BY dtInicial DESC LIMIT 1)
                  ";
    # lotacao
    if (!is_null($parametroLotacao)) {
        # Verifica se o que veio é numérico
        if (is_numeric($parametroLotacao)) {
            $select .= " AND (tblotacao.idlotacao = {$parametroLotacao})";
        } else { # senão é uma diretoria genérica
            $select .= " AND (tblotacao.DIR = '{$parametroLotacao}')";
        }
    }

    $select .= ") ORDER BY 7,8";

    $result = $servidor->select($select);

    $classeLot = new Lotacao();

    $relatorio = new Relatorio();
    $relatorio->set_titulo("Relatório de Servidores com Afastamento");
    $relatorio->set_tituloLinha2($classeLot->get_nomeDiretoriaSigla($parametroLotacao));
    $relatorio->set_subtitulo($parametroAno);
    $relatorio->set_width([10, 30, 10, 5, 10, 40]);
    $relatorio->set_label(['IdFuncional', 'Servidor', 'Data Inicial', 'Dias', 'Data Final', 'Descrição', "Lotação"]);
    $relatorio->set_align(["center", "left", "center", "center", "center", "left"]);
    $relatorio->set_funcao([null, null, "date_to_php", null, "date_to_php"]);
    $relatorio->set_classe([null, "pessoal"]);
    $relatorio->set_metodo([null, "get_nome"]);
    $relatorio->set_numGrupo(6);
    #$relatorio->set_bordaInterna(true);
    $relatorio->set_conteudo($result);

    # Cria um array com os anos possíveis
    $anoInicial = 1999;
    $anoAtual = date('Y');
    $anoExercicio = arrayPreenche($anoInicial, $anoAtual + 2, "d");

    $listaLotacao = $servidor->select('SELECT distinct DIR, DIR
                                              FROM tblotacao
                                             WHERE ativo');
    array_unshift($listaLotacao, array('*', 'Escolha a Lotação'));

    $relatorio->set_formCampos(array(
        array('nome' => 'parametroAno',
            'label' => 'Ano Exercício:',
            'tipo' => 'combo',
            'array' => $anoExercicio,
            'size' => 10,
            'padrao' => $parametroAno,
            'title' => 'Ano',
            'onChange' => 'formPadrao.submit();',
            'col' => 2,
            'linha' => 1),
        array('nome' => 'parametroLotacao',
            'label' => 'Lotação:',
            'tipo' => 'combo',
            'array' => $listaLotacao,
            'size' => 20,
            'padrao' => $parametroLotacao,
            'onChange' => 'formPadrao.submit();',
            'col' => 4,
            'linha' => 1),
    ));

    $relatorio->show();
    $page->terminaPagina();
}
