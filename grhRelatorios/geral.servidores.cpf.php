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
                     tbdocumentacao.cpf,
                     tbperfil.nome
                FROM tbservidor LEFT JOIN tbpessoa USING (idPessoa)
                                     JOIN tbdocumentacao USING (idpessoa)
                                     JOIN tbperfil USING (idPerfil)
                WHERE tbperfil.tipo <> "Outros"
            ORDER BY tbpessoa.nome';

    $result = $servidor->select($select);

    $relatorio = new Relatorio();
    $relatorio->set_titulo('Relatório Geral de Servidores Ativos e Inativos');
    $relatorio->set_subtitulo('Ordenados pelo Nome');
    $relatorio->set_label(['IdFuncional', 'Matrícula', 'Nome', 'CPF', 'Perfil']);
    $relatorio->set_align(["center", "center", "left", "cener"]);

    $relatorio->set_conteudo($result);
    $relatorio->show();

    $page->terminaPagina();
}