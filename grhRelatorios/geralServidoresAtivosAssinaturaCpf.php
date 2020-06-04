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
                     tbpessoa.nome,
                     tbdocumentacao.cpf,
                     tbservidor.idServidor,
                     "_________________________"
                FROM tbservidor LEFT JOIN tbpessoa USING (idPessoa)
                                LEFT JOIN tbdocumentacao USING (idPessoa)
               WHERE tbservidor.situacao = 1
                 AND tbservidor.idPerfil <> 10
            ORDER BY tbpessoa.nome';

    $result = $servidor->select($select);

    $relatorio = new Relatorio();
    $relatorio->set_titulo('Relatório Geral de Servidores Ativos');
    $relatorio->set_subtitulo('Assinatura');
    $relatorio->set_label(array('IdFuncional', 'Nome', 'CPF', 'Lotação', 'Assinatura'));
    $relatorio->set_width(array(10, 30, 10, 30, 20));
    $relatorio->set_align(array("center", "left", "center", "left"));
    $relatorio->set_classe(array(null, null, null, "pessoal"));
    $relatorio->set_metodo(array(null, null, null, "get_lotacaoRel"));

    $relatorio->set_conteudo($result);

    #$relatorio->set_botaoVoltar('../sistema/areaServidor.php');
    $relatorio->show();

    $page->terminaPagina();
}