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

    # Pega os parametros
    $parametroAno = get_session("parametroAno", date('Y'));
    $parametroLotacao = get_session("parametroLotacao");
    $parametroSituacao = get_session("parametroSituacao");
    
    # Transforma em nulo a máscara *
    if ($parametroLotacao == "*") {
        $parametroLotacao = null;
    }

    if ($parametroSituacao == "*") {
        $parametroSituacao = null;
    }

    ######

    /*
     * A primeira listagem so vale para os ativos ou todos
     * Dessa forma quando não for ativo ou todos não exibe essa primeira listagem
     */

    if ($parametroSituacao == 1 OR is_null($parametroSituacao)) {
        $select2 = "SELECT tbservidor.idFuncional,
                           tbpessoa.nome,
                           tbservidor.idServidor,
                           tbservidor.idServidor,
                           tbservidor.dtAdmissao,
                           '-',
                           tbservidor.idServidor
                      FROM tbpessoa LEFT JOIN tbservidor USING (idPessoa)
                                         JOIN tbhistlot USING (idServidor)
                                         JOIN tblotacao ON (tbhistlot.lotacao=tblotacao.idLotacao)
                                         JOIN tbsituacao ON (tbservidor.situacao = tbsituacao.idSituacao) 
                     WHERE tbhistlot.data = (select max(data) from tbhistlot where tbhistlot.idServidor = tbservidor.idServidor)
                     AND YEAR(tbservidor.dtAdmissao) < $parametroAno
                      ";

        if (!is_null($parametroLotacao)) {
            if (is_numeric($parametroLotacao)) {
                $select2 .= " AND (tblotacao.idlotacao = {$parametroLotacao})";
            } else { # senão é uma diretoria genérica
                $select2 .= " AND (tblotacao.DIR = '{$parametroLotacao}')";
            }
        }

        $select2 .= "
         AND tbservidor.situacao = 1
         AND tbpessoa.nome NOT IN 
         (SELECT tbpessoa.nome
         FROM tbpessoa LEFT JOIN tbservidor USING (idPessoa)
                            JOIN tbferias USING (idservidor)
                            JOIN tbhistlot USING (idServidor)
                            JOIN tblotacao ON (tbhistlot.lotacao = tblotacao.idLotacao)
        WHERE tbhistlot.data = (select max(data) from tbhistlot where tbhistlot.idServidor = tbservidor.idServidor)
              AND anoExercicio = $parametroAno";

        if (!is_null($parametroLotacao)) {
            if (is_numeric($parametroLotacao)) {
                $select2 .= " AND (tblotacao.idlotacao = {$parametroLotacao})";
            } else { # senão é uma diretoria genérica
                $select2 .= " AND (tblotacao.DIR = '{$parametroLotacao}')";
            }
        }

        $select2 .= "
            AND tbservidor.situacao = 1
       ORDER BY tbpessoa.nome asc)
          ORDER BY tbpessoa.nome asc";

        $result = $servidor->select($select2);

        $relatorio = new Relatorio();
        $relatorio->set_titulo('Relatório de Férias');
        $relatorio->set_tituloLinha2('Ano Exercício: ' . $parametroAno);

        if (!is_null($parametroLotacao)) {
            $relatorio->set_tituloLinha3($servidor->get_nomeLotacao($parametroLotacao));
        }

        #$relatorio->set_subtitulo('Agrupados pelo Total de Dias e Ordenado pelo Nome');
        $relatorio->set_subtitulo("== Não Solicitaram ==");

        $relatorio->set_label(array("Id", "Servidor", "Lotação", "Perfil", "Admissão", "Dias", "Situação"));
        $relatorio->set_align(array("center", "left", "left"));
        $relatorio->set_funcao(array(null, null, null, null, "date_to_php", null, "get_situacaoRel"));
        $relatorio->set_classe(array(null, null, "pessoal", "pessoal"));
        $relatorio->set_metodo(array(null, null, "get_lotacaoSimples", "get_perfilSimples"));
        $relatorio->set_bordaInterna(true);
        $relatorio->set_conteudo($result);

        $relatorio->set_dataImpressao(false);
        $relatorio->show();
    }

    #####

    $select1 = "(SELECT tbservidor.idFuncional,
                        tbpessoa.nome,
                        tbservidor.idServidor,
                        tbservidor.idServidor,
                        tbservidor.dtAdmissao,
                        sum(numDias) as soma,
                        tbservidor.idServidor
                   FROM tbpessoa LEFT JOIN tbservidor USING (idPessoa)
                                 LEFT JOIN tbferias USING (idServidor)
                                     JOIN tbhistlot USING (idServidor)
                                     JOIN tblotacao ON (tbhistlot.lotacao=tblotacao.idLotacao)
                 WHERE tbhistlot.data = (select max(data) from tbhistlot where tbhistlot.idServidor = tbservidor.idServidor)
                   AND anoExercicio = $parametroAno";

    # Verifica se tem filtro por lotação
    if (!is_null($parametroLotacao)) {  // senão verifica o da classe
        # Verifica se o que veio é numérico
        if (is_numeric($parametroLotacao)) {
            $select1 .= ' AND (tblotacao.idlotacao = "' . $parametroLotacao . '")';
        } else { # senão é uma diretoria genérica
            $select1 .= ' AND (tblotacao.DIR = "' . $parametroLotacao . '")';
        }
    }

    # Verifica se tem filtro por situação
    if (!is_null($parametroSituacao)) {
        $select1 .= " AND situacao = {$parametroSituacao}";
    }

    $select1 .= " GROUP BY tbpessoa.nome
                 ORDER BY soma,tbpessoa.nome)";

    $result = $servidor->select($select1);

    $relatorio = new Relatorio();

    if ($parametroSituacao == 1 OR is_null($parametroSituacao)) {
        $relatorio->set_cabecalhoRelatorio(false);
        $relatorio->set_menuRelatorio(false);
    } else {
        $relatorio->set_titulo('Relatório de Férias');
        $relatorio->set_tituloLinha2('Ano Exercício: ' . $parametroAno);

        $linha3 = $servidor->get_nomeSituacao($parametroSituacao);

        if (!is_null($parametroLotacao)) {
            $linha3 .= "<br/>{$servidor->get_nomeLotacao($parametroLotacao)}";
        }

        $relatorio->set_tituloLinha3($linha3);
    }
    
    #$relatorio->set_subtitulo('Agrupados por Mês - Ordenados pela Data Inicial');
    $relatorio->set_label(array("Id", "Servidor", "Lotação", "Perfil", "Admissão", "Dias", "Situação"));
    $relatorio->set_align(array("center", "left", "left"));
    $relatorio->set_funcao(array(null, null, null, null, "date_to_php", null, "get_situacaoRel"));
    $relatorio->set_classe(array(null, null, "pessoal", "pessoal"));
    $relatorio->set_metodo(array(null, null, "get_lotacaoSimples", "get_perfilSimples"));
    $relatorio->set_numGrupo(5);
    $relatorio->set_conteudo($result);
    $relatorio->set_bordaInterna(true);
    $relatorio->show();

    $page->terminaPagina();
}
