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
    $intra = new Intra();

    # Começa uma nova página
    $page = new Page();
    $page->set_title("Relatório de Aposentados por Mês de Aniversario");
    $page->iniciaPagina();

    # Pega os parâmetros dos relatórios
    $relatorioMes = post('mes', date('m'));

    ######

    $select = "SELECT tbpessoa.dtNasc,
                      tbpessoa.nome,
                      tbservidor.idServidor,
                      tbservidor.idServidor
                 FROM tbpessoa LEFT JOIN tbservidor ON (tbpessoa.idPessoa = tbservidor.idPessoa)
                   AND MONTH(dtNasc) = {$relatorioMes}
                WHERE tbservidor.situacao = 2
             ORDER BY day(tbpessoa.dtNasc)";

    $result = $servidor->select($select);

    $relatorio = new Relatorio();
    $relatorio->set_titulo('Relatório de Servidores Aposentados');
    $relatorio->set_subtitulo(get_nomeMes($relatorioMes));
    $relatorio->set_conteudo($result);

    $relatorio->set_label(["Dia", "Nome", "Cargo"]);
    $relatorio->set_align(["center", "left", "left"]);
    $relatorio->set_classe([null, null, 'Pessoal']);
    $relatorio->set_metodo([null, null, 'get_cargoSimples']);
    $relatorio->set_funcao(["date_to_php"]);

    $relatorio->set_formCampos(array(
        array('nome' => 'mes',
            'label' => 'Mês',
            'tipo' => 'combo',
            'array' => $mes,
            'size' => 10,
            'padrao' => $relatorioMes,
            'title' => 'Mês do Ano.',
            'onChange' => 'formPadrao.submit();',
            'col' => 3,
            'linha' => 1)));

    $relatorio->show();
    
    $page->terminaPagina();
}