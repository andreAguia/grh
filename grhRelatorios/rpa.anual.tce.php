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

    $select = "SELECT tbrpa_prestador.prestador,
                      tbrpa_prestador.especialidade,
                      servico,
                      tbrpa_prestador.cpf,
                      dtinicial,
                      dias,
                      ADDDATE(dtInicial,dias-1),
                      rubrica,
                      processo
                   FROM tbrpa_recibo JOIN tbrpa_prestador USING (idPrestador)
                   WHERE YEAR(dtPgto) = '{$parametroAno}'
                ORDER BY dtPgto";

    $result = $servidor->select($select);

    $relatorio = new Relatorio();
    $relatorio->set_titulo('Relatório Anual de RPA para o TCE');
    $relatorio->set_subtitulo('Ordenados pela Data de Pagamento');
    $relatorio->set_tituloLinha2($parametroAno);

    $relatorio->set_label(['Prestador', 'Especialidade', 'Serviço Prestado', 'CPF', 'Início', 'Dias', 'Término', 'Rubrica', 'Processo']);
    $relatorio->set_align(["left", "center", "left"]);
    $relatorio->set_funcao([null, null, null, null, "date_to_php", null, "date_to_php"]);
    #$relatorio->set_width([25,10,15,10,10,5,10,10,10]);   

    $relatorio->set_conteudo($result);
    $relatorio->show();

    $page->terminaPagina();
}