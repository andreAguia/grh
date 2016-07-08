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
                     month(tbferias.dtInicial)
                FROM tbfuncionario LEFT JOIN tbpessoa ON (tbfuncionario.idPessoa=tbpessoa.idPessoa)
                                        JOIN tbhistlot ON (tbfuncionario.matricula = tbhistlot.matricula)
                                        JOIN tblotacao ON (tbhistlot.lotacao=tblotacao.idLotacao)
                                        JOIN tbferias on (tbfuncionario.matricula = tbferias.matricula)
               WHERE tbfuncionario.sit = 1
                 AND tbhistlot.data = (select max(data) from tbhistlot where tbhistlot.matricula = tbfuncionario.matricula)
                 AND tbferias.status = "solicitada"
                 AND year(tbferias.dtInicial) = '.$anoBase.'
            ORDER BY month(tbferias.dtInicial), tbfuncionario.matricula';

    $result = $servidor->select($select);

    $relatorio = new Relatorio();
    $relatorio->set_titulo('Escala Anual de Férias Solicitadas');
    $relatorio->set_tituloLinha2($anoBase);
    $relatorio->set_subtitulo('Agrupados por Mês - Ordenados por Matrícula');

    $relatorio->set_label(array('Matrícula','Id','Nome','Lotação','Ano','Dt Inicial','Dias','Dt Final','Folha','Mês'));
    $relatorio->set_width(array(5,5,30,20,5,9,8,9,10));
    $relatorio->set_align(array("center","center","left"));
    $relatorio->set_funcao(array("dv",null,null,null,null,"date_to_php",null,null,null,"get_nomeMes"));

    $relatorio->set_conteudo($result);
    $relatorio->set_numGrupo(9);
    #$relatorio->set_botaoVoltar('../sistema/areaServidor.php');

    $relatorio->set_formCampos(array(
                               array ('nome' => 'anoBase',
                                      'label' => 'Ano Base:',
                                      'tipo' => 'texto',
                                      'size' => 4,
                                      'col' => 3,
                                      'title' => 'Ano',
                                      'padrao' => $anoBase,
                                      'onChange' => 'formPadrao.submit();',
                                      'linha' => 1)));

    $relatorio->set_formFocus('anoBase');
    $relatorio->set_formLink('?');
    $relatorio->show();
    
    $page->terminaPagina();
}
?>
