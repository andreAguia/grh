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
    Grh::listaDadosServidorRelatorio($idServidorPesquisado, 'Histórico de Férias');
    br();

    # Limita o tamanho da tela
    $grid = new Grid();
    $grid->abreColuna(12);

    # Exibe as férias pendentes
//    $ferias = new Ferias();
//    $pendentes = $ferias->exibeFeriasPendentes($idServidorPesquisado);
//    if (!empty($pendentes)) {
//        $callout = new Callout();
//        $callout->abre();
//        p("Atenção:<br/> {$pendentes}", 'center','f14');
//        $callout->fecha();
//    }
//    
//    $grid->fechaColuna();
//    $grid->abreColuna(4);
//
//    $listaFerias = new Ferias();
//    $lista = $listaFerias->get_feriasResumo($idServidorPesquisado);
//
//    p("Férias Fruídas & Solicitadas", "center", "f14");
//
//    $relatorio = new Relatorio();
//    $relatorio->set_cabecalhoRelatorio(false);
//    $relatorio->set_menuRelatorio(false);
//    $relatorio->set_subTotal(true);
//    $relatorio->set_totalRegistro(false);
//    $relatorio->set_label(["Exercício", "Dias", "Faltam"]);
//    $relatorio->set_bordaInterna(true);
//
//    $relatorio->set_conteudo($lista);
//    $relatorio->set_botaoVoltar(false);
//    $relatorio->set_totalRegistro(false);
//    $relatorio->set_dataImpressao(false);
//
//    $relatorio->show();
//
//    $grid->fechaColuna();
//    $grid->abreColuna(8);
    #p("Histórico", "center", "f14");

    $select = "SELECT anoExercicio,
                        status,
                        dtInicial,
                        numDias,
                        ADDDATE(dtInicial,numDias-1),
                        idFerias
                   FROM tbferias
                  WHERE idServidor = $idServidorPesquisado
               ORDER BY anoExercicio desc, dtInicial desc";

    $result = $pessoal->select($select);

    $relatorio = new Relatorio();
    #$relatorio->set_titulo($titulo);
    $relatorio->set_cabecalhoRelatorio(false);
    $relatorio->set_menuRelatorio(false);
    $relatorio->set_subTotal(true);
    $relatorio->set_totalRegistro(false);
    $relatorio->set_label(["Exercicio", "Status", "Data Inicial", "Dias", "Data Final", "Período"]);
    #$relatorio->set_width(array(10,10,10,5,8,10,15));
    $relatorio->set_align(['center']);
    $relatorio->set_funcao([null, null, 'date_to_php', null, 'date_to_php']);
    $relatorio->set_classe([null, null, null, null, null, "pessoal"]);
    $relatorio->set_metodo([null, null, null, null, null, "get_feriasPeriodo"]);

    $relatorio->set_rowspan(0);
    $relatorio->set_grupoCorColuna(0);
    $relatorio->set_bordaInterna(true);

    $relatorio->set_conteudo($result);
    #$relatorio->set_numGrupo(2);
    $relatorio->set_botaoVoltar(false);
    $relatorio->set_logServidor($idServidorPesquisado);
    $relatorio->set_logDetalhe("Visualizou o Relatório de Histórico de Férias");
    $relatorio->show();

    $grid->fechaColuna();
    $grid->fechaGrid();

    $page->terminaPagina();
}