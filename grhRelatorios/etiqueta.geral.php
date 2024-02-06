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

    # Pega os parâmetros
    $numEtiquetas = get('numEtiquetas', post('numEtiquetas', 1));
    $conteudo = get('conteudo', post('conteudo', "Matrícula e Nome"));
    $situacao = post('situacao', "*");

    # Situação
    $situacaoCombo = $pessoal->select('SELECT idsituacao, situacao
                                          FROM tbsituacao                                
                                      ORDER BY 1');
    array_unshift($situacaoCombo, array('*', '-- Todos --'));

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
            'linha' => 1),
        array('nome' => 'conteudo',
            'label' => 'Conteúdo:',
            'tipo' => 'combo',
            'array' => ["Matrícula e Nome", "Só Matrícula"],
            'size' => 3,
            'padrao' => $conteudo,
            'onChange' => 'formPadrao.submit();',
            'col' => 4,
            'linha' => 1),
        array('nome' => 'situacao',
            'label' => 'Situação:',
            'tipo' => 'combo',
            'array' => $situacaoCombo,
            'size' => 3,
            'padrao' => $situacao,
            'onChange' => 'formPadrao.submit();',
            'col' => 4,
            'linha' => 1),
    ));

    $menuRelatorio->set_formFocus('numEtiquetas');
    $menuRelatorio->set_formLink("?");

    $menuRelatorio->show();

    # Limita a página
    $grid = new Grid();
    $grid->abreColuna(12);

    /*
     * Dados Principais
     */

    $select = "SELECT idFuncional,
                      matricula,
                      idServidor
                 FROM tbservidor JOIN tbperfil USING (idPerfil)
                WHERE tbperfil.tipo <> 'Outros'";

    if ($situacao <> "*") {
        $select .= " AND situacao = {$situacao}";
    }

    $select .= " ORDER BY matricula";

    $result = $pessoal->select($select);

    # Quantas etiquetas por coluna  (Não funcionou)
    $pulaColuna = 17;

    # Pega a metade
    $metade = intval(count($result) / 2);

    # Inicializa o contador
    $contador = 0;

    # Grava no log a atividade
    $atividade = "Visualizou o Relatório Etiqueta Geral Ativos";
    if ($conteudo == "Matrícula e Nome") {
        $atividade .= " - (Matrícula e Nome)";
    } else {
        $atividade .= " - (Matrícula)";
    }
    $Objetolog = new Intra();
    $data = date("Y-m-d H:i:s");
    $Objetolog->registraLog($idUsuario, $data, $atividade);

    # Coluna
    $grid->fechaColuna();
    $grid->abreColuna(6);

    echo "<table width='100%' id='etiqueta' border='2px'>";

    foreach ($result as $item) {

        # Verifica se habilita a segunda coluna
        if ($contador == $metade) {
            echo "</table>";
            $grid->fechaColuna();
            $grid->abreColuna(6);
            echo "<table width='100%' id='etiqueta' border='2px'>";
        }

        # Contadores
        $contador++;

        # Pega os dados
        $matricula = dv($item["matricula"]);
        $nome = $pessoal->get_nome($item["idServidor"]);

        for ($i = 1; $i <= $numEtiquetas; $i++) {
            if ($conteudo == "Matrícula e Nome") {

                # Define a fonte a partir do número de letras no nome do servidor
                if (strlen($nome) > 20) {
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
            } else {
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
        }
    }

    echo "</table>";

    $grid->fechaColuna();
    $grid->fechaGrid();

    $page->terminaPagina();
}