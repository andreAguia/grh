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
                      tbperfil.nome,
                      tbtipolicenca.nome,
                      tblicenca.dtInicial,
                      tblicenca.numDias,
                      ADDDATE(tblicenca.dtInicial,tblicenca.numDias-1),
                      MONTH(ADDDATE(tblicenca.dtInicial,tblicenca.numDias-1))
                 FROM tbfuncionario LEFT JOIN tbpessoa ON (tbfuncionario.idPessoa = tbpessoa.idPessoa)
                                    LEFT JOIN tblicenca ON (tbfuncionario.matricula = tblicenca.matricula)
                                    LEFT JOIN tbtipolicenca ON (tblicenca.idTpLicenca = tbtipolicenca.idTpLicenca)
                                    LEFT JOIN tbperfil ON(tbfuncionario.idPerfil=tbperfil.idPerfil)
                WHERE tbfuncionario.Sit = 1
                  AND YEAR(ADDDATE(tblicenca.dtInicial,tblicenca.numDias-1)) = '.$relatorioAno.'   
             ORDER BY 8';

    $result = $pessoal->select($select);

    $relatorio = new Relatorio();
    $relatorio->set_titulo('Relatório Anual de Término de Licença');
    $relatorio->set_tituloLinha2($relatorioAno);
    $relatorio->set_subtitulo('Ordem de Data de Término da Licença');

    $relatorio->set_label(array('Matrícula','Id','Nome','Perfil','Licença','Data Inicial','Dias','Data Final','Mês'));
    $relatorio->set_width(array(6,5,25,10,20,10,5,10));
    $relatorio->set_align(array('center','center','left'));
    $relatorio->set_funcao(array('dv',null,null,null,null,"date_to_php",null,"date_to_php","get_nomeMes"));

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
                         'onChange' => 'formPadrao.submit();',
                         'padrao' => $relatorioAno,
                         'col' => 3,
                         'linha' => 1)));

    $relatorio->set_formFocus('ano');		
    $relatorio->set_formLink('?');
    $relatorio->show();

    $page->terminaPagina();
}