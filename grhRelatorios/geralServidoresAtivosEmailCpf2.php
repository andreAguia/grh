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
$acesso = Verifica::acesso($idUsuario, 2);

if ($acesso) {
    # Conecta ao Banco de Dados
    $servidor = new Pessoal();

    # Começa uma nova página
    $page = new Page();
    $page->iniciaPagina();

    ######

    $select = 'SELECT tbservidor.idFuncional,
                      idServidor,
                      idServidor,
                      tbdocumentacao.cpf,
                      idServidor
                FROM tbservidor LEFT JOIN tbpessoa USING (idPessoa)
                                LEFT JOIN tbdocumentacao USING (idPessoa)
               WHERE tbservidor.situacao = 1
                 AND tbservidor.idPerfil <> 10
            ORDER BY tbpessoa.nome';

    $result = $servidor->select($select);

    $relatorio = new Relatorio();
    $relatorio->set_titulo('Relatório Geral de Servidores Ativos');
    $relatorio->set_subtitulo('Emails e CPF<br/>Ordenados pelo Nome do Servidor');
    $relatorio->set_label(array('IdFuncional', 'Servidor','Lotação','CPF', 'Emails'));
    $relatorio->set_width(array(10,25,25,15,25));
    $relatorio->set_bordaInterna(true);
    $relatorio->set_align(array("center", "left", "left", "left","left"));
    $relatorio->set_classe(array(null,"pessoal","pessoal",null,"pessoal"));
    $relatorio->set_metodo(array(null,"get_nomeECargo","get_lotacaoRel",null,"get_emails"));

    $relatorio->set_conteudo($result);
    $relatorio->show();

    $page->terminaPagina();
}