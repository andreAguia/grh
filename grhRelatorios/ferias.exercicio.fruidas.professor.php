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
$acesso = Verifica::acesso($idUsuario, 2);

if ($acesso) {
    # Conecta ao Banco de Dados
    $servidor = new Pessoal();

    # Começa uma nova página
    $page = new Page();
    $page->iniciaPagina();

    # Pega o ano exercicio
    $parametroAno = post("parametroAno", date('Y') + 1);

    ######

    $select1 = "(SELECT tbservidor.idFuncional,
                        tbpessoa.nome,
                        tbservidor.idServidor,
                        tbservidor.idServidor,
                        tbferias.dtInicial,
                        tbferias.numDias,
                        date_format(ADDDATE(tbferias.dtInicial,tbferias.numDias-1),'%d/%m/%Y') as dtf
                   FROM tbpessoa LEFT JOIN tbservidor USING (idPessoa)
                                 LEFT JOIN tbcargo USING (idCargo)
                                      JOIN tbtipocargo USING (idTipoCargo)
                                 LEFT JOIN tbferias USING (idServidor)
                                     JOIN tbhistlot USING (idServidor)
                                     JOIN tblotacao ON (tbhistlot.lotacao=tblotacao.idLotacao)
                 WHERE tbhistlot.data = (select max(data) from tbhistlot where tbhistlot.idServidor = tbservidor.idServidor)
                   AND tbtipocargo.tipo = 'Professor'
                   AND anoExercicio = '{$parametroAno}'
                   AND situacao = 1
              ORDER BY tbpessoa.nome)";

    $result = $servidor->select($select1);

    $relatorio = new Relatorio();
    $relatorio->set_titulo('Relatório de Férias de Docentes Ativos');
    $relatorio->set_tituloLinha2('Ano Exercício: ' . $parametroAno);

    $relatorio->set_label(array("Id", "Servidor", "Cargo", "Lotação", "Início", "Dias", "Término"));
    $relatorio->set_align(array("center", "left", "left", "left"));
    $relatorio->set_funcao(array(null, null, null, null, "date_to_php"));
    $relatorio->set_classe(array(null, null, "pessoal", "pessoal"));
    $relatorio->set_metodo(array(null, null, "get_cargoSimples", "get_lotacaoSimples"));
    #$relatorio->set_numGrupo(5);
    $relatorio->set_conteudo($result);
    $relatorio->set_bordaInterna(true);

    # Cria um array com os anos possíveis
    $anoInicial = 1999;
    $anoAtual = date('Y');
    $anoExercicio = arrayPreenche($anoInicial, $anoAtual + 2, "d");

    $relatorio->set_formCampos(array(
        array('nome' => 'parametroAno',
            'label' => 'Ano:',
            'tipo' => 'combo',
            'array' => $anoExercicio,
            'size' => 10,
            'padrao' => $parametroAno,
            'title' => 'Ano',
            'onChange' => 'formPadrao.submit();',
            'col' => 2,
            'linha' => 1)
    ));

    $relatorio->set_formFocus('parametroAno');
    $relatorio->set_formLink('?');
    $relatorio->show();

    $page->terminaPagina();
}