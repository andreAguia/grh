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
    Grh::listaDadosServidorRelatorio($idServidorPesquisado,'Histórico de Triênios');
    
    br();
    $select = "SELECT dtInicial,
                      percentual,
                      dtInicioPeriodo,
                      dtFimPeriodo,
                      numProcesso,
                      concat(date_format(dtPublicacao,'%d/%m/%Y'),' - Pag ',pgPublicacao),
                      documento,
                      idTrienio
                 FROM tbtrienio
                WHERE idServidor = $idServidorPesquisado
             ORDER BY 2 desc";

    $result = $pessoal->select($select);

    $relatorio = new Relatorio();   
    $relatorio->set_cabecalhoRelatorio(FALSE);
    $relatorio->set_menuRelatorio(FALSE);
    $relatorio->set_subTotal(TRUE);
    $relatorio->set_totalRegistro(FALSE);
    #$relatorio->set_subtitulo("Todas as Licenças");
    $relatorio->set_label(array("a partir de","%","P.Aq.Início","P.Aq.Fim","Processo","DOERJ","Documento"));
    $relatorio->set_width(array(10,5,10,10,20,25,20));
    $relatorio->set_align(array('center'));
    $relatorio->set_funcao(array ("date_to_php",NULL,"date_to_php","date_to_php"));

    $relatorio->set_conteudo($result);
    #$relatorio->set_numGrupo(2);
    $relatorio->set_botaoVoltar(FALSE);
    $relatorio->set_logServidor($idServidorPesquisado);
    $relatorio->set_logDetalhe("Visualizou o Relatório de Histórico de Triênios");
    $relatorio->show();

    $page->terminaPagina();
}