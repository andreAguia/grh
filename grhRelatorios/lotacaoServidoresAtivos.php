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
    $servidor = new Pessoal();

    # Começa uma nova página
    $page = new Page();			
    $page->iniciaPagina();
    
    # Pega os parâmetros dos relatórios
    $lotacao = get('lotacao',post('lotacao'));

    ######
    
    $select ='SELECT tbservidor.idFuncional,
                     tbpessoa.nome,
                     tbservidor.idServidor,
                     concat(IFNULL(tblotacao.UADM,"")," - ",IFNULL(tblotacao.DIR,"")," - ",IFNULL(tblotacao.GER,"")," - ",IFNULL(tblotacao.nome,"")) lotacao,
                     tbperfil.nome,
                     tbservidor.dtAdmissao,
                     tbservidor.idServidor
                FROM tbservidor LEFT JOIN tbpessoa ON (tbservidor.idPessoa = tbpessoa.idPessoa)
                                        JOIN tbhistlot ON (tbservidor.idServidor = tbhistlot.idServidor)
                                        JOIN tblotacao ON (tbhistlot.lotacao=tblotacao.idLotacao)
                                   LEFT JOIN tbperfil ON (tbservidor.idPerfil = tbperfil.idPerfil)
               WHERE tbservidor.situacao = 1
                 AND tbhistlot.data = (select max(data) from tbhistlot where tbhistlot.idServidor = tbservidor.idServidor)
                 AND idlotacao="'.$lotacao.'"
            ORDER BY lotacao, tbpessoa.nome';

    $result = $servidor->select($select);

    $relatorio = new Relatorio();
    $relatorio->set_titulo('Relatório Geral de Servidores Ativos');
    $relatorio->set_subtitulo('Agrupados por Lotação - Ordenados pelo Nome');
    $relatorio->set_label(array('IdFuncional','Nome','Cargo','Lotação','Perfil','Admissão','Situação'));
    $relatorio->set_width(array(10,30,30,0,10,10,10));
    $relatorio->set_align(array("center","left","left"));
    $relatorio->set_funcao(array(null,null,null,null,null,"date_to_php"));
    
    $relatorio->set_classe(array(null,null,"pessoal",null,null,null,"pessoal"));
    $relatorio->set_metodo(array(null,null,"get_Cargo",null,null,null,"get_Situacao"));
    
    $relatorio->set_conteudo($result);
    $relatorio->set_numGrupo(3);
    
    $listaLotacao = $servidor->select('SELECT idlotacao, concat(IFNULL(tblotacao.UADM,"")," - ",IFNULL(tblotacao.DIR,"")," - ",IFNULL(tblotacao.GER,"")," - ",IFNULL(tblotacao.nome,"")) lotacao
                                          FROM tblotacao
                                       WHERE tblotacao.ativo  
                                      ORDER BY ativo desc,lotacao');
    array_unshift($listaLotacao,array('*','-- Selecione a Lotação --'));
    
    $relatorio->set_formCampos(array(
                               array ('nome' => 'lotacao',
                                      'label' => 'Lotação:',
                                      'tipo' => 'combo',
                                      'array' => $listaLotacao,
                                      'size' => 30,
                                      'padrao' => $lotacao,
                                      'title' => 'Mês',
                                      'onChange' => 'formPadrao.submit();',
                                      'linha' => 1)));
    
    $relatorio->set_formFocus('lotacao');
    $relatorio->set_formLink('?');
    $relatorio->show();

    $page->terminaPagina();
}