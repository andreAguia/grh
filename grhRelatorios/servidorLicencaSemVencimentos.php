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

    ######
    # pega o parametro (se tiver)
    $parametro = soNumeros(get('parametro'));

    # Dados do Servidor
    Grh::listaDadosServidorRelatorio($idServidorPesquisado, 'Histórico de Licenças e Afastamentos');

    br();
    # select da lista
    $selectLicença = "SELECT idLicencaSemVencimentos,
                             CASE tipo
                                  WHEN 1 THEN 'Inicial'
                                  WHEN 2 THEN 'Renovação'
                                  ELSE '--'
                              END,
                              idTpLicenca,
                              idLicencaSemVencimentos,
                              idLicencaSemVencimentos, 
                              idLicencaSemVencimentos
                         FROM tblicencasemvencimentos
                        WHERE idServidor=$idServidorPesquisado ORDER BY 3 desc";

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

    $relatorio->set_label(array("Status", "Tipo", "Licença Sem Vencimentos", "Dados", "Período", "Rioprevidência"));
    $relatorio->set_align(array("center", "center", "left", "left", "left"));

    $relatorio->set_classe(array("LicencaSemVencimentos", null, "LicencaSemVencimentos", "LicencaSemVencimentos", "LicencaSemVencimentos", "LicencaSemVencimentos"));
    $relatorio->set_metodo(array("exibeStatus", null, "get_nomeLicenca", "exibeDados", "exibePeriodo", "exibeRioprevidencia"));

    $relatorio->set_conteudo($result);
    #$relatorio->set_numGrupo(2);
    $relatorio->set_botaoVoltar(false);
    $relatorio->set_logDetalhe("Visualizou o Relatório de Histórico de Licenças Sem  Vencimentos");
    $relatorio->set_logServidor($idServidorPesquisado);
    $relatorio->show();

    $page->terminaPagina();
}