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
$acesso = Verifica::acesso($idUsuario, [1, 2, 12]);

if ($acesso) {
    # Conecta ao Banco de Dados
    $servidor = new Pessoal();

    # Começa uma nova página
    $page = new Page();
    $page->iniciaPagina();

    # Pega os parâmetros dos relatórios
    $relatorioDtInicial = post('dtInicial', date('Y') . "-01-01");
    $relatorioDtfinal = post('dtFinal', date('Y') . "-12-31");

    ######

    $select = "SELECT tbservidor.idFuncional,
                      tbpessoa.nome,
                      dtInicial,
                      tblicencapremio.numdias,
                      ADDDATE(dtInicial,tblicencapremio.numDias-1),
                      IFNULL(CONCAT(DATE_FORMAT(dtInicioPeriodo, '%d/%m/%Y'),' - ',DATE_FORMAT(dtFimPeriodo, '%d/%m/%Y')),'---'),
                      tbpublicacaopremio.dtPublicacao,
                      idLicencaPremio
                 FROM tbservidor JOIN tbpessoa USING (idPessoa)
                                 LEFT JOIN tblicencapremio  USING (idServidor)
                                 LEFT JOIN tbpublicacaopremio USING (idPublicacaoPremio)
                WHERE situacao = 1
                  AND ((dtInicial <= '{$relatorioDtInicial}' AND ADDDATE(dtInicial,tblicencapremio.numDias-1) >= '{$relatorioDtInicial}')
                   OR (dtInicial BETWEEN '{$relatorioDtInicial}' AND '{$relatorioDtfinal}')  
                   OR (ADDDATE(dtInicial,tblicencapremio.numDias-1) BETWEEN '{$relatorioDtInicial}' AND '{$relatorioDtfinal}'))
             ORDER BY tbpessoa.nome";

    $result = $servidor->select($select);

    $relatorio = new Relatorio();
    $relatorio->set_titulo('Relatório de Fruição de Licença Prêmio');
    $relatorio->set_tituloLinha2("Período de " . date_to_php($relatorioDtInicial) . " a " . date_to_php($relatorioDtfinal));
    $relatorio->set_subtitulo('Ordenados pelo Nome');
    $relatorio->set_width([10, 30, 10, 10, 5, 25, 10]);
    $relatorio->set_label(['IdFuncional', 'Nome', 'Data Inicial', 'Dias', 'Data Final', 'Período', 'Publicação']);
    $relatorio->set_align(["center", "left"]);
    $relatorio->set_funcao([null, null, "date_to_php", null, "date_to_php", null, "date_to_php"]);
    $relatorio->set_conteudo($result);

    $relatorio->set_formCampos(array(
        array('nome' => 'dtInicial',
            'label' => 'Início:',
            'tipo' => 'data',
            'size' => 4,
            'title' => 'Insira a data inicial',
            'col' => 3,
            'padrao' => $relatorioDtInicial,
            'onChange' => 'formPadrao.submit();',
            'linha' => 1),
        array('nome' => 'dtFinal',
            'label' => 'Término:',
            'tipo' => 'data',
            'size' => 4,
            'title' => 'Insira a data final',
            'col' => 3,
            'padrao' => $relatorioDtfinal,
            'onChange' => 'formPadrao.submit();',
            'linha' => 1),
    ));

    $relatorio->set_formFocus('ano');
    $relatorio->set_formLink('?');
    $relatorio->show();

    $page->terminaPagina();
}