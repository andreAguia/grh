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
    $anoBaseRel = post('anoBase',date('Y'));
    
    # Pega o ano exercicio quando vem da área de férias
    $parametroAnoExercicio = get("parametroAnoExercicio");
    
    if(is_null($parametroAnoExercicio)){
        $anoBase = $anoBaseRel;
    }else{
        $anoBase = $parametroAnoExercicio;
    }

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
                                     JOIN tbferias on (tbservidor.idServidor = tbferias.idServidor)
               WHERE tbservidor.situacao = 1
                 AND tbferias.status = "confirmadas"
                 AND year(tbferias.dtInicial) = '.$anoBase.'
            ORDER BY month(tbferias.dtInicial), tbservidor.idServidor';

    $result = $servidor->select($select);

    $relatorio = new Relatorio();
    $relatorio->set_titulo('Escala Anual de Férias Confirmadas');
    $relatorio->set_tituloLinha2($anoBase);
    $relatorio->set_subtitulo('Agrupados por Mês - Ordenados por Matrícula');

    $relatorio->set_label(array('IdFuncional','Nome','Lotação','Ano','Dt Inicial','Dias','Período','Dt Final','Folha','Mês'));
    #$relatorio->set_width(array(10,30,20,5,9,8,9,10));
    $relatorio->set_align(array("center","left","left"));
    $relatorio->set_funcao(array(NULL,NULL,NULL,NULL,"date_to_php",NULL,NULL,NULL,NULL,"get_nomeMes"));
     $relatorio->set_classe(array(NULL,NULL,"pessoal",NULL,NULL,NULL,"pessoal"));
    $relatorio->set_metodo(array(NULL,NULL,"get_lotacao",NULL,NULL,NULL,"get_feriasPeriodo"));

    $relatorio->set_conteudo($result);
    $relatorio->set_numGrupo(9);
    #$relatorio->set_botaoVoltar('../sistema/areaServidor.php');

    if(is_null($parametroAnoExercicio))
    {
    $relatorio->set_formCampos(array(
                               array ('nome' => 'anoBase',
                                      'label' => 'Ano Base:',
                                      'tipo' => 'texto',
                                      'size' => 4,
                                      'title' => 'Ano',
                                      'padrao' => $anoBase,
                                      'col' => 3,
                                      'linha' => 1)));

    $relatorio->set_formFocus('anoBase');
    $relatorio->set_formLink('?');
    }
    $relatorio->show();

    $page->terminaPagina();
}
