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
    Grh::listaDadosServidorRelatorio($idServidorPesquisado,'Relatório de Afastamento para Serviço Eleitorais no TRE');
    
    br();

    $select = "SELECT data,                                    
                      dias,
                      ADDDATE(data,dias-1),
                      folgas,
                      descricao,
                      documento,
                      year(data)
                 FROM tbtrabalhotre 
                WHERE idServidor = $idServidorPesquisado
             ORDER BY data";		


    $result = $pessoal->select($select);

    $relatorio = new Relatorio();
    $relatorio->set_cabecalhoRelatorio(FALSE);
    $relatorio->set_menuRelatorio(FALSE);
    $relatorio->set_subTotal(TRUE);
    #$relatorio->set_totalRegistro(FALSE);
    #$relatorio->set_titulo('Relatório Anual de Afastamento para Serviço Eleitorais no TRE');
    #$relatorio->set_subtitulo('Ordenado pelo Nome do Servidor');

    $relatorio->set_label(array('Data Inicial','Dias','Data Final','Folgas<br/>Concedidas','Descriçao','Documentaçao'));
    #$relatorio->set_width(array(10,30,20,10,10,10));
    $relatorio->set_align(array('center','center','center','center','left','left'));
    $relatorio->set_funcao(array("date_to_php",NULL,"date_to_php"));
    #$relatorio->set_classe(array(NULL,NULL,"pessoal"));
    #$relatorio->set_metodo(array(NULL,NULL,"get_lotacao"));  

    $relatorio->set_conteudo($result);
    $relatorio->set_numGrupo(6);
    $relatorio->show();

    $page->terminaPagina();
}