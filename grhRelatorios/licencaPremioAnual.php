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
                      tblicencaPremio.dtInicial,
                      tblicencaPremio.numDias,
                      ADDDATE(tblicencaPremio.dtInicial,tblicencaPremio.numDias-1),
                      MONTH(tblicencaPremio.dtInicial)
                 FROM tbservidor LEFT JOIN tbpessoa USING (idPessoa)
                                 LEFT JOIN tblicencaPremio USING (idServidor)
                WHERE tbservidor.situacao = 1
                  AND YEAR(tblicencaPremio.dtInicial) = '.$relatorioAno.'   
             ORDER BY 5';

    $result = $pessoal->select($select);

    $relatorio = new Relatorio();
    $relatorio->set_titulo('Relatório Anual de Licença Premio');
    $relatorio->set_subtitulo("Servidores com a Data Inicial da Licença em ".$relatorioAno);
    #$relatorio->set_tituloLinha3($relatorioAno);
    #$relatorio->set_subtitulo('Ordem de Data Inicial da Licença');

    $relatorio->set_label(array('IdFuncional','Nome','Data Inicial','Dias','Data Final','Mês'));
    $relatorio->set_width(array(15,40,15,5,15,0));
    $relatorio->set_align(array('center','left'));
    $relatorio->set_funcao(array(NULL,NULL,"date_to_php",NULL,"date_to_php","get_nomeMes"));

    $relatorio->set_conteudo($result);
    $relatorio->set_numGrupo(5);
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