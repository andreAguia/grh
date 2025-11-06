<?php

/**
 * Sistema GRH
 * 
 * Etiqueta servidor
 *   
 * By Alat
 */
# Inicia as variáveis que receberão as sessions
$idUsuario = null;
$idServidorPesquisado = null;

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

    # Pega quem assina
    $numInicial = post('numInicial', 1);
    $numFinal = post('numFinal', 500);
    $numColunas = post('numColunas', 4);
    $primeiraLinha = post('primeiraLinha', "Semana do Servidor 2025");
    $segundaLinhac1 = post('segundaLinhac1', "Sorteio 1");
    $segundaLinhac2 = post('segundaLinhac2', "Sorteio 2");

    $menuRelatorio = new menuRelatorio();
    $menuRelatorio->set_formCampos(array(
        array('nome' => 'primeiraLinha',
            'label' => 'Primeira Linha:',
            'tipo' => 'texto',
            'size' => 200,
            'padrao' => $primeiraLinha,
            'onChange' => 'formPadrao.submit();',
            'col' => 6,
            'linha' => 1),
        array('nome' => 'segundaLinhac1',
            'label' => 'Segunda Linha - Primeira Coluna:',
            'tipo' => 'texto',
            'size' => 200,
            'padrao' => $segundaLinhac1,
            'onChange' => 'formPadrao.submit();',
            'col' => 6,
            'linha' => 2),
        array('nome' => 'segundaLinhac2',
            'label' => 'Segunda Linha - Segunda Coluna:',
            'tipo' => 'texto',
            'size' => 200,
            'padrao' => $segundaLinhac2,
            'onChange' => 'formPadrao.submit();',
            'col' => 6,
            'linha' => 2),
        array('nome' => 'numInicial',
            'label' => 'Número Inicial:',
            'tipo' => 'texto',
            'size' => 3,
            'padrao' => $numInicial,
            'onChange' => 'formPadrao.submit();',
            'col' => 3,
            'linha' => 4),
        array('nome' => 'numFinal',
            'label' => 'Número Final:',
            'tipo' => 'texto',
            'size' => 3,
            'padrao' => $numFinal,
            'autofocus' => true,
            'onChange' => 'formPadrao.submit();',
            'col' => 3,
            'linha' => 4),
        array('nome' => 'numColunas',
            'label' => 'Número de Colunas:',
            'tipo' => 'combo',
            'array' => [2, 4],
            'size' => 3,
            'padrao' => $numColunas,
            'onChange' => 'formPadrao.submit();',
            'col' => 3,
            'linha' => 4),
    ));

    $menuRelatorio->set_formLink("?");
    $menuRelatorio->show();    

    /*
     * Dados Principais
     */

    # Grava no log a atividade
    $atividade = "Visualizou a listagem de números para sorteio";
    $Objetolog = new Intra();
    $data = date("Y-m-d H:i:s");
    $Objetolog->registraLog($idUsuario, $data, $atividade, null, null, 4);
    
    # Limita a página
    $grid = new Grid("center");    

    if ($numColunas == 4) {
        $incremento = 2;
        $quebra = 10;
        $grid->abreColuna(12);
        $porcentagem = 25;
    } else {
        $incremento = 1;
        $quebra = 11;
        $grid->abreColuna(7);
        $porcentagem = 50;
    }
    
    $contador = 1;

    br();
    echo "<table width='100%' id='etiqueta' border='2px'>";

    for ($i = $numInicial; $i <= $numFinal; $i += $incremento) {

        echo "<tr>";
        echo "<td style = 'width: {$porcentagem}%' align='center'>";

        p("{$primeiraLinha}<br/>{$segundaLinhac1}", "psorteioTexto");
        p(str_pad($i, 3, '0', STR_PAD_LEFT), "psorteioNumero");

        echo "</td>";
        echo "<td style = 'width: {$porcentagem}%' align='center'>";

        p("{$primeiraLinha}<br/>{$segundaLinhac2}", "psorteioTexto");
        p(str_pad($i, 3, '0', STR_PAD_LEFT), "psorteioNumero");

        echo "</td>";

        if ($numColunas == 4) {
            echo "<td style = 'width: 25%' align='center'>";

            p("{$primeiraLinha}<br/>{$segundaLinhac1}", "psorteioTexto");
            p(str_pad($i + 1, 3, '0', STR_PAD_LEFT), "psorteioNumero");

            echo "</td>";
            echo "<td style = 'width: 25%' align='center'>";

            p("{$primeiraLinha}<br/>{$segundaLinhac2}", "psorteioTexto");
            p(str_pad($i + 1, 3, '0', STR_PAD_LEFT), "psorteioNumero");
        }

        echo "</td>";
        echo "</tr>";

        if ($contador == $quebra) {
            echo "</table>";
            echo "<div style='page-break-before: always;'></div>";
            echo "<table width='100%' id='etiqueta' border='2px'>";
            $contador = 1;
        } else {
            $contador++;
        }
    }

    echo "</table>";

    $grid->fechaColuna();
    $grid->fechaGrid();

    $page->terminaPagina();
}