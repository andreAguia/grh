<?php
/**
 * Relatório
 *    
 * By Alat
 */

# Inicia as variáveis que receberão as sessions
$matricula = null;		  # Reservado para a matrícula do servidor logado

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
    $relatorioAno = post('ano',date('Y'));

    ######
    
    $select = 'SELECT tbfuncionario.matricula,
                      tbfuncionario.idfuncional,
                      tbpessoa.nome,
                      tblicenca.dtInicioPeriodo,
                      tblicenca.dtFimPeriodo,
                      tblicenca.dtInicial,
                      tblicenca.numDias,
                      ADDDATE(tblicenca.dtInicial,tblicenca.numDias-1),
                      MONTH(tblicenca.dtInicial)
                 FROM tbfuncionario LEFT JOIN tbpessoa ON (tbfuncionario.idPessoa = tbpessoa.idPessoa)
                                    LEFT JOIN tblicenca ON (tbfuncionario.matricula = tblicenca.matricula)
                WHERE tbfuncionario.Sit = 1
                  AND tblicenca.idTpLicenca = 6
                  AND YEAR(tblicenca.dtInicial) = '.$relatorioAno.'   
             ORDER BY 6';

    $result = $pessoal->select($select);

    $relatorio = new Relatorio();
    $relatorio->set_titulo('Relatório Anual de Licença Premio');
    $relatorio->set_tituloLinha2($relatorioAno);
    $relatorio->set_subtitulo('Ordem de Data Inicial da Licença');

    $relatorio->set_label(array('Matrícula','Id','Nome','P.Aquisitivo (Início)','P.Aquisitivo (Fim)','Data Inicial','Dias','Data Final','Mês'));
    $relatorio->set_width(array(10,10,35,10,10,10,5,10,0));
    $relatorio->set_align(array('center','center','left'));
    $relatorio->set_funcao(array('dv',null,null,"date_to_php","date_to_php","date_to_php",null,"date_to_php","get_nomeMes"));

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
                         'col' => 3,
                         'title' => 'Ano',
                         'onChange' => 'formPadrao.submit();',
                         'padrao' => $relatorioAno,
                         'linha' => 1)));

    $relatorio->set_formFocus('ano');		
    $relatorio->set_formLink('?');
    $relatorio->show();

    $page->terminaPagina();
}