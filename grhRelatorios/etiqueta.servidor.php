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
    $numEtiquetasInicial = get('numEtiquetasInicial', post('numEtiquetasInicial', 1));
    $numEtiquetasFinal = get('numEtiquetasFinal', post('numEtiquetasFinal', 1));
    $conteudo = get('conteudo', post('conteudo', "Matrícula e Nome"));

    $menuRelatorio = new menuRelatorio();
    $menuRelatorio->set_formCampos(array(
        array('nome' => 'numEtiquetasInicial',
            'label' => 'Número Inicial:',
            'tipo' => 'combo',
            'array' => [1, 2, 3, 4, 5, 6, 7, 8, 9, 10],
            'size' => 3,
            'padrao' => $numEtiquetasInicial,
            'onChange' => 'formPadrao.submit();',
            'col' => 3,
            'linha' => 1),
        array('nome' => 'numEtiquetasFinal',
            'label' => 'Número Final:',
            'tipo' => 'combo',
            'array' => [1, 2, 3, 4, 5, 6, 7, 8, 9, 10],
            'size' => 3,
            'padrao' => $numEtiquetasFinal,
            'onChange' => 'formPadrao.submit();',
            'col' => 3,
            'linha' => 1),
        array('nome' => 'conteudo',
            'label' => 'Conteúdo:',
            'tipo' => 'combo',
            'array' => ["Matrícula e Nome", "Só Matrícula", "Só Nome"],
            'size' => 3,
            'padrao' => $conteudo,
            'onChange' => 'formPadrao.submit();',
            'col' => 4,
            'linha' => 1),
    ));

    $menuRelatorio->set_formLink("?");
    $menuRelatorio->show();

    # Limita a página
    $grid = new Grid();
    $grid->abreColuna(6);

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

    # Grava no log a atividade
    $atividade = "Visualizou a etiqueta da pasta funcional de {$nome}";
    if ($conteudo == "Matrícula e Nome") {
        $atividade .= " - (Matrícula e Nome)";
    } else {
        $atividade .= " - (Matrícula)";
    }
    $Objetolog = new Intra();
    $data = date("Y-m-d H:i:s");
    $Objetolog->registraLog($idUsuario, $data, $atividade, null, null, 4, $idServidorPesquisado);

    br();
    echo "<table width='100%' id='etiqueta' border='2px'>";

    for ($i = $numEtiquetasInicial; $i <= $numEtiquetasFinal; $i++) {

        if ($conteudo == "Matrícula e Nome") {

            # Define a fonte a partir do número de letras no nome do servidor
            if (strlen($nome) > 25) {
                $matriculacss = "pmatriculaEtiqueta2";
                $nomecss = "pnomeEtiqueta2";
            } else {
                $matriculacss = "pmatriculaEtiqueta1";
                $nomecss = "pnomeEtiqueta1";
            }


            echo "<tr>";
            echo "<td style = 'width: 50%' align='center'>";
            p($matricula . " / " . $i, $matriculacss);
            p($nome, $nomecss);
            echo "</td>";
            echo"<td style = 'width: 0%'></td>";
            echo "<td style = 'width: 50%' align='center'>";
            p($matricula . " / " . $i, $matriculacss);
            p($nome, $nomecss);
            echo "</td>";
            echo "</tr>";
        }

        if ($conteudo == "Só Matrícula") {
            echo "<tr>";
            echo "<td style = 'width: 50%' align='center'>";
            p($matricula . " / " . $i, "pmatriculaEtiqueta1");
            echo "</td>";
            echo"<td style = 'width: 0%'></td>";
            echo "<td style = 'width: 50%' align='center'>";
            p($matricula . " / " . $i, "pmatriculaEtiqueta1");
            echo "</td>";
            echo "</tr>";
        }
        
        if ($conteudo == "Só Nome") {
                echo "<tr>";
                echo "<td style = 'width: 50%' align='center'>";
                if (strlen($nome) > 20) {
                    p($nome, "pnomeEtiqueta4");
                    p("({$perfil})", "pperfilEtiqueta1");
                } else {
                    p($nome, "pnomeEtiqueta3");                    
                    p("({$perfil})", "pperfilEtiqueta1");
                }
                echo "</td>";
                echo"<td style = 'width: 0%'></td>";
                echo "<td style = 'width: 50%' align='center'>";
                if (strlen($nome) > 20) {
                    p($nome, "pnomeEtiqueta4");
                    p("({$perfil})", "pperfilEtiqueta1");
                } else {
                    p($nome, "pnomeEtiqueta3");                    
                    p("({$perfil})", "pperfilEtiqueta1");
                }
                echo "</td>";
                echo "</tr>";
            }
    }

    echo "</table>";

    $grid->fechaColuna();
    $grid->fechaGrid();

    $page->terminaPagina();
}