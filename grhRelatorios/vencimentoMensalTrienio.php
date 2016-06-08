<?php
/**
 * Relatório Mensal de Vencimento de Triênio
 * 
 * Triênio
 *   
 * By Alat
 */

# Reservado para a matrícula do servidor logado
$matricula = null;

# Configuração
include ("../grhSistema/_config.php");

# Permissão de Acesso
$acesso = Verifica::acesso($matricula,13);

if($acesso)
{    
    # Conecta ao Banco de Dados
    $pessoal = new Pessoal();

    # Começa uma nova página
    $page = new Page();			
    $page->iniciaPagina();

    # Pega os parâmetros dos relatórios
    $relatorioMes = post('mes',date('m'));
    $relatorioAno = post('ano',date('Y'));

    ######
    
    $select = '(SELECT DISTINCT tbfuncionario.matricula,
                      tbfuncionario.idFuncional,  
                      tbpessoa.nome,
                      tbfuncionario.dtadmissao,
                      CONCAT(MAX(tbtrienio.percentual),"%"),
                      MAX(tbtrienio.dtInicial),
                      DATE_ADD(MAX(tbtrienio.dtInicial), INTERVAL 3 YEAR)
                 FROM tbfuncionario LEFT JOIN tbpessoa ON (tbfuncionario.idPessoa = tbpessoa.idPessoa)
                                    LEFT JOIN tbtrienio ON (tbtrienio.matricula = tbfuncionario.matricula)
                WHERE tbfuncionario.Sit = 1
                  AND idPerfil = 1              
             GROUP BY tbfuncionario.matricula
               HAVING YEAR (DATE_ADD(MAX(tbtrienio.dtInicial), INTERVAL 3 YEAR)) = "'.$relatorioAno.'"
                  AND month(DATE_ADD(MAX(tbtrienio.dtInicial), INTERVAL 3 YEAR))="'.$relatorioMes.'" 
             ORDER BY tbpessoa.nome)
             UNION 
                (SELECT DISTINCT tbfuncionario.matricula,
                      tbfuncionario.idFuncional,  
                      tbpessoa.nome,
                      tbfuncionario.dtadmissao,
                      "",
                      "",
                      DATE_ADD(tbfuncionario.dtadmissao, INTERVAL 3 YEAR)
                 FROM tbfuncionario LEFT JOIN tbpessoa ON (tbfuncionario.idPessoa = tbpessoa.idPessoa)
                                    LEFT JOIN tbtrienio ON (tbtrienio.matricula = tbfuncionario.matricula)
                WHERE tbfuncionario.Sit = 1
                  AND idPerfil = 1
                  AND tbtrienio.dtInicial is null
                  AND YEAR (DATE_ADD(tbfuncionario.dtadmissao, INTERVAL 3 YEAR)) = "'.$relatorioAno.'"
                  AND month(DATE_ADD(tbfuncionario.dtadmissao, INTERVAL 3 YEAR))= "'.$relatorioMes.'"               
             GROUP BY tbfuncionario.matricula           
             ORDER BY tbpessoa.nome) 
             ORDER BY 3';		


    $result = $pessoal->select($select);

    $relatorio = new Relatorio();
    $relatorio->set_titulo('Relatório de Vencimento de Triênios');
    $relatorio->set_tituloLinha2(get_nomeMes($relatorioMes).' / '.$relatorioAno);
    $relatorio->set_subtitulo('Ordenado por Nome do Servidor');

    $relatorio->set_label(array('Matrícula','IdFuncional','Nome','Admissão','Último Percentual','Último Triênio','Próximo Triênio'));
    $relatorio->set_width(array(10,10,40,10,5,10,10));
    $relatorio->set_align(array('center','center','left'));
    $relatorio->set_funcao(array("dv",null,null,"date_to_php",null,"date_to_php","date_to_php"));

    $relatorio->set_conteudo($result);
    #$relatorio->set_numGrupo(2);
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
                         'onChange' => 'formPadrao.submit();',
                         'padrao' => $relatorioAno,
                         'col' => 3,
                         'linha' => 1), 
                  array ('nome' => 'mes',
                         'label' => 'Mês',
                         'tipo' => 'combo',
                         'array' => $mes,
                         'size' => 10,
                         'padrao' => $relatorioMes,
                         'title' => 'Mês do Ano.',
                         'onChange' => 'formPadrao.submit();',
                         'col' => 3,
                         'linha' => 1)));

    $relatorio->set_formFocus('ano');		
    $relatorio->set_formLink('?');
    $relatorio->show();

    $page->terminaPagina();
}