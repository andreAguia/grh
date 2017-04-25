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
    $pessoal = new Pessoal();

    # Começa uma nova página
    $page = new Page();			
    $page->iniciaPagina();
    
    # Pega os parâmetros dos relatórios
    $relatorioAno = post('ano',date('Y'));

    ######
    
    $select = 'SELECT tbservidor.idfuncional,
                      tbpessoa.nome,
                      tblicenca.dtInicioPeriodo,
                      tblicenca.dtFimPeriodo,
                      tblicenca.dtInicial,
                      tblicenca.numDias,
                      ADDDATE(tblicenca.dtInicial,tblicenca.numDias-1),
                      MONTH(tblicenca.dtInicial)
                 FROM tbservidor LEFT JOIN tbpessoa ON (tbservidor.idPessoa = tbpessoa.idPessoa)
                                    LEFT JOIN tblicenca ON (tbservidor.idServidor = tblicenca.idServidor)
                WHERE tbservidor.situacao = 1
                  AND tblicenca.idTpLicenca = 6
                  AND YEAR(tblicenca.dtInicial) = '.$relatorioAno.'   
             ORDER BY 5';

    $result = $pessoal->select($select);

    $relatorio = new Relatorio();
    $relatorio->set_titulo('Relatório Anual de Licença Premio');
    $relatorio->set_tituloLinha2($relatorioAno);
    $relatorio->set_subtitulo('Ordem de Data Inicial da Licença');

    $relatorio->set_label(array('IdFuncional','Nome','P.Aquisitivo (Início)','P.Aquisitivo (Fim)','Data Inicial','Dias','Data Final','Mês'));
    $relatorio->set_width(array(10,40,10,10,10,10,10,0));
    $relatorio->set_align(array('center','left'));
    $relatorio->set_funcao(array(NULL,NULL,"date_to_php","date_to_php","date_to_php",NULL,"date_to_php","get_nomeMes"));

    $relatorio->set_conteudo($result);
    $relatorio->set_numGrupo(7);
    $relatorio->set_botaoVoltar(FALSE);
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