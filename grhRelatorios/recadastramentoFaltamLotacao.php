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

    ######

    $select = 'SELECT tbservidor.idFuncional,
                    tbpessoa.nome,
                    tbservidor.idServidor,
                    concat(IFNULL(tblotacao.UADM,"")," - ",IFNULL(tblotacao.DIR,"")," - ",IFNULL(tblotacao.GER,"")," - ",IFNULL(tblotacao.nome,"")) lotacao
               FROM tbservidor LEFT JOIN tbpessoa USING (idPessoa)
                               LEFT JOIN tbrecadastramento USING (idServidor)
                               LEFT JOIN tbperfil USING (idPerfil)
                               JOIN tbhistlot USING (idServidor)
                               JOIN tblotacao ON (tbhistlot.lotacao = tblotacao.idLotacao)
                               JOIN tbcargo USING (idCargo)
                               JOIN tbtipocargo USING (idTipoCargo)
             WHERE tbhistlot.data = (select max(data) from tbhistlot where tbhistlot.idServidor = tbservidor.idServidor)
               AND tbservidor.situacao = 1
               AND tbrecadastramento.dataAtualizacao is NULL
               ORDER BY lotacao, tbpessoa.nome';

    $result = $servidor->select($select);

    $relatorio = new Relatorio();

    $relatorio->set_titulo('Relatório De Servidores Ativos NÃO Recadastrados');
    $relatorio->set_subtitulo('Agrupada por Lotaçao - Ordenados pelo Nome');
    $relatorio->set_label(array('IdFuncional', 'Nome', 'Cargo', 'Lotação'));
    $relatorio->set_align(array("center", "left", "left", "left"));

    $relatorio->set_classe(array(NULL, NULL, "pessoal"));
    $relatorio->set_metodo(array(NULL, NULL, "get_CargoRel"));

    $relatorio->set_conteudo($result);
    $relatorio->set_numGrupo(3);
    #$relatorio->set_botaoVoltar('../sistema/areaServidor.php');
    $relatorio->show();

    $page->terminaPagina();
}