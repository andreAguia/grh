<?php
/**
 * Relatório
 *    
 * By Alat
 */

# Inicia as variáveis que receberão as sessions
$idUsuario = NULL;              # Servidor logado
$idServidorPesquisado = NULL;	# Servidor Editado na pesquisa do sistema do GRH

# Configuração
include ("../grhSistema/_config.php");

# Permissão de Acesso
$acesso = Verifica::acesso($idUsuario,2);

if($acesso)
{    
    # Conecta ao Banco de Dados
    $pessoal = new Pessoal();

    # Começa uma nova página
    $page = new Page();			
    $page->iniciaPagina();

    ######
    
    # Dados do Servidor
    Grh::listaDadosServidorRelatorio($idServidorPesquisado,'Histórico de Férias');
    br();
    
    # Limita o tamanho da tela
    $grid = new Grid();
    $grid->abreColuna(3);
    
    $lista = $pessoal->get_feriasResumo($idServidorPesquisado);
    
    p("Férias Fruídas & Solicitadas","center","f14");

    $relatorio = new Relatorio();   
    $relatorio->set_cabecalhoRelatorio(FALSE);
    $relatorio->set_menuRelatorio(FALSE);
    $relatorio->set_subTotal(TRUE);
    $relatorio->set_totalRegistro(FALSE);
    $relatorio->set_label(array("Exercício","Dias"));

    $relatorio->set_conteudo($lista);
    $relatorio->set_botaoVoltar(FALSE);
    $relatorio->set_totalRegistro(FALSE);
    $relatorio->set_dataImpressao(FALSE);

    $relatorio->show();
    
    $grid->fechaColuna();
    $grid->abreColuna(9);
    
    p("Histórico","center","f14");
    
    $select = "SELECT anoExercicio,
                        status,
                        dtInicial,
                        numDias,
                        idFerias,
                        ADDDATE(dtInicial,numDias-1)
                   FROM tbferias
                  WHERE idServidor = $idServidorPesquisado
               ORDER BY anoExercicio desc, dtInicial desc";

    $result = $pessoal->select($select);

    $relatorio = new Relatorio();   
    $relatorio->set_cabecalhoRelatorio(FALSE);
    $relatorio->set_menuRelatorio(FALSE);
    $relatorio->set_subTotal(TRUE);
    $relatorio->set_totalRegistro(FALSE);
    $relatorio->set_label(array("Exercicio","Status","Data Inicial","Dias","P","Data Final"));
    #$relatorio->set_width(array(10,10,10,5,8,10,15));
    $relatorio->set_align(array('center'));
    $relatorio->set_funcao(array(NULL,NULL,'date_to_php',NULL,NULL,'date_to_php'));
    $relatorio->set_classe(array(NULL,NULL,NULL,NULL,"pessoal"));
    $relatorio->set_metodo(array(NULL,NULL,NULL,NULL,"get_feriasPeriodo"));

    $relatorio->set_conteudo($result);
    #$relatorio->set_numGrupo(2);
    $relatorio->set_botaoVoltar(FALSE);
    $relatorio->set_logServidor($idServidorPesquisado);
    $relatorio->set_logDetalhe("Visualizou o Relatório de Histórico de Férias");
    $relatorio->show();
    
    $grid->fechaColuna();
    $grid->fechaGrid();

    $page->terminaPagina();
}