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
                     concat(IFnull(tblotacao.DIR,"")," - ",IFnull(tblotacao.GER,"")) lotacao,
                     tbperfil.nome,
                     tbservidor.dtAdmissao,
                     tbpessoa.sexo,
                     tbservidor.idServidor
                FROM tbservidor LEFT JOIN tbpessoa USING (idPessoa)
                                     JOIN tbhistlot USING (idServidor)
                                     JOIN tblotacao ON (tbhistlot.lotacao=tblotacao.idLotacao)
                                LEFT JOIN tbperfil USING (idPerfil)
                                LEFT JOIN tbcargo USING (idCargo)
                                     JOIN tbtipocargo USING (idTipoCargo) 
               WHERE tbservidor.situacao = 1
                 AND idPerfil = 1
                 AND tbtipocargo.tipo = "Adm/Tec"
                 AND tbhistlot.data = (select max(data) from tbhistlot where tbhistlot.idServidor = tbservidor.idServidor)
            ORDER BY sexo, tbpessoa.nome';

    $result = $servidor->select($select);

    $relatorio = new Relatorio();
    $relatorio->set_titulo('Relatório de Administrativos e Técnicos Estatutários Ativos');
    $relatorio->set_subtitulo('Agrupados por Sexo - Ordenados pelo Nome');
    $relatorio->set_label(['IdFuncional', 'Nome', 'Cargo', 'Lotação', 'Perfil', 'Admissão', 'Sexo']);
    #$relatorio->set_width(array(10,30,30,0,10,10,10));
    $relatorio->set_align(["center", "left", "left", "left"]);
    $relatorio->set_funcao([null, null, null, null, null, "date_to_php"]);

    $relatorio->set_classe([null, null, "pessoal"]);
    $relatorio->set_metodo([null, null, "get_Cargo"]);

    $relatorio->set_conteudo($result);
    $relatorio->set_numGrupo(6);
    $relatorio->show();

    $page->terminaPagina();
}