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
    Grh::listaDadosServidorRelatorio($idServidorPesquisado,'Cadastro de Parentes');
    
    # Pega o idPessoa
    $idPessoa = $pessoal->get_idPessoa($idServidorPesquisado);
    
    br();
    $select = "SELECT nome,
                      dtNasc,
                      tbparentesco.parentesco,
                      CASE sexo
                           WHEN 'F' THEN 'Feminino'
                           WHEN 'M' THEN 'Masculino'
                      end,
                      TIMESTAMPDIFF(YEAR,dtNasc,CURDATE()),
                      dependente,
                      auxCreche,
                      dtTermino,
                      idDependente
                 FROM tbdependente JOIN tbparentesco ON (tbparentesco.idParentesco = tbdependente.parentesco)
                WHERE idPessoa = $idPessoa
             ORDER BY dtNasc desc";

    $result = $pessoal->select($select);

    $relatorio = new Relatorio();   
    $relatorio->set_cabecalhoRelatorio(FALSE);
    $relatorio->set_menuRelatorio(FALSE);
    $relatorio->set_subTotal(TRUE);
    $relatorio->set_totalRegistro(FALSE);
    $relatorio->set_label(array("Nome","Nascimento","Parentesco","Sexo","Idade","Dependente no IR","Auxílio Creche","Término do Aux. Creche"));
    #$relatorio->set_width(array(10,10,10,5,8,10,15));
    $relatorio->set_align(array('left'));
    $relatorio->set_funcao(array(NULL,"date_to_php",NULL,NULL,NULL,NULL,NULL,"date_to_php"));
    
    $relatorio->set_conteudo($result);
    #$relatorio->set_numGrupo(2);
    $relatorio->set_botaoVoltar(FALSE);
    $relatorio->set_logServidor($idServidorPesquisado);
    $relatorio->set_logDetalhe("Visualizou o Relatório de Lista de Parentes");
    $relatorio->show();

    $page->terminaPagina();
}