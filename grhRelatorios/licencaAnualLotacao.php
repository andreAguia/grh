<?php
/**
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
    
    # Pega os parâmetros dos relatórios
    $relatorioAno = post('ano',date('Y'));
    $relatorioLotacao = post('lotacao');
    
    if($relatorioLotacao == "*"){
        $relatorioLotacao = NULL;
    }

    ######
    
    $relatorio = new Relatorio();

    $select = '(SELECT tbservidor.idfuncional,
                      tbpessoa.nome,
                      tbperfil.nome,
                      concat(IFNULL(tblotacao.UADM,"")," - ",IFNULL(tblotacao.DIR,"")," - ",IFNULL(tblotacao.GER,"")) lotacao,
                      CONCAT(tbtipolicenca.nome," ",IFNULL(tbtipolicenca.lei,"")),
                      tblicenca.dtInicial,
                      tblicenca.numDias,
                      ADDDATE(tblicenca.dtInicial,tblicenca.numDias-1)
                 FROM tbservidor LEFT JOIN tbpessoa USING (idPessoa)
                                      JOIN tbhistlot USING (idServidor)
                                      JOIN tblotacao ON (tbhistlot.lotacao=tblotacao.idLotacao)
                                 LEFT JOIN tblicenca USING (idServidor)
                                 LEFT JOIN tbtipolicenca USING (idTpLicenca)
                                 LEFT JOIN tbperfil USING (idPerfil)
                WHERE tbservidor.situacao = 1
                  AND tbhistlot.data = (select max(data) from tbhistlot where tbhistlot.idServidor = tbservidor.idServidor)
                  AND (((YEAR(tblicenca.dtInicial) =  '.$relatorioAno.') OR (YEAR(ADDDATE(tblicenca.dtInicial,tblicenca.numDias-1)) = '.$relatorioAno.')) 
                      OR ((YEAR(tblicenca.dtInicial) <  '.$relatorioAno.') AND (YEAR(ADDDATE(tblicenca.dtInicial,tblicenca.numDias-1)) > '.$relatorioAno.')))';
    # lotacao
    if(!is_null($relatorioLotacao)){
        # Verifica se o que veio é numérico
        if(is_numeric($relatorioLotacao)){
            $select .= ' AND (tblotacao.idlotacao = "'.$relatorioLotacao.'")';                
            $relatorio->set_tituloLinha3($pessoal->get_nomeLotacao($relatorioLotacao)." - ".$pessoal->get_nomeCompletoLotacao($relatorioLotacao));
        }else{ # senão é uma diretoria genérica
            $select .= ' AND (tblotacao.DIR = "'.$relatorioLotacao.'")';                
            $relatorio->set_tituloLinha3($relatorioLotacao);
        }
    }
    
    $select .= 'ORDER BY tblicenca.dtInicial)
             UNION
             (SELECT tbservidor.idfuncional,
                     tbpessoa.nome,
                     tbperfil.nome,
                     concat(IFNULL(tblotacao.UADM,"")," - ",IFNULL(tblotacao.DIR,"")," - ",IFNULL(tblotacao.GER,"")) lotacao,
                     (SELECT CONCAT(tbtipolicenca.nome," ",IFNULL(tbtipolicenca.lei,"")) FROM tbtipolicenca WHERE idTpLicenca = 6),
                     tblicencapremio.dtInicial,
                     tblicencapremio.numDias,
                     ADDDATE(tblicencapremio.dtInicial,tblicencapremio.numDias-1)
                FROM tbtipolicenca,tbservidor LEFT JOIN tbpessoa USING (idPessoa)
                                                   JOIN tbhistlot USING (idServidor)
                                                   JOIN tblotacao ON (tbhistlot.lotacao=tblotacao.idLotacao)
                                              LEFT JOIN tblicencapremio USING (idServidor)
                                              LEFT JOIN tbperfil USING (idPerfil)
                WHERE tbtipolicenca.idTpLicenca = 6 AND tbservidor.situacao = 1
                  AND tbhistlot.data = (select max(data) from tbhistlot where tbhistlot.idServidor = tbservidor.idServidor)
                  AND (((YEAR(tblicencapremio.dtInicial) =  '.$relatorioAno.') OR (YEAR(ADDDATE(tblicencapremio.dtInicial,tblicencapremio.numDias-1)) = '.$relatorioAno.'))
                      OR ((YEAR(tblicencapremio.dtInicial) <  '.$relatorioAno.') AND (YEAR(ADDDATE(tblicencapremio.dtInicial,tblicencapremio.numDias-1)) > '.$relatorioAno.')))';
    
    # lotacao
    if(!is_null($relatorioLotacao)){
        # Verifica se o que veio é numérico
        if(is_numeric($relatorioLotacao)){
            $select .= ' AND (tblotacao.idlotacao = "'.$relatorioLotacao.'")';
        }else{ # senão é uma diretoria genérica
            $select .= ' AND (tblotacao.DIR = "'.$relatorioLotacao.'")';
        }
    }
    
    $select .= 'ORDER BY tblicencapremio.dtInicial) ORDER BY 6';

    $result = $pessoal->select($select);
    
    $relatorio->set_titulo('Relatório Anual de Servidores em Licença e/ou Afastamanto');
    $relatorio->set_tituloLinha2($relatorioAno);
    $relatorio->set_subtitulo('Ordem Decrescente de Data Inicial da Licença');

    $relatorio->set_label(array('IdFuncional','Nome','Perfil','Lotaçao','Licença','Data Inicial','Dias','Data Final'));
    #$relatorio->set_width(array(10,30,10,25,10,5,10));
    $relatorio->set_align(array('center','left','center','left','left'));
    $relatorio->set_funcao(array(NULL,NULL,NULL,NULL,NULL,"date_to_php",NULL,"date_to_php"));

    $relatorio->set_conteudo($result);
    #$relatorio->set_numGrupo(2);
    $relatorio->set_botaoVoltar(FALSE);
    
    # Dados da combo lotacao
    $lotacao = $pessoal->select('(SELECT idlotacao, concat(IFNULL(tblotacao.DIR,"")," - ",IFNULL(tblotacao.GER,"")," - ",IFNULL(tblotacao.nome,"")) lotacao
                                  FROM tblotacao
                                 WHERE ativo) UNION (SELECT distinct DIR, DIR
                                  FROM tblotacao
                                 WHERE ativo)
                              ORDER BY 2');
    array_unshift($lotacao,array('*','-- Todos --'));
    
    #$relatorio->set_bordaInterna(TRUE);
    #$relatorio->set_cabecalho(FALSE);
    $relatorio->set_formCampos(array(
                  array ('nome' => 'ano',
                         'label' => 'Ano:',
                         'tipo' => 'texto',
                         'size' => 4,
                         'title' => 'Ano',
                         'col' => 3,
                         'padrao' => $relatorioAno,
                         'onChange' => 'formPadrao.submit();',
                         'linha' => 1),
                  array ('nome' => 'lotacao',
                         'label' => 'Lotação',
                         'tipo' => 'combo',
                         'array' => $lotacao,
                         'col' => 9,
                         'size' => 50,
                         'padrao' => $relatorioLotacao,
                         'title' => 'Filtra por Lotação.',
                         'onChange' => 'formPadrao.submit();',
                         'linha' => 1)));

    $relatorio->set_formFocus('ano');		
    $relatorio->set_formLink('?');
    $relatorio->show();

    $page->terminaPagina();
}