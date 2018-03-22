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
    $select = '(SELECT CONCAT(tbtipolicenca.nome," ",IFNULL(tbtipolicenca.lei,"")),
                        CASE tipo
                           WHEN 1 THEN "Inicial"
                           WHEN 2 THEN "Prorrogação"
                           end,
                        CASE alta
                           WHEN 1 THEN "Sim"
                           WHEN 2 THEN "Não"
                           end,
                        dtInicial,
                        numdias,
                        ADDDATE(dtInicial,numDias-1),
                        CONCAT(tblicenca.idTpLicenca,"&",idLicenca),
                        dtPublicacao,
                        idLicenca
                   FROM tblicenca LEFT JOIN tbtipolicenca ON tblicenca.idTpLicenca = tbtipolicenca.idTpLicenca
                  WHERE idServidor='.$idServidorPesquisado.')
                  UNION
                  (SELECT (SELECT CONCAT(tbtipolicenca.nome," ",IFNULL(tbtipolicenca.lei,"")) FROM tbtipolicenca WHERE idTpLicenca = 6),
                          "",
                          "",
                          dtInicial,
                          tblicencaPremio.numdias,
                          ADDDATE(dtInicial,tblicencaPremio.numDias-1),
                          CONCAT("6&",tblicencaPremio.idServidor),
                          tbPublicacaoPremio.dtPublicacao,
                          idLicencaPremio
                     FROM tblicencaPremio LEFT JOIN tbPublicacaoPremio USING (idPublicacaoPremio)
                    WHERE tblicencaPremio.idServidor = '.$idServidorPesquisado.')
                 ORDER BY 4 desc';

    $result = $pessoal->select($select);

    $relatorio = new Relatorio();   
    $relatorio->set_cabecalhoRelatorio(FALSE);
    $relatorio->set_menuRelatorio(FALSE);
    $relatorio->set_subTotal(TRUE);
    $relatorio->set_numeroOrdem(TRUE);
    $relatorio->set_numeroOrdemTipo("d");
    $relatorio->set_totalRegistro(FALSE);
    $relatorio->set_bordaInterna(TRUE);
    $relatorio->set_label(array("Licença","Tipo","Alta","Inicio","Dias","Término","Processo","Publicação","Pag."));
    #$relatorio->set_width(array(23,10,5,10,17,10,10,10,5));
    $relatorio->set_align(array('left'));
    $relatorio->set_funcao(array(NULL,NULL,NULL,'date_to_php',NULL,'date_to_php','exibeProcessoPremio','date_to_php'));

    $relatorio->set_conteudo($result);
    #$relatorio->set_numGrupo(2);
    $relatorio->set_botaoVoltar(FALSE);
    $relatorio->set_logDetalhe("Visualizou o Relatório de Histórico de Licenças");
    $relatorio->set_logServidor($idServidorPesquisado);
    $relatorio->show();

    $page->terminaPagina();
}