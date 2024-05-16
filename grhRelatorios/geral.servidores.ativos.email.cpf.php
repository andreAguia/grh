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
                      idServidor,
                      idServidor,
                      tbdocumentacao.cpf,
                      idServidor
                FROM tbservidor LEFT JOIN tbpessoa USING (idPessoa)
                                LEFT JOIN tbdocumentacao USING (idPessoa)
                                     JOIN tbperfil USING (idPerfil)     
               WHERE tbservidor.situacao = 1
                 AND tbperfil.tipo <> "Outros"
            ORDER BY tbpessoa.nome';

    $result = $servidor->select($select);

    $relatorio = new Relatorio();
    $relatorio->set_titulo('Relatório Geral de Servidores Ativos');
    $relatorio->set_subtitulo('Email Instiotucional e CPF<br/>Ordenados pelo Nome do Servidor');
    $relatorio->set_label(['IdFuncional', 'Servidor','Lotação','CPF', 'Emails']);
    $relatorio->set_width([10,25,25,15,25]);
    $relatorio->set_bordaInterna(true);
    $relatorio->set_align(["center", "left", "left", "left","left"]);
    $relatorio->set_classe([null,"pessoal","pessoal",null,"pessoal"]);
    $relatorio->set_metodo([null,"get_nomeECargo","get_lotacaoRel",null,"get_emailUenf"]);
    $relatorio->set_conteudo($result);
    $relatorio->show();

    $page->terminaPagina();
}