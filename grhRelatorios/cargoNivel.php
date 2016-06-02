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
$acesso = Verifica::acesso($matricula,13);

if($acesso)
{    
    # Conecta ao Banco de Dados
    $pessoal = new Pessoal();

    # Começa uma nova página
    $page = new Page();			
    $page->iniciaPagina();

    ######
    
    $select ='SELECT tbcargo.nome, 
                     tbcargo.tpCargo,
                     tbplano.numDecreto
                FROM tbcargo LEFT JOIN tbplano ON (tbcargo.idPlano = tbplano.idPlano)
           ORDER BY tbcargo.tpCargo, tbcargo.nome';

    $result = $pessoal->select($select);

    $relatorio = new Relatorio();
    $relatorio->set_titulo('Relatório de Cargos');
    $relatorio->set_subtitulo('Agrupados por Nível - Ordenados pelo Nome do Cargo');

    $relatorio->set_label(array('Cargo','Tipo','Plano de Cargos'));
    $relatorio->set_width(array(60,20,20));
    $relatorio->set_align(array("center"));

    $relatorio->set_conteudo($result);
    $relatorio->set_numGrupo(1);
    $relatorio->show();

    $page->terminaPagina();
}