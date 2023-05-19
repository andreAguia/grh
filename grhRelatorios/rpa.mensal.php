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
    $parametroMes = get_session('parametroMes', date("m"));

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
                        idRecibo
                   FROM tbrpa_recibo JOIN tbrpa_prestador USING (idPrestador)
                   WHERE YEAR(dtPgto) = '{$parametroAno}'
                     AND MONTH(dtPgto) = '{$parametroMes}'
                ORDER BY dtPgto";

    $result = $servidor->select($select);

    $relatorio = new Relatorio();
    $relatorio->set_titulo('Relatório Mensal de RPA');
    $relatorio->set_subtitulo('Ordenados pela Data de Pagamento');
    $relatorio->set_tituloLinha2(get_nomeMes($parametroMes) . ' / ' . $parametroAno);

    $relatorio->set_label(['Número', 'Pagamento', 'Prestador', 'Nascimento', 'PIS/PASEP', 'Valor', 'INSS', 'IRRS', 'Total']);
    $relatorio->set_align(["center", "center", "left", "center", "center", "right", "right", "right", "right", "right"]);

    $relatorio->set_classe([null, null, null, null, null, "Rpa", "RpaInss", "RpaIr", "Rpa"]);
    $relatorio->set_metodo([null, null, null, null, null, "getValor", "exibeValor", "exibeValor", "exibeValorTotal"]);
    $relatorio->set_funcao([null, "date_to_php", null, "date_to_php", null, "formataMoeda2"]);

    $relatorio->set_conteudo($result);
    $relatorio->show();

    $page->terminaPagina();
}