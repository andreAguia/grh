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
    Grh::listaDadosServidorRelatorio($idServidorPesquisado,'Relatório de Histórico de Solicitação de Readaptação');
    
    # Pega o idPessoa
    $idPessoa = $pessoal->get_idPessoa($idServidorPesquisado);
    
    # Pega o número do processo (Quando tem)
    $reducao = new ReducaoCargaHoraria($idServidorPesquisado);
    $processo = trataNulo($reducao->get_numProcesso());
    
    br();
    $select = "SELECT CASE tipo
                                WHEN 1 THEN 'Ex-Ofício'
                                WHEN 2 THEN 'Solicitada'
                                ELSE '--'
                            END,
                            idReadaptacao,
                            processo,
                            idReadaptacao,                                     
                            idReadaptacao,
                            idReadaptacao,
                            idReadaptacao,
                            idReadaptacao,
                            idReadaptacao,                                   
                            idReadaptacao
                       FROM tbreadaptacao
                      WHERE idServidor = $idServidorPesquisado
                   ORDER BY dtSolicitacao desc";

    $result = $pessoal->select($select);

    $relatorio = new Relatorio();   
    $relatorio->set_cabecalhoRelatorio(FALSE);
    $relatorio->set_menuRelatorio(FALSE);
    $relatorio->set_subTotal(TRUE);
    $relatorio->set_totalRegistro(FALSE);
    $relatorio->set_label(array("Tipo","Status","Processo","Solicitado em:","Pericia","Resultado","Publicação","Período","Documentos"));    
    $relatorio->set_align(array("center","center","center","center","left","center","center","left","left"));
    #$relatorio->set_funcao(array(NULL,"date_to_php"));
    
    $relatorio->set_classe(array(NULL,"Readaptacao",NULL,"Readaptacao","Readaptacao","Readaptacao","Readaptacao","Readaptacao","Readaptacao"));
    $relatorio->set_metodo(array(NULL,"exibeStatus",NULL,"exibeSolicitacao","exibeDadosPericia","exibeResultado","exibePublicacao","exibePeriodo","exibeBotaoDocumentos"));

    $relatorio->set_conteudo($result);
    #$relatorio->set_numGrupo(2);
    $relatorio->set_botaoVoltar(FALSE);
    $relatorio->set_logServidor($idServidorPesquisado);
    $relatorio->set_logDetalhe("Visualizou o Relatório de Histórico de Solicitação de Readaptação");
    $relatorio->show();

    $page->terminaPagina();
}