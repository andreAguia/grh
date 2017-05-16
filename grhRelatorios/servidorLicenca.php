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
    Grh::listaDadosServidorRelatorio($idServidorPesquisado,'Histórico de Licenças');
    
    br();
    $select = "SELECT tbtipolicenca.nome,
                    tbtipolicenca.lei,
                    dtInicial,
                    numdias,
                    ADDDATE(dtInicial,numDias-1),
                    tblicenca.processo,
                    dtInicioPeriodo,
                    dtFimPeriodo,
                    dtPublicacao,
                    pgPublicacao,
                    idLicenca
               FROM tblicenca LEFT JOIN tbtipolicenca ON tblicenca.idTpLicenca = tbtipolicenca.idTpLicenca
              WHERE idServidor=$idServidorPesquisado
           ORDER BY tblicenca.dtInicial desc";

    $result = $pessoal->select($select);

    $relatorio = new Relatorio();   
    $relatorio->set_cabecalhoRelatorio(FALSE);
    $relatorio->set_menuRelatorio(FALSE);
    $relatorio->set_subTotal(TRUE);
    $relatorio->set_totalRegistro(FALSE);
    #$relatorio->set_subtitulo("Todas as Licenças");
    $relatorio->set_label(array("Licença","Lei","Inicio","Dias","Término","Processo","P.Aq.Início","P.Aq.Fim","Publicação","Pag."));
    #$relatorio->set_width(array(23,10,5,10,17,10,10,10,5));
    $relatorio->set_align(array('left'));
    $relatorio->set_funcao(array(NULL,NULL,'date_to_php',NULL,'date_to_php',NULL,'date_to_php','date_to_php','date_to_php'));

    $relatorio->set_conteudo($result);
    #$relatorio->set_numGrupo(2);
    $relatorio->set_botaoVoltar(FALSE);
    $relatorio->set_logDetalhe("Visualizou o Relatório de Histórico de Licenças");
    $relatorio->set_logServidor($idServidorPesquisado);
    $relatorio->show();

    $page->terminaPagina();
}