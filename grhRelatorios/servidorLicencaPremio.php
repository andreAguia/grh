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
    # Dados do Servidor
    Grh::listaDadosServidorRelatorio($idServidorPesquisado, 'Relatório de ' . $pessoal->get_licencaNome(6));
    $nome = $pessoal->get_licencaNome(6);
    br();

    ###### Licenças Prêmio Fruídas

    $select = 'SELECT tbpublicacaopremio.dtPublicacao,
                      tbpublicacaopremio.dtInicioPeriodo,
                      tbpublicacaopremio.dtFimPeriodo,
                      dtInicial,
                      tblicencapremio.numdias,
                      ADDDATE(dtInicial,tblicencapremio.numDias-1),
                      idLicencaPremio
                 FROM tblicencapremio LEFT JOIN tbpublicacaopremio USING (idPublicacaoPremio)
                WHERE tblicencapremio.idServidor = ' . $idServidorPesquisado . '
             ORDER BY dtInicial desc';

    $result = $pessoal->select($select);

    $relatorio = new Relatorio();
    $relatorio->set_cabecalhoRelatorio(false);
    $relatorio->set_menuRelatorio(false);
    $relatorio->set_subTotal(true);
    $relatorio->set_totalRegistro(false);
    $relatorio->set_dataImpressao(false);
    $relatorio->set_numeroOrdem(true);
    $relatorio->set_numeroOrdemTipo("d");
    $relatorio->set_subtitulo("Licenças Fruídas");
    $relatorio->set_label(array("Publicação", "Início do Período", "Fim do Período", "Inicio", "Dias", "Término"));
    #$relatorio->set_width(array(23,10,5,10,17,10,10,10,5));
    $relatorio->set_align(array('center'));
    $relatorio->set_funcao(array('date_to_php', 'date_to_php', 'date_to_php', 'date_to_php', null, 'date_to_php'));
    #$relatorio->set_classe(array(null,null,null,'LicencaPremio'));
    #$relatorio->set_metodo(array(null,null,null,'get_publicacao'));

    $relatorio->set_conteudo($result);
    #$relatorio->set_numGrupo(2);
    $relatorio->set_botaoVoltar(false);
    $relatorio->set_logDetalhe("Visualizou o Relatório de Histórico de $nome");
    $relatorio->set_logServidor($idServidorPesquisado);
    $relatorio->show();
    br();

    ###### Dados

    $licenca = new LicencaPremio();
    $numProcesso = $licenca->get_numProcesso($idServidorPesquisado);
    $diasPublicados = $licenca->get_numDiasPublicados($idServidorPesquisado);
    $diasFruidos = $licenca->get_numDiasFruidos($idServidorPesquisado);
    $diasDisponiveis = $licenca->get_numDiasDisponiveis($idServidorPesquisado);

    # Tabela de Serviços
    $tabela = array(array('Processo', $numProcesso),
        array('Dias Publicados', $diasPublicados),
        array('Dias Fruídos', $diasFruidos),
        array('Disponíveis', $diasDisponiveis));

    # Limita o tamanho da tela
    $grid = new Grid();
    $grid->abreColuna(3);

    $relatorio = new Relatorio();
    $relatorio->set_cabecalhoRelatorio(false);
    $relatorio->set_menuRelatorio(false);
    $relatorio->set_subTotal(false);
    $relatorio->set_totalRegistro(false);
    $relatorio->set_subtitulo("Dados");
    $relatorio->set_label(array('Descrição', 'Valor'));
    $relatorio->set_align(array('left', 'center'));
    $relatorio->set_totalRegistro(false);
    $relatorio->set_dataImpressao(false);

    $relatorio->set_conteudo($tabela);
    #$relatorio->set_numGrupo(2);
    $relatorio->set_botaoVoltar(false);
    $relatorio->set_log(false);
    $relatorio->show();

    $grid->fechaColuna();
    $grid->abreColuna(9);

    ###### Publicações

    $select = "SELECT dtPublicacao,
                    dtInicioPeriodo,
                    dtFimPeriodo,
                    numDias,
                    idPublicacaoPremio,
                    idPublicacaoPremio,
                    idPublicacaoPremio
               FROM tbpublicacaopremio
               WHERE idServidor = $idServidorPesquisado
            ORDER BY dtInicioPeriodo desc";

    $result = $pessoal->select($select);

    $relatorio = new Relatorio();
    $relatorio->set_cabecalhoRelatorio(false);
    $relatorio->set_menuRelatorio(false);
    $relatorio->set_subTotal(true);
    $relatorio->set_totalRegistro(false);
    $relatorio->set_subtitulo("Publicações");

    $relatorio->set_label(array("Data da Publicação", "Período Aquisitivo <br/> Início", "Período Aquisitivo <br/> Fim", "Dias <br/> Publicados", "Dias <br/> Fruídos", "Dias <br/> Disponíveis"));
    #$relatorio->set_width(array(15,5,15,15,15,10,10,10));
    $relatorio->set_align(array("center"));
    $relatorio->set_numeroOrdem(true);
    $relatorio->set_numeroOrdemTipo("d");
    $relatorio->set_funcao(array('date_to_php', 'date_to_php', 'date_to_php'));
    $relatorio->set_classe(array(null, null, null, null, 'LicencaPremio', 'LicencaPremio'));
    $relatorio->set_metodo(array(null, null, null, null, 'get_numDiasFruidosPorPublicacao', 'get_numDiasDisponiveisPorPublicacao'));

    #$relatorio->set_dataImpressao(false);
    $relatorio->set_conteudo($result);
    #$relatorio->set_numGrupo(2);
    $relatorio->set_botaoVoltar(false);
    $relatorio->set_log(false);
    $relatorio->show();

    $grid->fechaColuna();
    $grid->fechaGrid();

    # Procedimentos
    #br();
    #$licenca->exibeProcedimentos();

    $page->terminaPagina();
}