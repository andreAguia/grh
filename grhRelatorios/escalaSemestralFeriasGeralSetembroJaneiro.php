<?php
/**
 * Sistema GRH
 * 
 * Relatório
 *   
 * By Alat
 */

# Inicia as variáveis que receberão as sessions
$matricula = null;		  # Reservado para a matrícula do servidor logado

# Configuração
include ("../grhSistema/_config.php");

# Permissão de Acesso
$acesso = Verifica::acesso($matricula,13);

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
                     tbferias.folha
                FROM tbfuncionario LEFT JOIN tbpessoa ON (tbfuncionario.idPessoa=tbpessoa.idPessoa)
                                        JOIN tbhistlot ON (tbfuncionario.matricula = tbhistlot.matricula)
                                        JOIN tblotacao ON (tbhistlot.lotacao=tblotacao.idLotacao)
                                        JOIN tbferias on (tbfuncionario.matricula = tbferias.matricula)
               WHERE tbfuncionario.sit = 1
                 AND tbhistlot.data = (select max(data) from tbhistlot where tbhistlot.matricula = tbfuncionario.matricula)             
                 AND year(tbferias.dtInicial) = '.$anoBase.'
                 AND (status = "solicitada" OR status = "confirmada") 
                 AND (month(tbferias.dtInicial)= 9 
                  OR month(tbferias.dtInicial)= 10
                  OR month(tbferias.dtInicial)= 11
                  OR month(tbferias.dtInicial)= 12
                  OR (month(tbferias.dtInicial)= 1) AND(year(tbferias.dtInicial) = '.($anoBase+1).'))             
            ORDER BY tbferias.dtInicial';

    $result = $servidor->select($select);

    $relatorio = new Relatorio();
    $relatorio->set_titulo('Escala Semestral de Férias');
    $relatorio->set_tituloLinha2('Setembro / '.$anoBase.' a Janeiro / '.($anoBase+1));
    $relatorio->set_subtitulo('Confirmadas e Solicitadas - Ordenados pela Data Inicial');

    $relatorio->set_label(array('Matrícula','Id','Nome','Lotação','Ano','Dt Inicial','Dias','Dt Final','Folha'));
    $relatorio->set_width(array(5,5,30,20,5,9,8,9,10));
    $relatorio->set_align(array("center","center","left","left"));
    $relatorio->set_funcao(array("dv",null,null,null,null,"date_to_php"));

    $relatorio->set_conteudo($result);
    #$relatorio->set_numGrupo(8);
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
                                      'linha' => 1)));

    $relatorio->set_formFocus('anoBase');
    $relatorio->set_formLink('?');
    $relatorio->show();

    $page->terminaPagina();
}
?>
