<?php

/**
 * Sistema GRH
 * 
 * Capa da Pasta do Servidor
 *   
 * By Alat
 */
# Inicia as variáveis que receberão as sessions
$idUsuario = null;              # Servidor logado
$idServidorPesquisado = null; # Servidor Editado na pesquisa do sistema do GRH
# Configuração
include ("../grhSistema/_config.php");

# Permissão de Acesso
$acesso = Verifica::acesso($idUsuario, 2);

if ($acesso) {
    # Conecta ao Banco de Dados
    $pessoal = new Pessoal();

    # Começa uma nova página
    $page = new Page();
    $page->iniciaPagina();

    # Grava no log a atividade
    $atividade = 'Visualizou a Capa da Pasta';
    $Objetolog = new Intra();
    $data = date("Y-m-d H:i:s");
    $Objetolog->registraLog($idUsuario, $data, $atividade, null, null, 4, $idServidorPesquisado);

    # Menu do Relatório
    $menuRelatorio = new menuRelatorio();
    $menuRelatorio->show();

    # Cabeçalho
    $cabecalho = new Relatorio();
    $cabecalho->exibeCabecalho();

    br(4);

    echo '<table id="tabelaCapaPasta">';

    # Nome
    echo '<tr>';
    echo '<td id="capaNome">Nome:</td>';
    echo '<td id="capaItem">';
    p($pessoal->get_nome($idServidorPesquisado));
    echo '</td>';
    echo '</tr>';

    # Id Funcional
    echo '<tr>';
    echo '<td id="capaNome">Id Funcional:</td>';
    echo '<td id="capaItem">' . $pessoal->get_idFuncional($idServidorPesquisado) . '</td>';
    echo '</tr>';

    # Matrícula
    echo '<tr>';
    echo '<td id="capaNome">Matrícula:</td>';
    echo '<td id="capaItem">' . dv($idServidorPesquisado) . '</td>';
    echo '</tr>';

    # Perfil
    echo '<tr>';
    echo '<td id="capaNome">Perfil:</td>';
    echo '<td id="capaItem">' . $pessoal->get_perfil($idServidorPesquisado) . '</td>';
    echo '</tr>';

    # Cargo
    echo '<tr>';
    echo '<td id="capaNome">Cargo:</td>';
    echo '<td id="capaItem">' . $pessoal->get_cargo($idServidorPesquisado) . '</td>';
    echo '</tr>';

    # Admissão
    echo '<tr>';
    echo '<td id="capaNome">Admissão:</td>';
    echo '<td id="capaItem">' . $pessoal->get_dtAdmissao($idServidorPesquisado) . '</td>';
    echo '</tr>';

    $idPessoa = $pessoal->get_idPessoa($idServidorPesquisado);

    # CPF
    echo '<tr>';
    echo '<td id="capaNome">CPF:</td>';
    echo '<td id="capaItem">' . $pessoal->get_cpf($idPessoa) . '</td>';
    echo '</tr>';

    # Identidade
    echo '<tr>';
    echo '<td id="capaNome">Identidade:</td>';
    echo '<td id="capaItem">' . $pessoal->get_identidade($idPessoa) . '</td>';
    echo '</tr>';

    # Pis/Pasep
    echo '<tr>';
    echo '<td id="capaNome">Pis/Pasep:</td>';
    echo '<td id="capaItem">' . $pessoal->get_pis($idPessoa) . '</td>';
    echo '</tr>';

    echo '</table>';
    echo "<div style='page-break-before:always;'>&nbsp</div>";
    $page->terminaPagina();
}