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

    $select = 'SELECT tbservidor.idServidor,
                     tbservidor.idServidor,
                     concat(IFnull(tblotacao.UADM,"")," - ",IFnull(tblotacao.DIR,"")) lotacao,
                     tbcomissao.dtNom
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
                 AND (tbcomissao.idTipoComissao = 16 OR tbcomissao.idTipoComissao = 17 OR tbcomissao.idTipoComissao = 19)
                 AND tbservidor.situacao = 1
                 AND tbhistlot.data = (select max(data) from tbhistlot where tbhistlot.idServidor = tbservidor.idServidor)
                 AND (DIR = "CBB" OR DIR = "CCTA" OR DIR = "CCT" OR DIR = "CCH") 
            ORDER BY lotacao, tbcomissao.idTipoComissao, tbpessoa.nome';

    $result = $servidor->select($select);

    $relatorio = new Relatorio();
    $relatorio->set_titulo('Relatório de Servodores com Cargo em Comissão Eletivos');
    $relatorio->set_subtitulo('Agrupados por Lotação');
    $relatorio->set_label(['Servidor', 'Comissão', 'Lotação', 'Nomeação']);
    $relatorio->set_width([40, 40, 0, 20]);
    $relatorio->set_align(["left", "left"]);
    $relatorio->set_funcao([null, null, null, "date_to_php"]);

    $relatorio->set_classe(["pessoal", "pessoal"]);
    $relatorio->set_metodo(["get_nomeECargoEId", "get_CargoComissao2"]);

    $relatorio->set_conteudo($result);
    $relatorio->set_numGrupo(2);
    $relatorio->set_bordaInterna(true);

    $relatorio->show();

    $page->terminaPagina();
}