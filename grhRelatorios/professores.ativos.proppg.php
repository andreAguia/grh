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

    $select = 'SELECT tbdocumentacao.cpf,
                      tbpessoa.nome,
                      tbservidor.idServidor,
                      tbpessoa.dtNasc,
                      tbpais.pais,
                      tbservidor.dtAdmissao,
                      tbservidor.idServidor
                 FROM tbservidor JOIN tbpessoa USING (idPessoa)
                                 JOIN tbperfil USING (idPerfil)     
                                 JOIN tbdocumentacao USING (idPessoa)
                                 JOIN tbpais ON (tbpessoa.paisOrigem = tbpais.idPais)
               WHERE tbservidor.situacao = 1
                 AND tbperfil.tipo <> "Outros"
                 AND (idCargo = 128 OR idCargo = 129)
            ORDER BY tbpessoa.nome';

    $result = $servidor->select($select);

    $relatorio = new Relatorio();
    $relatorio->set_titulo('Relatório de Professores Ativos');
    $relatorio->set_subtitulo('Ordenados por Nome');
    $relatorio->set_label(['CPF', 'Nome', 'Emails', 'Nascimento', 'Pais de Origem', 'Admissão', 'Situação']);
    $relatorio->set_align(["center", "left", "left", "left"]);
    $relatorio->set_funcao([null, null, null, "date_to_php", null, "date_to_php"]);

    $relatorio->set_classe([null, null, "pessoal", null, null, null, "pessoal"]);
    $relatorio->set_metodo([null, null, "get_emails", null, null, null, "get_Situacao"]);
    $relatorio->set_bordaInterna(true);

    $relatorio->set_conteudo($result);
    $relatorio->show();

    $page->terminaPagina();
}