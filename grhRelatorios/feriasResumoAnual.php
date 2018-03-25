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
    
    # Transforma em nulo a máscara *
    if($lotacaoArea == "*"){
        $lotacaoArea = NULL;
    }
    
    # Relatório 1
    $select = 'SELECT tbservidor.idFuncional,
                      tbpessoa.nome,
                      SUM(tbferias.numDias),
                      tbservidor.idServidor,
                      tbservidor.idServidor
                 FROM tbferias JOIN tbservidor USING (idServidor)
                               JOIN tbpessoa USING (idPessoa)
                               JOIN tbhistlot ON (tbservidor.idServidor = tbhistlot.idServidor)
                               JOIN tblotacao ON (tbhistlot.lotacao=tblotacao.idLotacao)
                WHERE anoExercicio = '.$anoBase.'
                  AND tbhistlot.data = (select max(data) from tbhistlot where tbhistlot.idServidor = tbservidor.idServidor)';
    
    if(!is_null($lotacaoArea)){
        $select .= ' AND tbhistlot.lotacao = '.$lotacaoArea;
    }
    
    $select .= ' GROUP BY 2
                 ORDER BY 3 desc, 2';
    
    /*
    $select = 'SELECT tbservidor.idServidor,
                      tbservidor.idFuncional,
                      tbpessoa.nome,
                      0,
                      tbservidor.idServidor
                 FROM tbservidor JOIN tbpessoa USING (idpessoa)
                WHERE tbservidor.situacao = 1
                  AND tbservidor.idServidor NOT IN(
               SELECT tbservidor.idServidor
                 FROM tbservidor JOIN tbferias ON (tbferias.idServidor = tbservidor.idServidor)
                WHERE tbservidor.situacao = 1                   
                  AND anoExercicio = '.$anoBase.'
                  ORDER BY 3';
    */
    $result = $servidor->select($select);

    $relatorio = new Relatorio();
    $relatorio->set_titulo('Resumo Anual de Férias');
    $relatorio->set_tituloLinha2($anoBase);
    if(!is_null($lotacaoArea)){
        $relatorio->set_tituloLinha3($servidor->get_nomeLotacao($lotacaoArea));
    }
    $relatorio->set_subtitulo('Agrupados por Número de Dias de Férias');
    $relatorio->set_label(array("Id Funcional","Nome","Dias de férias","Cargo","Lotação"));
    $relatorio->set_width(array(10,30,0,30,30));
    $relatorio->set_align(array("center","left",NULL,"left","left"));
    $relatorio->set_classe(array(NULL,NULL,NULL,"pessoal","pessoal"));
    $relatorio->set_metodo(array(NULL,NULL,NULL,"get_cargo","get_lotacaoSimples"));
    $relatorio->set_conteudo($result);
    $relatorio->set_numGrupo(2);
    $relatorio->set_dataImpressao(FALSE);
    $relatorio->show();

    ### Relatório 2

    $select2 =  'SELECT tbservidor.idFuncional,
                        tbpessoa.nome,
                        "Servidores que Não Solicitaram Férias",
                        tbservidor.idServidor,
                        tbservidor.idServidor
                   FROM tbservidor JOIN tbpessoa USING(idpessoa)
                                   JOIN tbhistlot ON (tbservidor.idServidor = tbhistlot.idServidor)
                                   JOIN tblotacao ON (tbhistlot.lotacao=tblotacao.idLotacao)
                  WHERE tbservidor.situacao = 1
                  AND tbhistlot.data = (select max(data) from tbhistlot where tbhistlot.idServidor = tbservidor.idServidor)';
    
    if(!is_null($lotacaoArea)){
        $select2 .= ' AND tbhistlot.lotacao = '.$lotacaoArea;
    }
    
    $select2 .= '
                    AND tbservidor.idServidor NOT IN(
                 SELECT tbservidor.idServidor
                   FROM tbservidor JOIN tbferias ON (tbferias.idServidor = tbservidor.idServidor)
                                   JOIN tbhistlot ON (tbservidor.idServidor = tbhistlot.idServidor)
                                   JOIN tblotacao ON (tbhistlot.lotacao=tblotacao.idLotacao)
                  WHERE tbservidor.situacao = 1                   
                    AND anoExercicio = '.$anoBase.'
                    AND tbhistlot.data = (select max(data) from tbhistlot where tbhistlot.idServidor = tbservidor.idServidor)';
    
    if(!is_null($lotacaoArea)){
        $select2 .= ' AND tbhistlot.lotacao = '.$lotacaoArea;
    }
    
    $select2 .= ')
                    ORDER BY 2';

    $result2 = $servidor->select($select2);

    $relatorio2 = new Relatorio();
    $relatorio2->set_titulo('');
    #$relatorio->set_tituloLinha2('Exercício: '.$anoBase);
    #$relatorio->set_subtitulo('Agrupados por Número de Dias de Férias');
    $relatorio2->set_label(array("Id Funcional","Nome","Dias de férias","Cargo","Lotação"));
    $relatorio2->set_width(array(10,30,0,30,30));
    $relatorio2->set_align(array("center","left",NULL,"left","left"));
    $relatorio2->set_classe(array(NULL,NULL,NULL,"pessoal","pessoal"));
    $relatorio2->set_metodo(array(NULL,NULL,NULL,"get_cargo","get_lotacaoSimples"));
    $relatorio2->set_conteudo($result2);
    $relatorio2->set_cabecalhoRelatorio(FALSE);
    $relatorio2->set_menuRelatorio(FALSE);
    $relatorio2->set_numGrupo(2);
    $relatorio2->set_log(FALSE);
    $relatorio2->show();

    $page->terminaPagina();
}
