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
    Grh::listaDadosServidorRelatorio($idServidorPesquisado,'Relatório de Atestados (Faltas Abonadas)');
    
    # Pega o idPessoa
    $idPessoa = $pessoal->get_idPessoa($idServidorPesquisado);
    
    br();
    $select = "SELECT dtInicio,
                    numDias,
                    ADDDATE(dtInicio,numDias-1),
                    nome_medico,
                    especi_medico,
                    tipo,
                    tbparentesco.Parentesco,
                    tbatestado.obs
                   FROM tbatestado LEFT JOIN tbparentesco ON (tbatestado.parentesco = tbparentesco.idParentesco)
                  WHERE idServidor = $idServidorPesquisado
               ORDER BY 1 desc";

    $result = $pessoal->select($select);

    $relatorio = new Relatorio();   
    $relatorio->set_cabecalhoRelatorio(FALSE);
    $relatorio->set_menuRelatorio(FALSE);
    $relatorio->set_subTotal(TRUE);
    $relatorio->set_totalRegistro(FALSE);
    $relatorio->set_label(array("Data Inicial","Dias","Data Término","Médico","Especialidade","Tipo","Parentesco","Obs"));
    #$relatorio->set_width(array(10,80));
    $relatorio->set_align(array("center","center","center","left","center","center","center","left"));
    $relatorio->set_funcao(array ("date_to_php",NULL,"date_to_php"));

    $relatorio->set_conteudo($result);
    #$relatorio->set_numGrupo(2);
    $relatorio->set_botaoVoltar(FALSE);
    $relatorio->set_logServidor($idServidorPesquisado);
    $relatorio->set_logDetalhe("Visualizou o Relatório da Atestados (Faltas Abonadas)");
    $relatorio->show();

    $page->terminaPagina();
}