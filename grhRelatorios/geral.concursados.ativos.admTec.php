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

# Pega a sigla
$sigla = get("sigla");

if ($acesso) {
    # Conecta ao Banco de Dados
    $servidor = new Pessoal();

    # Começa uma nova página
    $page = new Page();
    $page->iniciaPagina();

    ######

    $select = "SELECT tbservidor.idFuncional,
                     tbpessoa.nome,
                     tbservidor.idServidor,
                     tbtipocargo.cargo,
                     tbservidor.idServidor,
                     tbperfil.nome,
                     tbservidor.dtAdmissao,
                     tbservidor.idServidor
                FROM tbservidor LEFT JOIN tbpessoa ON (tbservidor.idPessoa = tbpessoa.idPessoa)                                    
                                LEFT JOIN tbcargo USING (idCargo)
                                     JOIN tbtipocargo USING (idTipoCargo)
                                     JOIN tbperfil USING (idPerfil)
               WHERE (idPerfil = 1 OR idPerfil = 4)                       
                 AND situacao = 1
                 AND tbtipocargo.tipo = 'Adm/Tec'
                 AND tbtipocargo.sigla = '{$sigla}'                 
            ORDER BY tbtipocargo.cargo, tbpessoa.nome";

    $result = $servidor->select($select);

    $relatorio = new Relatorio();
    $relatorio->set_titulo('Relatório de Servidores Concursados Ativos<br/>Administrativos e Técnicos');
    $relatorio->set_subtitulo('Ordenados pelo Nome');
    $relatorio->set_label(['IdFuncional', 'Nome', 'Cargo', 'Tipo', 'Lotação', 'Perfil', 'Admissão', 'Situação']);
    $relatorio->set_align(["center", "left", "left", "left", "left"]);
    $relatorio->set_funcao([null, null, null, null, null, null, "date_to_php"]);

    $relatorio->set_classe([null, null, "pessoal", null, "pessoal", null, null, "pessoal"]);
    $relatorio->set_metodo([null, null, "get_Cargo", null, "get_Lotacao", null, null, "get_Situacao"]);

    $relatorio->set_conteudo($result);
    $relatorio->set_numGrupo(3);
    $relatorio->set_totalRegistro(false);
    $relatorio->show();

    $page->terminaPagina();
}