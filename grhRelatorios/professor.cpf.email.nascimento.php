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

    $select = 'SELECT tbservidor.idServidor,
                      cpf,
                      dtNasc,
                      tbservidor.idServidor
                 FROM tbservidor LEFT JOIN tbpessoa USING (idPessoa)
                                 LEFT JOIN tbdocumentacao USING (idPessoa)
                WHERE situacao = 1 
                 AND (idCargo = 128 OR idCargo = 129)
             ORDER BY tbpessoa.nome';

    $result = $servidor->select($select);

    $relatorio = new Relatorio();
    $relatorio->set_titulo('Relatório de Professores Ativos');
    $relatorio->set_subtitulo('(Com CPF, Data de Nascimento e Emails');
    $relatorio->set_label(['Nome', 'CPF', 'Nascimento', 'Emails']);
    #$relatorio->set_width(array(35, 15, 10, 20, 10));
    $relatorio->set_bordaInterna(true);
    $relatorio->set_align(["left", null, null, "left", "left"]);
    $relatorio->set_funcao([null, null, "date_to_php"]);
    $relatorio->set_classe(["Pessoal", null, null, "Pessoal"]);
    $relatorio->set_metodo(["get_nomeECargo", null, null, "get_emails"]);
    $relatorio->set_conteudo($result);
    $relatorio->show();

    $page->terminaPagina();
}