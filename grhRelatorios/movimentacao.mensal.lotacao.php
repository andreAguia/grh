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

    # Pega os parâmetros dos relatórios
    $relatorioMes = post('mes', date('m'));
    $relatorioAno = post('ano', date('Y'));

    ######

    $select = "SELECT tbservidor.idFuncional,
                      tbpessoa.nome,
                      tbhistlot.data,
                      tbhistlot.idHistLot,
                      tbhistlot.lotacao
                 FROM tbservidor JOIN tbpessoa USING (idPessoa)
                                 JOIN tbhistlot USING (idServidor)
               WHERE situacao = 1 
                 AND MONTH(tbhistlot.data) = {$relatorioMes}
                 AND YEAR(tbhistlot.data) = {$relatorioAno}  
             ORDER BY tbhistlot.data";

    $result = $servidor->select($select);

    $relatorio = new Relatorio();
    $relatorio->set_titulo('Relatório Mensal de Movimentação de Lotação de Servidores');
    $relatorio->set_subtitulo('Ordenados pela Data da Movimentação');
    $relatorio->set_tituloLinha2(get_nomeMes($relatorioMes) . ' / ' . $relatorioAno);

    $relatorio->set_label(["IdFuncional", "Nome", "Data", "Saiu de", "Foi para"]);
    $relatorio->set_width([10, 20, 10, 30, 30]);
    $relatorio->set_align(["center", "left", "center", "left", "left"]);
    $relatorio->set_funcao([null, null, "date_to_php"]);

    $relatorio->set_classe([null, null, null, "Lotacao", "Lotacao"]);
    $relatorio->set_metodo([null, null, null, "getLotacaoAnterior", "getLotacao"]);

    $relatorio->set_formCampos(array(
        array('nome' => 'ano',
            'label' => 'Ano:',
            'tipo' => 'texto',
            'size' => 4,
            'title' => 'Ano',
            'padrao' => $relatorioAno,
            'onChange' => 'formPadrao.submit();',
            'col' => 3,
            'linha' => 1),
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

    $relatorio->set_formFocus('ano');
    $relatorio->set_formLink('?');

    $relatorio->set_conteudo($result);
    $relatorio->show();

    $page->terminaPagina();
}
