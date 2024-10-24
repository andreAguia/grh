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

# Verifica qual será o id
if (empty($idServidorPesquisado)) {
    alert("É necessário informar o id do Servidor.");
}

# Permissão de Acesso
$acesso = Verifica::acesso($idUsuario, [1, 2, 12]);

if ($acesso) {
    # Conecta ao Banco de Dados    
    $pessoal = new Pessoal();

    # Começa uma nova página
    $page = new Page();
    $page->iniciaPagina();

    # Pega quem assina
    $numEtiquetas = get('numEtiquetas', post('numEtiquetas', 1));

    $menuRelatorio = new menuRelatorio();
    $menuRelatorio->set_formCampos(array(
        array('nome' => 'numEtiquetas',
            'label' => 'Número de Etiquetas:',
            'tipo' => 'combo',
            'array' => [1, 2, 3, 4, 5, 6, 7, 8, 9, 10],
            'size' => 3,
            'padrao' => $numEtiquetas,
            'onChange' => 'formPadrao.submit();',
            'col' => 3,
            'linha' => 2),
    ));

    $menuRelatorio->set_formLink("?");
    $menuRelatorio->show();

    # Limita a página
    $grid = new Grid();
    $grid->abreColuna(7);

    /*
     * Dados Principais
     */

    $select = "SELECT idFuncional,
                      matricula,
                      tbpessoa.nome
                 FROM tbservidor LEFT JOIN tbpessoa ON (tbservidor.idPessoa = tbpessoa.idPessoa)
                WHERE tbservidor.idServidor = {$idServidorPesquisado}";

    $result = $pessoal->select($select, false);

    $matricula = dv($result["matricula"]);
    $nome = $pessoal->get_nome($idServidorPesquisado);
    $perfil = $pessoal->get_perfilSimples($idServidorPesquisado);
    $cargo = $pessoal->get_cargoSigla($idServidorPesquisado);
    $idPerfil = $pessoal->get_idPerfil($idServidorPesquisado);

    # Grava no log a atividade
    $atividade = "Visualizou a etiqueta da pasta funcional de {$nome}";
    $Objetolog = new Intra();
    $data = date("Y-m-d H:i:s");
    $Objetolog->registraLog($idUsuario, $data, $atividade, null, null, 4, $idServidorPesquisado);

    br();
    echo "<table width='100%' id='etiqueta' border='2px'>";

    for ($i = 1; $i <= $numEtiquetas; $i++) {


        echo "<tr>";
        echo "<td style = 'width: 50%' align='center'>";
        # Escolhe pelo tamanho do nome
        if (strlen($nome) > 20) {
            p($nome, "pnomeEtiqueta4");
        } else {
            p($nome, "pnomeEtiqueta3");
        }

        p("({$matricula} - {$perfil})", "pperfilEtiqueta1");
        echo "</td>";
        echo"<td style = 'width: 0%'></td>";
        echo "<td style = 'width: 50%' align='center'>";
        # Escolhe pelo tamanho do nome
        if (strlen($nome) > 20) {
            p($nome, "pnomeEtiqueta4");
        } else {
            p($nome, "pnomeEtiqueta3");
        }

        p("({$matricula} - {$perfil})", "pperfilEtiqueta1");
        echo "</td>";
        echo "</tr>";
    }

    echo "</table>";

    $grid->fechaColuna();
    $grid->fechaGrid();

    $page->terminaPagina();
}