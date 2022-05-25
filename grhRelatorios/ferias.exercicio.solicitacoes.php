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

    $select = "SELECT tbservidor.idfuncional,        
                     tbpessoa.nome,
                     tbservidor.idServidor,
                     tbferias.anoExercicio,
                     tbferias.dtInicial,
                     tbferias.numDias,
                     date_format(ADDDATE(tbferias.dtInicial,tbferias.numDias-1),'%d/%m/%Y') as dtf,
                     idFerias,
                     CONCAT(month(tbferias.dtInicial),'/',year(tbferias.dtInicial)),
                     tbservidor.idServidor
                FROM tbservidor LEFT JOIN tbpessoa ON (tbservidor.idPessoa=tbpessoa.idPessoa)
                                     JOIN tbferias ON (tbservidor.idServidor = tbferias.idServidor)
                                     JOIN tbhistlot ON (tbservidor.idServidor = tbhistlot.idServidor)
                                     JOIN tblotacao ON (tbhistlot.lotacao = tblotacao.idLotacao)
               WHERE anoExercicio = {$parametroAno}
                 AND tbhistlot.data = (select max(data) from tbhistlot where tbhistlot.idServidor = tbservidor.idServidor)";

    # Verifica se tem filtro por lotação
    if (!is_null($parametroLotacao)) {  // senão verifica o da classe
        # Verifica se o que veio é numérico
        if (is_numeric($parametroLotacao)) {
            $select .= ' AND (tblotacao.idlotacao = "' . $parametroLotacao . '")';
        } else { # senão é uma diretoria genérica
            $select .= ' AND (tblotacao.DIR = "' . $parametroLotacao . '")';
        }
    }

    # Verifica se tem filtro por situação
    if (!is_null($parametroSituacao)) {
        $select .= " AND situacao = {$parametroSituacao}";
    }

    # Verifica se tem filtro por perfil
    if (!is_null($parametroPerfil)) {
        $select .= " AND idPerfil = {$parametroPerfil}";
    }

    $select .= ' ORDER BY year(tbferias.dtInicial), month(tbferias.dtInicial), tbferias.dtInicial';

    $result = $servidor->select($select);

    $relatorio = new Relatorio();
    $relatorio->set_titulo('Relatório Anual de Férias');
    $relatorio->set_tituloLinha2("Ano Exercício: " . $parametroAno);

    $linha3 = "Servidores {$servidor->get_nomeSituacao($parametroSituacao)}s";

    if (!is_null($parametroPerfil)) {
        $linha3 .= "<br/>Perfil: {$servidor->get_nomePerfil($parametroPerfil)}";
    }

    if (!is_null($parametroLotacao)) {
        $linha3 .= "<br/>{$servidor->get_nomeLotacao($parametroLotacao)}";
    }

    $relatorio->set_tituloLinha3($linha3);

    $relatorio->set_subtitulo('Agrupados por Mês - Ordenados pela Data Inicial');
    $relatorio->set_bordaInterna(true);
    $relatorio->set_label(array('IdFuncional', 'Nome', 'Lotação', 'Exercício', 'Dt Inicial', 'Dias', 'Dt Final', 'Período', 'Mês', 'Situação'));
    $relatorio->set_align(array("center", "left", "left"));
    $relatorio->set_funcao(array(null, null, null, null, "date_to_php", null, null, null, "acertaDataFerias", "get_situacaoRel"));
    $relatorio->set_classe(array(null, null, "pessoal", null, null, null, null, "pessoal"));
    $relatorio->set_metodo(array(null, null, "get_lotacaoSimples", null, null, null, null, "get_feriasPeriodo"));

    $relatorio->set_conteudo($result);
    $relatorio->set_numGrupo(8);
    $relatorio->show();

    $page->terminaPagina();
}
