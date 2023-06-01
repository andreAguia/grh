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
                     tbdocumentacao.cpf,
                     tbpessoa.emailUenf
                FROM tbservidor LEFT JOIN tbpessoa USING (idPessoa)
                                LEFT JOIN tbdocumentacao USING (idPessoa)
                                     JOIN tbperfil USING (idPerfil)     
               WHERE tbservidor.situacao = 1
                 AND tbperfil.tipo <> "Outros"
            ORDER BY tbpessoa.nome';

    $result = $servidor->select($select);

    $relatorio = new Relatorio();
    $relatorio->set_titulo('Relatório Geral de Servidores Ativos');
    $relatorio->set_subtitulo('Email UENF e CPF<br/>Ordenados pelo Nome do Servidor');
    $relatorio->set_label(['IdFuncional', 'Nome', 'CPF', 'Email Uenf']);
    $relatorio->set_align(["center", "left", "center", "left"]);
    $relatorio->set_conteudo($result);
    $relatorio->show();

    $page->terminaPagina();
}