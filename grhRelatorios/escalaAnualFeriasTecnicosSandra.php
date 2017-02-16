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
    $lotacao = post('lotacao',66);

    ######
    
    $select ='SELECT tbservidor.idFuncional,
                     tbpessoa.nome,
                     tbservidor.dtAdmissao,
                     "01/01/2017",
                     "31/12/2017",
                     "_____/_____/2017",
                     "_________________________________",
                     concat(IFNULL(tblotacao.UADM,"")," - ",IFNULL(tblotacao.DIR,"")," - ",IFNULL(tblotacao.GER,"")," - ",IFNULL(tblotacao.nome,"")) lotacao
                FROM tbservidor LEFT JOIN tbpessoa ON (tbservidor.idPessoa = tbpessoa.idPessoa)
                                LEFT JOIN tbhistlot ON (tbservidor.idServidor = tbhistlot.idServidor)
                                LEFT JOIN tblotacao ON (tbhistlot.lotacao=tblotacao.idLotacao)
               WHERE tbservidor.situacao = 1
                 AND idPerfil = 1
                 AND tbhistlot.data = (select max(data) from tbhistlot where tbhistlot.idServidor = tbservidor.idServidor)
                 AND tblotacao.idLotacao = '.$lotacao.'
            ORDER BY 8, tbpessoa.nome';


    $result = $servidor->select($select);
    $numReg = $servidor->count($select);
    
    $relatorio = new Relatorio();
    $relatorio->set_titulo('Escala Anual de Férias dos Tecnicos Estatutários');
    $relatorio->set_subtitulo('Janeiro - Dezembro 2017');
    $relatorio->set_label(array('IdFuncional','Nome','Admissão','Início do Prazo para o Gozo','Ultimo Prazo para o Gozo','Início Previsto do Gozo','Observação','Lotação'));
    $relatorio->set_width(array(5,30,10,10,10,15,20,0));
    $relatorio->set_align(array("center","left"));
    $relatorio->set_funcao(array(null,null,"date_to_php"));
    $relatorio->set_numGrupo(7);
    
    $relatorio->set_conteudo($result);
    $relatorio->set_dataImpressao(false);
    $relatorio->set_totalRegistro(false);
    $relatorio->set_subTotal(false);    
    
    switch ($numReg){
        case ($numReg <= 5):
            $relatorio->set_espacamento(10);
            break;
        
        case 6:
            $relatorio->set_espacamento(8);
            break; 
        
        case 7:
            $relatorio->set_espacamento(6);
            break; 
        
        case 8:
            $relatorio->set_espacamento(4);
            break; 
        
        case 9:
            $relatorio->set_espacamento(2);
            break; 
        
        case ($numReg > 9):
            $relatorio->set_espacamento(1);
            break; 
    }
    
    # Pega os dados da combo lotacao
    $selectLot = $servidor->select('SELECT distinct idlotacao,
                                concat(IFNULL(tblotacao.UADM,"")," - ",IFNULL(tblotacao.DIR,"")," - ",IFNULL(tblotacao.GER,"")," - ",IFNULL(tblotacao.nome,"")) lotacao
                           FROM tbservidor LEFT JOIN tbpessoa ON (tbservidor.idPessoa = tbpessoa.idPessoa)
                                           LEFT JOIN tbhistlot ON (tbservidor.idServidor = tbhistlot.idServidor)
                                           LEFT JOIN tblotacao ON (tbhistlot.lotacao=tblotacao.idLotacao)
                          WHERE tbservidor.situacao = 1
                            AND idPerfil = 1
                            AND tbhistlot.data = (select max(data) from tbhistlot where tbhistlot.idServidor = tbservidor.idServidor)
                       ORDER BY 2');
    
    $relatorio->set_formCampos(array(
                               array ('nome' => 'lotacao',
                                      'label' => 'Lotação:',
                                      'tipo' => 'combo',
                                      'array' => $selectLot,
                                      'col' => 12,
                                      'size' => 50,
                                      'padrao' => $lotacao,
                                      'title' => 'Mês',
                                      'onChange' => 'formPadrao.submit();',
                                      'linha' => 1)));

    $relatorio->set_formFocus('anoBase');
    $relatorio->set_formLink('?');
    $relatorio->show();
    
    # Rodapé
    $texto1 = "1. Esta Escala tem como base os meses de Janeiro a Dezembro/2017, portanto todos os servidores com direito a férias deverão efetuar a marcação referente ao exercício 2017, conforme Art. 90 §1º a §8ºdo Estatuto dos Funcionários Públicos Civis do Est. do Rio de Janeiro.<br/>"
            . "2. Deverá ser devolvida à Gerência de Recursos Humanos - GRH, após o respectivo preenchimento da coluna 'início previsto do gozo', até o dia 31 de OUTUBRO/2016, a fim de que esta acompanhe a concessão das férias e faça a atualização constante dos empregados desligados.";
    $texto2 = "3. Esta escala deverá ter a ciência do chefe imediato, e na impossibilidade do mesmo, deverá ser assinada pelo chefe superior.<br/>"
            . "4. Eventuais alterações na data de concessão deverão ser comunicadas à Gerência de Recursos Humanos, com antecedência mínima de 60 (sessenta) dias a contar da data de início das férias.<br/>"
            . "5. Será encaminhado posteriormente cópia da referida Escala de Férias a cada setor, a fim de que cada responsável pela área de trabalho acompanhe as respectivas datas de início de férias.";
    
    if($numReg < 6){
        br();
    }
    
    $grid = new Grid();
    $grid->abreColuna(12);
        p("Instruções para o Preenchimento e Rotina","center","f14");
    $grid->fechaColuna();
    $grid->fechaGrid();
    
    $grid = new Grid();
    $grid->abreColuna(6);
        p($texto1,"f11");
    $grid->fechaColuna();
    $grid->abreColuna(6);
        p($texto2,"f11");
    $grid->fechaColuna();
    $grid->fechaGrid();
    
    if($numReg < 8){
        br();
    }
    
    $grid = new Grid();
    $grid->abreColuna(4);
        p("Data: ______/_______/_______","center","f12");
    $grid->fechaColuna();
    $grid->abreColuna(4);
        p("Local: ________________________","center","f12");
    $grid->fechaColuna();
    $grid->abreColuna(4);
        p("_______________________________________<br/>Assinatura e Carimbo do Chefe Imediato","center","f12");
    $grid->fechaColuna();
    $grid->fechaGrid();
    
    $page->terminaPagina();
}