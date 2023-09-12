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

    # Pega os parâmetros
    $parametroAno = get_session('parametroAno', date("Y"));

    ######

    $select = "SELECT LPAD(idRecibo,4,'0'),                       
                        tbrpa_prestador.idPrestador,
                        idRecibo,
                        idRecibo,
                        idRecibo,
                        idRecibo
                   FROM tbrpa_recibo JOIN tbrpa_prestador USING (idPrestador)
                   WHERE (((YEAR(dtInicial) = '{$parametroAno}') OR (YEAR(ADDDATE(dtInicial,numDias-1)) = '{$parametroAno}')) 
                      OR ((YEAR(dtInicial) < '{$parametroAno}') AND (YEAR(ADDDATE(dtInicial,numDias-1)) > '{$parametroAno}')))
                ORDER BY dtInicial";

    $result = $servidor->select($select);

    $relatorio = new Relatorio();
    $relatorio->set_titulo('Relatório Anual de RPA');
    $relatorio->set_subtitulo('Ordenados pela Data Inicial');
    $relatorio->set_tituloLinha3($parametroAno);

    $relatorio->set_label(['Número', 'Prestador', 'Serviço / Processo', 'Período', 'Valores']);
    $relatorio->set_align(["center", "left", "left", "center"]);
    $relatorio->set_width([5, 20, 20, 10, 15, 15, 5]);
    $relatorio->set_bordaInterna(true);

    $relatorio->set_classe([null, "RpaPrestador", "Rpa", "Rpa", "Rpa"]);
    $relatorio->set_metodo([null, "exibePrestador2", "exibeServicoProcesso", "exibePeriodo", "exibeValoresRelatorio"]);

    $relatorio->set_conteudo($result);
    $relatorio->show();

    $page->terminaPagina();
}