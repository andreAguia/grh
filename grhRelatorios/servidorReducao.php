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
    
    # Pega o número do processo
    $processo = $pessoal->get_numProcessoReducao($idServidorPesquisado);
    
    br();
    $select = "SELECT dtSolicitacao,
                    dtPericia,
                    CASE
                    WHEN resultado = 1 THEN 'Deferido'
                    WHEN resultado = 2 THEN 'Indeferido'
                    ELSE '---'
                    END,
                    dtPublicacao,
                    dtInicio,
                    periodo,
                    ADDDATE(dtInicio, INTERVAL periodo MONTH),
                    numCiInicio,
                    numCiTermino,
                    idReducao
               FROM tbreducao
              WHERE idServidor = $idServidorPesquisado
           ORDER BY 1 desc";

    $result = $pessoal->select($select);

    $relatorio = new Relatorio();   
    $relatorio->set_cabecalhoRelatorio(FALSE);
    $relatorio->set_menuRelatorio(FALSE);
    $relatorio->set_subTotal(TRUE);
    $relatorio->set_totalRegistro(FALSE);
    $relatorio->set_label(array("Solicitado em:","Pericia","Resultado","Publicação","Início","Período<br/>(Meses)","Término","CI Início","CI Término"));
    $relatorio->set_subtitulo("Processo: ".$processo);
    $relatorio->set_align(array("center"));
    $relatorio->set_funcao(array("date_to_php","date_to_php",NULL,"date_to_php","date_to_php",NULL,"date_to_php"));

    $relatorio->set_conteudo($result);
    #$relatorio->set_numGrupo(2);
    $relatorio->set_botaoVoltar(FALSE);
    $relatorio->set_logServidor($idServidorPesquisado);
    $relatorio->set_logDetalhe("Visualizou o Relatório de Histórico de Solicitação de Redução da Carga Horária");
    $relatorio->show();

    $page->terminaPagina();
}