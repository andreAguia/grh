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
                      tbdocumentacao.CPF,
                      tbservidor.idServidor,
                      tbservidor.idServidor
                FROM tbservidor JOIN tbpessoa USING (idPessoa)
                                JOIN tbdocumentacao USING (idPessoa)
                                JOIN tbcargo USING (idCargo)
                                JOIN tbtipocargo USING (idTipoCargo)
               WHERE situacao = 1
                 AND tbtipocargo.tipo = "Professor"
                 AND tbservidor.idPerfil = 1
            ORDER BY tbpessoa.nome';

    $result = $servidor->select($select);

    $relatorio = new Relatorio();
    $relatorio->set_titulo('Relatório de Professores Ativos');
    $relatorio->set_subtitulo('Ordenados pelo Nome');
    $relatorio->set_label(["idFuncional", "Nome", "CPF", "Email", "Cargo"]);
    $relatorio->set_width([10, 25, 15, 25, 20]);
    $relatorio->set_align(["center", "left", "center", "left", "left"]);

    $relatorio->set_classe([null, null, null, "pessoal", "pessoal"]);
    $relatorio->set_metodo([null, null, null, "get_emails", "get_cargoSimples"]);

    $relatorio->set_conteudo($result);
    $relatorio->show();

    $page->terminaPagina();
}