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

    $select = 'SELECT distinct tbservidor.idFuncional,
                     tbpessoa.nome,
                     tbservidor.idServidor,
                     concat(tblotacao.UADM," - ",tblotacao.DIR," - ",tblotacao.GER) lotacao,                 
                     tbservidor.dtAdmissao,
                     CONCAT(tbconcurso.anoBase," - ",tbconcurso.regime," - ",tbconcurso.orgExecutor) as aa
                FROM tbservidor LEFT JOIN tbpessoa ON (tbservidor.idPessoa = tbpessoa.idPessoa)
                                   LEFT JOIN tbcargo ON (tbservidor.idCargo = tbcargo.idCargo)
                                        JOIN tbhistlot ON (tbservidor.idServidor = tbhistlot.idServidor)
                                        JOIN tblotacao ON (tbhistlot.lotacao=tblotacao.idLotacao)
                                   LEFT JOIN tbsituacao ON (tbservidor.situacao = tbsituacao.idSituacao)
                                   LEFT JOIN tbconcurso ON (tbservidor.idConcurso = tbconcurso.idConcurso)
                WHERE tbservidor.situacao = 1 AND tbservidor.idPerfil = 1
                  AND tbhistlot.data = (select max(data) from tbhistlot where tbhistlot.idServidor = tbservidor.idServidor)
                  AND tbconcurso.anoBase IS NOT NULL
             ORDER BY tbconcurso.anoBase, tbpessoa.nome';

    $result = $servidor->select($select);

    $relatorio = new Relatorio();
    $relatorio->set_titulo('Relatório de Estatutários');
    $relatorio->set_subtitulo('Agrupados por Concurso - Ordenados pelo Nome do Servidor');

    $relatorio->set_label(array('IdFuncional', 'Nome', 'Cargo', 'Lotação', 'Admissão', ''));
    $relatorio->set_width(array(10, 30, 20, 30, 10));
    $relatorio->set_align(array("center", "left", "left", "left"));
    $relatorio->set_funcao(array(NULL, NULL, NULL, NULL, "date_to_php"));
    $relatorio->set_classe(array(NULL, NULL, "Pessoal"));
    $relatorio->set_metodo(array(NULL, NULL, "get_cargo"));

    $relatorio->set_conteudo($result);
    $relatorio->set_numGrupo(5);
    #$relatorio->set_botaoVoltar('../sistema/areaServidor.php');
    $relatorio->show();

    $page->terminaPagina();
}
?>
