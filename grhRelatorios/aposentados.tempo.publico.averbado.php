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
                      empresa
                 FROM tbservidor LEFT JOIN tbpessoa USING (idPessoa)
                                      JOIN tbdocumentacao USING (idpessoa)
                                      JOIN tbaverbacao USING (idServidor)
                WHERE (idPerfil = 1 OR idPerfil = 4)
                  AND situacao = 2
                  AND empresaTipo = 1
            ORDER BY tbpessoa.nome';

    $result = $servidor->select($select);

    $relatorio = new Relatorio();
    $relatorio->set_titulo('Relatório Geral de Servidores Aposentados');
    $relatorio->set_subtitulo('Com Tempo Publico Averbado');
    $relatorio->set_label(array('IdFuncional', 'Nome', 'CPF','Empresa Publica'));
    #$relatorio->set_width(array(10,30,30,0,10,10,10));
    $relatorio->set_align(array("center", "left", "center","left"));

    $relatorio->set_conteudo($result);
    $relatorio->show();

    $page->terminaPagina();
}