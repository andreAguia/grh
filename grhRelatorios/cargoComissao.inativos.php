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
    $pessoal = new Pessoal();

    # Começa uma nova página
    $page = new Page();
    $page->iniciaPagina();

    ######

    $select = 'SELECT descricao,
                     simbolo,
                     valsal,
                     vagas
                FROM tbtipocomissao
                WHERE NOT ativo
           ORDER BY simbolo';

    $result = $pessoal->select($select);

    $relatorio = new Relatorio();
    $relatorio->set_titulo('Relatório de Cargos em Comissão Inativos');
    #$relatorio->set_subtitulo('Agrupados por Instituição');

    $relatorio->set_label(['Cargo', 'Símbolo', 'Valor', 'Vagas']);
    $relatorio->set_width([50, 20, 20, 10]);
    $relatorio->set_align(["left"]);
    $relatorio->set_funcao([null, null, 'formataMoeda']);

    $relatorio->set_colunaSomatorio(3);
    $relatorio->set_textoSomatorio('Total de Vagas:');
    $relatorio->set_exibeSomatorioGeral(false);
    $relatorio->set_totalRegistro(false);

    $relatorio->set_conteudo($result);
    $relatorio->show();

    $page->terminaPagina();
}