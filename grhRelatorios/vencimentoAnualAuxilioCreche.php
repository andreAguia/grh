<?php
/**
 * Relatório
 *   
 * By Alat
 */

# Servidor logado 
$idUsuario = null;

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

    # Pega os parâmetros dos relatórios
    #$relatorioMes = post('mes',date('m'));
    $relatorioAno = post('ano',date('Y'));

    ######
    
    $select = 'SELECT tbfuncionario.matricula,
                      tbfuncionario.idfuncional,
                      tbpessoa.nome,
                      tbdependente.nome,
                      tbdependente.dtNasc,
                      dtTermino,
                      ciExclusao,
                      processo,
                      MONTH(dtTermino)
                 FROM tbdependente JOIN tbpessoa ON(tbpessoa.idpessoa = tbdependente.idpessoa)
                                   JOIN tbfuncionario ON (tbfuncionario.idPessoa = tbpessoa.idPessoa)
                WHERE YEAR(dtTermino) = "'.$relatorioAno.'"
                  AND tbfuncionario.sit = 1     
             ORDER BY dtTermino';

    $result = $pessoal->select($select);

    $relatorio = new Relatorio();
    $relatorio->set_titulo('Relatório Anual de Vencimento de Auxílio Creche');
    $relatorio->set_tituloLinha2('Servidores Ativos');
    $relatorio->set_tituloLinha3($relatorioAno);
    $relatorio->set_subtitulo('Ordenado por Data de Término do Auxílio Creche');

    $relatorio->set_label(array("Matrícula","Id","Servidor","Dependente","Nascimento","Término do Aux.","CI Exclusão","Processo","Mês"));
    $relatorio->set_width(array(10,5,20,20,10,10,10,14));
    $relatorio->set_align(array("center","center","left","left"));
    $relatorio->set_funcao(array("dv",null,null,null,"date_to_php","date_to_php",null,null,"get_nomeMes"));

    $relatorio->set_conteudo($result);
    $relatorio->set_numGrupo(8);
    $relatorio->set_botaoVoltar(false);
    #$relatorio->set_zebrado(false);
    #$relatorio->set_bordaInterna(true);
    #$relatorio->set_cabecalho(false);
    $relatorio->set_formCampos(array(
                  array ('nome' => 'ano',
                         'label' => 'Ano:',
                         'tipo' => 'texto',
                         'size' => 4,
                         'title' => 'Ano',
                         'padrao' => $relatorioAno,
                         'onChange' => 'formPadrao.submit();',
                         'col' => 3,
                         'linha' => 1)));

    $relatorio->set_formFocus('ano');		
    $relatorio->set_formLink('?');
    $relatorio->show();

    $page->terminaPagina();
}
?>
