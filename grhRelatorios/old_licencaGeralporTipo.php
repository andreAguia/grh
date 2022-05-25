<?php

/**
 * Relatório
 *    
 * By Alat
 */
# Servidor logado 
$idUsuario = null;

# Configuração
include ("../grhSistema/_config.php");

# Permissão de Acesso
$acesso = Verifica::acesso($idUsuario, [1, 2, 12]);

if ($acesso) {

    # Conecta ao Banco de Dados
    $pessoal = new Pessoal();

    # Começa uma nova página
    $page = new Page();
    $page->iniciaPagina();

    # Pega os parâmetros dos relatórios
    $relatorioLicenca = post('licenca', 800);

    ######

    $relatorio = new Relatorio();

    $select = 'SELECT tbservidor.idfuncional,
                      tbpessoa.nome,
                      tbperfil.nome,
                      idServidor,
                      CONCAT(tbtipolicenca.nome,"<br/>",IFnull(tbtipolicenca.lei,"")),
                      tblicenca.dtInicial,
                      tblicenca.numDias,
                      ADDDATE(tblicenca.dtInicial,tblicenca.numDias-1),
                      dtPublicacao,
                      tblicenca.processo,
                      tblicenca.obs,
                      idServidor
                 FROM tbservidor LEFT JOIN tbpessoa USING (idPessoa)
                                 LEFT JOIN tblicenca USING (idServidor)
                                 LEFT JOIN tbtipolicenca USING (idTpLicenca)
                                 LEFT JOIN tbperfil USING (idPerfil)
                WHERE tbtipolicenca.idTpLicenca = ' . $relatorioLicenca . ' 
             ORDER BY tblicenca.dtInicial desc';

    $result = $pessoal->select($select);

    #$nomeLicenca = $pessoal->get_licencaNome($relatorioLicenca);
    #$leiLicenca = $pessoal->get_licencaLei($relatorioLicenca);

    $relatorio->set_titulo('Relatório Geral de Servidores em Licença e/ou Afastamanto');
    #$relatorio->set_tituloLinha2($nomeLicenca);

    $relatorio->set_subtitulo('Ordem Decrescente de Data Inicial da Licença');
    $relatorio->set_label(array('IdFuncional', 'Nome', 'Perfil', 'Lotaçao', 'Licença', 'Data Inicial', 'Dias', 'Data Final', "Publicação", "Processo", "Obs", "Situação"));

    $relatorio->set_classe(array(null, null, null, "pessoal", null, null, null, null, null, null, null, "pessoal"));
    $relatorio->set_metodo(array(null, null, null, "get_LotacaoRel", null, null, null, null, null, null, null, "get_situacao"));

    $relatorio->set_align(array('center', 'left', 'center', 'left', 'left', 'center', 'center', 'center', 'center', 'left', 'left'));
    $relatorio->set_funcao(array(null, null, null, null, null, "date_to_php", null, "date_to_php", "date_to_php"));

    $relatorio->set_conteudo($result);
    $relatorio->set_numGrupo(4);
    $relatorio->set_botaoVoltar(false);

    # Dados da combo licena
    $licenca = $pessoal->select('SELECT idTpLicenca,
                                         CONCAT(tbtipolicenca.nome," ",IFnull(tbtipolicenca.lei,"")) as licenca
                                    FROM tbtipolicenca
                                    WHERE tbtipolicenca.idTpLicenca = 5
                                       OR tbtipolicenca.idTpLicenca = 8
                                       OR tbtipolicenca.idTpLicenca = 16
                                ORDER BY 2');
    array_unshift($licenca, array('800', 'Escolha um tipo de Licença ou Afastamento'));

    $relatorio->set_formCampos(array(
        array('nome' => 'licenca',
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