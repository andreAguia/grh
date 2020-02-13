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
    $relatorioLicenca = post('licenca',800);

    ######
    
    $relatorio = new Relatorio();
    
    if($relatorioLicenca <> 6){

        $select = 'SELECT tbservidor.idfuncional,
                      tbpessoa.nome,
                      tbperfil.nome,
                      idServidor,
                      CONCAT(tbtipolicenca.nome," ",IFNULL(tbtipolicenca.lei,"")),
                      tblicenca.dtInicial,
                      tblicenca.numDias,
                      ADDDATE(tblicenca.dtInicial,tblicenca.numDias-1),
                      idServidor
                 FROM tbservidor LEFT JOIN tbpessoa USING (idPessoa)
                                 LEFT JOIN tblicenca USING (idServidor)
                                 LEFT JOIN tbtipolicenca USING (idTpLicenca)
                                 LEFT JOIN tbperfil USING (idPerfil)
                WHERE tbtipolicenca.idTpLicenca = '.$relatorioLicenca.' 
             ORDER BY tblicenca.dtInicial';
    }else{
        $select = 'SELECT tbservidor.idfuncional,
                     tbpessoa.nome,
                     tbperfil.nome,
                     idServidor,
                     (SELECT CONCAT(tbtipolicenca.nome," ",IFNULL(tbtipolicenca.lei,"")) FROM tbtipolicenca WHERE idTpLicenca = 6),
                     tblicencapremio.dtInicial,
                     tblicencapremio.numDias,
                     ADDDATE(tblicencapremio.dtInicial,tblicencapremio.numDias-1),
                     idServidor
                FROM tbtipolicenca,tbservidor LEFT JOIN tbpessoa USING (idPessoa)
                                              LEFT JOIN tblicencapremio USING (idServidor)
                                              LEFT JOIN tbperfil USING (idPerfil)
                WHERE tbtipolicenca.idTpLicenca = 6
                 ORDER BY tblicencapremio.dtInicial';
    }

    $result = $pessoal->select($select);
    
    #$nomeLicenca = $pessoal->get_licencaNome($relatorioLicenca);
    #$leiLicenca = $pessoal->get_licencaLei($relatorioLicenca);
    
    $relatorio->set_titulo('Relatório Geral de Servidores em Licença e/ou Afastamanto');
    #$relatorio->set_tituloLinha2($nomeLicenca);
    
    $relatorio->set_subtitulo('Ordem Decrescente de Data Inicial da Licença');
    $relatorio->set_label(array('IdFuncional','Nome','Perfil','Lotaçao','Licença','Data Inicial','Dias','Data Final',"Situação"));
    
    $relatorio->set_classe(array(NULL,NULL,NULL,"pessoal",NULL,NULL,NULL,NULL,"pessoal"));
    $relatorio->set_metodo(array(NULL,NULL,NULL,"get_LotacaoRel",NULL,NULL,NULL,NULL,"get_situacao"));    
    
    $relatorio->set_align(array('center','left','center','left','left'));
    $relatorio->set_funcao(array(NULL,NULL,NULL,NULL,NULL,"date_to_php",NULL,"date_to_php"));

    $relatorio->set_conteudo($result);
    $relatorio->set_numGrupo(4);
    $relatorio->set_botaoVoltar(FALSE);
    
    # Dados da combo licena
    $licenca = $pessoal->select('SELECT idTpLicenca,
                                         CONCAT(tbtipolicenca.nome," ",IFNULL(tbtipolicenca.lei,"")) as licenca
                                    FROM tbtipolicenca
                                ORDER BY 2');
    array_unshift($licenca,array('800','Escolha um tipo de Licença ou Afastamento'));
    
    $relatorio->set_formCampos(array(
                  array ('nome' => 'licenca',
                         'label' => 'Licença/Afastamento',
                         'tipo' => 'combo',
                         'array' => $licenca,
                         'col' => 12,
                         'size' => 50,
                         'padrao' => $relatorioLicenca,
                         'title' => 'Filtra por Licenca ou Afastamento.',
                         'onChange' => 'formPadrao.submit();',
                         'linha' => 1)));

    $relatorio->set_formFocus('ano');		
    $relatorio->set_formLink('?');
    $relatorio->show();

    $page->terminaPagina();
}