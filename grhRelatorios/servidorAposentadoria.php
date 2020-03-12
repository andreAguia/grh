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
    $aposentadoria = new Aposentadoria();

    # Começa uma nova página
    $page = new Page();			
    $page->iniciaPagina();
    
    ######
    
    # Abre o Grid
    $grid = new Grid();
    $grid->abreColuna(12);
    
    # Dados do Servidor
    Grh::listaDadosServidorRelatorio($idServidorPesquisado,'Previsão de Aposentadoria');
    br();
    
##############################################################################################################################################
#   Regras
##############################################################################################################################################
    
    # Regras da Aposentadoria
    $painel = new Callout("secondary");
    $painel->abre();
    
    $aposentadoria->exibeRegras(TRUE);
    
    $painel->fecha();
    
##############################################################################################################################################
#   Previsão de Aposentadoria
##############################################################################################################################################
    
    $painel = new Callout("secondary");
    $painel->abre();
    
    $aposentadoria->exibePrevisao($idServidorPesquisado, TRUE);
    
    $painel->fecha();
    
##############################################################################################################################################
#   Tempo de Serviço
##############################################################################################################################################
    
    $painel = new Callout("secondary");
    $painel->abre();
    
    $aposentadoria->exibeTempo($idServidorPesquisado, TRUE);
    
    
    $select = "SELECT dtInicial,
                      dtFinal,
                      dias,
                      empresa,
                      CASE empresaTipo
                         WHEN 1 THEN 'Pública'
                         WHEN 2 THEN 'Privada'
                      END,
                      CASE regime
                         WHEN 1 THEN 'Celetista'
                         WHEN 2 THEN 'Estatutário'
                      END,
                      cargo,
                      dtPublicacao,
                      processo,
                      idAverbacao
                 FROM tbaverbacao
                WHERE idServidor = $idServidorPesquisado
             ORDER BY 1 desc";

    $result = $pessoal->select($select);
    #array_push($result,array(NULL,NULL,$publica + $privada,NULL,NULL,NULL,NULL,NULL,NULL));
    #array_push($result,array(NULL,NULL,$publica + $privada,NULL,NULL,NULL,NULL,NULL,NULL));

    $relatorio = new Relatorio();
    $relatorio->set_subtitulo('Tempo de Serviço Averbado');
    $relatorio->set_cabecalhoRelatorio(FALSE);
    $relatorio->set_menuRelatorio(FALSE);
    $relatorio->set_subTotal(TRUE);
    $relatorio->set_totalRegistro(FALSE);
    $relatorio->set_label(array("Data Inicial","Data Final","Dias","Empresa","Tipo","Regime","Cargo","Publicação","Processo"));
    $relatorio->set_colunaSomatorio(2);
    $relatorio->set_textoSomatorio("Total de Dias Averbados:");
    $relatorio->set_exibeSomatorioGeral(FALSE);
    $relatorio->set_align(array('center','center','center','left'));
    $relatorio->set_funcao(array("date_to_php","date_to_php",NULL,NULL,NULL,NULL,NULL,"date_to_php"));

    $relatorio->set_conteudo($result);
    #$relatorio->set_numGrupo(2);
    $relatorio->set_botaoVoltar(FALSE);
    $relatorio->set_logServidor($idServidorPesquisado);
    $relatorio->set_logDetalhe("Visualizou o Relatório de Tempo de Serviço Averbado");
    $relatorio->show();
    
    $painel->fecha();
    $grid->fechaColuna();
    $grid->fechaGrid();

    $page->terminaPagina();
}