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
    
    # Pega os parâmetros dos relatórios
    $relatorioLotacao = post('lotacao','CBB');

    ######
    
    $select ='SELECT tbpessoa.nome,
                     concat(IFNULL(tblotacao.DIR,"")," - ",IFNULL(tblotacao.GER,"")) lotacao,                     
                     tbservidor.idServidor,
                     tbtipocargo.nivel
                FROM tbservidor LEFT JOIN tbpessoa USING (idPessoa)
                                     JOIN tbhistlot USING (idServidor)
                                     JOIN tblotacao ON (tbhistlot.lotacao=tblotacao.idLotacao)
                                LEFT JOIN tbcargo USING (idCargo)
                                     JOIN tbtipocargo USING (idTipoCargo) 
               WHERE tbservidor.situacao = 1
                 AND (idPerfil = 1 OR idPerfil = 4)
                 AND tbtipocargo.tipo = "Adm/Tec"
                 AND tbhistlot.data = (select max(data) from tbhistlot where tbhistlot.idServidor = tbservidor.idServidor)';
    
    # Lotação
    if(!is_null($relatorioLotacao)){
        # Verifica se o que veio é numérico
        if(is_numeric($relatorioLotacao)){
            $select .= ' AND (tblotacao.idlotacao = "'.$relatorioLotacao.'")';
            $titulo = $servidor->get_nomeLotacao($relatorioLotacao);
        }else{ # senão é uma diretoria genérica
            $select .= ' AND (tblotacao.DIR = "'.$relatorioLotacao.'")';
            $titulo = "Lotação: ".$relatorioLotacao."<br/>";
        }
    }
    
    $select .= ' ORDER BY tbtipocargo.nivel, tbpessoa.nome';

    $result = $servidor->select($select);

    $relatorio = new Relatorio();
    $relatorio->set_titulo('Relatório de Servidores Administrativos e Técnicos Ativos');
    $relatorio->set_tituloLinha2($titulo);
    $relatorio->set_subtitulo('Ordenados pela Lotação e Nome');
    $relatorio->set_label(array('Nome','Lotação','Cargo','Nível'));
    #$relatorio->set_width(array(10,30,30,0,10,10,10));
    $relatorio->set_align(array("left","left","left"));
    #$relatorio->set_funcao(array(NULL,NULL,NULL,NULL,NULL,"date_to_php"));
    
    $relatorio->set_classe(array(NULL,NULL,"pessoal"));
    $relatorio->set_metodo(array(NULL,NULL,"get_CargoSimples"));
    
    $relatorio->set_conteudo($result);
    
    # Dados da combo lotacao
    $lotacao = $servidor->select('(SELECT idlotacao, concat(IFNULL(tblotacao.DIR,"")," - ",IFNULL(tblotacao.GER,"")," - ",IFNULL(tblotacao.nome,"")) lotacao
                                              FROM tblotacao
                                             WHERE ativo) UNION (SELECT distinct DIR, DIR
                                              FROM tblotacao
                                             WHERE ativo)
                                          ORDER BY 2');
    
    $relatorio->set_formCampos(array(
                  array ('nome' => 'lotacao',
                         'label' => 'Lotação',
                         'tipo' => 'combo',
                         'array' => $lotacao,
                         'col' => 12,
                         'size' => 10,
                         'padrao' => $relatorioLotacao,
                         'title' => 'Filtra por Lotação.',
                         'onChange' => 'formPadrao.submit();',
                         'linha' => 1)));

    $relatorio->set_formFocus('ano');		
    $relatorio->set_formLink('?');
    $relatorio->set_numGrupo(3);
    $relatorio->show();

    $page->terminaPagina();
}