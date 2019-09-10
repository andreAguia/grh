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
    Grh::listaDadosServidorRelatorio($idServidorPesquisado,'Relatório de Histórico de Solicitação de Redução da Carga Horária');
    
    # Pega o idPessoa
    $idPessoa = $pessoal->get_idPessoa($idServidorPesquisado);
    
    # Pega o número do processo (Quando tem)
    $reducao = new ReducaoCargaHoraria($idServidorPesquisado);
    $processo = trataNulo($reducao->get_numProcesso());
    
    br();
    $select = "SELECT CASE tipo
                        WHEN 1 THEN 'Inicial'
                        WHEN 2 THEN 'Renovação'
                        ELSE '--'
                      END,idReducao,
                      dtSolicitacao,
                      idReducao,
                      CASE resultado
                        WHEN 1 THEN 'Deferido'
                        WHEN 2 THEN 'Indeferido'
                      ELSE '---'
                      END,
                      idReducao,
                      idReducao,
                      idReducao,                                   
                      idReducao               
                 FROM tbreducao
                WHERE idServidor = $idServidorPesquisado
             ORDER BY status, dtInicio desc";

    $result = $pessoal->select($select);

    $relatorio = new Relatorio();   
    $relatorio->set_cabecalhoRelatorio(FALSE);
    $relatorio->set_menuRelatorio(FALSE);
    $relatorio->set_subTotal(TRUE);
    $relatorio->set_totalRegistro(FALSE);
    $relatorio->set_label(array("Tipo","Status","Solicitado em:","Pericia","Resultado","Publicação","Período","CI"));
    $relatorio->set_subtitulo("Processo: ".$processo);
    
    $relatorio->set_align(array("center","center","center","left","center","center","left","left"));
    $relatorio->set_funcao(array(NULL,NULL,"date_to_php"));
    
    $relatorio->set_classe(array(NULL,"ReducaoCargaHoraria",NULL,"ReducaoCargaHoraria",NULL,"ReducaoCargaHoraria","ReducaoCargaHoraria","ReducaoCargaHoraria"));
    $relatorio->set_metodo(array(NULL,"exibeStatus",NULL,"exibeDadosPericia",NULL,"exibePublicacao","exibePeriodo","exibeCi"));

    $relatorio->set_conteudo($result);
    #$relatorio->set_numGrupo(2);
    $relatorio->set_botaoVoltar(FALSE);
    $relatorio->set_logServidor($idServidorPesquisado);
    $relatorio->set_logDetalhe("Visualizou o Relatório de Histórico de Solicitação de Redução da Carga Horária");
    $relatorio->show();

    $page->terminaPagina();
}