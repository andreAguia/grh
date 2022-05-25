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
                     tbservidor.matricula,
                     tbpessoa.nome,
                     tbservidor.idServidor,
                     concat(IFnull(tblotacao.UADM,"")," - ",IFnull(tblotacao.DIR,"")," - ",IFnull(tblotacao.GER,"")," - ",IFnull(tblotacao.nome,"")) lotacao,
                     tbservidor.idServidor,
                     tbperfil.nome,
                     tbsituacao.situacao
                FROM tbservidor LEFT JOIN tbpessoa ON (tbservidor.idPessoa = tbpessoa.idPessoa)                                    
                                     JOIN tbhistlot ON (tbservidor.idServidor = tbhistlot.idServidor)
                                     JOIN tblotacao ON (tbhistlot.lotacao=tblotacao.idLotacao)
                                LEFT JOIN tbperfil ON (tbservidor.idPerfil = tbperfil.idPerfil)
                                     JOIN tbsituacao ON (tbservidor.situacao = tbsituacao.idSituacao)
               WHERE (tbservidor.situacao = 1 OR tbservidor.situacao = 2)
                 AND (tbservidor.idPerfil = 1 OR tbservidor.idPerfil = 4)
                 AND tbhistlot.data = (select max(data) from tbhistlot where tbhistlot.idServidor = tbservidor.idServidor)
            ORDER BY tbpessoa.nome';

    $result = $servidor->select($select);

    $relatorio = new Relatorio();
    $relatorio->set_titulo('Relatório de Ativos e Aposentados Com Endereço');
    $relatorio->set_subtitulo('Ordenado pelo nome');
    $relatorio->set_label(array('IdFuncional', 'Matrícula', 'Nome', 'Cargo', 'Lotação', 'Endereço', 'Perfil', 'Situação'));
    #$relatorio->set_width(array(10,30,30,0,10,10,10));
    $relatorio->set_align(array("center", "center", "left", "left", "left", "left"));
    $relatorio->set_funcao(array(null, "dv"));

    $relatorio->set_classe(array(null, null, null, "pessoal", null, "pessoal"));
    $relatorio->set_metodo(array(null, null, null, "get_cargo", null, "get_endereco"));

    $relatorio->set_conteudo($result);
    #$relatorio->set_numGrupo(7);
    #$relatorio->set_botaoVoltar('../sistema/areaServidor.php');
    $relatorio->show();

    $page->terminaPagina();
}