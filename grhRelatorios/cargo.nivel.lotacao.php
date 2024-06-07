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
    $pessoal = new Pessoal();

    # Começa uma nova página
    $page = new Page();
    $page->iniciaPagina();

    # Pega os parâmetros dos relatórios
    $parametroPerfil = post('perfil', '*');

    ######
    # Pega as diretorias ativas
    $select1 = 'SELECT DISTINCT dir
                FROM tblotacao
               WHERE ativo';

    $diretorias = $pessoal->select($select1);

    # Pega os cargos
    $select2 = 'SELECT idtipocargo, cargo
                FROM tbtipocargo
            ORDER BY idTipoCargo';

    $cargos = $pessoal->select($select2);
    $numeroCargos = $pessoal->count($select2);

    # Cria um array onde terá os resultados
    $resultado = array();

    # Cria e preenche o array do total da coluna
    #$totalColuna = array();
    $totalColuna = array_fill(0, $numeroCargos + 2, 0);

    # Cria e preenche o array do label
    $label = array("Diretoria");
    foreach ($cargos as $cc) {
        $label[] = $cc[1];
    }
    $label[] = "Total";

    # Zera o contador de linha
    $linha = 0;

    # Trata o parametro do perfil transformanto * em nulo
    if ($parametroPerfil == "*") {
        $idPerfil = null;
    } else {
        $idPerfil = $parametroPerfil;
    }

    # Percorre as diretorias
    foreach ($diretorias as $dd) {
        $resultado[$linha][0] = $dd[0];     // Sigoa da Diretoria 
        $coluna = 1;                        // Inicia a coluna
        $totalLinha = 0;                    // Zera totalizador de cada linha
        # Percorre as colunas / Cargos
        foreach ($cargos as $cc) {
            $quantidade = $pessoal->get_numServidoresAtivosCargoLotacao($cc[0], $dd[0], $idPerfil);    // Pega a quantidade de servidores
            $resultado[$linha][$coluna] = $quantidade;                                        // Joga para o array de exibição
            $totalLinha = $totalLinha + $quantidade;                                          // Soma o total da linha a quantidade da coluna
            $totalColuna[$coluna] += $quantidade;                                             // Soma o total da coluna a quantidade da linha
            $coluna++;
        }

        # Faz a última coluna com o total da linha
        $resultado[$linha][$coluna] = $totalLinha;
        $totalColuna[$coluna] += $totalLinha;
        $linha++;
    }

    $relatorio = new Relatorio();
    $relatorio->set_titulo('Relatório de Quantidade de Servidores Ativos por Diretoria / Cargo');
    $relatorio->set_label($label);
    $relatorio->set_align(["left"]);
    $relatorio->set_conteudo($resultado);
    $relatorio->set_totalRegistro(false);
    $relatorio->set_colunaSomatorio([1, 2, 3, 4, 5, 6, 7]);

    if ($parametroPerfil <> "*") {
        $relatorio->set_subtitulo($pessoal->get_nomePerfil($parametroPerfil));
    }

    $listaPerfil = $pessoal->select('SELECT idPerfil, 
                                            nome,
                                            tipo
                                       FROM tbperfil
                                      WHERE tipo <> "Outros"
                                   ORDER BY tipo, nome');
    array_unshift($listaPerfil, array('*', '-- Todos --'));

    $relatorio->set_formCampos(array(
        array('nome' => 'perfil',
            'label' => 'Perfil:',
            'tipo' => 'combo',
            'optgroup' => true,
            'array' => $listaPerfil,
            'size' => 30,
            'col' => 4,
            'padrao' => $parametroPerfil,
            'onChange' => 'formPadrao.submit();',
            'linha' => 1)));

    $relatorio->show();
    $page->terminaPagina();
}