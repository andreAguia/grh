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

    $select2 .= "
         AND tbservidor.situacao = 1        
         ORDER BY tbpessoa.nome asc";

    $result = $servidor->select($select2);

    $relatorio = new Relatorio();
    $relatorio->set_titulo('Relatório de Férias com Pendências');

    if (!is_null($parametroLotacao)) {
        $relatorio->set_tituloLinha3($servidor->get_nomeLotacao($parametroLotacao));
    }

    $relatorio->set_label(array("Id", "Servidor", "Lotação", "Admissão","Pendências","Situação"));
    $relatorio->set_align(array("center", "left"));
    $relatorio->set_funcao(array(null, null, null, "date_to_php", "trataNulo", "get_situacaoRel"));
    $relatorio->set_classe(array(null, "pessoal", "pessoal", null, "Ferias"));
    $relatorio->set_metodo(array(null, "get_nomeECargoEPerfil", "get_lotacaoSimples", null, "exibeFeriasPendentes"));
    $relatorio->set_bordaInterna(true);
    $relatorio->set_conteudo($result);
    $relatorio->show();

    $page->terminaPagina();
}
