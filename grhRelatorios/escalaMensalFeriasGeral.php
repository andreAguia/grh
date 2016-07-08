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
    
    $select ='SELECT tbfuncionario.matricula,
                     tbfuncionario.idfuncional,
                     tbpessoa.nome,
                     CONCAT(substring(tblotacao.UADM,1,3),"-",tblotacao.DIR,"-",tblotacao.GER) lotacao,
                     tbferias.anoExercicio,
                     tbferias.dtInicial,
                     CONCAT(tbferias.numDias,"(",tbferias.periodo,")"),
                     date_format(ADDDATE(tbferias.dtInicial,tbferias.numDias-1),"%d/%m/%Y")as dtf,
                     tbferias.folha,
                     IF(retornoAvisoFerias,"Sim","Não"),
                     status
                FROM tbfuncionario LEFT JOIN tbpessoa ON (tbfuncionario.idPessoa=tbpessoa.idPessoa)
                                        JOIN tbhistlot ON (tbfuncionario.matricula = tbhistlot.matricula)
                                        JOIN tblotacao ON (tbhistlot.lotacao=tblotacao.idLotacao)
                                        JOIN tbferias on (tbfuncionario.matricula = tbferias.matricula)
               WHERE tbfuncionario.sit = 1
                 AND tbhistlot.data = (select max(data) from tbhistlot where tbhistlot.matricula = tbfuncionario.matricula)             
                 AND year(tbferias.dtInicial) = '.$anoBase.'
                 AND month(tbferias.dtInicial)="'.$mesBase.'"
            ORDER BY status, month(tbferias.dtInicial), tbpessoa.nome';

    $result = $servidor->select($select);

    $relatorio = new Relatorio();
    $relatorio->set_titulo('Escala Mensal de Férias Geral');
    $relatorio->set_tituloLinha2(get_nomeMes($mesBase).' / '.$anoBase);
    $relatorio->set_subtitulo('Ordenados pela Nome do Servidor');

    $relatorio->set_label(array('Matrícula','Id','Nome','Lotação','Ano','Dt Inicial','Dias','Dt Final','Folha','Aviso de Férias','Status'));
    $relatorio->set_width(array(5,5,25,20,5,7,7,7,10,10));
    $relatorio->set_align(array("center","center","left"));
    $relatorio->set_funcao(array("dv",null,null,null,null,"date_to_php"));

    $relatorio->set_conteudo($result);
    $relatorio->set_numGrupo(10);
    #$relatorio->set_botaoVoltar('../sistema/areaServidor.php');

    $relatorio->set_formCampos(array(
                               array ('nome' => 'anoBase',
                                      'label' => 'Ano:',
                                      'tipo' => 'texto',
                                      'size' => 4,
                                      'title' => 'Ano',
                                      'padrao' => $anoBase,
                                      'onChange' => 'formPadrao.submit();',
                                      'col' => 3,
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
