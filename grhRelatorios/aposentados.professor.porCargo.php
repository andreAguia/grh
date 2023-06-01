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
                     tbtipocargo.cargo,
                     tbservidor.idServidor,
                     tbperfil.nome,
                     tbservidor.dtAdmissao,
                     tbservidor.dtdemissao
                FROM tbservidor JOIN tbpessoa USING (idPessoa)
                                LEFT JOIN tbcargo USING (idCargo)
                                     JOIN tbtipocargo USING (idTipoCargo)
                                JOIN tbperfil USING (idPerfil)
               WHERE tbservidor.situacao = 2
                 AND tbperfil.tipo <> "Outros" 
                 AND tbtipocargo.tipo = "Professor"
            ORDER BY tbtipocargo.cargo, tbpessoa.nome';

    $result = $servidor->select($select);

    $relatorio = new Relatorio();
    $relatorio->set_titulo('Relatório de Professores Aposentados');
    $relatorio->set_subtitulo("Agrupado pelo Cargo - Ordenados pelo Nome");
    $relatorio->set_label(['IdFuncional', 'Nome', 'Cargo', 'Lotação', 'Perfil', 'Admissão', 'Aposentadoria']);
    $relatorio->set_align(["center", "left", "left", "left"]);
    $relatorio->set_funcao([null, null, null, null, null, "date_to_php", "date_to_php"]);
    $relatorio->set_numGrupo(2);

    $relatorio->set_classe([null, null, null, "pessoal"]);
    $relatorio->set_metodo([null, null, null, "get_Lotacao"]);

    $relatorio->set_conteudo($result);
    #$relatorio->set_numGrupo(3);
    #$relatorio->set_botaoVoltar('../sistema/areaServidor.php');
    $relatorio->show();

    $page->terminaPagina();
}