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
    $nome = $pessoal->get_licencaNome(6);
    br();
    
    ###### Licenças Prêmio Fruídas
    
    $select = 'SELECT tbpublicacaopremio.dtPublicacao,
                      tbpublicacaopremio.dtInicioPeriodo,
                      tbpublicacaopremio.dtFimPeriodo,
                      dtInicial,
                      tblicencapremio.numdias,
                      ADDDATE(dtInicial,tblicencapremio.numDias-1),
                      idLicencaPremio
                 FROM tblicencapremio LEFT JOIN tbpublicacaopremio USING (idPublicacaoPremio)
                WHERE tblicencapremio.idServidor = '.$idServidorPesquisado.'
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
    $relatorio->set_label(array("Publicação","Início do Período","Fim do Período","Inicio","Dias","Término"));
    #$relatorio->set_width(array(23,10,5,10,17,10,10,10,5));
    $relatorio->set_align(array('center'));
    $relatorio->set_funcao(array('date_to_php','date_to_php','date_to_php','date_to_php',NULL,'date_to_php'));
    #$relatorio->set_classe(array(NULL,NULL,NULL,'LicencaPremio'));
    #$relatorio->set_metodo(array(NULL,NULL,NULL,'get_publicacao'));

    $relatorio->set_conteudo($result);
    #$relatorio->set_numGrupo(2);
    $relatorio->set_botaoVoltar(FALSE);
    $relatorio->set_logDetalhe("Visualizou o Relatório de Histórico de $nome");
    $relatorio->set_logServidor($idServidorPesquisado);
    $relatorio->show();
    br();
    
    ###### Dados
    
    $licenca = new LicencaPremio();
    $numProcesso = $licenca->get_numProcesso($idServidorPesquisado);
    $diasPublicados = $licenca->get_numDiasPublicados($idServidorPesquisado);
    $diasFruidos = $licenca->get_numDiasFruidos($idServidorPesquisado);
    $diasDisponiveis = $licenca->get_numDiasDisponiveis($idServidorPesquisado);    
    
    # Tabela de Serviços
    $tabela = array(array('Processo',$numProcesso),
                    array('Dias Publicados',$diasPublicados),
                    array('Dias Fruídos',$diasFruidos),
                    array('Disponíveis',$diasDisponiveis));
            
    # Limita o tamanho da tela
    $grid = new Grid();
    $grid->abreColuna(3);
        
    $relatorio = new Relatorio();   
    $relatorio->set_cabecalhoRelatorio(FALSE);
    $relatorio->set_menuRelatorio(FALSE);
    $relatorio->set_subTotal(FALSE);
    $relatorio->set_totalRegistro(FALSE);
    $relatorio->set_subtitulo("Dados");
    $relatorio->set_label(array('Descrição','Valor'));
    $relatorio->set_align(array('left','center'));
    $relatorio->set_totalRegistro(FALSE);
    $relatorio->set_dataImpressao(FALSE);

    $relatorio->set_conteudo($tabela);
    #$relatorio->set_numGrupo(2);
    $relatorio->set_botaoVoltar(FALSE);
    $relatorio->set_log(FALSE);            
    $relatorio->show();
    
    $grid->fechaColuna();
    $grid->abreColuna(9);
    
    ###### Publicações

    $select = "SELECT dtPublicacao,
                    dtInicioPeriodo,
                    dtFimPeriodo,
                    numDias,
                    idPublicacaoPremio,
                    idPublicacaoPremio,
                    idPublicacaoPremio
               FROM tbpublicacaopremio
               WHERE idServidor = $idServidorPesquisado
            ORDER BY dtInicioPeriodo desc";

    $result = $pessoal->select($select);

    $relatorio = new Relatorio();   
    $relatorio->set_cabecalhoRelatorio(FALSE);
    $relatorio->set_menuRelatorio(FALSE);
    $relatorio->set_subTotal(TRUE);
    $relatorio->set_totalRegistro(FALSE);
    $relatorio->set_subtitulo("Publicações");
    
    $relatorio->set_label(array("Data da Publicação","Período Aquisitivo <br/> Início","Período Aquisitivo <br/> Fim","Dias <br/> Publicados","Dias <br/> Fruídos","Dias <br/> Disponíveis"));
    #$relatorio->set_width(array(15,5,15,15,15,10,10,10));
    $relatorio->set_align(array("center"));
    $relatorio->set_numeroOrdem(TRUE);
    $relatorio->set_numeroOrdemTipo("d");
    $relatorio->set_funcao(array('date_to_php','date_to_php','date_to_php'));
    $relatorio->set_classe(array(NULL,NULL,NULL,NULL,'LicencaPremio','LicencaPremio'));
    $relatorio->set_metodo(array(NULL,NULL,NULL,NULL,'get_numDiasFruidosPorPublicacao','get_numDiasDisponiveisPorPublicacao'));
    
    #$relatorio->set_dataImpressao(FALSE);
    $relatorio->set_conteudo($result);
    #$relatorio->set_numGrupo(2);
    $relatorio->set_botaoVoltar(FALSE);
    $relatorio->set_log(FALSE);
    $relatorio->show();
    
    $grid->fechaColuna();
    $grid->fechaGrid();
    
    # Procedimentos
    #br();
    #$licenca->exibeProcedimentos();

    $page->terminaPagina();
}