<?php
/**
 * Sistema GRH
 * 
 * Relatório
 *   
 * By Alat
 */

# Servidor logado 
$idUsuario = null;

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
    $anoBase = post('anoBase',date('Y'));
    
    # Relatório 1
    $select = 'SELECT tbservidor.idFuncional,
                      tbpessoa.nome,
                      SUM(tbferias.numDias),
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
    $relatorio->set_titulo('Total de Férias Fruídas, Confirmadas ou Solicitadas');
    $relatorio->set_tituloLinha2('Exercício: '.$anoBase);
    $relatorio->set_subtitulo('Agrupados por Número de Dias de Férias');
    $relatorio->set_label(array("Id Funcional","Nome","Dias de férias","Lotação"));
    #$relatorio->set_width(array(10,45,10,35));
    $relatorio->set_align(array("center","left",null,"left"));
    $relatorio->set_classe(array(null,null,null,"pessoal"));
    $relatorio->set_metodo(array(null,null,null,"get_lotacao"));
    $relatorio->set_conteudo($result);
    $relatorio->set_numGrupo(2);
    $relatorio->set_dataImpressao(false);

    $relatorio->set_formCampos(array(
                               array ('nome' => 'anoBase',
                                      'label' => 'Ano Base:',
                                      'tipo' => 'texto',
                                      'size' => 4,
                                      'title' => 'Ano',
                                      'onChange' => 'formPadrao.submit();',
                                      'padrao' => $anoBase,
                                      'col' => 3,
                                      'autofocus' => true,
                                      'linha' => 1)));
    $relatorio->set_formLink('?');
    $relatorio->show();

    ### Relatório 2

    $select2 = 'SELECT tbservidor.idFuncional,
                      tbpessoa.nome,
                      "Férias Não Solicitadas",
                      tbservidor.idServidor
                 FROM tbservidor JOIN tbpessoa USING (idpessoa)
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
    $relatorio2->set_label(array("Id Funcional","Nome","Dias de férias","Lotação"));
    $relatorio2->set_width(array(10,45,10,35));
    $relatorio2->set_align(array("center","left",null,"left"));
    $relatorio2->set_classe(array(null,null,null,"pessoal"));
    $relatorio2->set_metodo(array(null,null,null,"get_lotacao"));
    $relatorio2->set_conteudo($result2);
    $relatorio2->set_cabecalhoRelatorio(false);
    $relatorio2->set_menuRelatorio(false);
    $relatorio2->set_numGrupo(2);
    $relatorio2->set_log(false);
    $relatorio2->show();

    $page->terminaPagina();
}
