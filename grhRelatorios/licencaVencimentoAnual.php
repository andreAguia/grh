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
    $relatorioAno = post('ano',date('Y'));

    ######
    
    $select = '(SELECT tbservidor.idfuncional,
                      tbpessoa.nome,
                      tbperfil.nome,
                      CONCAT(tbtipolicenca.nome,"@",IFNULL(lei,"")),
                      tblicenca.dtInicial,
                      tblicenca.numDias,
                      ADDDATE(tblicenca.dtInicial,tblicenca.numDias-1),
                      MONTH(ADDDATE(tblicenca.dtInicial,tblicenca.numDias-1))
                 FROM tbservidor LEFT JOIN tbpessoa USING (idPessoa)
                                 LEFT JOIN tblicenca USING (idServidor)
                                 LEFT JOIN tbtipolicenca USING (idTpLicenca)
                                 LEFT JOIN tbperfil USING (idPerfil)
                WHERE tbservidor.situacao = 1
                  AND YEAR(ADDDATE(tblicenca.dtInicial,tblicenca.numDias-1)) = '.$relatorioAno.'   
             ORDER BY 7) 
                UNION 
                (SELECT tbservidor.idfuncional,
                      tbpessoa.nome,
                      tbperfil.nome,
                      "Licença Prêmio",
                      tblicencaPremio.dtInicial,
                      tblicencaPremio.numDias,
                      ADDDATE(tblicencaPremio.dtInicial,tblicencaPremio.numDias-1),
                      MONTH(ADDDATE(tblicencaPremio.dtInicial,tblicencaPremio.numDias-1))
                 FROM tbservidor LEFT JOIN tbpessoa USING (idPessoa)
                                 LEFT JOIN tblicencaPremio USING (idServidor)
                                 LEFT JOIN tbperfil USING (idPerfil)
                WHERE tbservidor.situacao = 1
                  AND YEAR(ADDDATE(tblicencaPremio.dtInicial,tblicencaPremio.numDias-1)) = '.$relatorioAno.'   
             ORDER BY 7) ORDER BY 7';

    $result = $pessoal->select($select);

    $relatorio = new Relatorio();
    $relatorio->set_titulo('Relatório Anual de Término de Licença');
    $relatorio->set_tituloLinha2($relatorioAno);
    $relatorio->set_subtitulo('Ordem de Data de Término da Licença');

    $relatorio->set_label(array('IdFuncional','Nome','Perfil','Licença','Data Inicial','Dias','Data Final','Mês'));
    $relatorio->set_width(array(10,30,10,25,10,5,10));
    $relatorio->set_align(array('center','left'));
    $relatorio->set_funcao(array(NULL,NULL,NULL,"exibeLeiLicenca","date_to_php",NULL,"date_to_php","get_nomeMes"));

    $relatorio->set_conteudo($result);
    $relatorio->set_numGrupo(7);
    $relatorio->set_botaoVoltar(FALSE);
    $relatorio->set_formCampos(array(
                  array ('nome' => 'ano',
                         'label' => 'Ano:',
                         'tipo' => 'texto',
                         'size' => 4,
                         'title' => 'Ano',
                         'onChange' => 'formPadrao.submit();',
                         'padrao' => $relatorioAno,
                         'col' => 3,
                         'linha' => 1)));

    $relatorio->set_formFocus('ano');		
    $relatorio->set_formLink('?');
    $relatorio->show();

    $page->terminaPagina();
}