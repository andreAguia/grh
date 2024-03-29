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

    # Pega os parametros
    $parametroAno = get_session("parametroAno", date('Y'));
    $parametroLotacao = get_session("parametroLotacao");
    $parametroSituacao = get_session("parametroSituacao");
    $parametroPerfil = get_session("parametroPerfil");

    # Transforma em nulo a máscara *
    if ($parametroLotacao == "*") {
        $parametroLotacao = null;
    }

    if ($parametroSituacao == "*") {
        $parametroSituacao = null;
    }

    if ($parametroPerfil == "*") {
        $parametroPerfil = null;
    }

    ############################################################################

    /*
     * Exibe os servidores ativos que não solicitartam férias nesse exercício
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
                                         JOIN tbperfil USING (idPerfil)
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

        # Verifica se tem filtro por perfil
        if (is_null($parametroPerfil)) {
            $select2 .= " AND tbperfil.tipo <> 'Outros'";
        } else {
            $select2 .= " AND idPerfil = {$parametroPerfil}";
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

        # Verifica se tem filtro por perfil
        if (!is_null($parametroPerfil)) {
            $select2 .= " AND idPerfil = {$parametroPerfil}";
        }

        $select2 .= "
            AND tbservidor.situacao = 1
       ORDER BY tbpessoa.nome asc)
          ORDER BY tbpessoa.nome asc";

        $result = $servidor->select($select2);
        $count1 = $servidor->count($select2);

        if ($count1 > 0) {

            $relatorio = new Relatorio();
            $relatorio->set_titulo('Relatório de Férias');
            $relatorio->set_tituloLinha2('Ano Exercício: ' . $parametroAno);

            $linha3 = "Servidores {$servidor->get_nomeSituacao($parametroSituacao)}s";

            if (!is_null($parametroPerfil)) {
                $linha3 .= "<br/>Perfil: {$servidor->get_nomePerfil($parametroPerfil)}";
            }

            if (!is_null($parametroLotacao)) {
                $linha3 .= "<br/>{$servidor->get_nomeLotacao($parametroLotacao)}";
            }

            $relatorio->set_tituloLinha3($linha3);
            
            $relatorio->set_subtitulo("== Servidores que Não Solicitaram Férias ==");
            $relatorio->set_label(array("Id", "Servidor", "Lotação", "Perfil", "Admissão", "Dias", "Situação"));
            $relatorio->set_align(array("center", "left", "left"));
            $relatorio->set_funcao(array(null, null, null, null, "date_to_php", null, "get_situacaoRel"));
            $relatorio->set_classe(array(null, null, "pessoal", "pessoal"));
            $relatorio->set_metodo(array(null, null, "get_lotacaoSimples", "get_perfilSimples"));
            $relatorio->set_bordaInterna(true);
            $relatorio->set_conteudo($result);

            $relatorio->set_dataImpressao(false);
            $relatorio->show();
            
            $soma1 = $relatorio->get_totalRegistroValor();
        }
    }

    ############################################################################
    /*
     * Exibe os que solicitaram agrupados por total de dias
     */

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

    # Verifica se tem filtro por perfil
    if (!is_null($parametroPerfil)) {
        $select1 .= " AND idPerfil = {$parametroPerfil}";
    }

    $select1 .= " GROUP BY tbpessoa.nome
                 ORDER BY soma,tbpessoa.nome)";

    $result = $servidor->select($select1);

    $relatorio = new Relatorio();

    # Verifica se já teve o título na listagem acima
    if ($parametroSituacao == 1 OR is_null($parametroSituacao)) {

        # Verifica se teve listagem acima
        if ($count1 > 0) {
            $relatorio->set_cabecalhoRelatorio(false);
            $relatorio->set_menuRelatorio(false);
        } else {
            $relatorio->set_titulo('Relatório de Férias');
            $relatorio->set_tituloLinha2('Ano Exercício: ' . $parametroAno);
            
            $linha3 = "Servidores {$servidor->get_nomeSituacao($parametroSituacao)}s";

            if (!is_null($parametroPerfil)) {
                $linha3 .= "<br/>Perfil: {$servidor->get_nomePerfil($parametroPerfil)}";
            }

            if (!is_null($parametroLotacao)) {
                $linha3 .= "<br/>{$servidor->get_nomeLotacao($parametroLotacao)}";
            }

            $relatorio->set_tituloLinha3($linha3);
        }
    } else {
        $relatorio->set_titulo('Relatório de Férias');
        $relatorio->set_tituloLinha2('Ano Exercício: ' . $parametroAno);

        $linha3 = "Servidores {$servidor->get_nomeSituacao($parametroSituacao)}s";

        if (!is_null($parametroPerfil)) {
            $linha3 .= "<br/>{$servidor->get_nomePerfil($parametroPerfil)}";
        }

        if (!is_null($parametroLotacao)) {
            $linha3 .= "<br/>{$servidor->get_nomeLotacao($parametroLotacao)}";
        }

        $relatorio->set_tituloLinha3($linha3);
    }

    $relatorio->set_subtitulo("== Servidores que Solicitaram Férias ==");
    $relatorio->set_label(array("Id", "Servidor", "Lotação", "Perfil", "Admissão", "Dias", "Situação"));
    $relatorio->set_align(array("center", "left", "left"));
    $relatorio->set_funcao(array(null, null, null, null, "date_to_php", null, "get_situacaoRel"));
    $relatorio->set_classe(array(null, null, "pessoal", "pessoal"));
    $relatorio->set_metodo(array(null, null, "get_lotacaoSimples", "get_perfilSimples"));
    $relatorio->set_numGrupo(5);
    $relatorio->set_conteudo($result);
    $relatorio->set_bordaInterna(true);
    $relatorio->show();

     if (!empty($soma1)) {
        $soma2 = $relatorio->get_totalRegistroValor();
        p("Total Geral de Registros: " . ($soma1 + $soma2), "f12", "center");
    }

    $page->terminaPagina();
}
