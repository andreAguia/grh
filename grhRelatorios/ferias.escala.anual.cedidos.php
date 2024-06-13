<?php

/**
 * Sistema GRH
 * 
 * Escala de férias de Cedidos da Uenf
 *   
 * By Alat
 */
# Inicia as variáveis que receberão as sessions
$idUsuario = null;

# Configuração
include ("../grhSistema/_config.php");

# Pega os parâmetros do relatório
$postIdServidor = post('postIdServidor');
$parametroAno = post('parametroAno', date('Y') + 1);

# Permissão de Acesso
$acesso = Verifica::acesso($idUsuario, [1, 2, 12]);

if ($acesso) {
    # Conecta ao Banco de Dados    
    $pessoal = new Pessoal();

    # Começa uma nova página
    $page = new Page();
    $page->iniciaPagina();

    # Limita a página
    $grid = new Grid();
    $grid->abreColuna(12);

    # select da combo 
    $select = "SELECT tbservidor.idServidor,
                      tbpessoa.nome,
                      tbhistcessao.orgao
                 FROM tbhistcessao LEFT JOIN tbservidor USING (idServidor)
                                        JOIN tbpessoa USING (idPessoa)
                WHERE tbservidor.situacao = 1
                  AND idPerfil = 1
                  AND (tbhistcessao.dtFim IS NULL OR (now() BETWEEN tbhistcessao.dtInicio AND tbhistcessao.dtFim))
             ORDER BY tbhistcessao.orgao, tbpessoa.nome";

    $servidores = $pessoal->select($select);
    array_unshift($servidores, array(null, "Selecione o Servidor"));

    # Cria um array com os anos possíveis
    $anoInicial = 1993;
    $anoAtual = date('Y') + 1;
    $anoExercicio = arrayPreenche($anoInicial, $anoAtual, "d");

    # Menu do Relatório
    $menuRelatorio = new menuRelatorio();
    $menuRelatorio->set_botaoVoltar(null);
    $menuRelatorio->set_formCampos(array(
        array('nome' => 'postIdServidor',
            'label' => 'Servidor Cedido',
            'tipo' => 'combo',
            'array' => $servidores,
            'valor' => $postIdServidor,
            'size' => 100,
            'optgroup' => true,
            'title' => 'Qual o servidor cedido?',
            'onChange' => 'formPadrao.submit();',
            'col' => 6,
            'linha' => 1),
        array('nome' => 'parametroAno',
            'label' => 'Ano de Admissão:',
            'tipo' => 'combo',
            'size' => 10,
            'padrao' => $parametroAno,
            'array' => $anoExercicio,
            'title' => 'Ano',
            'onChange' => 'formPadrao.submit();',
            'col' => 3,
            'linha' => 1)
    ));
    $menuRelatorio->set_formLink("?");
    $menuRelatorio->show();

    # Cabeçalho do Relatório (com o logotipo)
    $relatorio = new Relatorio();
    $relatorio->exibeCabecalho();

    # Limita o tamanho da tela
    $grid = new Grid("center");
    $grid->abreColuna(11);
    br();

    # Declaração
    p("Programa de Fruição de Férias", "pRelatorioTitulo");
    p("Servidor UENF à Disposição de Outros Órgãos", "pRelatorioSubtitulo");
    br(2);

    if (empty($postIdServidor)) {
        $result[0] = "---";
        $result[1] = "---";
        $result[2] = "---";
    } else {
        # Pega os dados
        $select = "SELECT tbservidor.idFuncional,
                      tbpessoa.nome,
                      tbhistcessao.orgao
                 FROM tbhistcessao LEFT JOIN tbservidor USING (idServidor)
                                        JOIN tbpessoa USING (idPessoa)
                WHERE tbservidor.situacao = 1
                  AND idPerfil = 1
                  AND (tbhistcessao.dtFim IS NULL OR (now() BETWEEN tbhistcessao.dtInicio AND tbhistcessao.dtFim))
                  AND idServidor = {$postIdServidor}
             ORDER BY tbpessoa.nome";

        $result = $pessoal->select($select, false);
    }

    # Inicia a tabela
    echo '<table class="tabelaRelatorioFicha"';

    echo "<tr style='height:60px'>";
    echo "<td style='width:70%'>Órgão Cessionário: <b>{$result[2]}</b></td>";
    echo "<td style='width:30%'>Exercício <b>{$parametroAno}</b></td>";
    echo "</tr>";

    # Fecha a tabela
    echo '</table>';

    br(2);

    # Inicia a tabela
    echo '<table class="tabelaRelatorioFicha"';

    # Cabeçalho
    echo "<tr style='height:35px'>";
    echo "<td style='width:15%'>IdFuncional</td>";
    echo "<td style='width:35%'>Nome do Servidor</td>";
    echo "<td style='width:50%'>Dias de Férias</td>";
    echo "</tr>";

    # Informação
    echo "<tr style='height:80px'>";
    echo "<td style='width:15%'><b>{$result[0]}</b></td>";
    echo "<td style='width:45%'><b>{$result[1]}</b></td>";
    echo "<td style='width:40%'></td>";
    echo "</tr>";

    # Fecha a tabela
    echo '</table>';

    br(15);

    # Inicia a tabela
    echo '<table class="tabelaRelatorio"';

    # data e Assinatura
    echo "<tr>";
    echo "<td style='width:40%'>______ / ______ / ______</td>";
    echo "<td style='width:60%'>____________________________________________________</td>";
    echo "</tr>";

    echo "<tr>";
    echo "<td style='width:40%'>Data</td>";
    echo "<td style='width:60%'>Assinatura</td>";
    echo "</tr>";

    # Fecha a tabela
    echo '</table>';

    $grid->fechaColuna();
    $grid->fechaGrid();

    $page->terminaPagina();
}