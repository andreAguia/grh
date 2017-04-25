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
    Grh::listaDadosServidorRelatorio($idServidorPesquisado,'Histórico de Progressões e Enquadramentos');
    
    br();
    $select = "SELECT tbprogressao.dtInicial,
                      tbtipoprogressao.nome,
                      CONCAT(tbclasse.faixa,' - ',tbclasse.valor) as vv,
                      numProcesso,
                      CONCAT(date_format(dtPublicacao,'%d/%m/%Y'),' - Pag ',pgPublicacao),
                      documento,
                      tbprogressao.idProgressao
                 FROM tbprogressao JOIN tbtipoprogressao ON (tbprogressao.idTpProgressao = tbtipoprogressao.idTpProgressao)
                                   JOIN tbclasse ON (tbprogressao.idClasse = tbclasse.idClasse)
                WHERE idServidor = $idServidorPesquisado
             ORDER BY tbprogressao.dtInicial desc";

    $result = $pessoal->select($select);

    $relatorio = new Relatorio();   
    $relatorio->set_cabecalhoRelatorio(FALSE);
    $relatorio->set_menuRelatorio(FALSE);
    $relatorio->set_subTotal(TRUE);
    $relatorio->set_totalRegistro(FALSE);
    $relatorio->set_label(array("Data Inicial","Tipo de aumento","Valor","Processo","DOERJ","Documento"));
    $relatorio->set_width(array(10,25,15,18,17,15));
    $relatorio->set_align(array('center'));
    $relatorio->set_funcao(array ('date_to_php'));

    $relatorio->set_conteudo($result);
    #$relatorio->set_numGrupo(2);
    $relatorio->set_botaoVoltar(FALSE);
    $relatorio->show();

    $page->terminaPagina();
}