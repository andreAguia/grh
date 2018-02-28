<?php
/**
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
    $pessoal = new Pessoal();

    # Começa uma nova página
    $page = new Page();			
    $page->iniciaPagina();
    
    # Pega os parâmetros dos relatórios
    $relatorioMes = post('mes',date('m'));
    $relatorioAno = post('ano',date('Y'));

    ######

    $data = $relatorioAno.'-'.$relatorioMes.'-01';

    $select = '(SELECT tbservidor.idfuncional,
                      tbpessoa.nome,
                      tbperfil.nome,
                      CONCAT(tbtipolicenca.nome,"<br/>",IFNULL(tbtipolicenca.lei,"")),
                      tblicenca.dtInicial,
                      tblicenca.numDias,
                      ADDDATE(tblicenca.dtInicial,tblicenca.numDias-1)
                 FROM tbservidor LEFT JOIN tbpessoa USING (idPessoa)
                                 LEFT JOIN tblicenca USING (idServidor)
                                 LEFT JOIN tbtipolicenca USING (idTpLicenca)
                                 LEFT JOIN tbperfil USING (idPerfil)
                WHERE tbservidor.situacao = 1
                  AND (("'.$data.'" BETWEEN dtInicial AND ADDDATE(tblicenca.dtInicial,tblicenca.numDias-1))
                   OR  (LAST_DAY("'.$data.'") BETWEEN dtInicial AND ADDDATE(tblicenca.dtInicial,tblicenca.numDias-1))
                   OR  ("'.$data.'" < dtInicial AND LAST_DAY("'.$data.'") > ADDDATE(tblicenca.dtInicial,tblicenca.numDias-1)))
             ORDER BY tblicenca.dtInicial)
             UNION
             (SELECT tbservidor.idfuncional,
                     tbpessoa.nome,
                     tbperfil.nome,
                     CONCAT(tbtipolicenca.nome,"<br/>",IFNULL(tbtipolicenca.lei,"")),
                     tblicencaPremio.dtInicial,
                     tblicencaPremio.numDias,
                     ADDDATE(tblicencaPremio.dtInicial,tblicencaPremio.numDias-1)
                FROM tbTipoLicenca,tbservidor LEFT JOIN tbpessoa USING (idPessoa)
                                LEFT JOIN tblicencaPremio USING (idServidor)
                                LEFT JOIN tbperfil USING (idPerfil)
                WHERE tbtipolicenca.idTpLicenca = 6 AND tbservidor.situacao = 1
                  AND (("'.$data.'" BETWEEN tblicencaPremio.dtInicial AND ADDDATE(tblicencaPremio.dtInicial,tblicencaPremio.numDias-1))
                   OR  (LAST_DAY("'.$data.'") BETWEEN tblicencaPremio.dtInicial AND ADDDATE(tblicencaPremio.dtInicial,tblicencaPremio.numDias-1))
                   OR  ("'.$data.'" < tblicencaPremio.dtInicial AND LAST_DAY("'.$data.'") > ADDDATE(tblicencaPremio.dtInicial,tblicencaPremio.numDias-1)))
             ORDER BY tblicencaPremio.dtInicial) ORDER BY 5';

    $result = $pessoal->select($select);

    $relatorio = new Relatorio();
    $relatorio->set_titulo('Relatório Mensal de Servidores em Licença');
    $relatorio->set_tituloLinha2(get_nomeMes($relatorioMes).' / '.$relatorioAno);
    $relatorio->set_subtitulo('Ordem Decrescente de Data Inicial da Licença');

    $relatorio->set_label(array('IdFuncional','Nome','Perfil','Licença','Data Inicial','Dias','Data Final'));
    $relatorio->set_width(array(10,30,10,25,10,5,10));
    $relatorio->set_align(array('center','left'));
    $relatorio->set_funcao(array(NULL,NULL,NULL,NULL,"date_to_php",NULL,"date_to_php"));

    $relatorio->set_conteudo($result);
    #$relatorio->set_numGrupo(2);
    $relatorio->set_botaoVoltar(FALSE);
    
    #$relatorio->set_bordaInterna(TRUE);
    #$relatorio->set_cabecalho(FALSE);
    $relatorio->set_formCampos(array(
                  array ('nome' => 'ano',
                         'label' => 'Ano:',
                         'tipo' => 'texto',
                         'size' => 4,
                         'title' => 'Ano',
                         'col' => 3,
                         'padrao' => $relatorioAno,
                         'onChange' => 'formPadrao.submit();',
                         'linha' => 1), 
                  array ('nome' => 'mes',
                         'label' => 'Mês',
                         'tipo' => 'combo',
                         'array' => $mes,
                         'col' => 3,
                         'size' => 10,
                         'padrao' => $relatorioMes,
                         'title' => 'Mês do Ano.',
                         'onChange' => 'formPadrao.submit();',
                         'linha' => 1)));

    $relatorio->set_formFocus('ano');		
    $relatorio->set_formLink('?');
    $relatorio->show();

    $page->terminaPagina();
}