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
                     tbservidor.dtAdmissao
                FROM tbservidor LEFT JOIN tbpessoa ON (tbservidor.idPessoa = tbpessoa.idPessoa)                                    
                                     JOIN tbhistlot ON (tbservidor.idServidor = tbhistlot.idServidor)
                                     JOIN tblotacao ON (tbhistlot.lotacao=tblotacao.idLotacao)
                                LEFT JOIN tbperfil ON (tbservidor.idPerfil = tbperfil.idPerfil)
               WHERE tbservidor.situacao = 1
                 AND tbhistlot.data = (select min(data) from tbhistlot where tbhistlot.idServidor = tbservidor.idServidor)
                 AND (tblotacao.UADM = "FENORTE" OR tblotacao.UADM = "TECNORTE")
            ORDER BY tbpessoa.nome';

    $result = $servidor->select($select);

    $relatorio = new Relatorio();
    $relatorio->set_titulo('Relatório de Servidores Ex-Fenorte Ativos');
    $relatorio->set_subtitulo('Estatutários');
    $relatorio->set_label(['IdFuncional', 'Servidor', 'Cargo', 'Lotação', 'Admissão']);
    $relatorio->set_align(["center", "left", "left", "left"]);
    $relatorio->set_funcao([null, null, null, null, "date_to_php"]);

    $relatorio->set_classe([null, null, "pessoal", "pessoal"]);
    $relatorio->set_metodo([null, null, "get_cargo", "get_lotacao"]);

    $relatorio->set_conteudo($result);
    $relatorio->show();

    $page->terminaPagina();
}