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
                     concat(IFNULL(tblotacao.UADM,"")," - ",IFNULL(tblotacao.DIR,"")," - ",IFNULL(tblotacao.GER,"")," - ",IFNULL(tblotacao.nome,"")) lotacao,
                     tbservidor.idServidor,
                     tbservidor.idServidor,
                     tbservidor.idServidor,
                     tbservidor.idServidor,
                     tbperfil.nome
                FROM tbservidor LEFT JOIN tbpessoa ON (tbservidor.idPessoa = tbpessoa.idPessoa) 
                                     JOIN tbhistlot ON (tbservidor.idServidor = tbhistlot.idServidor)
                                     JOIN tblotacao ON (tbhistlot.lotacao=tblotacao.idLotacao)
                                LEFT JOIN tbperfil ON (tbservidor.idPerfil = tbperfil.idPerfil)
               WHERE tbhistlot.data = (select max(data) from tbhistlot where tbhistlot.idServidor = tbservidor.idServidor)
                 AND tbservidor.situacao = 1
                 AND tbservidor.idPerfil <> 10
            ORDER BY lotacao, tbpessoa.nome';


    $result = $servidor->select($select);

    $relatorio = new Relatorio();
    $relatorio->set_titulo('Relatório de Servidores com Endereço, Emails e Telefones');
    $relatorio->set_subtitulo('Agrupado por Lotaçao e Ordenado pelo nome');
    $relatorio->set_label(array('IdFuncional', 'Nome', 'Lotação', 'Cargo', 'Endereço', 'E-mail', 'Telefones', 'Perfil'));
    $relatorio->set_bordaInterna(TRUE);
    $relatorio->set_align(array("center", "left", "left", "left", "left", "left", "left"));
    #$relatorio->set_funcao(array(NULL,NULL,NULL,NULL,"plm"));

    $relatorio->set_classe(array(NULL, NULL, NULL, "pessoal", "pessoal", "pessoal", "pessoal"));
    $relatorio->set_metodo(array(NULL, NULL, NULL, "get_cargo", "get_enderecoRel", "get_emails", "get_telefones"));
    $relatorio->set_numGrupo(2);
    $relatorio->set_conteudo($result);
    $relatorio->show();

    $page->terminaPagina();
}