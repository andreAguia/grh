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
    
    # Pega o ano exercicio quando vem da área de férias
    $anoBase = get("parametroAnoExercicio",date('Y'));
    
    # Pega a lotação quando vem da área de férias
    $lotacaoArea = get("lotacaoArea");
    
    ######
    
    $select ='SELECT tbservidor.idfuncional,        
                     tbpessoa.nome,
                     tbservidor.idServidor,
                     tbferias.anoExercicio,
                     tbferias.dtInicial,
                     tbferias.numDias,
                     idFerias,
                     date_format(ADDDATE(tbferias.dtInicial,tbferias.numDias-1),"%d/%m/%Y")as dtf,
                     tbferias.folha,
                     month(tbferias.dtInicial)
                FROM tbservidor LEFT JOIN tbpessoa ON (tbservidor.idPessoa=tbpessoa.idPessoa)
                                     JOIN tbferias ON (tbservidor.idServidor = tbferias.idServidor)
                                     JOIN tbhistlot ON (tbservidor.idServidor = tbhistlot.idServidor)
                                     JOIN tblotacao ON (tbhistlot.lotacao=tblotacao.idLotacao)
               WHERE tbservidor.situacao = 1
                 AND tbferias.status = "fruída"
                 AND anoExercicio = '.$anoBase.'
                 AND tbhistlot.data = (select max(data) from tbhistlot where tbhistlot.idServidor = tbservidor.idServidor)';
    
    if(!is_null($lotacaoArea)){
        $select .= ' AND tbhistlot.lotacao = '.$lotacaoArea;
    }
    
    $select .= ' ORDER BY month(tbferias.dtInicial), tbferias.dtInicial';

    $result = $servidor->select($select);

    $relatorio = new Relatorio();
    $relatorio->set_titulo('Escala Anual de Férias Fruídas');
    $relatorio->set_tituloLinha2($anoBase);
    
    if(!is_null($lotacaoArea)){
        $relatorio->set_tituloLinha3($servidor->get_nomeLotacao($lotacaoArea));
    }
    
    $relatorio->set_subtitulo('Agrupados por Mês - Ordenados por Data');

    $relatorio->set_label(array('IdFuncional','Nome','Lotação','Ano','Dt Inicial','Dias','Período','Dt Final','Folha','Mês'));
    #$relatorio->set_width(array(10,30,20,5,9,8,9,10));
    $relatorio->set_align(array("center","left","left"));
    $relatorio->set_funcao(array(NULL,NULL,NULL,NULL,"date_to_php",NULL,NULL,NULL,NULL,"get_nomeMes"));
    $relatorio->set_classe(array(NULL,NULL,"pessoal",NULL,NULL,NULL,"pessoal"));
    $relatorio->set_metodo(array(NULL,NULL,"get_lotacaoSimples",NULL,NULL,NULL,"get_feriasPeriodo"));

    $relatorio->set_conteudo($result);
    $relatorio->set_numGrupo(9);
    $relatorio->show();

    $page->terminaPagina();
}
