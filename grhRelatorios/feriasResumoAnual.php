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
    
    # Relatório 1
    $select = 'SELECT tbservidor.idFuncional,
                      tbpessoa.nome,
                      SUM(tbferias.numDias),
                      tbservidor.idServidor,
                      tbservidor.idServidor
                 FROM tbferias JOIN tbservidor USING (idServidor)
                               JOIN tbpessoa USING (idPessoa)
                WHERE tbservidor.situacao = 1                   
                  AND anoExercicio = '.$anoBase.'
                  AND tbferias.status <> "cancelada"
                  GROUP BY 2
                  ORDER BY 3,2';
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
                  AND tbferias.status <> "cancelada")
                  ORDER BY 3';
    */
    $result = $servidor->select($select);

    $relatorio = new Relatorio();
    $relatorio->set_titulo('Resumo Anual de Férias');
    $relatorio->set_tituloLinha2($anoBase);
    $relatorio->set_subtitulo('Agrupados por Número de Dias de Férias');
    $relatorio->set_label(array("Id Funcional","Nome","Dias de férias","Cargo","Lotação"));
    #$relatorio->set_width(array(10,45,10,35));
    $relatorio->set_align(array("center","left",NULL,"left","left"));
    $relatorio->set_classe(array(NULL,NULL,NULL,"pessoal","pessoal"));
    $relatorio->set_metodo(array(NULL,NULL,NULL,"get_cargo","get_lotacaoSimples"));
    $relatorio->set_conteudo($result);
    $relatorio->set_numGrupo(2);
    $relatorio->set_dataImpressao(FALSE);
    $relatorio->show();

    ### Relatório 2

    $select2 = 'SELECT tbservidor.idFuncional,
                      tbpessoa.nome,
                      "Férias Não Solicitadas",
                      tbservidor.idServidor,
                      tbservidor.idServidor
                 FROM tbservidor JOIN tbpessoa USING(idpessoa)
                WHERE tbservidor.situacao = 1
                  AND tbservidor.idServidor NOT IN(
               SELECT tbservidor.idServidor
                 FROM tbservidor JOIN tbferias ON (tbferias.idServidor = tbservidor.idServidor)
                WHERE tbservidor.situacao = 1                   
                  AND anoExercicio = '.$anoBase.'
                  AND tbferias.status <> "cancelada")
                  ORDER BY 2';

    $result2 = $servidor->select($select2);

    $relatorio2 = new Relatorio();
    $relatorio2->set_titulo('');
    #$relatorio->set_tituloLinha2('Exercício: '.$anoBase);
    #$relatorio->set_subtitulo('Agrupados por Número de Dias de Férias');
    $relatorio2->set_label(array("Id Funcional","Nome","Dias de férias","Cargo","Lotação"));
    $relatorio2->set_width(array(10,45,10,35));
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
