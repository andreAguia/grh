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
    
    # Pega os parâmetros
    $parametroAno = get_session('parametroAno',date("Y"));
    $parametroLotacao = get_session('parametroLotacao');
    $parametroStatus = get_session('parametroStatus');
    
    # Transforma em nulo a máscara *
    if($parametroLotacao == "*"){
        $parametroLotacao = NULL;
    }
    
    # Transforma em nulo a máscara *
    if($parametroStatus == "Todos"){
        $parametroStatus = NULL;
    }
    
    ######
    
    $select ="SELECT tbservidor.idfuncional,        
                     tbpessoa.nome,
                     concat(IFNULL(tblotacao.DIR,''),' - ',IFNULL(tblotacao.GER,''),' - ',IFNULL(tblotacao.nome,'')) lotacao,
                     tbferias.anoExercicio,
                     tbferias.dtInicial,
                     tbferias.numDias,
                     date_format(ADDDATE(tbferias.dtInicial,tbferias.numDias-1),'%d/%m/%Y') as dtf,
                     idFerias,
                     tbsituacao.situacao
                FROM tbservidor LEFT JOIN tbpessoa ON (tbservidor.idPessoa=tbpessoa.idPessoa)
                                     JOIN tbferias ON (tbservidor.idServidor = tbferias.idServidor)
                                     JOIN tbhistlot ON (tbservidor.idServidor = tbhistlot.idServidor)
                                     JOIN tblotacao ON (tbhistlot.lotacao=tblotacao.idLotacao)
                                     JOIN tbsituacao ON (tbservidor.situacao = tbsituacao.idSituacao) 
               WHERE YEAR(tbferias.dtInicial) = $parametroAno
                 AND tbhistlot.data = (select max(data) from tbhistlot where tbhistlot.idServidor = tbservidor.idServidor)";
    
    # Lotação
    if(($parametroLotacao <> "*") AND ($parametroLotacao <> "")){
        $select .= ' AND (tblotacao.idlotacao = "'.$parametroLotacao.'")';
    }

    # Status
    if(($parametroStatus <> "Todos") AND ($parametroStatus <> "")){
        $select .= ' AND (tbferias.status = "'.$parametroStatus.'")';
    }
    
    $select .= " ORDER BY lotacao, tbferias.dtInicial";
    
    $result = $servidor->select($select);
    
    $relatorio = new Relatorio();
    
    # Status no subtítulo
    if(!is_null($parametroStatus)){
        $relatorio->set_tituloLinha3('Ferias '.plm($parametroStatus).'s');
    }
    
    $relatorio->set_titulo('Relatório Anual de Férias');
    $relatorio->set_tituloLinha2($parametroAno);    
    $relatorio->set_subtitulo('Agrupados por Lotação - Ordenados pela Data Inicial');

    $relatorio->set_label(array('IdFuncional','Nome','Lotação','Exercício','Dt Inicial','Dias','Dt Final','Período','Situação'));
    #$relatorio->set_width(array(10,30,20,5,9,8,9,10));
    $relatorio->set_align(array("center","left","left"));
    $relatorio->set_funcao(array(NULL,NULL,NULL,NULL,"date_to_php"));
    $relatorio->set_classe(array(NULL,NULL,NULL,NULL,NULL,NULL,NULL,"pessoal"));
    $relatorio->set_metodo(array(NULL,NULL,NULL,NULL,NULL,NULL,NULL,"get_feriasPeriodo"));

    $relatorio->set_conteudo($result);
    $relatorio->set_numGrupo(2);
    $relatorio->show();

    $page->terminaPagina();
}
