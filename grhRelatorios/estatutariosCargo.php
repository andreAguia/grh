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

    ######

    $select = 'SELECT tbservidor.idFuncional,
                     tbpessoa.nome,
                     CONCAT(tbtipocargo.cargo," - ",tbcargo.nome),
                     CONCAT(tblotacao.UADM," - ",tblotacao.DIR," - ",tblotacao.GER) lotacao,
                     tbperfil.nome,
                     tbservidor.dtAdmissao,
                     tbservidor.idServidor
                FROM tbservidor LEFT JOIN tbpessoa ON (tbservidor.idPessoa = tbpessoa.idPessoa)
                                LEFT JOIN tbcargo ON (tbservidor.idCargo = tbcargo.idCargo)
                                LEFT JOIN tbtipocargo ON (tbcargo.idtipocargo = tbtipocargo.idtipocargo)
                                     JOIN tbhistlot ON (tbservidor.idServidor = tbhistlot.idServidor)
                                     JOIN tblotacao ON (tbhistlot.lotacao=tblotacao.idLotacao)
                                LEFT JOIN tbsituacao ON (tbservidor.situacao = tbsituacao.idSituacao)
                                LEFT JOIN tbperfil ON (tbservidor.idPerfil = tbperfil.idPerfil)
                WHERE tbservidor.situacao = 1 AND tbservidor.idPerfil = 1
                  AND tbhistlot.data = (select max(data) from tbhistlot where tbhistlot.idServidor = tbservidor.idServidor)
             ORDER BY tbtipocargo.cargo, tbcargo.nome, tbpessoa.nome';

    $result = $servidor->select($select);

    $relatorio = new Relatorio();
    $relatorio->set_titulo('Relatório de Estatutários');
    $relatorio->set_subtitulo('Agrupados por Cargo - Ordenados pelo Nome');
    $relatorio->set_label(array('IdFuncional', 'Nome', 'Cargo', 'Lotação', 'Perfil', 'Admissão', 'Situação'));
    $relatorio->set_width(array(10, 30, 0, 30, 10, 10, 10));
    $relatorio->set_align(array("center", "left", "left", "left"));
    $relatorio->set_funcao(array(null, null, null, null, null, "date_to_php"));
    $relatorio->set_classe(array(null, null, null, null, null, null, "Pessoal"));
    $relatorio->set_metodo(array(null, null, null, null, null, null, "get_Situacao"));
    $relatorio->set_conteudo($result);
    $relatorio->set_numGrupo(2);
    #$relatorio->set_botaoVoltar('../sistema/areaServidor.php');
    $relatorio->show();

    $page->terminaPagina();
}