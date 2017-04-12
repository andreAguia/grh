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
    $mesBase = post('mesBase',date('m'));

    ######
    
    $select ='SELECT tbservidor.idfuncional,
                     tbpessoa.nome,
                     CONCAT(tblotacao.UADM,"-",tblotacao.DIR,"-",tblotacao.GER) lotacao,
                     tbferias.anoExercicio,
                     tbferias.dtInicial,
                     CONCAT(tbferias.numDias,"(",tbferias.periodo,")"),
                     date_format(ADDDATE(tbferias.dtInicial,tbferias.numDias-1),"%d/%m/%Y")as dtf,
                     tbferias.folha,
                     status
                FROM tbservidor LEFT JOIN tbpessoa ON (tbservidor.idPessoa=tbpessoa.idPessoa)
                                        JOIN tbhistlot ON (tbservidor.idServidor = tbhistlot.idServidor)
                                        JOIN tblotacao ON (tbhistlot.lotacao=tblotacao.idLotacao)
                                        JOIN tbferias on (tbservidor.idServidor = tbferias.idServidor)
               WHERE tbservidor.situacao = 1
                 AND tbhistlot.data = (select max(data) from tbhistlot where tbhistlot.idServidor = tbservidor.idServidor)             
                 AND year(tbferias.dtInicial) = '.$anoBase.'
                 AND month(tbferias.dtInicial)="'.$mesBase.'"
            ORDER BY 3,tbpessoa.nome';

    $result = $servidor->select($select);

    $relatorio = new Relatorio();
    $relatorio->set_titulo('Escala Mensal de Férias Geral');
    $relatorio->set_tituloLinha2(get_nomeMes($mesBase).' / '.$anoBase);
    $relatorio->set_subtitulo('Agrupados por Lotação e Ordenados pelo Nome do Servidor');



    $relatorio->set_label(array('IdFuncional','Nome','Lotação','Ano','Dt Inicial','Dias','Dt Final','Folha','Status'));
    $relatorio->set_width(array(10,30,20,5,9,8,9,10,10));
    $relatorio->set_align(array("center","left"));
    $relatorio->set_funcao(array(null,null,null,null,"date_to_php"));

    $relatorio->set_conteudo($result);
    $relatorio->set_numGrupo(2);
    #$relatorio->set_botaoVoltar('../sistema/areaServidor.php');

    $relatorio->set_formCampos(array(
                               array ('nome' => 'anoBase',
                                      'label' => 'Ano:',
                                      'tipo' => 'texto',
                                      'size' => 4,
                                      'title' => 'Ano',
                                      'padrao' => $anoBase,
                                      'col' => 3,
                                      'onChange' => 'formPadrao.submit();',
                                      'linha' => 1),
                               array ('nome' => 'mesBase',
                                      'label' => 'Mês:',
                                      'tipo' => 'combo',
                                      'array' => $mes,
                                      'size' => 10,
                                      'padrao' => $mesBase,
                                      'title' => 'Mês',
                                      'onChange' => 'formPadrao.submit();',
                                      'col' => 3,
                                      'linha' => 1)));

    $relatorio->set_formFocus('anoBase');
    $relatorio->set_formLink('?');
    $relatorio->show();

    $page->terminaPagina();
}
