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

    # Pega os parâmetros dos relatórios
    $relatorioMes = post('mes',date('m'));
    $relatorioAno = post('ano',date('Y'));

    # Começa uma nova página
    $page = new Page();			
    $page->iniciaPagina();

    ######

    $data = $relatorioAno.'-'.$relatorioMes.'-01';
    $select = 'SELECT tbservidor.idfuncional,
                      tbpessoa.nome,
                      tbperfil.nome,
                      tbatestado.dtInicio,
                      tbatestado.numDias,
                      ADDDATE(tbatestado.dtInicio,tbatestado.numDias-1),
                      tbatestado.nome_medico,
                      tbatestado.especi_medico,
                      tbatestado.tipo
                 FROM tbservidor LEFT JOIN tbpessoa ON (tbservidor.idPessoa = tbpessoa.idPessoa)
                                 LEFT JOIN tbatestado ON (tbservidor.idServidor = tbatestado.idServidor)                                
                                 LEFT JOIN tbperfil ON(tbservidor.idPerfil = tbperfil.idPerfil)
                WHERE tbservidor.situacao = 1
                  AND (("'.$data.'" BETWEEN dtInicio AND ADDDATE(tbatestado.dtInicio,tbatestado.numDias-1))
                   OR  (LAST_DAY("'.$data.'") BETWEEN dtInicio AND ADDDATE(tbatestado.dtInicio,tbatestado.numDias-1))
                   OR  ("'.$data.'" < dtInicio AND LAST_DAY("'.$data.'") > ADDDATE(tbatestado.dtInicio,tbatestado.numDias-1)))
             ORDER BY tbatestado.dtInicio';		


    $result = $pessoal->select($select);

    $relatorio = new Relatorio();
    $relatorio->set_titulo('Relatório Mensal de Servidores com Atestado');
    $relatorio->set_tituloLinha2(get_nomeMes($relatorioMes).' / '.$relatorioAno);
    $relatorio->set_subtitulo('Ordenado pela Data Inicial do Atestado');

    $relatorio->set_label(array('Id','Nome','Perfil','Data Inicial','Dias','Data Final','Médico','Especialidade','Tipo'));
    $relatorio->set_width(array(5,20,10,10,5,10,15,15,10));
    $relatorio->set_align(array('center','left'));
    $relatorio->set_funcao(array(null,null,null,"date_to_php",null,"date_to_php"));

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
                         'col' => 3,
                         'array' => $mes,
                         'size' => 10,
                         'padrao' => $relatorioMes,
                         'title' => 'Mês do Ano.',
                         'onChange' => 'formPadrao.submit();',
                         'linha' => 1)));

    $relatorio->set_formFocus('ano');		
    $relatorio->set_formLink('?');
    $relatorio->show();

    $page->terminaPagina();
}