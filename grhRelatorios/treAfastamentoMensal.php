<?php
/**
 * Relatório
 *    
 * By Alat
 */

## Reservado para a matrícula do servidor logado
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

    $data = $relatorioAno.'-'.$relatorioMes.'-01';

    $select = 'SELECT tbfuncionario.matricula,
                      tbpessoa.nome,
                      tbperfil.nome,
                      data,                                    
                      dias,
                      ADDDATE(data,dias-1),
                      folgas,
                      documento
                 FROM tbfuncionario LEFT JOIN tbpessoa ON (tbfuncionario.idPessoa = tbpessoa.idPessoa)
                                    LEFT JOIN tbfolgatre ON (tbfuncionario.matricula = tbfolgatre.matricula)                                
                                    LEFT JOIN tbperfil ON(tbfuncionario.idPerfil=tbperfil.idPerfil)
                WHERE tbfuncionario.Sit = 1
                  AND (("'.$data.'" BETWEEN data AND ADDDATE(data,dias-1))
                   OR  (LAST_DAY("'.$data.'") BETWEEN data AND ADDDATE(data,dias-1))
                   OR  ("'.$data.'" < data AND LAST_DAY("'.$data.'") > ADDDATE(data,dias-1)))
             ORDER BY data desc';		


    $result = $pessoal->select($select);

    $relatorio = new Relatorio();
    $relatorio->set_titulo('Relatório Mensal de Afastamento para Serviço Eleitorais no TRE');
    $relatorio->set_tituloLinha2(get_nomeMes($relatorioMes).' / '.$relatorioAno);
    $relatorio->set_subtitulo('Ordem de Data Inicial do Afastamento');

    $relatorio->set_label(array('Matrícula','Nome','Perfil','Data Inicial','Dias','Data Final'));
    $relatorio->set_width(array(10,30,20,10,10,10));
    $relatorio->set_align(array('center'));
    $relatorio->set_funcao(array('dv',null,null,"date_to_php",null,"date_to_php"));

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
                         'col' => 3,
                         'title' => 'Ano',
                         'onChange' => 'formPadrao.submit();',
                         'padrao' => $relatorioAno,
                         'linha' => 1), 
                  array ('nome' => 'mes',
                         'label' => 'Mês',
                         'tipo' => 'combo',
                         'array' => $mes,
                         'size' => 10,
                         'col' => 3,
                         'padrao' => $relatorioMes,
                         'title' => 'Mês do Ano.',
                         'onChange' => 'formPadrao.submit();',
                         'linha' => 1)));

    $relatorio->set_formFocus('ano');		
    $relatorio->set_formLink('?');
    $relatorio->show();

    $page->terminaPagina();
}