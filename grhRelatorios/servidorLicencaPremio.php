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
$acesso = Verifica::acesso($idUsuario, [1, 2, 12]);

if ($acesso) {
    # Conecta ao Banco de Dados
    $pessoal = new Pessoal();

    # Começa uma nova página
    $page = new Page();
    $page->iniciaPagina();

    # Limita o tamanho da tela
    $grid = new Grid();
    $grid->abreColuna(12);

    ######
    # Dados do Servidor
    Grh::listaDadosServidorRelatorio($idServidorPesquisado, 'Relatório de Licença Prêmio');
    $nome = $pessoal->get_licencaNome(6);

    # Pega o número de vínculos
    $licenca = new LicencaPremio();
    $numVinculos = $licenca->get_numVinculosPremio($idServidorPesquisado);

    if ($numVinculos > 1) {
        tituloRelatorio('Observação:');
        p("Servidor tem mais de um vínculo com a Universidade e é possivel que tenha direito a outros períodos aquisitivos de licença prêmio.", "f12", "");
    }

    ###### Licenças Prêmio Fruídas
    tituloRelatorio('Licenças Fruídas');

    $select = 'SELECT tbpublicacaopremio.dtPublicacao,
                      IFNULL(CONCAT(DATE_FORMAT(dtInicioPeriodo, "%d/%m/%Y")," - ",DATE_FORMAT(dtFimPeriodo, "%d/%m/%Y")),"---"),
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
    #$relatorio->set_subtitulo("Licenças Fruídas");
    $relatorio->set_label(array("Publicação", "Período Aquisitivo", "Inicio", "Dias", "Término"));
    #$relatorio->set_width(array(23,10,5,10,17,10,10,10,5));
    $relatorio->set_align(array('center'));
    $relatorio->set_funcao(array('date_to_php', null, 'date_to_php', null, 'date_to_php'));
//    $relatorio->set_classe(array(null, 'LicencaPremio'));
//    $relatorio->set_metodo(array(null, 'exibePeriodoAquisitivo'));

    $relatorio->set_conteudo($result);
    #$relatorio->set_numGrupo(2);
    $relatorio->set_botaoVoltar(false);
    $relatorio->set_logDetalhe("Visualizou o Relatório de Histórico de $nome");
    $relatorio->set_logServidor($idServidorPesquisado);
    $relatorio->show();

    # Exibe as licenças prêmio de outros vinculos com a UENF                
    $numVinculos = $licenca->get_numVinculosPremio($idServidorPesquisado);

    # Exibe o tempo de licença anterior
    # Verifica se tem vinculos anteriores
    if ($numVinculos > 0) {

        # Carrega um array com os idServidor de cada vinculo
        $vinculos = $pessoal->get_vinculos($idServidorPesquisado);

        # Percorre os vinculos
        foreach ($vinculos as $tt) {

            # Pega o perfil da cada vínculo
            $idPerfilPesquisado = $pessoal->get_idPerfil($tt[0]);

            if ($idServidorPesquisado <> $tt[0]) {

                # Verifica se é estatutário
                if ($idPerfilPesquisado == 1) {
                    # Cria um menu
                    $menu = new MenuBar();

                    # Número do processo
                    $licenca->exibeLicencaPremioRelatorio($tt[0]);
                }
            }
        }
    }

    ###### Dados
    $licenca->exibePublicacoesPremioRelatório($idServidorPesquisado);

    $grid->fechaColuna();
    $grid->fechaGrid();

    $page->terminaPagina();
}