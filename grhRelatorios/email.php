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

    ######
    
    $select ='SELECT tbfuncionario.matricula,        
                     tbfuncionario.idfuncional,
                     tbpessoa.nome,
                     tbcontatos.numero
                FROM tbcontatos inner join tbpessoa USING (idpessoa)
          INNER JOIN tbfuncionario USING (idpessoa)
               WHERE tbcontatos.tipo = "E-mail"
                 AND tbfuncionario.Sit = 1
            ORDER BY tbpessoa.nome';

    $result = $servidor->select($select);

    $relatorio = new Relatorio();
    $relatorio->set_titulo('Relatório de E-mail dos Servidores Ativos');
    $relatorio->set_subtitulo('Ordenados pelo Nome');
    $relatorio->set_label(array('Matricula','Id','Nome','E-Mail'));
    $relatorio->set_width(array(10,10,40,40));
    $relatorio->set_align(array("center","center","left","left"));
    $relatorio->set_funcao(array("dv"));
    $relatorio->set_conteudo($result);
    $relatorio->show();

    $page->terminaPagina();
}