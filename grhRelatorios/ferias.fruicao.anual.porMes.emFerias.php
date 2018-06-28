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
    
    # Pega o ano exercicio
    $parametroAno = post("parametroAno",date('Y'));
    
    ######
    
    $select ="SELECT tbservidor.idfuncional,        
                     tbpessoa.nome,
                     tbservidor.idServidor,
                     tbferias.anoExercicio,
                     tbferias.dtInicial,
                     tbferias.numDias,
                     date_format(ADDDATE(tbferias.dtInicial,tbferias.numDias-1),'%d/%m/%Y') as dtf,
                     idFerias,
                     CONCAT(month(dtInicial),'/',year(dtInicial)) as tt,
                     tbsituacao.situacao
                FROM tbservidor LEFT JOIN tbpessoa ON (tbservidor.idPessoa=tbpessoa.idPessoa)
                                     JOIN tbferias ON (tbservidor.idServidor = tbferias.idServidor)
                                     JOIN tbhistlot ON (tbservidor.idServidor = tbhistlot.idServidor)
                                     JOIN tblotacao ON (tbhistlot.lotacao=tblotacao.idLotacao)
                                     JOIN tbsituacao ON (tbservidor.situacao = tbsituacao.idSituacao) 
               WHERE ((YEAR(tbferias.dtInicial) = $parametroAno)
                   OR (YEAR(ADDDATE(tbferias.dtInicial,tbferias.numDias-1)) = $parametroAno)
                   OR (YEAR(tbferias.dtInicial) < $parametroAno AND YEAR(ADDDATE(tbferias.dtInicial,tbferias.numDias-1)) > $parametroAno))
                 AND tbhistlot.data = (select max(data) from tbhistlot where tbhistlot.idServidor = tbservidor.idServidor)
            ORDER BY tbferias.dtInicial";
    
    $result = $servidor->select($select);

    $relatorio = new Relatorio();
    $relatorio->set_titulo('Servidores em Férias por Ano de Fruição');
    $relatorio->set_tituloLinha2($parametroAno);    
    $relatorio->set_subtitulo('Agrupados por Mês da data Inicial - Ordenados pela Data Inicial');
    $relatorio->set_label(array('IdFuncional','Nome','Lotação','Exercício','Dt Inicial','Dias','Dt Final','Período','Mês','Situação'));
    $relatorio->set_width(array(10,30,20,5,9,8,9,10));
    $relatorio->set_align(array("center","left","left"));
    $relatorio->set_funcao(array(NULL,NULL,NULL,NULL,"date_to_php",NULL,NULL,NULL,"get_nomeMesAno"));
    $relatorio->set_classe(array(NULL,NULL,"pessoal",NULL,NULL,NULL,NULL,"pessoal"));
    $relatorio->set_metodo(array(NULL,NULL,"get_lotacaoSimples",NULL,NULL,NULL,NULL,"get_feriasPeriodo"));

    $relatorio->set_conteudo($result);
    $relatorio->set_numGrupo(8);
    
    $relatorio->set_formCampos(array(
                               array ('nome' => 'parametroAno',
                                      'label' => 'Mês:',
                                      'tipo' => 'texto',
                                      'size' => 10,
                                      'padrao' => $parametroAno,
                                      'title' => 'Ano',
                                      'onChange' => 'formPadrao.submit();',
                                      'col' => 3,
                                      'linha' => 1)));

    $relatorio->set_formFocus('mesBase');
    $relatorio->set_formLink('?');    
    $relatorio->show();

    $page->terminaPagina();
}
