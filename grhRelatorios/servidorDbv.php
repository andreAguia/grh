<?php
/**
 * Sistema GRH
 * 
 * Relatório
 *   
 * By Alat
 */

# Inicia as variáveis que receberão as sessions
$matricula = null;		  # Reservado para a matrícula do servidor logado

# Configuração
include ("../grhSistema/_config.php");

# Permissão de Acesso
$acesso = Verifica::acesso($matricula);

if($acesso)
{    
    # Conecta ao Banco de Dados
    $servidor = new Pessoal();

    # Começa uma nova página
    $page = new Page();			
    $page->iniciaPagina();
    
    # Pega os parâmetros dos relatórios
    $anoBase = post('anoBase',date('Y')-1);    
    
    ######
    
    $select ='SELECT tbfuncionario.matricula,
                     tbpessoa.nome,
                     tbdbvControle.data,
                     tbdbvControle.anoBase
                FROM tbdbvControle LEFT JOIN tbfuncionario ON (tbdbvControle.matricula = tbfuncionario.matricula)
                                   LEFT JOIN tbpessoa ON (tbfuncionario.idPessoa = tbpessoa.idPessoa)
                WHERE tbdbvControle.anoBase = '.$anoBase.'
            ORDER BY tbdbvControle.data';

    $result = $servidor->select($select);

    $relatorio = new Relatorio();
    $relatorio->set_titulo('Relatório de Servidores que Entregaram a Declaração de Bens e Valores');
    $relatorio->set_tituloLinha2($anoBase);

    $relatorio->set_label(array('Matrícula','Nome','Data da Entrega',''));
    $relatorio->set_width(array(20,40,20,0));
    $relatorio->set_align(array('center'));
    $relatorio->set_funcao(array("dv",null,"date_to_php"));

    $relatorio->set_conteudo($result);
    $relatorio->set_numGrupo(3);
    #$relatorio->set_botaoVoltar('../sistema/areaServidor.php');

    $relatorio->set_formCampos(array(
                               array ('nome' => 'anoBase',
                                      'label' => 'Ano Base:',
                                      'tipo' => 'texto',
                                      'size' => 4,
                                      'title' => 'Ano',
                                      'padrao' => $anoBase,
                                      'onChange' => 'formPadrao.submit();',
                                      'col' => 3,
                                      'linha' => 1)));

    $relatorio->set_formFocus('anoBase');
    $relatorio->set_formLink('?');
    $relatorio->show();

    $page->terminaPagina();
}