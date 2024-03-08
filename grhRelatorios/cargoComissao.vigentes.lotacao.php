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
                     tbservidor.idServidor,
                     tbservidor.idServidor,
                     concat(IFnull(tblotacao.UADM,"")," - ",IFnull(tblotacao.DIR,"")) lotacao,
                     tbperfil.nome,
                     tbservidor.dtAdmissao,
                     tbservidor.idServidor
                FROM tbservidor LEFT JOIN tbpessoa ON (tbservidor.idPessoa = tbpessoa.idPessoa)
                                     JOIN tbhistlot ON (tbservidor.idServidor = tbhistlot.idServidor)
                                     JOIN tblotacao ON (tbhistlot.lotacao=tblotacao.idLotacao)
                                LEFT JOIN tbperfil ON (tbservidor.idPerfil = tbperfil.idPerfil)
                                LEFT JOIN tbcomissao ON(tbservidor.idServidor = tbcomissao.idServidor)
                                     JOIN tbtipocomissao ON(tbcomissao.idTipoComissao=tbtipocomissao.idTipoComissao)
                                     JOIN tbdescricaocomissao  USING (idDescricaoComissao)
                                     JOIN tbtiponomeacao ON (tbcomissao.tipo = tbtiponomeacao.idTipoNomeacao)
               WHERE tbtiponomeacao.visibilidade <> 2
                 AND tbtipocomissao.ativo IS true
                 AND (tbcomissao.dtExo IS null OR CURDATE() < tbcomissao.dtExo)
                 AND tbcomissao.tipo <> 3
                 AND tbservidor.situacao = 1
                 AND tbservidor.idPerfil = 1
                 AND tbhistlot.data = (select max(data) from tbhistlot where tbhistlot.idServidor = tbservidor.idServidor)
            ORDER BY lotacao, tbpessoa.nome';

    $result = $servidor->select($select);

    $relatorio = new Relatorio();
    $relatorio->set_titulo('Relatório de Servidores Estatutários Ativos');
    $relatorio->set_subtitulo('Agrupados por Lotação - Ordenados pelo Nome');
    $relatorio->set_label(array('IdFuncional', 'Nome', 'Cargo', 'Lotação', 'Perfil', 'Admissão', 'Situação'));
    $relatorio->set_width(array(10, 30, 30, 0, 10, 10, 10));
    $relatorio->set_align(array("center", "left", "left"));
    $relatorio->set_funcao(array(null, null, null, null, null, "date_to_php"));

    $relatorio->set_classe(array(null, null, "pessoal","pessoal", null, null, null, "pessoal"));
    $relatorio->set_metodo(array(null, null, "get_CargoSimples","get_CargoComissao", null, null, null, "get_Situacao"));

    $relatorio->set_conteudo($result);
    $relatorio->set_numGrupo(4);
    #$relatorio->set_botaoVoltar('../sistema/areaServidor.php');
    $relatorio->show();

    $page->terminaPagina();
}