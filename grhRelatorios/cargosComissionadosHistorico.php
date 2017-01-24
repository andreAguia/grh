<?php
/**
 * Sistema GRH
 * 
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

    ######

    $servidor = new Pessoal();
    $select ='SELECT distinct tbservidor.idFuncional,
                     tbservidor.matricula,
                     tbpessoa.nome,
                     tbcomissao.dtNom,
                     tbcomissao.dtExo,
                     tbcomissao.descricao,
                     tbtipocomissao.simbolo
                FROM tbservidor LEFT JOIN tbpessoa ON (tbservidor.idPessoa = tbpessoa.idPessoa)
                                LEFT JOIN tbcomissao ON(tbservidor.idServidor = tbcomissao.idServidor)
                                     JOIN tbtipocomissao ON(tbcomissao.idTipoComissao=tbtipocomissao.idTipoComissao)
                WHERE tbtipocomissao.ativo                     
           ORDER BY 7, 6, 4';

    $result = $servidor->select($select);

    $relatorio = new Relatorio();
    $relatorio->set_titulo('Relatório de Servidores com Cargos em Comissão');
    $relatorio->set_subtitulo('Agrupados pelo Símbolo - Ordenados Cronologicamente');
    $relatorio->set_label(array('IdFuncional','Matrícula','Nome','Nomeação','Exoneração','Descrição'));
    $relatorio->set_width(array(10,10,30,15,15,20,0));
    $relatorio->set_align(array("center","center","left","center","center","left"));
    $relatorio->set_funcao(array(null,"dv",null,"date_to_php","date_to_php"));
    $relatorio->set_conteudo($result);
    $relatorio->set_numGrupo(6);
    #$relatorio->set_botaoVoltar('../sistema/areaServidor.php');
    $relatorio->show();

    $page->terminaPagina();
}