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
    $relatorioMes = post('mes',date('m'));
    $relatorioAno = post('ano',date('Y'));

    ######
    
    $data = $relatorioAno.'-'.$relatorioMes.'-01';

    $select = 'SELECT tbservidor.idServidor,
                      tbpessoa.nome,
                      tbperfil.nome,
                      data,                                    
                      dias,
                      ADDDATE(data,dias-1)
                 FROM tbservidor LEFT JOIN tbpessoa ON (tbservidor.idPessoa = tbpessoa.idPessoa)
                                    LEFT JOIN tbfolga ON (tbservidor.idServidor = tbfolga.idServidor)                                
                                    LEFT JOIN tbperfil ON(tbservidor.idPerfil=tbperfil.idPerfil)
                WHERE tbservidor.situacao = 1
                  AND (("'.$data.'" BETWEEN data AND ADDDATE(data,dias-1))
                   OR  (LAST_DAY("'.$data.'") BETWEEN data AND ADDDATE(data,dias-1))
                   OR  ("'.$data.'" < data AND LAST_DAY("'.$data.'") > ADDDATE(data,dias-1)))
             ORDER BY data';		


    $result = $pessoal->select($select);

    $relatorio = new Relatorio();
    $relatorio->set_titulo('Relatório Mensal de Folgas Fruídas do TRE');
    $relatorio->set_tituloLinha2(get_nomeMes($relatorioMes).' / '.$relatorioAno);
    $relatorio->set_subtitulo('Ordem de Data Inicial da Folga');

    $relatorio->set_label(array('IdFuncional','Nome','Perfil','Data Inicial','Dias','Data Final'));
    $relatorio->set_width(array(10,30,20,10,10,10));
    $relatorio->set_align(array('center'));
    $relatorio->set_funcao(array(null,null,null,"date_to_php",null,"date_to_php"));

    $relatorio->set_conteudo($result);
    #$relatorio->set_numGrupo(2);
    $relatorio->set_botaoVoltar(false);
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