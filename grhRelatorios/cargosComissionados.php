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
    $pessoal = new Pessoal();

    # Começa uma nova página
    $page = new Page();			
    $page->iniciaPagina();

    ######

    $servidor = new Pessoal();
    $select ='SELECT distinct tbservidor.idFuncional,
                     tbpessoa.nome,
                     tbcomissao.idComissao,
                     IF(protempore,"Sim",""),
                     tbcomissao.dtNom,
                     tbperfil.nome,
                     concat(tbtipocomissao.simbolo," - ",tbtipocomissao.descricao," (",tbtipocomissao.vagas," vaga(s))") comissao
                FROM tbservidor LEFT JOIN tbpessoa ON (tbservidor.idPessoa = tbpessoa.idPessoa) 
                                LEFT JOIN tbperfil ON (tbservidor.idPerfil = tbperfil.idPerfil)
                                LEFT JOIN tbcomissao ON(tbservidor.idServidor = tbcomissao.idServidor)
                                     JOIN tbtipocomissao ON(tbcomissao.idTipoComissao=tbtipocomissao.idTipoComissao)
              WHERE tbservidor.situacao = 1
                AND tbcomissao.dtExo is NULL
           ORDER BY comissao, tbpessoa.nome';

    $result = $servidor->select($select);

    $relatorio = new Relatorio();
    $relatorio->set_titulo('Relatório de Servidores com Cargos em Comissão');
    $relatorio->set_subtitulo('Agrupados por Cargo - Ordenados pelo Nome');
    $relatorio->set_label(array('IdFuncional','Nome','Descrição/Lotação','Pro Tempore','Nomeação','Perfil',''));
    $relatorio->set_funcao(array(NULL,NULL,"descricaoComissao",NULL,"date_to_php"));
    #$relatorio->set_width(array(10,30,20,0,25,10));
    $relatorio->set_align(array("center","left","left","center","center"));
    #$relatorio->set_classe(array(NULL,NULL,NULL,NULL,"Pessoal"));
    #$relatorio->set_metodo(array(NULL,NULL,NULL,NULL,"get_Lotacao"));
    $relatorio->set_conteudo($result);
    $relatorio->set_numGrupo(6);
    #$relatorio->set_botaoVoltar('../sistema/areaServidor.php');
    $relatorio->show();

    $page->terminaPagina();
}