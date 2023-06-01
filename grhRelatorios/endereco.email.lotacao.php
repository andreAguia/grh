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
                     concat(IFnull(tblotacao.UADM,"")," - ",IFnull(tblotacao.DIR,"")," - ",IFnull(tblotacao.GER,"")," - ",IFnull(tblotacao.nome,"")) lotacao,
                     tbservidor.idServidor,
                     tbservidor.idServidor,
                     tbservidor.idServidor,
                     tbservidor.idServidor,
                     tbperfil.nome
                FROM tbservidor LEFT JOIN tbpessoa ON (tbservidor.idPessoa = tbpessoa.idPessoa) 
                                     JOIN tbhistlot ON (tbservidor.idServidor = tbhistlot.idServidor)
                                     JOIN tblotacao ON (tbhistlot.lotacao=tblotacao.idLotacao)
                                     JOIN tbperfil USING (idPerfil)
               WHERE tbservidor.situacao = 1
                 AND tbperfil.tipo <> "Outros"
                 AND tbhistlot.data = (select max(data) from tbhistlot where tbhistlot.idServidor = tbservidor.idServidor)
            ORDER BY lotacao, tbpessoa.nome';


    $result = $servidor->select($select);

    $relatorio = new Relatorio();
    $relatorio->set_titulo('Relatório de Servidores Ativos com Endereço, Emails e Telefones');
    $relatorio->set_subtitulo('Agrupado por Lotaçao e Ordenado pelo nome');
    $relatorio->set_label(['IdFuncional', 'Nome', 'Lotação', 'Cargo', 'Endereço', 'E-mail', 'Telefones', 'Perfil']);
    $relatorio->set_align(["center", "left", "left", "left", "left", "left", "left"]);
    $relatorio->set_classe([null, null, null, "pessoal", "pessoal", "pessoal", "pessoal"]);
    $relatorio->set_metodo([null, null, null, "get_cargo", "get_enderecoRel", "get_emails", "get_telefones"]);
    $relatorio->set_bordaInterna(true);
    $relatorio->set_numGrupo(2);
    $relatorio->set_conteudo($result);
    $relatorio->show();

    $page->terminaPagina();
}