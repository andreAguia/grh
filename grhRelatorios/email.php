<?php
/**
 * Sistema GRH
 * 
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
    $servidor = new Pessoal();

    # Começa uma nova página
    $page = new Page();			
    $page->iniciaPagina();

    ######
    
    $select ='SELECT tbservidor.idfuncional,
                     tbpessoa.nome,
                     tbcontatos.numero
                FROM tbcontatos INNER JOIN tbpessoa USING (idpessoa)
          INNER JOIN tbservidor USING (idpessoa)
               WHERE (tbcontatos.tipo = "E-mail" OR tbcontatos.tipo = "E-mail Principal")
                 AND tbservidor.situacao = 1
            ORDER BY tbpessoa.nome';

    $result = $servidor->select($select);

    $relatorio = new Relatorio();
    $relatorio->set_titulo('Relatório de E-mail dos Servidores Ativos');
    $relatorio->set_subtitulo('Ordenados pelo Nome');
    $relatorio->set_label(array('IdFuncional','Nome','E-Mail'));
    #$relatorio->set_width(array(10,40,50));
    $relatorio->set_align(array("center","left","left"));
    $relatorio->set_conteudo($result);
    $relatorio->show();

    $page->terminaPagina();
}