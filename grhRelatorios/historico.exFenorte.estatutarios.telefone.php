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
                     tbservidor.idServidor,
                     tbservidor.idServidor,
                     tbservidor.idServidor
                FROM tbservidor LEFT JOIN tbpessoa ON (tbservidor.idPessoa = tbpessoa.idPessoa)                                    
                                     JOIN tbhistlot ON (tbservidor.idServidor = tbhistlot.idServidor)
                                     JOIN tblotacao ON (tbhistlot.lotacao=tblotacao.idLotacao)
                                LEFT JOIN tbperfil ON (tbservidor.idPerfil = tbperfil.idPerfil)
               WHERE tbhistlot.data = (select min(data) from tbhistlot where tbhistlot.idServidor = tbservidor.idServidor)
                 AND (tblotacao.UADM = "FENORTE" OR tblotacao.UADM = "TECNORTE")
                 AND tbservidor.idPerfil = 1
            ORDER BY tbpessoa.nome';

    $result = $servidor->select($select);

    $relatorio = new Relatorio();
    $relatorio->set_titulo('Relatório de Servidores');
    $relatorio->set_subtitulo('Ex-Fenorte');
    $relatorio->set_label(['IdFuncional', 'Servidor', 'Telefones', "E-mails"]);
    $relatorio->set_align(["center", "left", "left", "left"]);

    $relatorio->set_classe([null, "pessoal", "pessoal", "pessoal"]);
    $relatorio->set_metodo([null, "get_nomeECargoELotacaoESituacao", "get_telefones", "get_emails"]);
    $relatorio->set_bordaInterna(true);

    $relatorio->set_conteudo($result);
    $relatorio->show();

    $page->terminaPagina();
}