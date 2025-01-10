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
    $situacao = post('situacao', "*");
    $comeca = post('comeca', "*");
    $termina = post('termina', "*");

    # Situação
    $situacaoCombo = $pessoal->select('SELECT idsituacao, situacao
                                          FROM tbsituacao                                
                                      ORDER BY 1');
    array_unshift($situacaoCombo, array('*', '-- Todos --'));
        
    # Servidores
    $comboselect = "SELECT tbpessoa.nome,
                           tbpessoa.nome
                      FROM tbservidor JOIN tbperfil USING (idPerfil)
                                      JOIN tbpessoa USING (idPessoa)
                     WHERE tbperfil.tipo <> 'Outros'";

    if ($situacao <> "*") {
        $comboselect .= " AND situacao = {$situacao}";
    }

    $comboselect .= " ORDER BY tbpessoa.nome";
    $servidoresCombo = $pessoal->select($comboselect);
    
    array_unshift($servidoresCombo, array('*', '-- Todos --'));

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
        array('nome' => 'situacao',
            'label' => 'Situação:',
            'tipo' => 'combo',
            'array' => $situacaoCombo,
            'size' => 3,
            'padrao' => $situacao,
            'onChange' => 'formPadrao.submit();',
            'col' => 4,
            'linha' => 1),
        array('nome' => 'comeca',
            'label' => 'Começa em:',
            'tipo' => 'combo',
            'array' => $servidoresCombo,
            'size' => 3,
            'padrao' => $comeca,
            'onChange' => 'formPadrao.submit();',
            'col' => 10,
            'linha' => 2),
        array('nome' => 'termina',
            'label' => 'Termina em:',
            'tipo' => 'combo',
            'array' => $servidoresCombo,
            'size' => 3,
            'padrao' => $termina,
            'onChange' => 'formPadrao.submit();',
            'col' => 10,
            'linha' => 2),
    ));

    $menuRelatorio->set_formLink("?");
    $menuRelatorio->show();

    # Limita a página
    $grid = new Grid();
    $grid->abreColuna(12);

    /*
     * Dados Principais
     */

    # Inicializa o contador
    $contador = 0;

    # Grava no log a atividade
    $atividade = "Visualizou o Relatório Etiqueta Geral Ativos";
    $Objetolog = new Intra();
    $data = date("Y-m-d H:i:s");
    $Objetolog->registraLog($idUsuario, $data, $atividade);

    # Coluna
    $grid->fechaColuna();
    $grid->abreColuna(7);

    # Servidores
    $select = "SELECT idServidor,
                      tbpessoa.nome,
                      idFuncional,
                      matricula,
                      idServidor
                 FROM tbservidor JOIN tbperfil USING (idPerfil)
                                 JOIN tbpessoa USING (idPessoa)
                WHERE tbperfil.tipo <> 'Outros'";
    
    if ($comeca <> "*" AND $termina <> "*") {
        $select .= " AND tbpessoa.nome BETWEEN '{$comeca}' AND '{$termina}'";
    }

    if ($situacao <> "*") {
        $select .= " AND situacao = {$situacao}";
    }

    $select .= " ORDER BY tbpessoa.nome";
    $result = $pessoal->select($select);

    echo "<table width='100%' id='etiqueta' border='2px'>";

    foreach ($result as $item) {

        # Pega os dados
        $matricula = dv($item["matricula"]);
        $nome = $pessoal->get_nome($item["idServidor"]);
        $perfil = $pessoal->get_perfilSimples($item["idServidor"]);
        $cargo = $pessoal->get_cargoSigla($item["idServidor"]);
        $idPerfil = $pessoal->get_idPerfil($item["idServidor"]);

        # quantas etiquetas
        for ($i = 1; $i <= $numEtiquetas; $i++) {

            echo "<tr>";
            echo "<td style = 'width: 50%' align='center'>";
            p($nome, "pnomeEtiqueta4");
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
    }

    echo "</table>";

    $grid->fechaColuna();
    $grid->fechaGrid();

    $page->terminaPagina();
}