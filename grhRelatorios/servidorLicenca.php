<?php

/**
 * Relatório
 *    
 * By Alat
 */
# Inicia as variáveis que receberão as sessions
$idUsuario = null;              # Servidor logado
$idServidorPesquisado = null; # Servidor Editado na pesquisa do sistema do GRH
# Configuração
include ("../grhSistema/_config.php");

# Permissão de Acesso
$acesso = Verifica::acesso($idUsuario, 2);

if ($acesso) {
    # Conecta ao Banco de Dados
    $pessoal = new Pessoal();

    # Começa uma nova página
    $page = new Page();
    $page->iniciaPagina();

    ######
    # pega o parametro (se tiver)
    $parametro = soNumeros(get('parametro'));

    # Dados do Servidor
    Grh::listaDadosServidorRelatorio($idServidorPesquisado, 'Histórico de Licenças e Afastamentos');

    br();
    # select da lista
    $selectLicença = '(SELECT CONCAT(tbtipolicenca.nome,"<br/>",IFnull(tbtipolicenca.lei,"")),
                                 CASE alta
                                    WHEN 1 THEN "Sim"
                                    WHEN 2 THEN "Não"
                                    end,
                                 dtInicial,
                                 numdias,
                                 ADDDATE(dtInicial,numDias-1),
                                 CONCAT(tblicenca.idTpLicenca,"&",idLicenca),
                                 dtPublicacao,
                                 idLicenca
                            FROM tblicenca LEFT JOIN tbtipolicenca ON tblicenca.idTpLicenca = tbtipolicenca.idTpLicenca
                           WHERE idServidor=' . $idServidorPesquisado;
    if (!vazio($parametro)) {
        $selectLicença .= ' AND tbtipolicenca.idTpLicenca = ' . $parametro . ')';
    } else {
        $selectLicença .= ')
                           UNION
                           (SELECT (SELECT CONCAT(tbtipolicenca.nome,"<br/>",IFnull(tbtipolicenca.lei,"")) FROM tbtipolicenca WHERE idTpLicenca = 6),
                                   "",
                                   dtInicial,
                                   tblicencapremio.numdias,
                                   ADDDATE(dtInicial,tblicencapremio.numDias-1),
                                   CONCAT("6&",tblicencapremio.idServidor),
                                   tbpublicacaopremio.dtPublicacao,                                       
                                   "-"
                              FROM tblicencapremio LEFT JOIN tbpublicacaopremio USING (idPublicacaoPremio)
                             WHERE tblicencapremio.idServidor = ' . $idServidorPesquisado . ')
                                 UNION
                           (SELECT CONCAT(tbtipolicenca.nome,"<br/>",IFnull(tbtipolicenca.lei,"")),
                                   "",
                                   tblicencasemvencimentos.dtInicial,
                                   tblicencasemvencimentos.numdias,
                                   CONCAT("tblicencasemvencimentos","&",idLicencaSemVencimentos),
                                   CONCAT(tblicencasemvencimentos.idTpLicenca,"&",idLicencaSemVencimentos),
                                   tblicencasemvencimentos.dtPublicacao,
                                   "-"
                              FROM tblicencasemvencimentos LEFT JOIN tbtipolicenca USING (idTpLicenca)
                             WHERE tblicencasemvencimentos.idServidor = ' . $idServidorPesquisado . ')
                          ORDER BY 3 desc';
    }

    $result = $pessoal->select($selectLicença);

    $relatorio = new Relatorio();
    $relatorio->set_cabecalhoRelatorio(false);
    $relatorio->set_menuRelatorio(false);

    # Tiver parâmetro exibe subtitulo
    if (!vazio($parametro)) {
        $relatorio->set_subtitulo($pessoal->get_nomeTipoLicenca($parametro));
    }

    $relatorio->set_subTotal(true);
    $relatorio->set_numeroOrdem(true);
    $relatorio->set_numeroOrdemTipo("d");
    $relatorio->set_totalRegistro(false);
    $relatorio->set_bordaInterna(true);
    $relatorio->set_label(array("Licença", "Alta", "Inicio", "Dias", "Término", "Processo", "Publicação"));
    #$relatorio->set_width(array(23,10,5,10,17,10,10,10,5));
    $relatorio->set_align(array('left'));
    $relatorio->set_funcao(array(null, null, 'date_to_php', null, 'exibeDtTermino', 'exibeProcesso', 'date_to_php'));

    $relatorio->set_conteudo($result);
    #$relatorio->set_numGrupo(2);
    $relatorio->set_botaoVoltar(false);
    $relatorio->set_logDetalhe("Visualizou o Relatório de Histórico de Licenças e Afastamentos");
    $relatorio->set_logServidor($idServidorPesquisado);
    $relatorio->show();

    $page->terminaPagina();
}