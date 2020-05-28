<?php

/**
 * Sistema GRH
 * 
 * Relatório
 *   
 * By Alat
 */
# Servidor logado 
$idUsuario = NULL;

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

    # Pega os parâmetros
    $parametroAno = get_session('parametroAno', date("Y"));
    $parametroLotacao = get_session('parametroLotacao');
    $parametroStatus = get_session('parametroStatus');

    # Transforma em nulo a máscara *
    if ($parametroLotacao == "*") {
        $parametroLotacao = NULL;
    }

    # Transforma em nulo a máscara *
    if ($parametroStatus == "Todos") {
        $parametroStatus = NULL;
    }

    ######

    $select = "SELECT tbservidor.idfuncional,        
                     tbpessoa.nome,
                     tbservidor.idServidor,
                     tbferias.anoExercicio,
                     tbferias.dtInicial,
                     tbferias.numDias,
                     date_format(ADDDATE(tbferias.dtInicial,tbferias.numDias-1),'%d/%m/%Y') as dtf,
                     idFerias,
                     month(dtInicial),
                     tbsituacao.situacao
                FROM tbservidor LEFT JOIN tbpessoa ON (tbservidor.idPessoa=tbpessoa.idPessoa)
                                     JOIN tbferias ON (tbservidor.idServidor = tbferias.idServidor)
                                     JOIN tbhistlot ON (tbservidor.idServidor = tbhistlot.idServidor)
                                     JOIN tblotacao ON (tbhistlot.lotacao=tblotacao.idLotacao)
                                     JOIN tbsituacao ON (tbservidor.situacao = tbsituacao.idSituacao) 
               WHERE YEAR(tbferias.dtInicial) = $parametroAno
                 AND tbhistlot.data = (select max(data) from tbhistlot where tbhistlot.idServidor = tbservidor.idServidor)";

    # Lotação
    if (($parametroLotacao <> "*") AND ($parametroLotacao <> "")) {
        # Verifica se o que veio é numérico
        if (is_numeric($parametroLotacao)) {
            $select .= ' AND (tblotacao.idlotacao = "' . $parametroLotacao . '")';
        } else { # senão é uma diretoria genérica
            $select .= ' AND (tblotacao.DIR = "' . $parametroLotacao . '")';
        }
    }

    # Status
    if (($parametroStatus <> "Todos") AND ($parametroStatus <> "")) {
        $select .= ' AND (tbferias.status = "' . $parametroStatus . '")';
    }

    $select .= ' ORDER BY tbferias.dtInicial';

    $result = $servidor->select($select);

    $relatorio = new Relatorio();
    $relatorio->set_titulo('Relatório Anual de Férias');
    $relatorio->set_tituloLinha2($parametroAno);

    $titulo3 = NULL;

    # Lotação no subtítulo
    if (!is_null($parametroLotacao)) {
        $titulo3 .= $servidor->get_nomeLotacao($parametroLotacao);
    }

    # Status no subtítulo
    if (!is_null($parametroStatus)) {
        $titulo3 .= '<br/>(' . $parametroStatus . 's)';
    }

    $relatorio->set_tituloLinha3($titulo3);
    $relatorio->set_subtitulo('Agrupados por Mês da data Inicial - Ordenados pela Data Inicial');

    $relatorio->set_label(array('IdFuncional', 'Nome', 'Lotação', 'Exercício', 'Dt Inicial', 'Dias', 'Dt Final', 'Período', 'Mês', 'Situação'));
    $relatorio->set_width(array(10, 30, 20, 5, 9, 8, 9, 10));
    $relatorio->set_align(array("center", "left", "left"));
    $relatorio->set_funcao(array(NULL, NULL, NULL, NULL, "date_to_php", NULL, NULL, NULL, "get_nomeMes"));
    $relatorio->set_classe(array(NULL, NULL, "pessoal", NULL, NULL, NULL, NULL, "pessoal"));
    $relatorio->set_metodo(array(NULL, NULL, "get_lotacaoSimples", NULL, NULL, NULL, NULL, "get_feriasPeriodo"));

    $relatorio->set_conteudo($result);
    $relatorio->set_numGrupo(8);
    $relatorio->show();

    $page->terminaPagina();
}
