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
    $lotacao = get('lotacao',post('lotacao'));
    
    $subTitulo = NULL;

    ######
    
    $select ='SELECT tbservidor.idfuncional,
                     tbpessoa.nome,
                     concat(IFNULL(tblotacao.UADM,"")," - ",IFNULL(tblotacao.DIR,"")," - ",IFNULL(tblotacao.GER,"")) lotacao,
                     CONCAT("(",IFNULL(telResidencialDDD,"--"),") ",IFNULL(telResidencial,"---")),
                     CONCAT("(",IFNULL(telCelularDDD,"--"),") ",IFNULL(telCelular,"---")),
                     CONCAT("(",IFNULL(telRecadosDDD,"--"),") ",IFNULL(telRecados,"---"))
                FROM tbservidor JOIN tbpessoa USING (idpessoa)
                JOIN tbhistlot ON (tbservidor.idServidor = tbhistlot.idServidor)
                JOIN tblotacao ON (tbhistlot.lotacao=tblotacao.idLotacao)
               WHERE tbservidor.situacao = 1
                 AND idPerfil <> 10
                 AND tbhistlot.data = (select max(data) from tbhistlot where tbhistlot.idServidor = tbservidor.idServidor)';
    
    if(!is_null($lotacao)){
        # Verifica se o que veio é numérico
        if(is_numeric($lotacao)){
            $select .= ' AND (tblotacao.idlotacao = "'.$lotacao.'")';                
            $subTitulo .= "Lotação: ".$servidor->get_nomeCompletoLotacao($lotacao)."<br/>";
        }else{ # senão é uma diretoria genérica
            $select .= ' AND (tblotacao.DIR = "'.$lotacao.'")';                
            $subTitulo .= "Lotação: ".$lotacao."<br/>";
        }
    }
        
    $select .= ' ORDER BY tbpessoa.nome';

    $result = $servidor->select($select);

    $relatorio = new Relatorio();
    $relatorio->set_titulo('Relatório de Telefones dos Servidores Ativos');
    $relatorio->set_subtitulo($subTitulo.'Ordenados pelo Nome');
    $relatorio->set_label(array('IdFuncional','Nome','Residencial','Celular','Recados'));
    #$relatorio->set_width(array(10,40,50));
    $relatorio->set_align(array("center","left","left","left"));
    
    $relatorio->set_conteudo($result);
    
    $listaLotacao = $servidor->select('(SELECT idlotacao, concat(IFNULL(tblotacao.DIR,"")," - ",IFNULL(tblotacao.GER,"")," - ",IFNULL(tblotacao.nome,"")) lotacao
                                              FROM tblotacao
                                             WHERE ativo) UNION (SELECT distinct DIR, DIR
                                              FROM tblotacao
                                             WHERE ativo)
                                          ORDER BY 2');
    
    array_unshift($listaLotacao,array('*','-- Todos --'));
    
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