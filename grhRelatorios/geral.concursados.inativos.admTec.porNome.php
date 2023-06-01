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
                     tbperfil.nome,
                     tbservidor.dtAdmissao,
                     tbservidor.dtDemissao,
                     tbservidor.idServidor
                FROM tbservidor LEFT JOIN tbpessoa ON (tbservidor.idPessoa = tbpessoa.idPessoa)                                    
                                LEFT JOIN tbcargo USING (idCargo)
                                     JOIN tbtipocargo USING (idTipoCargo)
                                LEFT JOIN tbperfil ON (tbservidor.idPerfil = tbperfil.idPerfil)
               WHERE tbservidor.situacao <> 1
                 AND tbperfil.tipo <> "Outros"
                 AND idConcurso IS NOT NULL
                 AND tbtipocargo.tipo = "Adm/Tec"
            ORDER BY tbpessoa.nome';

    $result = $servidor->select($select);

    $relatorio = new Relatorio();
    $relatorio->set_titulo('Relatório de Servidores Concursados Inativos - Administrativos e Técnicos');
    $relatorio->set_subtitulo('Ordenados pelo Nome');
    $relatorio->set_label(['IdFuncional', 'Nome', 'Cargo', 'Lotação', 'Perfil', 'Admissão', 'Saída', 'Situação']);
    $relatorio->set_align(["center", "left", "left", "left"]);
    $relatorio->set_funcao([null, null, null, null, null, "date_to_php", "date_to_php"]);

    $relatorio->set_classe([null, null, "pessoal", "pessoal", null, null, null, "pessoal"]);
    $relatorio->set_metodo([null, null, "get_Cargo", "get_Lotacao", null, null, null, "get_Situacao"]);

    $relatorio->set_conteudo($result);
    $relatorio->show();

    $page->terminaPagina();
}