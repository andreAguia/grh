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

    $select = "SELECT tbservidor.matricula,
                      tbpessoa.nome,
                      tbservidor.idServidor,
                      tbservidor.dtAdmissao,
                      tbservidor.idServidor
                FROM tbservidor JOIN tbpessoa USING (idPessoa)
                                JOIN tbdocumentacao USING (idPessoa)
                                JOIN tbcargo USING (idCargo)
                                JOIN tbtipocargo USING (idTipoCargo)
               WHERE tbtipocargo.tipo = 'Professor'
                 AND (tbservidor.idPerfil = 1 OR tbservidor.idPerfil = 4)
            ORDER BY tbpessoa.nome";

    $result = $servidor->select($select);

    $relatorio = new Relatorio();
    $relatorio->set_titulo('Relatório de Professores');
    $relatorio->set_subtitulo('Ordenados pelo Nome');
    $relatorio->set_label(["Matrícula", "Nome", "Email", "Admissão", "Situação"]);
    $relatorio->set_align(["center", "left", "left", "left"]);
    $relatorio->set_funcao(["dv", null, null, "date_to_php"]);

    $relatorio->set_classe([null, null, "pessoal", null, "pessoal"]);
    $relatorio->set_metodo([null, null, "get_emailUenf", null, "get_Situacao"]);

    $relatorio->set_conteudo($result);
    $relatorio->show();

    $page->terminaPagina();
}