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
                      tbservidor.idServidor,
                      "_________________________"
                 FROM tbservidor LEFT JOIN tbpessoa ON (tbservidor.idPessoa = tbpessoa.idPessoa)
                                      JOIN tbperfil USING (idPerfil)     
               WHERE tbservidor.situacao = 1
                 AND tbperfil.tipo <> "Outros"
             ORDER BY tbpessoa.nome';

    $result = $servidor->select($select);

    $relatorio = new Relatorio();
    $relatorio->set_titulo('Relatório Geral de Servidores Ativos');
    $relatorio->set_subtitulo('Ordenados pelo Nome');
    $relatorio->set_label(['IdFuncional', 'Nome', 'Lotação', 'Assinatura']);
    $relatorio->set_width([10, 40, 30, 20]);
    $relatorio->set_align(["center", "left", "left"]);
    $relatorio->set_classe([null, null, "pessoal"]);
    $relatorio->set_metodo([null, null, "get_lotacaoRel"]);

    $relatorio->set_conteudo($result);
    $relatorio->show();

    $page->terminaPagina();
}