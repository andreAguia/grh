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
    $parametroAno = get("parametroAno", date('Y'));

    # Pega a lotação
    $parametroLotacao = get("parametroLotacao");

    # Transforma em nulo a máscara *
    if ($parametroLotacao == "*") {
        $parametroLotacao = null;
    }

    ######

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

        # Verifica se o que veio é numérico
        if (is_numeric($parametroLotacao)) {
            $select2 .= ' AND (tblotacao.idlotacao = "' . $parametroLotacao . '")';
        } else { # senão é uma diretoria genérica
            $select2 .= ' AND (tblotacao.DIR = "' . $parametroLotacao . '")';
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

        # Verifica se o que veio é numérico
        if (is_numeric($parametroLotacao)) {
            $select2 .= ' AND (tblotacao.idlotacao = "' . $parametroLotacao . '")';
        } else { # senão é uma diretoria genérica
            $select2 .= ' AND (tblotacao.DIR = "' . $parametroLotacao . '")';
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

    $relatorio->set_subtitulo('Agrupados pelo Total de Dias e Ordenado pelo Nome');
    $relatorio->set_subtitulo("== Não Solicitaram ==");

    $relatorio->set_label(array("Id", "Servidor", "Lotação", "Perfil", "Admissão", "Dias", "Situação"));
    $relatorio->set_align(array("center", "left", "left"));
//    $relatorio->set_funcao(array(null, null, null, null, "date_to_php"));
    $relatorio->set_funcao(array(null, null, null, null, "date_to_php",null, "get_situacaoRel"));
    $relatorio->set_classe(array(null, null, "pessoal", "pessoal"));
    $relatorio->set_metodo(array(null, null, "get_lotacaoSimples", "get_perfilSimples"));
    $relatorio->set_bordaInterna(true);
    $relatorio->set_conteudo($result);


    $relatorio->set_dataImpressao(false);
    $relatorio->show();

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
                                     JOIN tbsituacao ON (tbservidor.situacao = tbsituacao.idSituacao) 
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

    $select1 .= " GROUP BY tbpessoa.nome
                 ORDER BY soma,tbpessoa.nome)";

    $result = $servidor->select($select1);

    $relatorio = new Relatorio();
    $relatorio->set_cabecalhoRelatorio(false);
    $relatorio->set_menuRelatorio(false);

    $relatorio->set_label(array("Id", "Servidor", "Lotação", "Perfil", "Admissão", "Dias", "Situação"));
    $relatorio->set_align(array("center", "left", "left"));
    $relatorio->set_funcao(array(null, null, null, null, "date_to_php",null, "get_situacaoRel"));
    $relatorio->set_classe(array(null, null, "pessoal", "pessoal"));
    $relatorio->set_metodo(array(null, null, "get_lotacaoSimples", "get_perfilSimples"));
    $relatorio->set_numGrupo(5);
    $relatorio->set_conteudo($result);
    $relatorio->set_bordaInterna(true);

    $relatorio->set_dataImpressao(false);
    $relatorio->show();

    $page->terminaPagina();
}
