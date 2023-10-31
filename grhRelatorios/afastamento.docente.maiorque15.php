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
    $page->set_title("Professores com Mais de 15 Dias de Afastamento");
    $page->iniciaPagina();

    # Pega os parâmetros dos relatórios
    $relatorioDtInicial = post('dtInicial', date('Y') . "-01-01");
    $relatorioDtfinal = post('dtFinal', date('Y') . "-12-31");

    ######
    # Licença Geral
    $select = "(SELECT tbservidor.idFuncional,
                      tbpessoa.nome,
                      tbservidor.idServidor,
                      tblicenca.dtInicial,
                      tblicenca.numDias,
                      ADDDATE(tblicenca.dtInicial,tblicenca.numDias-1),
                      CONCAT(tbtipolicenca.nome,IF(alta=1,' - Com Alta',''))
                 FROM tbservidor JOIN tbpessoa USING (idPessoa)
                            LEFT JOIN tblicenca USING (idServidor)
                            LEFT JOIN tbtipolicenca USING (idTpLicenca)
                WHERE situacao = 1
                  AND (idCargo = 128 OR idCargo = 129) 
                  AND numDias > 15
                  AND ((tblicenca.dtInicial <= '{$relatorioDtInicial}' AND ADDDATE(tblicenca.dtInicial,tblicenca.numDias-1) >= '{$relatorioDtInicial}')
                   OR (tblicenca.dtInicial BETWEEN '{$relatorioDtInicial}' AND '{$relatorioDtfinal}')  
                   OR (ADDDATE(tblicenca.dtInicial,tblicenca.numDias-1) BETWEEN '{$relatorioDtInicial}' AND '{$relatorioDtfinal}'))";

    # Licença Prêmio               
    $select .= ") UNION (
               SELECT tbservidor.idFuncional,
                      tbpessoa.nome,
                      tbservidor.idServidor,
                      tblicencapremio.dtInicial,
                      tblicencapremio.numDias,
                      ADDDATE(tblicencapremio.dtInicial,tblicencapremio.numDias-1),
                      (SELECT tbtipolicenca.nome FROM tbtipolicenca WHERE idTpLicenca = 6)
                 FROM tbservidor JOIN tbpessoa USING (idPessoa)
                            LEFT JOIN tblicencapremio  USING (idServidor)
                WHERE situacao = 1
                  AND (idCargo = 128 OR idCargo = 129) 
                  AND numDias > 15
                  AND ((tblicencapremio.dtInicial <= '{$relatorioDtInicial}' AND ADDDATE(tblicencapremio.dtInicial,tblicencapremio.numDias-1) >= '{$relatorioDtInicial}')
                   OR (tblicencapremio.dtInicial BETWEEN '{$relatorioDtInicial}' AND '{$relatorioDtfinal}')  
                   OR (ADDDATE(tblicencapremio.dtInicial,tblicencapremio.numDias-1) BETWEEN '{$relatorioDtInicial}' AND '{$relatorioDtfinal}'))";

    # Férias
    $select .= ") UNION (
               SELECT tbservidor.idFuncional,
                      tbpessoa.nome,
                      tbservidor.idServidor,
                      tbferias.dtInicial,
                      tbferias.numDias,
                      ADDDATE(tbferias.dtInicial,tbferias.numDias-1),
                      CONCAT('Férias ',tbferias.anoExercicio)
                 FROM tbservidor JOIN tbpessoa USING (idPessoa)
                            LEFT JOIN tbferias USING (idServidor)
                WHERE situacao = 1
                  AND (idCargo = 128 OR idCargo = 129)
                  AND month(tbferias.dtInicial) <> 1
                  AND month(tbferias.dtInicial) <> 2
                  AND numDias > 15
                  AND ((tbferias.dtInicial <= '{$relatorioDtInicial}' AND ADDDATE(tbferias.dtInicial,tbferias.numDias-1) >= '{$relatorioDtInicial}')
                   OR (tbferias.dtInicial BETWEEN '{$relatorioDtInicial}' AND '{$relatorioDtfinal}')  
                   OR (ADDDATE(tbferias.dtInicial,tbferias.numDias-1) BETWEEN '{$relatorioDtInicial}' AND '{$relatorioDtfinal}'))";

    # Faltas abonadas
    $select .= ") UNION (
               SELECT tbservidor.idFuncional,
                      tbpessoa.nome,
                      tbservidor.idServidor,
                      tbatestado.dtInicio,
                      tbatestado.numDias,
                      ADDDATE(tbatestado.dtInicio,tbatestado.numDias-1),
                      'Falta Abonada'
                 FROM tbservidor JOIN tbpessoa USING (idPessoa)
                            LEFT JOIN tbatestado USING (idServidor)
                WHERE situacao = 1
                  AND (idCargo = 128 OR idCargo = 129) 
                  AND numDias > 15
                  AND ((tbatestado.dtInicio <= '{$relatorioDtInicial}' AND ADDDATE(tbatestado.dtInicio,tbatestado.numDias-1) >= '{$relatorioDtInicial}')
                   OR (tbatestado.dtInicio BETWEEN '{$relatorioDtInicial}' AND '{$relatorioDtfinal}')  
                   OR (ADDDATE(tbatestado.dtInicio,tbatestado.numDias-1) BETWEEN '{$relatorioDtInicial}' AND '{$relatorioDtfinal}'))";

    # Trabalhando TRE
    $select .= ") UNION (
               SELECT tbservidor.idFuncional,
                      tbpessoa.nome,
                      tbservidor.idServidor,
                      tbtrabalhotre.data,
                      tbtrabalhotre.dias,
                      ADDDATE(tbtrabalhotre.data,tbtrabalhotre.dias-1),
                      'Trabalhando no TRE'
                 FROM tbservidor JOIN tbpessoa USING (idPessoa)
                            LEFT JOIN tbtrabalhotre USING (idServidor)
                WHERE situacao = 1
                  AND (idCargo = 128 OR idCargo = 129) 
                  AND dias > 15
                  AND ((tbtrabalhotre.data <= '{$relatorioDtInicial}' AND ADDDATE(tbtrabalhotre.data,tbtrabalhotre.dias-1) >= '{$relatorioDtInicial}')
                   OR (tbtrabalhotre.data BETWEEN '{$relatorioDtInicial}' AND '{$relatorioDtfinal}')  
                   OR (ADDDATE(tbtrabalhotre.data,tbtrabalhotre.dias-1) BETWEEN '{$relatorioDtInicial}' AND '{$relatorioDtfinal}'))";

    # Folga TRE
    $select .= ") UNION (
               SELECT tbservidor.idFuncional,
                      tbpessoa.nome,
                      tbservidor.idServidor,
                      tbfolga.data,
                      tbfolga.dias,
                      ADDDATE(tbfolga.data,tbfolga.dias-1),
                      'Folga TRE'
                 FROM tbservidor JOIN tbpessoa USING (idPessoa)
                            LEFT JOIN tbfolga USING (idServidor)
                WHERE situacao = 1
                  AND (idCargo = 128 OR idCargo = 129) 
                  AND dias > 15
                  AND ((tbfolga.data <= '{$relatorioDtInicial}' AND ADDDATE(tbfolga.data,tbfolga.dias-1) >= '{$relatorioDtInicial}')
                   OR (tbfolga.data BETWEEN '{$relatorioDtInicial}' AND '{$relatorioDtfinal}')  
                   OR (ADDDATE(tbfolga.data,tbfolga.dias-1) BETWEEN '{$relatorioDtInicial}' AND '{$relatorioDtfinal}'))";

    # Licença Sem Vencimentos
    $select .= ") UNION (
               SELECT tbservidor.idFuncional,
                      tbpessoa.nome,
                      tbservidor.idServidor,
                      tblicencasemvencimentos.dtInicial,
                      tblicencasemvencimentos.numDias,
                      ADDDATE(tblicencasemvencimentos.dtInicial,tblicencasemvencimentos.numDias-1),
                      tbtipolicenca.nome
                 FROM tbservidor JOIN tbpessoa USING (idPessoa)
                            LEFT JOIN tblicencasemvencimentos USING (idServidor)
                            JOIN tbtipolicenca ON (tblicencasemvencimentos.idTpLicenca = tbtipolicenca.idTpLicenca)
                WHERE situacao = 1
                  AND (idCargo = 128 OR idCargo = 129) 
                  AND numDias > 15
                  AND ((tblicencasemvencimentos.dtInicial <= '{$relatorioDtInicial}' AND ADDDATE(tblicencasemvencimentos.dtInicial,tblicencasemvencimentos.numDias-1) >= '{$relatorioDtInicial}')
                   OR (tblicencasemvencimentos.dtInicial BETWEEN '{$relatorioDtInicial}' AND '{$relatorioDtfinal}')  
                   OR (ADDDATE(tblicencasemvencimentos.dtInicial,tblicencasemvencimentos.numDias-1) BETWEEN '{$relatorioDtInicial}' AND '{$relatorioDtfinal}'))";

    # Licença Médica Sem Alta
    $select .= ") UNION (
               SELECT T2.idFuncional,
                      tbpessoa.nome,
                      T2.idServidor,
                      tblicenca.dtInicial,
                      tblicenca.numDias,
                      ADDDATE(tblicenca.dtInicial,tblicenca.numDias-1),
                      CONCAT(tbtipolicenca.nome,' - <b>(Em Aberto)</b>')
                 FROM tbservidor AS T2 LEFT JOIN tbpessoa USING (idPessoa)
                                      LEFT JOIN tblicenca USING (idServidor)
                                      LEFT JOIN tbtipolicenca USING (idTpLicenca)
                 
                WHERE situacao = 1
                  AND (idCargo = 128 OR idCargo = 129) 
                  AND (idTpLicenca = 1 OR idTpLicenca = 2 OR idTpLicenca = 30)
                  AND alta <> 1
                  AND (tblicenca.dtInicial <= '{$relatorioDtfinal}')
                  AND idLicenca = (SELECT idLicenca FROM tblicenca AS T1 WHERE T1.idServidor = T2.idServidor AND (T1.idTpLicenca = 1 OR T1.idTpLicenca = 2 OR T1.idTpLicenca = 30) ORDER BY dtInicial DESC LIMIT 1)
                  ";

    $select .= ") ORDER BY 2";

    $result = $servidor->select($select);

    $relatorio = new Relatorio();
    $relatorio->set_titulo('Relatório de Professores com Mais de 15 Dias de Afastamento');
    $relatorio->set_tituloLinha2("Período de " . date_to_php($relatorioDtInicial) . " a " . date_to_php($relatorioDtfinal));
    $relatorio->set_subtitulo('Descartando as Férias com Data Inicial em Janeiro e Fevereiro<br/>Ordenado pelo Nome');
    $relatorio->set_width([10, 30, 20, 10, 5, 10, 25]);
    $relatorio->set_label(['IdFuncional', 'Nome', 'Cargo', 'Data Inicial', 'Dias', 'Data Final', 'Descrição']);
    $relatorio->set_align(["center", "left", "left", "center", "center", "center", "center"]);
    $relatorio->set_funcao([null, null, null, "date_to_php", null, "date_to_php"]);
    $relatorio->set_classe([null, null, "pessoal"]);
    $relatorio->set_metodo([null, null, "get_cargoSimples"]);
    $relatorio->set_bordaInterna(true);
    $relatorio->set_conteudo($result);

    $relatorio->set_formCampos(array(
        array('nome' => 'dtInicial',
            'label' => 'Início:',
            'tipo' => 'data',
            'size' => 4,
            'title' => 'Insira a data inicial',
            'col' => 3,
            'padrao' => $relatorioDtInicial,
            'linha' => 1),
        array('nome' => 'dtFinal',
            'label' => 'Término:',
            'tipo' => 'data',
            'size' => 4,
            'title' => 'Insira a data final',
            'col' => 3,
            'padrao' => $relatorioDtfinal,
            'linha' => 1),
        array('nome' => 'submit',
            'valor' => 'Atualiza',
            'label' => '-',
            'size' => 4,
            'col' => 3,
            'tipo' => 'submit',
            'title' => 'Atualiza a tabela',
            'linha' => 1),
    ));

    $relatorio->set_formFocus('ano');
    $relatorio->set_formLink('?');
    $relatorio->show();

    $page->terminaPagina();
}