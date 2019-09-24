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
    
    # Pega o ano exercicio
    $parametroAno = get("parametroAno",date('Y'));
    
    # Pega a lotação
    $parametroLotacao = get("parametroLotacao");
    
    # Transforma em nulo a máscara *
    if($parametroLotacao == "*"){
        $parametroLotacao = NULL;
    }
    
    ######
    
    $select ='SELECT tbservidor.idfuncional,        
                     tbpessoa.nome,
                     tbservidor.idServidor,
                     tbferias.anoExercicio,
                     tbferias.dtInicial,
                     tbferias.numDias,
                     date_format(ADDDATE(tbferias.dtInicial,tbferias.numDias-1),"%d/%m/%Y") as dtf,
                     idFerias,
                     CONCAT(month(tbferias.dtInicial),"/",year(tbferias.dtInicial)),
                     tbsituacao.situacao
                FROM tbservidor LEFT JOIN tbpessoa ON (tbservidor.idPessoa=tbpessoa.idPessoa)
                                     JOIN tbferias ON (tbservidor.idServidor = tbferias.idServidor)
                                     JOIN tbhistlot ON (tbservidor.idServidor = tbhistlot.idServidor)
                                     JOIN tblotacao ON (tbhistlot.lotacao = tblotacao.idLotacao)
                                     JOIN tbsituacao ON (tbservidor.situacao = tbsituacao.idSituacao) 
               WHERE anoExercicio = '.$parametroAno.'
                 AND tbhistlot.data = (select max(data) from tbhistlot where tbhistlot.idServidor = tbservidor.idServidor)';
    
    if(!is_null($parametroLotacao)){
        
        # Verifica se o que veio é numérico
        if(is_numeric($parametroLotacao)){
            $select .= ' AND (tblotacao.idlotacao = "'.$parametroLotacao.'")'; 
        }else{ # senão é uma diretoria genérica
            $select .= ' AND (tblotacao.DIR = "'.$parametroLotacao.'")'; 
        }
        
    }
    
    $select .= ' ORDER BY year(tbferias.dtInicial), month(tbferias.dtInicial), tbferias.dtInicial';
    
    $result = $servidor->select($select);

    $relatorio = new Relatorio();
    $relatorio->set_titulo('Relatório Anual de Férias');
    $relatorio->set_tituloLinha2("Ano Exercício: ".$parametroAno);
    
    if(!is_null($parametroLotacao)){
        $relatorio->set_tituloLinha3($servidor->get_nomeLotacao($parametroLotacao));
    }
    
    $relatorio->set_subtitulo('Agrupados por Mês - Ordenados pela Data Inicial');

    $relatorio->set_label(array('IdFuncional','Nome','Lotação','Exercício','Dt Inicial','Dias','Dt Final','Período','Mês','Situação'));
    #$relatorio->set_width(array(10,30,20,5,9,8,9,10));
    $relatorio->set_align(array("center","left","left"));
    $relatorio->set_funcao(array(NULL,NULL,NULL,NULL,"date_to_php",NULL,NULL,NULL,"acertaDataFerias"));
    $relatorio->set_classe(array(NULL,NULL,"pessoal",NULL,NULL,NULL,NULL,"pessoal"));
    $relatorio->set_metodo(array(NULL,NULL,"get_lotacaoSimples",NULL,NULL,NULL,NULL,"get_feriasPeriodo"));

    $relatorio->set_conteudo($result);
    $relatorio->set_numGrupo(8);
    $relatorio->show();

    $page->terminaPagina();
}
