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
                     concat(IFNULL(tblotacao.UADM,"")," - ",IFNULL(tblotacao.DIR,"")," - ",IFNULL(tblotacao.GER,"")," - ",IFNULL(tblotacao.nome,"")) lotacao,
                     tbservidor.idServidor,
                     tbrecadastramento.dataAtualizacao
                FROM tbrecadastramento LEFT JOIN tbservidor USING (idServidor)
                                            JOIN tbpessoa USING (idPessoa)
                                            JOIN tbhistlot USING (idServidor)
                                            JOIN tblotacao ON (tbhistlot.lotacao = tblotacao.idLotacao)
              WHERE tbhistlot.data = (select max(data) from tbhistlot where tbhistlot.idServidor = tbservidor.idServidor)
                AND tbservidor.situacao <> 1
                ORDER BY lotacao, tbpessoa.nome';

    $result = $servidor->select($select);

    $relatorio = new Relatorio();

    $relatorio->set_titulo('Relatório De Servidores Inativos Recadastrados');
    $relatorio->set_subtitulo('Agrupada por Lotaçao - Ordenados pelo Nome');
    $relatorio->set_label(array('IdFuncional', 'Nome', 'Cargo', 'Lotação', 'Situacao', 'Atualizado em:'));
    $relatorio->set_align(array("center", "left", "left", "left"));
    $relatorio->set_funcao(array(NULL, NULL, NULL, NULL, NULL, "date_to_php"));

    $relatorio->set_classe(array(NULL, NULL, "pessoal", NULL, "pessoal"));
    $relatorio->set_metodo(array(NULL, NULL, "get_CargoRel", NULL, "get_situacao"));

    $relatorio->set_conteudo($result);
    $relatorio->set_numGrupo(3);
    #$relatorio->set_botaoVoltar('../sistema/areaServidor.php');
    $relatorio->show();

    $page->terminaPagina();
}