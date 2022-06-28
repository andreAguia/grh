<?php

/**
 * Relatório
 *    
 * By Alat
 */
# Inicia as variáveis que receberão as sessions
$idUsuario = null;              # Servidor logado
$idServidorPesquisado = null; # Servidor Editado na pesquisa do sistema do GRH
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
    # Dados do Servidor
    Grh::listaDadosServidorRelatorio($idServidorPesquisado, 'Relatório Geral do TRE');

    br();

    #####################################
    $grid = new Grid();

    # Resumo
    $grid->abreColuna(4);

    # Pegas os valores
    $diasTrabalhados = $pessoal->get_treDiasTrabalhados($idServidorPesquisado);
    $folgasConcedidas = $pessoal->get_treFolgasConcedidas($idServidorPesquisado);
    $folgasFruidas = $pessoal->get_treFolgasFruidas($idServidorPesquisado);
    $folgasPendentes = $folgasConcedidas - $folgasFruidas;

    $resumo = Array(
        Array('Dias Trabalhados', $diasTrabalhados),
        Array('Folgas Concedidas', $folgasConcedidas),
        Array('Folgas Fruídas', $folgasFruidas),
        Array('Folgas Pendentes', $folgasPendentes)
    );

    tituloRelatorio('Resumo');
    $relatorio = new Relatorio();
    $relatorio->set_cabecalhoRelatorio(false);
    $relatorio->set_menuRelatorio(false);
    $relatorio->set_subTotal(false);
    $relatorio->set_totalRegistro(false);
    $relatorio->set_dataImpressao(false);
    $relatorio->set_label(["Folgas", "Dias"]);
    $relatorio->set_align(['left']);
    $relatorio->set_conteudo($resumo);
    $relatorio->show();

    $grid->fechaColuna();
    #####################################
    # Dias Trabalhados e Folgas Concedidas
    $grid->abreColuna(8);

    $select = 'SELECT data,
                      ADDDATE(data,dias-1),
                      dias,
                      folgas
                 FROM tbtrabalhotre
                WHERE idServidor=' . $idServidorPesquisado . '
             ORDER BY data desc';

    $dtrab = $pessoal->select($select);

    tituloRelatorio('Dias Trabalhados e Folgas Concedidas');
    $relatorio = new Relatorio();
    $relatorio->set_cabecalhoRelatorio(false);
    $relatorio->set_menuRelatorio(false);
    $relatorio->set_subTotal(false);
    $relatorio->set_totalRegistro(false);
    $relatorio->set_dataImpressao(false);
    $relatorio->set_label(["Início", "Término", "Dias Trabalhados", "Folgas Concedidas"]);
    $relatorio->set_funcao(["date_to_php", "date_to_php"]);
    $relatorio->set_colunaSomatorio([2, 3]);
    $relatorio->set_conteudo($dtrab);
    $relatorio->show();

    #####################################
    #  Folgas Fruídas

    $select = 'SELECT data,
                    ADDDATE(data,dias-1),                                 
                    dias,
                    idFolga
               FROM tbfolga
              WHERE idServidor=' . $idServidorPesquisado . '
           ORDER BY data desc';

    $folgas = $pessoal->select($select);
    
    
    tituloRelatorio('Folgas Fruídas');
    $relatorio = new Relatorio();
    $relatorio->set_cabecalhoRelatorio(false);
    $relatorio->set_menuRelatorio(false);
    $relatorio->set_subTotal(false);
    $relatorio->set_totalRegistro(false);
    $relatorio->set_dataImpressao(false);
    $relatorio->set_label(["Início", "Término", "Folgas Fruídas"]);
    $relatorio->set_funcao(["date_to_php", "date_to_php"]);
    $relatorio->set_colunaSomatorio(2);
    $relatorio->set_conteudo($folgas);
    $relatorio->show();

    $grid->fechaColuna();
    $grid->fechaGrid();

    $page->terminaPagina();
}