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

    ######

    # Pega os parâmetros dos relatórios
    $relatorioMes = post('mes',date('m'));
    $relatorioAno = post('ano',date('Y'));

    $data = $relatorioAno.'-'.$relatorioMes.'-01';

    $select = 'SELECT tbfuncionario.matricula,
                      tbfuncionario.idfuncional,
                      tbpessoa.nome,
                      tbperfil.nome,
                      tbfaltas.data,
                      tbfaltas.numDias,
                      ADDDATE(data,numDias-1)
                 FROM tbfuncionario LEFT JOIN tbpessoa ON (tbfuncionario.idPessoa = tbpessoa.idPessoa)
                                    LEFT JOIN tbfaltas ON (tbfuncionario.matricula = tbfaltas.matricula)                                
                                    LEFT JOIN tbperfil ON(tbfuncionario.idPerfil=tbperfil.idPerfil)
                WHERE tbfuncionario.Sit = 1
                  AND (("'.$data.'" BETWEEN data AND ADDDATE(data,numDias-1))
                   OR  (LAST_DAY("'.$data.'") BETWEEN data AND ADDDATE(data,numDias-1))
                   OR  ("'.$data.'" < data AND LAST_DAY("'.$data.'") > ADDDATE(data,numDias-1)))
             ORDER BY data desc';		

    $result = $pessoal->select($select);

    $relatorio = new Relatorio();
    $relatorio->set_titulo('Relatório Mensal de Faltas de Servidores');
    $relatorio->set_tituloLinha2(get_nomeMes($relatorioMes).' / '.$relatorioAno);
    $relatorio->set_subtitulo('Ordem Decrescente de Data Inicial da Falta');

    $relatorio->set_label(array('Matrícula','Id','Nome','Perfil','Data Inicial','Dias','Data Final'));
    $relatorio->set_width(array(10,5,25,20,10,10,10));
    $relatorio->set_align(array("center","center","left"));
    $relatorio->set_funcao(array('dv',null,null,null,"date_to_php",null,"date_to_php"));

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
                         'padrao' => $relatorioAno,
                         'onChange' => 'formPadrao.submit();',
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
?>
