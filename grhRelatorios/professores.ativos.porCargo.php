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
                     tbservidor.idServidor
                 FROM tbservidor JOIN tbpessoa USING (idPessoa)
                                 JOIN tbperfil USING (idPerfil)      
                                 LEFT JOIN tbcargo USING (idCargo)
                                 JOIN tbtipocargo USING (idTipoCargo)
               WHERE tbservidor.situacao = 1
                 AND tbperfil.tipo <> "Outros"
                 AND tbtipocargo.tipo = "Professor"
            ORDER BY tbtipocargo.cargo, tbpessoa.nome';

    $result = $servidor->select($select);

    $relatorio = new Relatorio();
    $relatorio->set_titulo('Relatório de Professores Ativos');
    $relatorio->set_tituloLinha2('Agrupado pelo Cargo');
    $relatorio->set_subtitulo("Ordenados pelo Nome");
    $relatorio->set_label(['IdFuncional', 'Nome', 'Cargo', 'Lotação', 'Perfil', 'Admissão', 'Situação']);
    $relatorio->set_align(["center", "left", "left", "left"]);
    $relatorio->set_funcao([null, null, null, null, null, "date_to_php"]);
    $relatorio->set_numGrupo(2);

    $relatorio->set_classe([null, null, null, "pessoal", null, null, "pessoal"]);
    $relatorio->set_metodo([null, null, null, "get_Lotacao", null, null, "get_Situacao"]);

    $relatorio->set_conteudo($result);
    $relatorio->show();

    $page->terminaPagina();
}