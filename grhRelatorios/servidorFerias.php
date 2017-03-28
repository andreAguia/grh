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
    Grh::listaDadosServidorRelatorio($idServidorPesquisado,'Histórico de Férias');
    
    br();
    $select = "SELECT anoExercicio,
                        status,
                        dtInicial,
                        numDias,
                        periodo,
                        ADDDATE(dtInicial,numDias-1),
                        documento,
                        folha
                   FROM tbferias
                  WHERE idServidor = $idServidorPesquisado
               ORDER BY dtInicial desc";

    $result = $pessoal->select($select);

    $relatorio = new Relatorio();   
    $relatorio->set_cabecalhoRelatorio(false);
    $relatorio->set_menuRelatorio(false);
    $relatorio->set_subTotal(true);
    $relatorio->set_totalRegistro(false);
    $relatorio->set_label(array("Exercicio","Status","Data Inicial","Dias","P","Data Final","Documento 1/3","Folha"));
    #$relatorio->set_width(array(10,10,10,5,8,10,15));
    $relatorio->set_align(array('center'));
    $relatorio->set_funcao(array (null,null,'date_to_php',null,null,'date_to_php'));

    $relatorio->set_conteudo($result);
    #$relatorio->set_numGrupo(2);
    $relatorio->set_botaoVoltar(false);
    $relatorio->show();

    $page->terminaPagina();
}