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
$acesso = Verifica::acesso($idUsuario, [2, 10]);

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
                        dtPgto,                                     
                        tbrpa_prestador.prestador,
                        tbrpa_prestador.dtNascimento,
                        inss,
                        idRecibo,
                        idRecibo,
                        idRecibo,
                        idRecibo,
                        month(dtPgto),
                        idRecibo
                   FROM tbrpa_recibo JOIN tbrpa_prestador USING (idPrestador)
                   WHERE YEAR(dtPgto) = '{$parametroAno}'
                ORDER BY dtPgto";

    $result = $servidor->select($select);

    $relatorio = new Relatorio();
    $relatorio->set_titulo('Relatório Anual de RPA');
    $relatorio->set_subtitulo('Agrupado por Mês e Ordenados pela Data de Pagamento');
    $relatorio->set_tituloLinha2($parametroAno);

    $relatorio->set_label(['Número', 'Pagamento', 'Prestador', 'Nascimento', 'PIS/PASEP', 'Valor', 'INSS', 'IRRS', 'Total', 'Mês']);
    $relatorio->set_align(["center", "center", "left", "center", "center", "right", "right", "right", "right", "right"]);

    $relatorio->set_classe([null, null, null, null, null, "Rpa", "RpaInss", "RpaIr", "Rpa"]);
    $relatorio->set_metodo([null, null, null, null, null, "getValor", "exibeValor", "exibeValor", "exibeValorTotal"]);
    $relatorio->set_funcao([null, "date_to_php", null, "date_to_php", null, "formataMoeda2", null, null, null, 'get_NomeMes']);
    $relatorio->set_numGrupo(9);
    #$relatorio->set_colunaSomatorio(5);

    $relatorio->set_conteudo($result);
    $relatorio->show();

    $page->terminaPagina();
}