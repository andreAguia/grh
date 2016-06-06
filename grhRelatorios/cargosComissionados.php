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
    $pessoal = new Pessoal();

    # Começa uma nova página
    $page = new Page();			
    $page->iniciaPagina();

    ######

    $servidor = new Pessoal();
    $select ='SELECT distinct tbfuncionario.matricula,
                     tbfuncionario.idFuncional,
                     tbpessoa.nome,
                     tbcomissao.descricao,
                     concat(tbtipocomissao.simbolo," - ",tbtipocomissao.descricao," (",tbtipocomissao.vagas," vaga(s))") comissao,
                     tbfuncionario.matricula,                 
                     tbperfil.nome
                FROM tbfuncionario LEFT JOIN tbpessoa ON (tbfuncionario.idPessoa = tbpessoa.idPessoa)                                               
                                   LEFT JOIN tbsituacao ON (tbfuncionario.Sit = tbsituacao.idSit)
                                   LEFT JOIN tbperfil ON (tbfuncionario.idPerfil = tbperfil.idPerfil)
                                   LEFT JOIN tbcomissao ON(tbfuncionario.matricula = tbcomissao.matricula)
                                        JOIN tbtipocomissao ON(tbcomissao.idTipoComissao=tbtipocomissao.idTipoComissao)
              WHERE tbfuncionario.sit = 1
                AND tbcomissao.dtExo is NULL
           ORDER BY comissao, tbpessoa.nome';

    $result = $servidor->select($select);

    $relatorio = new Relatorio();
    $relatorio->set_titulo('Relatório de Servidores com Cargos em Comissão');
    $relatorio->set_subtitulo('Agrupados por Cargo - Ordenados pelo Nome');
    $relatorio->set_label(array('Matricula','Id','Nome','Descrição','Cargo','Lotação','Perfil'));
    $relatorio->set_width(array(10,10,25,20,0,25,5));
    $relatorio->set_align(array("center","center","left","left","left","left"));
    $relatorio->set_funcao(array("dv"));
    $relatorio->set_classe(array(null,null,null,null,null,"Pessoal"));
    $relatorio->set_metodo(array(null,null,null,null,null,"get_Lotacao"));
    $relatorio->set_conteudo($result);
    $relatorio->set_numGrupo(4);
    #$relatorio->set_botaoVoltar('../sistema/areaServidor.php');
    $relatorio->show();

    $page->terminaPagina();
}