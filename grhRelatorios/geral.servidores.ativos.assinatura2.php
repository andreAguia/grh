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

    $select = 'SELECT tbpessoa.nome,
                      "_____________________",
                      "_____________________",
                      "_____________________"
                 FROM tbservidor LEFT JOIN tbpessoa ON (tbservidor.idPessoa = tbpessoa.idPessoa)
                                      JOIN tbperfil USING (idPerfil)     
               WHERE tbservidor.situacao = 1
                 AND tbperfil.tipo <> "Outros"
             ORDER BY tbpessoa.nome';

    $result = $servidor->select($select);

    $relatorio = new Relatorio();
    $relatorio->set_titulo('Relatório Geral de Servidores Ativos');
    $relatorio->set_subtitulo('Ordenados pelo Nome');
    $relatorio->set_label(['Nome','Assinatura', 'Assinatura', 'Assinatura']);
    #$relatorio->set_width([10, 40, 30, 20]);
    $relatorio->set_align(["left"]);
    #$relatorio->set_bordaInterna(true);
    $relatorio->set_espacamento(6);
    $relatorio->set_numeroOrdem(true);
    $relatorio->set_conteudo($result);
    $relatorio->show();

    $page->terminaPagina();
}