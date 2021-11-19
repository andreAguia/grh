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

    $select2 = "SELECT tbservidor.idFuncional,
                           tbservidor.idServidor,
                           tbservidor.idServidor,
                           tbservidor.dtAdmissao,
                           tbservidor.idServidor,
                           tbservidor.idServidor
                      FROM tbpessoa LEFT JOIN tbservidor USING (idPessoa)
                                         JOIN tbhistlot USING (idServidor)
                                         JOIN tblotacao ON (tbhistlot.lotacao=tblotacao.idLotacao)
                                         JOIN tbsituacao ON (tbservidor.situacao = tbsituacao.idSituacao) 
                     WHERE tbhistlot.data = (select max(data) from tbhistlot where tbhistlot.idServidor = tbservidor.idServidor)";

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
         ORDER BY tbpessoa.nome asc";

    $result = $servidor->select($select2);

    $relatorio = new Relatorio();
    $relatorio->set_titulo('Relatório de Férias com Pendências');

    $linha3 = "Servidores {$servidor->get_nomeSituacao($parametroSituacao)}s";

    if (!is_null($parametroPerfil)) {
        $linha3 .= "<br/>Perfil: {$servidor->get_nomePerfil($parametroPerfil)}";
    }

    if (!is_null($parametroLotacao)) {
        $linha3 .= "<br/>{$servidor->get_nomeLotacao($parametroLotacao)}";
    }

    $relatorio->set_tituloLinha3($linha3);

    $relatorio->set_label(array("Id", "Servidor", "Lotação", "Admissão", "Pendências", "Situação"));
    $relatorio->set_align(array("center", "left"));
    $relatorio->set_funcao(array(null, null, null, "date_to_php", "trataNulo", "get_situacaoRel"));
    $relatorio->set_classe(array(null, "pessoal", "pessoal", null, "Ferias"));
    $relatorio->set_metodo(array(null, "get_nomeECargoEPerfil", "get_lotacaoSimples", null, "exibeFeriasPendentes"));
    $relatorio->set_bordaInterna(true);
    $relatorio->set_conteudo($result);
    $relatorio->show();

    $page->terminaPagina();
}
