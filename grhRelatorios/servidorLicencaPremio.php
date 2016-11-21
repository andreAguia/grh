<?php
/**
 * Relatório
 *    
 * By Alat
 */

# Inicia as variáveis que receberão as sessions
$idUsuario = null;              # Servidor logado
$idServidorPesquisado = null;	# Servidor Editado na pesquisa do sistema do GRH

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
    Grh::listaDadosServidorRelatorio($idServidorPesquisado,'Relatório de Licenças Prêmio');
    
    br();    
    ###### Licenças PrÊmio

    $select = "SELECT tbtipolicenca.nome,
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
              WHERE idServidor = $idServidorPesquisado
                AND tblicenca.idTpLicenca = 6  
           ORDER BY tblicenca.dtInicial desc";

    $result = $pessoal->select($select);

    $relatorio = new Relatorio();   
    $relatorio->set_cabecalhoRelatorio(false);
    $relatorio->set_menuRelatorio(false);
    $relatorio->set_subTotal(true);
    $relatorio->set_totalRegistro(false);
    $relatorio->set_dataImpressao(false);
    $relatorio->set_subtitulo("Licenças Fruídas");
    $relatorio->set_label(array("Licença","Inicio","Dias","Término","Processo","P.Aq.Início","P.Aq.Fim","Publicação","Pag."));
    $relatorio->set_width(array(23,10,5,10,17,10,10,10,5));
    $relatorio->set_align(array('center'));
    $relatorio->set_funcao(array(null,'date_to_php',null,'date_to_php',null,'date_to_php','date_to_php','date_to_php'));

    $relatorio->set_conteudo($result);
    #$relatorio->set_numGrupo(2);
    $relatorio->set_botaoVoltar(false);
    $relatorio->show();
    
    ###### Publicações

    $select = "SELECT dtPublicacao,
                      pgPublicacao,
                      dtInicioPeriodo,
                      dtFimPeriodo,                                  
                      processo,
                      numDias,
                      idPublicacaoPremio,
                      idPublicacaoPremio
                 FROM tbpublicacaoPremio
                WHERE idServidor = $idServidorPesquisado
             ORDER BY dtPublicacao desc";

    $result = $pessoal->select($select);

    $relatorio = new Relatorio();   
    $relatorio->set_cabecalhoRelatorio(false);
    $relatorio->set_menuRelatorio(false);
    $relatorio->set_subTotal(true);
    $relatorio->set_totalRegistro(false);
    $relatorio->set_subtitulo("Publicações");
    $relatorio->set_label(array("Data da Publicação","Pag.","P.Aq.Início","P.Aq.Fim","Processo","Dias Publicados","Dias Fruídos","Disponíveis"));
    $relatorio->set_width(array(10,5,14,14,20,8,8,8));
    $relatorio->set_align(array('center'));    
    $relatorio->set_classe(array(null,null,null,null,null,null,'Pessoal','Pessoal'));
    $relatorio->set_metodo(array(null,null,null,null,null,null,'get_licencaPremioNumDiasFruidasPorId','get_licencaPremioNumDiasDisponiveisPorId'));
    $relatorio->set_funcao(array('date_to_php',null,'date_to_php','date_to_php',null));
    $relatorio->set_dataImpressao(false);
    $relatorio->set_conteudo($result);
    #$relatorio->set_numGrupo(2);
    $relatorio->set_botaoVoltar(false);
    $relatorio->show();
    
    ###### Resumo
    
    $diasPublicados = $pessoal->get_licencaPremioNumDiasPublicadaPorMatricula($idServidorPesquisado);
    $diasFruidos = $pessoal->get_licencaPremioNumDiasFruidos($idServidorPesquisado);
    $diasDisponiveis = $diasPublicados - $diasFruidos; 
    
    $tabela = array(array('Dias Publicados','Dias Fruídos','Disponíveis'),
                    array($diasPublicados,$diasFruidos,$diasDisponiveis));
    
    $relatorio = new Relatorio();   
    $relatorio->set_cabecalhoRelatorio(false);
    $relatorio->set_menuRelatorio(false);
    $relatorio->set_subTotal(false);
    $relatorio->set_totalRegistro(false);
    #$relatorio->set_subtitulo("Resumo");
    $relatorio->set_label(array("","",""));
    $relatorio->set_width(array(33,33,33));
    $relatorio->set_align(array('center'));
    $relatorio->set_linhaFinal(TRUE);

    $relatorio->set_conteudo($tabela);
    #$relatorio->set_numGrupo(2);
    $relatorio->set_botaoVoltar(false);
                
    $relatorio->show();

    $page->terminaPagina();
}