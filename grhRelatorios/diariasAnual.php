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

    ######

    # Pega os parâmetros dos relatórios
    $anoBase = post('anoBase',date('Y'));

    $select ='SELECT CONCAT(numeroCi,"/",YEAR(dataCi)),
                                     matricula,
                                     origem,
                                     destino,
                                     dataSaida,
                                     dataChegada,
                                     valor
                            FROM tbdiaria
                            WHERE YEAR(dataSaida) = '.$anoBase.'
                            ORDER BY 1';

    $result = $servidor->select($select);

    $relatorio = new Relatorio();
    $relatorio->set_titulo('Relatório Anual de Diária ');
    $relatorio->set_tituloLinha2('(por data de saída)');
    $relatorio->set_tituloLinha3($anoBase);
    $relatorio->set_subtitulo('Ordenados por Número da CI');

    $relatorio->set_label(array('CI','Servidor','Origem','Destino','Saída','Chegada','Valor'));
    $relatorio->set_width(array(10,25,20,15,10,10,10));
    $relatorio->set_align(array("center","left"));
    
    $relatorio->set_funcao(array(null,null,null,null,"date_to_php","date_to_php","formataMoeda"));
    $relatorio->set_classe(array(null,'Pessoal'));
    $relatorio->set_metodo(array(null,'get_nome')); 

    $relatorio->set_conteudo($result);
    #$relatorio->set_numGrupo(9);
    #$relatorio->set_botaoVoltar('../sistema/areaServidor.php');

    $relatorio->set_formCampos(array(
                               array ('nome' => 'anoBase',
                                      'label' => 'Ano Base:',
                                      'tipo' => 'texto',
                                      'size' => 4,
                                      'title' => 'Ano',
                                      'padrao' => $anoBase,
                                      'onChange' => 'formPadrao.submit();',
                                      'col' => 3,
                                      'linha' => 1)));

    $relatorio->set_formFocus('anoBase');
    $relatorio->set_formLink('?');
    
    $relatorio->set_colunaSomatorio(6);
    $relatorio->set_funcaoSomatorio('formataMoeda');
    $relatorio->set_textoSomatorio('Total das Diárias:');
    $relatorio->set_exibeSomatorioGeral(false);
    
    $relatorio->show();

    $page->terminaPagina();
}
?>
