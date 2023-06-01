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
                     tbperfil.nome,
                     tbservidor.dtAdmissao,
                     tbservidor.dtDemissao,
                     tbsituacao.situacao
                FROM tbservidor LEFT JOIN tbpessoa ON (tbservidor.idPessoa = tbpessoa.idPessoa)                                    
                                     JOIN tbhistlot ON (tbservidor.idServidor = tbhistlot.idServidor)
                                     JOIN tblotacao ON (tbhistlot.lotacao=tblotacao.idLotacao)
                                LEFT JOIN tbperfil ON (tbservidor.idPerfil = tbperfil.idPerfil)
                                     JOIN tbsituacao ON (tbservidor.situacao = tbsituacao.idSituacao)
               WHERE tbservidor.situacao <> 1
                 AND (tbservidor.idPerfil = 1 OR tbservidor.idPerfil = 4)
                 AND tbhistlot.data = (select min(data) from tbhistlot where tbhistlot.idServidor = tbservidor.idServidor)
                 AND (tblotacao.UADM = "FENORTE" OR tblotacao.UADM = "TECNORTE")
            ORDER BY tbsituacao.situacao, tbpessoa.nome';

    $result = $servidor->select($select);

    $relatorio = new Relatorio();
    $relatorio->set_titulo('Relatório de Servidores Inativos');
    $relatorio->set_subtitulo('Ex-Fenorte');
    $relatorio->set_label(['IdFuncional', 'Nome', 'Cargo', 'Perfil', 'Admissão', 'Saída', 'Situação']);
    $relatorio->set_align(["center", "left", "left"]);
    $relatorio->set_funcao([null, null, null, null, "date_to_php", "date_to_php"]);

    $relatorio->set_classe([null, null, "pessoal"]);
    $relatorio->set_metodo([null, null, "get_Cargo"]);

    $relatorio->set_conteudo($result);
    $relatorio->set_numGrupo(6);
    $relatorio->show();

    $page->terminaPagina();
}