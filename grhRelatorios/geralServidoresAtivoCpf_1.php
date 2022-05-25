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
                      tbdocumentacao.cpf,
                      tbservidor.idServidor
                 FROM tbservidor LEFT JOIN tbpessoa ON (tbservidor.idPessoa = tbpessoa.idPessoa)                                     
                                 LEFT JOIN tbdocumentacao ON (tbdocumentacao.idPessoa = tbpessoa.idPessoa)
               WHERE situacao = 1
            ORDER BY tbpessoa.nome';

    $result = $servidor->select($select);

    $relatorio = new Relatorio();
    $relatorio->set_titulo('Relatório Geral de Servidores Ativos com CPF');
    $relatorio->set_subtitulo('Ordenados pelo Nome');
    $relatorio->set_label(array('IdFuncional', 'Nome', 'Lotação', 'CPF', 'Situação'));
    $relatorio->set_width(array(10,30,30,0,10,20));
    $relatorio->set_align(array("center", "left", "left"));

    $relatorio->set_classe(array(null, null, "pessoal"));
    $relatorio->set_metodo(array(null, null, "get_lotacao"));
    $relatorio->set_funcao(array(null, null, null, null, "get_situacaoRel"));

    $relatorio->set_conteudo($result);
    #$relatorio->set_numGrupo(3);
    #$relatorio->set_botaoVoltar('../sistema/areaServidor.php');
    $relatorio->show();

    $page->terminaPagina();
}