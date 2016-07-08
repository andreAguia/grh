<?php
/**
 * Relatório Anual de Vencimento de Triênio
 * 
 * Triênio
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
    
    $select = '(SELECT tbfuncionario.matricula,
                      tbfuncionario.idFuncional,  
                      tbpessoa.nome,
                      tbfuncionario.dtadmissao,
                      CONCAT(MAX(tbtrienio.percentual),"%"),
                      MAX(tbtrienio.dtInicial),
                      DATE_ADD(MAX(tbtrienio.dtInicial), INTERVAL 3 YEAR),
                      month(DATE_ADD(MAX(tbtrienio.dtInicial), INTERVAL 3 YEAR))
                 FROM tbfuncionario LEFT JOIN tbpessoa ON (tbfuncionario.idPessoa = tbpessoa.idPessoa)
                                    LEFT JOIN tbtrienio ON (tbtrienio.matricula = tbfuncionario.matricula)
                WHERE tbfuncionario.Sit = 1
                  AND idPerfil = 1              
                GROUP BY tbfuncionario.matricula
               HAVING YEAR (DATE_ADD(MAX(tbtrienio.dtInicial), INTERVAL 3 YEAR)) = "'.$relatorioAno.'"
             ORDER BY tbfuncionario.matricula)
             UNION
             (SELECT  tbfuncionario.matricula,
                      tbfuncionario.idFuncional,  
                      tbpessoa.nome,
                      tbfuncionario.dtadmissao,
                      "",
                      "",
                      DATE_ADD(tbfuncionario.dtadmissao, INTERVAL 3 YEAR),
                      month(DATE_ADD(tbfuncionario.dtadmissao, INTERVAL 3 YEAR))
                 FROM tbfuncionario LEFT JOIN tbpessoa ON (tbfuncionario.idPessoa = tbpessoa.idPessoa)
                                    LEFT JOIN tbtrienio ON (tbtrienio.matricula = tbfuncionario.matricula)
                WHERE tbfuncionario.Sit = 1
                  AND idPerfil = 1              
             GROUP BY tbfuncionario.matricula
             HAVING YEAR (DATE_ADD(tbfuncionario.dtadmissao, INTERVAL 3 YEAR)) = "'.$relatorioAno.'"
                 AND MAX(tbtrienio.dtInicial) IS NULL
             ORDER BY tbfuncionario.matricula)
             ORDER BY 8,3';		


    $result = $pessoal->select($select);

    $relatorio = new Relatorio();
    $relatorio->set_titulo('Relatório Anual de Vencimento de Triênios - '.$relatorioAno);
    $relatorio->set_subtitulo('Ordenado por Nome do Servidor');

    $relatorio->set_label(array('Matrícula','IdFuncional','Nome','Admissão','Último Percentual','Último Triênio','Próximo Triênio','Mês'));
    $relatorio->set_width(array(10,10,40,10,5,10,10));
    $relatorio->set_align(array('center','center','left'));
    $relatorio->set_funcao(array("dv",null,null,"date_to_php",null,"date_to_php","date_to_php","get_nomeMes"));

    $relatorio->set_conteudo($result);
    $relatorio->set_numGrupo(7);
    $relatorio->set_botaoVoltar(false);
    $relatorio->set_zebrado(true);
    #$relatorio->set_bordaInterna(true);
    #$relatorio->set_cabecalho(false);
    $relatorio->set_formCampos(array(
                  array ('nome' => 'ano',
                         'label' => 'Ano:',
                         'tipo' => 'texto',
                         'size' => 4,
                         'onChange' => 'formPadrao.submit();',
                         'title' => 'Ano',
                         'padrao' => $relatorioAno,
                         'col' => 3,
                         'linha' => 1)));

    $relatorio->set_formFocus('ano');		
    $relatorio->set_formLink('?');
    $relatorio->show();

    $page->terminaPagina();
}