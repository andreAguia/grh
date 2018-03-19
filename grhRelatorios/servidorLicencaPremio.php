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
    Grh::listaDadosServidorRelatorio($idServidorPesquisado,'Relatório de '.$pessoal->get_licencaNome(6));
    
    br(); 
    
    ###### Resumo
    $licenca = new LicencaPremio();
    $numProcesso = $licenca->get_numProcesso($idServidorPesquisado);
    $diasPublicados = $licenca->get_numDiasPublicados($idServidorPesquisado);
    $diasFruidos = $licenca->get_numDiasFruidos($idServidorPesquisado);
    $diasDisponiveis = $licenca->get_numDiasDisponiveis($idServidorPesquisado);
    $nome = $pessoal->get_licencaNome(6);
    
    $tabela = array(array($numProcesso,$diasPublicados,$diasFruidos,$diasDisponiveis));
    
    $relatorio = new Relatorio();   
    $relatorio->set_cabecalhoRelatorio(FALSE);
    $relatorio->set_menuRelatorio(FALSE);
    $relatorio->set_subTotal(FALSE);
    $relatorio->set_totalRegistro(FALSE);
    #$relatorio->set_subtitulo("Resumo");
    $relatorio->set_label(array('Processo','Dias Publicados','Dias Fruídos','Disponíveis'));
    #$relatorio->set_width(array(33,33,33));
    $relatorio->set_align(array('center'));
    $relatorio->set_totalRegistro(FALSE);
    $relatorio->set_dataImpressao(FALSE);

    $relatorio->set_conteudo($tabela);
    #$relatorio->set_numGrupo(2);
    $relatorio->set_botaoVoltar(FALSE);
    $relatorio->set_log(FALSE);            
    $relatorio->show();
    
    br();
    ###### Licenças Prêmio Fruídas
    
    $select = 'SELECT dtInicial,
                      tblicencaPremio.numdias,
                      ADDDATE(dtInicial,tblicencaPremio.numDias-1),
                      tbPublicacaoPremio.dtPublicacao,
                      tbPublicacaoPremio.pgPublicacao,
                      tbPublicacaoPremio.dtInicioPeriodo,
                      tbPublicacaoPremio.dtFimPeriodo,
                      idLicencaPremio
                 FROM tblicencaPremio LEFT JOIN tbPublicacaoPremio USING (idPublicacaoPremio)
                WHERE tblicencaPremio.idServidor = '.$idServidorPesquisado.'
             ORDER BY dtInicial desc';

    $result = $pessoal->select($select);

    $relatorio = new Relatorio();   
    $relatorio->set_cabecalhoRelatorio(FALSE);
    $relatorio->set_menuRelatorio(FALSE);
    $relatorio->set_subTotal(TRUE);
    $relatorio->set_totalRegistro(FALSE);
    $relatorio->set_dataImpressao(FALSE);
    $relatorio->set_numeroOrdem(TRUE);
    $relatorio->set_numeroOrdemTipo("d");
    $relatorio->set_subtitulo("Licenças Fruídas");
    $relatorio->set_label(array("Inicio","Dias","Término","Publicação","Página","Início do Período","Fim do Período"));
    #$relatorio->set_width(array(23,10,5,10,17,10,10,10,5));
    $relatorio->set_align(array('center'));
    $relatorio->set_funcao(array('date_to_php',NULL,'date_to_php','date_to_php',NULL,'date_to_php','date_to_php'));
    #$relatorio->set_classe(array(NULL,NULL,NULL,'LicencaPremio'));
    #$relatorio->set_metodo(array(NULL,NULL,NULL,'get_publicacao'));

    $relatorio->set_conteudo($result);
    #$relatorio->set_numGrupo(2);
    $relatorio->set_botaoVoltar(FALSE);
    $relatorio->set_logDetalhe("Visualizou o Relatório de Histórico de $nome");
    $relatorio->set_logServidor($idServidorPesquisado);
    $relatorio->show();
    
    ###### Publicações

    $select = "SELECT dtPublicacao,
                    pgPublicacao,
                    dtInicioPeriodo,
                    dtFimPeriodo,
                    numDias,
                    idPublicacaoPremio,
                    idPublicacaoPremio,
                    idPublicacaoPremio
               FROM tbpublicacaopremio
               WHERE idServidor = $idServidorPesquisado
            ORDER BY dtPublicacao desc";

    $result = $pessoal->select($select);

    $relatorio = new Relatorio();   
    $relatorio->set_cabecalhoRelatorio(FALSE);
    $relatorio->set_menuRelatorio(FALSE);
    $relatorio->set_subTotal(TRUE);
    $relatorio->set_totalRegistro(FALSE);
    $relatorio->set_subtitulo("Publicações");
    
    $relatorio->set_label(array("Data da Publicação","Pag.","Período Aquisitivo <br/> Início","Período Aquisitivo <br/> Fim","Dias <br/> Publicados","Dias <br/> Fruídos","Dias <br/> Disponíveis"));
    #$relatorio->set_width(array(15,5,15,15,15,10,10,10));
    $relatorio->set_align(array("center"));
    $relatorio->set_numeroOrdem(TRUE);
    $relatorio->set_numeroOrdemTipo("d");
    $relatorio->set_funcao(array('date_to_php',NULL,'date_to_php','date_to_php'));
    $relatorio->set_classe(array(NULL,NULL,NULL,NULL,NULL,'LicencaPremio','LicencaPremio'));
    $relatorio->set_metodo(array(NULL,NULL,NULL,NULL,NULL,'get_numDiasFruidosPorPublicacao','get_numDiasDisponiveisPorPublicacao'));
    
    #$relatorio->set_dataImpressao(FALSE);
    $relatorio->set_conteudo($result);
    #$relatorio->set_numGrupo(2);
    $relatorio->set_botaoVoltar(FALSE);
    $relatorio->set_log(FALSE);
    $relatorio->show();

    $page->terminaPagina();
}