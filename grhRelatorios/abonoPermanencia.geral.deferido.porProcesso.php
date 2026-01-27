<?php

/**
 * Relatório
 *    
 * By Alat
 */
# Inicia as variáveis
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
    $page->set_title("Servidores com Abono Permanência Deferido");
    $page->iniciaPagina();

    ######   

    br();
    $select = "SELECT idFuncional,
                      tbpessoa.nome,
                      idServidor,
                      idServidor,
                      tbabono.data,
                      tbabono.processo,
                      tbabono.obs
                 FROM tbservidor LEFT JOIN tbpessoa USING (idPessoa)
                                      JOIN tbabono USING (idServidor)
                WHERE tbabono.status = 1 
                  AND situacao = 1
             ORDER BY tbabono.processo asc";

    $result = $pessoal->select($select);

    $relatorio = new Relatorio();
    $relatorio->set_titulo('Relatório Geral de Estatutários Ativos');
    $relatorio->set_tituloLinha2('com Abono Permanencia Deferido');
    $relatorio->set_subtitulo('Ordenados pelo Processo');
    $relatorio->set_label(["IdFuncional", "Nome", "Cargo", "Sexo", "Data", "Processo", "Fundamentação Legal"]);
    $relatorio->set_width([10, 20, 15, 10, 10, 15, 20]);
    $relatorio->set_align(["center", "left", "left", "center", "center", "left", "left"]);
    $relatorio->set_funcao([null, null, null, null, "date_to_php"]);
    $relatorio->set_classe([null, null, "pessoal", "pessoal"]);
    $relatorio->set_metodo([null, null, "get_cargoRel", "get_sexo"]);

    $relatorio->set_conteudo($result);
    $relatorio->set_logServidor($idServidorPesquisado);
    $relatorio->set_logDetalhe("Visualizou o Relatório de Servidores com Abono Permanencia");
    $relatorio->show();

    $page->terminaPagina();
}