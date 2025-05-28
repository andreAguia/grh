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

    # Pega os parâmetros
    $parametroAno = get_session('parametroAno', date("Y"));
    $parametroLotacao = get_session('parametroLotacao');
    $parametroStatus = get_session('parametroStatus');
    $parametroPerfil = get_session("parametroPerfil");
    $parametroCargo = get_session('parametroCargo');

    # Transforma em nulo a máscara *
    if ($parametroLotacao == "*") {
        $parametroLotacao = null;
    }

    # Transforma em nulo a máscara *
    if ($parametroStatus == "Todos") {
        $parametroStatus = null;
    }

    if ($parametroPerfil == "*") {
        $parametroPerfil = null;
    }

    ############################################################################
    # Inicia o subtitulo
    $subtitulo = null;

    # Monta o select
    $select = "SELECT tbservidor.idfuncional,        
                     tbpessoa.nome,
                     concat(IFnull(tblotacao.DIR,''),' - ',IFnull(tblotacao.GER,''),' - ',IFnull(tblotacao.nome,'')) lotacao,
                     tbferias.anoExercicio,
                     tbferias.dtInicial,
                     tbferias.numDias,
                     date_format(ADDDATE(tbferias.dtInicial,tbferias.numDias-1),'%d/%m/%Y') as dtf,
                     idFerias,
                     tbsituacao.situacao
                FROM tbservidor LEFT JOIN tbpessoa ON (tbservidor.idPessoa=tbpessoa.idPessoa)
                                     JOIN tbferias ON (tbservidor.idServidor = tbferias.idServidor)
                                     JOIN tbhistlot ON (tbservidor.idServidor = tbhistlot.idServidor)
                                     JOIN tblotacao ON (tbhistlot.lotacao=tblotacao.idLotacao)
                                     JOIN tbsituacao ON (tbservidor.situacao = tbsituacao.idSituacao) 
                                     LEFT JOIN tbcargo ON (tbservidor.idCargo = tbcargo.idCargo)
                                     JOIN tbtipocargo ON (tbcargo.idTipoCargo = tbtipocargo.idTipoCargo)
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

        # SubTítulo
        $subtitulo .= $servidor->get_nomeLotacao($parametroLotacao);
    }

    # Cargo
    if (($parametroCargo <> "*") AND ($parametroCargo <> "")) {
        if ($parametroCargo == "Professor") {
            $select .= ' AND (tbcargo.idcargo = 128 OR  tbcargo.idcargo = 129)';
            $subtitulo .= "<br/>Professores";
        } else {
            $select .= ' AND (tbtipocargo.cargo = "' . $parametroCargo . '")';
            $subtitulo .= "<br/>$parametroCargo";
        }
    }

    # Status
    if (($parametroStatus <> "Todos") AND ($parametroStatus <> "")) {
        $select .= ' AND (tbferias.status = "' . $parametroStatus . '")';
        
        # SubTítulo
        $subtitulo .= "<br/>Ferias {$parametroStatus}s";
    }

    # Perfil
    if (!is_null($parametroPerfil)) {
        $select .= " AND idPerfil = {$parametroPerfil}";
        
        # SubTítulo
        $subtitulo .= "<br/>Perfil: {$servidor->get_nomePerfil($parametroPerfil)}";
    }

    $select .= " ORDER BY lotacao, tbferias.dtInicial";
    $result = $servidor->select($select);

    $relatorio = new Relatorio();    
    $relatorio->set_titulo('Relatório Anual de Férias');
    $relatorio->set_tituloLinha2($parametroAno);
    
    $relatorio->set_tituloLinha3($subtitulo);
    $relatorio->set_subtitulo('Agrupados por Lotação - Ordenados pela Data Inicial');

    $relatorio->set_label(['IdFuncional', 'Nome', 'Lotação', 'Exercício', 'Dt Inicial', 'Dias', 'Dt Final', 'Período', 'Situação']);
    $relatorio->set_align(["center", "left", "left"]);
    $relatorio->set_funcao([null, null, null, null, "date_to_php"]);
    $relatorio->set_classe([null, null, null, null, null, null, null, "pessoal"]);
    $relatorio->set_metodo([null, null, null, null, null, null, null, "get_feriasPeriodo"]);

    $relatorio->set_conteudo($result);
    $relatorio->set_numGrupo(2);
    $relatorio->show();

    $page->terminaPagina();
}
