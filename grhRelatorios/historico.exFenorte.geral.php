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
    $concurso = new Concurso();

    # Começa uma nova página
    $page = new Page();
    $page->iniciaPagina();

    # Pega os parâmetros dos relatórios
    $parametroConcurso = post('concurso', 1);

    ######

    $select = 'SELECT tbservidor.idServidor,
                     tbpessoa.nome,
                     tbservidor.idServidor,
                     tbperfil.nome,
                     tbservidor.dtAdmissao,
                     tbservidor.dtDemissao,
                     tbsituacao.situacao
                FROM tbservidor LEFT JOIN tbpessoa ON (tbservidor.idPessoa = tbpessoa.idPessoa)                                    
                                     JOIN tbhistlot ON (tbservidor.idServidor = tbhistlot.idServidor)
                                     JOIN tblotacao ON (tbhistlot.lotacao=tblotacao.idLotacao)
                                     JOIN tbsituacao ON (tbservidor.situacao = tbsituacao.idsituacao)
                                LEFT JOIN tbperfil ON (tbservidor.idPerfil = tbperfil.idPerfil)
               WHERE (tbservidor.idPerfil = 1 OR tbservidor.idPerfil = 4)
                 AND tbhistlot.data = (select min(data) from tbhistlot where tbhistlot.idServidor = tbservidor.idServidor)
                 AND (tblotacao.UADM = "FENORTE" OR tblotacao.UADM = "TECNORTE")
            ORDER BY tbservidor.situacao, tbpessoa.nome';

    $result = $servidor->select($select);

    $relatorio = new Relatorio();
    $relatorio->set_titulo('Relatório Geral de Servidores Ex-Fenorte');

    #$relatorio->set_subtitulo('Concurso de ' . $concurso->get_nomeConcurso($parametroConcurso));
    $relatorio->set_label(['IdFuncional', 'Nome', 'Cargo', 'Perfil', 'Admissão', 'Saída', 'Situação']);
    $relatorio->set_width([10, 30, 20, 20, 10, 10]);
    $relatorio->set_align(["center", "left", "left"]);
    $relatorio->set_funcao([null, null, null, null, "date_to_php", "date_to_php"]);

    $relatorio->set_classe(["pessoal", null, "pessoal"]);
    $relatorio->set_metodo(["get_idFuncionalEMatricula", null, "get_cargoSimples"]);

    $relatorio->set_conteudo($result);
    $relatorio->set_bordaInterna(true);
    $relatorio->set_numGrupo(6);
    $relatorio->show();

    $page->terminaPagina();
}