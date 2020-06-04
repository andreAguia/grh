<?php

/**
 * Rotina de solicitação de férias
 * exibe o aviso de férias em pdf para impressão
 * 
 * @param	$id	integer	-> o id das férias a ser impresso
 *
 * By Alat
 */
# Inicia as variáveis que receberão as sessions
$idUsuario = null;              # Servidor logado
$idServidorPesquisado = null; # Servidor Editado na pesquisa do sistema do GRH
# configurações
include ("../grhSistema/_config.php");

# pega os dados das férias
$row = get('row');                      // pega o array
$row = unserialize(urldecode($row));    // monta o array
# pega os dados do servidor
$servidor = get('servidor');                      // pega o array
$servidor = unserialize(urldecode($servidor));    // monta o array
# Permissão de Acesso
$acesso = Verifica::acesso($idUsuario, 2);

if ($acesso) {
    # Conecta ao Banco de Dados
    $pessoal = new Pessoal();

    # Cria um novo objeto PDF
    $pdf = new FPDF('P', 'mm', 'A4');
    $pdf->AliasNbPages();

    # Variáveis de construção da página
    $ypos = 30; # Valor inicial de y
    # Inicia a página
    $pdf->AddPage();

    $pdf->SetXY(10, $ypos);

    # Título
    $pdf->SetFont('Arial', '', 16);
    $pdf->Cell(190, 20, "SOLICITAÇÃO DE FÉRIAS", 0, 1, 'C');

    $ypos += 40;
    $pdf->SetXY(30, $ypos);
    $pdf->SetFont('Arial', '', 12);

    # Dados do servidor
    $idFuncional = $pessoal->get_idFuncional($idServidorPesquisado);
    $pdf->Cell(20, 5, "idFunc:", 0, 0, 'L');
    $pdf->Cell(15, 5, $idFuncional, 0, 1, 'L');

    $pdf->SetX(30);
    $pdf->Cell(20, 5, "Nome:", 0, 0, 'L');
    $pdf->SetFont('Arial', 'B');
    $pdf->Cell(100, 5, $servidor[0], 0, 1, 'L');

    $pdf->SetFont('Arial', '', 12);
    $pdf->SetX(30);
    $pdf->Cell(20, 5, "Cargo:", 0, 0, 'L');
    $pdf->Cell(100, 5, $servidor[1], 0, 1, 'L');

    $pdf->SetX(30);
    $pdf->Cell(20, 5, "Perfil:", 0, 0, 'L');
    $pdf->Cell(100, 5, $servidor[2], 0, 1, 'L');

    $pdf->SetX(30);
    $pdf->Cell(20, 5, "Lotação:", 0, 0, 'L');
    $pdf->Cell(100, 5, $servidor[3], 0, 1, 'L');

    $ypos += 40;
    $pdf->SetY($ypos);
    $pdf->Cell(190, 7, "PERÍODO", 1, 1, 'C');

    # Dados do período de féias
    $ypos += 20;
    $pdf->SetXY(30, $ypos);
    $pdf->Cell(50, 5, "Nº de dias:", 0, 0, 'L');

    if (($row[0] <> "") && ($row[0] <> "Único")) {
        $pdf->Cell(15, 5, $row[3], 0, 0, 'L');
        $pdf->Cell(1, 5, "(", 0, 0, 'L');
        $pdf->Cell(5, 5, $row[0], 0, 0, 'L');
        $pdf->Cell(50, 5, " período)", 0, 1, 'L');
    } else {
        $pdf->Cell(15, 5, $row[3], 0, 1, 'L');
    }

    $pdf->SetX(30);
    $pdf->Cell(50, 5, "Gozo das férias:", 0, 0, 'L');
    $pdf->Cell(100, 5, "De " . dataExtenso($row[2]) . " a " . dataExtenso($row[4]), 0, 1, 'L');

    $ypos += 20;
    $pdf->SetY($ypos);
    $pdf->Cell(190, 7, "ANO CIVIL: " . $row[1], 0, 1, 'C');

    $ypos += 20;
    $pdf->SetXY(30, $ypos);
    $pdf->Cell(190, 7, "Campos dos Goytacazes, " . dataExtenso(date("d/m/Y")), 0, 1, 'L');

    $ypos += 20;
    $pdf->SetXY(30, $ypos);
    $pdf->Cell(190, 7, "CIENTE:", 0, 1, 'L');

    $ypos += 20;
    $pdf->SetXY(30, $ypos);
    $pdf->Cell(80, 7, "Carimbo e Assinatura do Servidor", 'T', 0, 'C');
    $pdf->Cell(8, 7, "  ", 0, 0, 'C');
    $pdf->Cell(80, 7, "Carimbo e Assinatura da Chefia Imediata", 'T', 1, 'C');

    # Encerra o documento PDF
    $pdf->Output();
}