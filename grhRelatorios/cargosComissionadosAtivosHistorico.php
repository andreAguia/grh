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
	
    # Pega o cargo
    $cargo = get('cargo');
	

    # Começa uma nova página
    $page = new Page();			
    $page->iniciaPagina();

    ######

    $servidor = new Pessoal();
    $select ='SELECT distinct tbservidor.idFuncional,
                     tbservidor.matricula,
                     tbpessoa.nome,
                     tbcomissao.idComissao,
                     tbcomissao.dtNom,
                     tbcomissao.dtExo,
                     concat(tbcomissao.descricao," ",if(protempore = 1," (pro tempore)","")),
                     concat(tbtipocomissao.simbolo," - ",tbtipocomissao.descricao)
                FROM tbservidor LEFT JOIN tbpessoa ON (tbservidor.idPessoa = tbpessoa.idPessoa)
                                LEFT JOIN tbcomissao ON(tbservidor.idServidor = tbcomissao.idServidor)
                                     JOIN tbtipocomissao ON(tbcomissao.idTipoComissao=tbtipocomissao.idTipoComissao)
               WHERE tbtipocomissao.ativo';
				
	if(!is_null($cargo)){
		$select .= ' AND tbtipocomissao.idTipoComissao = '.$cargo;
	}
			                    
    $select .= ' ORDER BY 8, tbcomissao.descricao,tbcomissao.dtNom desc';

    $result = $servidor->select($select);

    $relatorio = new Relatorio();
    $relatorio->set_titulo('Relatório Histórico de Servidores com Cargos em Comissão');
    $relatorio->set_tituloLinha2('Cargos Ativos');
    $relatorio->set_subtitulo('Agrupados pelo Símbolo - Ordenados Cronologicamente');
    $relatorio->set_label(array('IdFuncional','Matrícula','Nome','Descrição','Nomeação','Exoneração'));
    #$relatorio->set_width(array(10,10,30,15,15,20,0));
    $relatorio->set_align(array("center","center","left","left","center","center"));
    $relatorio->set_funcao(array(NULL,"dv",NULL,"descricaoComissao","date_to_php","date_to_php"));
    $relatorio->set_conteudo($result);
    $relatorio->set_numGrupo(7);
    #$relatorio->set_botaoVoltar('../sistema/areaServidor.php');
    $relatorio->show();

    $page->terminaPagina();
}