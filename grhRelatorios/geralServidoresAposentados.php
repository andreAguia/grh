<?php
/**
 * Relatório
 *    
 * By Alat
 */

# Servidor logado 
$idUsuario = NULL;

# Configuração
include ("../grhSistema/_config.php");

# Permissão de Acesso
$acesso = Verifica::acesso($idUsuario,2);

if($acesso)
{    
    # Conecta ao Banco de Dados
    $servidor = new Pessoal();

    # Começa uma nova página
    $page = new Page();			
    $page->iniciaPagina();
    
    # Pega os parâmetros dos relatórios
    $relatorioAno = post('ano',date('Y'));

    ######

    $select = 'SELECT tbservidor.idfuncional,
                      tbpessoa.nome,
                      tbservidor.idServidor,
                      tbservidor.dtAdmissao,
                      tbservidor.dtDemissao,
                      tbcontatos.numero
                 FROM tbservidor LEFT JOIN tbpessoa ON (tbservidor.idPessoa = tbpessoa.idPessoa)
                                 LEFT JOIN tbcontatos ON (tbservidor.idPessoa = tbcontatos.idPessoa)
                WHERE YEAR(tbservidor.dtDemissao) = "'.$relatorioAno.'"
                  AND situacao = 2   
             ORDER BY dtDemissao';		


    $result = $servidor->select($select);

    $relatorio = new Relatorio();
    $relatorio->set_titulo('Relatório Anual de Servidores Aposentados em '.$relatorioAno);
    $relatorio->set_subtitulo('Ordenado pela Data de Saída');

    $relatorio->set_label(array('IdFuncional','Nome','Cargo','Admissão','Saída','Contato'));
    #$relatorio->set_width(array(10,20,10,10,10,10,10,10,10));
    $relatorio->set_align(array('center','left','left','center','center','left'));
    $relatorio->set_funcao(array(NULL,NULL,NULL,"date_to_php","date_to_php"));
    
    $relatorio->set_classe(array(NULL,NULL,"pessoal"));
    $relatorio->set_metodo(array(NULL,NULL,"get_cargo"));

    $relatorio->set_conteudo($result);
    #$relatorio->set_numGrupo(5);
    $relatorio->set_botaoVoltar(FALSE);
    $relatorio->set_formCampos(array(
                  array ('nome' => 'ano',
                         'label' => 'Ano:',
                         'tipo' => 'texto',
                         'size' => 4,
                         'title' => 'Ano',
                         'onChange' => 'formPadrao.submit();',
                         'padrao' => $relatorioAno,
                         'col' => 3,
                         'linha' => 1)));

    $relatorio->set_formFocus('ano');		
    $relatorio->set_formLink('?');
    $relatorio->show();

    $page->terminaPagina();
}
?>
