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
                     tbdocumentacao.cpf,
                     tbpessoa.dtNasc
                FROM tbservidor LEFT JOIN tbpessoa ON (tbservidor.idPessoa = tbpessoa.idPessoa)
                                LEFT JOIN tbdocumentacao ON (tbdocumentacao.idPessoa = tbpessoa.idPessoa)
                WHERE tbservidor.situacao = 1 AND tbservidor.idPerfil = 1
             ORDER BY tbpessoa.nome';

    $result = $servidor->select($select);

    $relatorio = new Relatorio();
    $relatorio->set_titulo('Relatório de Estatutários');
    $relatorio->set_subtitulo('(Ex-Fenorte)');
    $relatorio->set_label(array('Nome', 'CPF', 'Nascimento'));
    $relatorio->set_width(array(50, 30, 20));
    $relatorio->set_align(array("left", "center", "center"));
    $relatorio->set_funcao(array(null, null, "date_to_php"));
    #$relatorio->set_classe(array(null,null,null,null,null,null,"Pessoal"));
    #$relatorio->set_metodo(array(null,null,null,null,null,null,"get_Situacao"));    
    $relatorio->set_conteudo($result);
    #$relatorio->set_numGrupo(2);
    #$relatorio->set_botaoVoltar('../sistema/areaServidor.php');
    $relatorio->show();

    $page->terminaPagina();
}