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

if($acesso){    
    # Conecta ao Banco de Dados
    $pessoal = new Pessoal();

    # Começa uma nova página
    $page = new Page();			
    $page->iniciaPagina();

    ######
    
    # Dados do Servidor
    Grh::listaDadosServidorRelatorio($idServidorPesquisado,'Relatório de Folgas Fruídas do TRE');
    
    br();
    
    #####################################
    $grid = new Grid();
    $grid->abreColuna(4);
    
    $folgasConcedidas = $pessoal->get_folgasConcedidas($idServidorPesquisado);
    $folgasFruidas = $pessoal->get_folgasFruidas($idServidorPesquisado);
    $folgasPendentes = $folgasConcedidas - $folgasFruidas;
     
     $select = "SELECT YEAR(data) as ano,
                       IFNULL(sum(folgas),0)
                  FROM tbtrabalhotre
                 WHERE idServidor = $idServidorPesquisado
                GROUP BY ano ORDER BY ano";
     
     $result = $pessoal->select($select);

    $relatorio = new Relatorio();
    $relatorio->set_cabecalhoRelatorio(FALSE);
    $relatorio->set_subtitulo('Folgas Concedidas');
    $relatorio->set_menuRelatorio(FALSE);
    $relatorio->set_subTotal(FALSE);
    $relatorio->set_totalRegistro(FALSE);
    $relatorio->set_dataImpressao(FALSE);
    $relatorio->set_label(array('Ano','Folgas Concedidas'));
    $relatorio->set_align(array('center'));
    $relatorio->set_conteudo($result);
    $relatorio->show();
    p($folgasConcedidas." dias","f11","center");
    
    $grid->fechaColuna();
    #####################################
    $grid->abreColuna(4);
   
            
     $select = "SELECT YEAR(data) as ano,
                       IFNULL(sum(dias),0)
                  FROM tbfolga
                 WHERE idServidor = $idServidorPesquisado
                GROUP BY ano ORDER BY ano";
     
     $result = $pessoal->select($select);

    $relatorio = new Relatorio();
    $relatorio->set_cabecalhoRelatorio(FALSE);
    $relatorio->set_subtitulo('Folgas Fruidas');
    $relatorio->set_menuRelatorio(FALSE);
    $relatorio->set_subTotal(FALSE);
    $relatorio->set_totalRegistro(FALSE);
    $relatorio->set_dataImpressao(FALSE);
    $relatorio->set_label(array('Ano','Folgas Fruidas'));
    $relatorio->set_align(array('center'));
    $relatorio->set_conteudo($result);
    $relatorio->show();
    p($folgasFruidas." dias","f11","center");
    
    $grid->fechaColuna();
    #####################################
    $grid->abreColuna(4);
    
    $folgas = Array(Array('Folgas Concedidas',$folgasConcedidas),
                            Array('Folgas Fruídas',$folgasFruidas));
    
    $relatorio = new Relatorio();
    $relatorio->set_cabecalhoRelatorio(FALSE);
    $relatorio->set_subtitulo('Resumo Geral');
    $relatorio->set_menuRelatorio(FALSE);
    $relatorio->set_subTotal(FALSE);
    $relatorio->set_totalRegistro(FALSE);
    $relatorio->set_dataImpressao(FALSE);
    $relatorio->set_label(array("Folgas","Dias"));
    $relatorio->set_align(array('left'));
    $relatorio->set_conteudo($folgas);
    $relatorio->show();
    p($folgasPendentes." dias","f11","center");
    
    $grid->fechaColuna();
    $grid->fechaGrid();
    ############################################################

    $select = "SELECT data,                                    
                      dias,
                      ADDDATE(data,dias-1),
                      year(data)
                 FROM tbfolga
                WHERE idServidor = $idServidorPesquisado
             ORDER BY data";				


    $result = $pessoal->select($select);

    $relatorio = new Relatorio();
    $relatorio->set_cabecalhoRelatorio(FALSE);
    $relatorio->set_menuRelatorio(FALSE);
    $relatorio->set_subTotal(TRUE);
    #$relatorio->set_titulo('Relatório Mensal de Folgas Fruídas do TRE');
    #$relatorio->set_tituloLinha2($relatorioAno);
    #$relatorio->set_subtitulo('Ordenado pelo Nome do Servidor');

    $relatorio->set_label(array('Data Inicial','Dias','Data Final'));
    #$relatorio->set_width(array(10,30,20,10,10,10));
    $relatorio->set_align(array('center'));
    $relatorio->set_funcao(array("date_to_php",NULL,"date_to_php"));
    #$relatorio->set_classe(array(NULL,NULL,"pessoal"));
    #$relatorio->set_metodo(array(NULL,NULL,"get_lotacao"));  

    $relatorio->set_conteudo($result);
    $relatorio->set_numGrupo(3);
    $relatorio->show();
    
    
    
    

    $page->terminaPagina();
}