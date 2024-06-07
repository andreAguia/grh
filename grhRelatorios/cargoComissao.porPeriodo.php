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

    # Pega os valores
    $parametroCargo = post('parametroCargo', 13);
    $parametroDescricao = post('parametroDescricao', 'Todos');

    $parametrodtInicial = post('parametrodtInicial', null);
    $parametrodtFinal = post('parametrodtFinal', null);

    # Começa uma nova página
    $page = new Page();
    $page->iniciaPagina();

    ######

    $subTitulo = null;

    # select
    $select = "SELECT tbcomissao.idServidor,
                      tbcomissao.idComissao,
                      tbcomissao.idComissao,
                      tbcomissao.idDescricaoComissao,
                      tbcomissao.idComissao,
                      tbcomissao.dtExo
                 FROM tbservidor LEFT JOIN tbpessoa USING (idPessoa)
                                 LEFT JOIN tbcomissao USING(idServidor)
                                 LEFT JOIN tbdescricaocomissao USING (idDescricaoComissao)
                                      JOIN tbtipocomissao ON(tbcomissao.idTipoComissao=tbtipocomissao.idTipoComissao)
                                      JOIN tbtiponomeacao ON (tbcomissao.tipo = tbtiponomeacao.idTipoNomeacao)
                WHERE tbtiponomeacao.visibilidade <> 2
                  AND tbtipocomissao.idTipoComissao = {$parametroCargo}
                  AND tbcomissao.tipo <> 3";

    # Descrição
    if ($parametroDescricao <> "Todos") {
        $select .= " AND tbcomissao.idDescricaoComissao = {$parametroDescricao}";
    }

    # Período
    if (!empty($parametrodtInicial)) {
        $select .= " AND ((tbcomissao.dtNom <= '{$parametrodtInicial}' AND tbcomissao.dtExo >= '{$parametrodtInicial}')
                      OR (tbcomissao.dtNom BETWEEN '{$parametrodtInicial}' AND '{$parametrodtFinal}')  
                      OR (tbcomissao.dtExo BETWEEN '{$parametrodtInicial}' AND '{$parametrodtFinal}')
                      OR (tbcomissao.dtNom <= '{$parametrodtInicial}' AND tbcomissao.dtExo IS null)
                      )";
    }

    $select .= " ORDER BY tbdescricaocomissao.descricao, tbcomissao.dtNom desc";

    $result = $pessoal->select($select);

    # Monta o Relatório
    $relatorio = new Relatorio();
    $relatorio->set_titulo("Histórico de Servidores Nomeados");
    $relatorio->set_subtitulo2($pessoal->get_nomeCargoComissao($parametroCargo));
    $relatorio->set_subtitulo("Período " . date_to_php($parametrodtInicial) . " a " . date_to_php($parametrodtFinal));
    $relatorio->set_label(['Nome', 'Nomeação', 'Exoneração', 'Descrição', 'Ocupante Anterior']);
    $relatorio->set_conteudo($result);
    $relatorio->set_align(["left", "left", "left", "left", "left"]);
    $relatorio->set_classe(["Pessoal", "CargoComissao", "CargoComissao", "CargoComissao", "CargoComissao"]);
    $relatorio->set_metodo(["get_nomeECargoSimplesEPerfil", "exibeDadosNomeacao", "exibeDadosExoneracao", "get_descricao", "exibeOcupanteAnterior"]);
    $relatorio->set_bordaInterna(true);
    $relatorio->set_numGrupo(3);

    $cargo = $pessoal->select('SELECT tbtipocomissao.idTipoComissao,concat(tbtipocomissao.simbolo," - ",tbtipocomissao.descricao)
                                              FROM tbtipocomissao
                                              WHERE ativo
                                          ORDER BY tbtipocomissao.simbolo');

    $select = 'SELECT idDescricaoComissao,
                              tbdescricaocomissao.descricao
                         FROM tbdescricaocomissao JOIN tbtipocomissao USING (idTipoComissao)
                        WHERE tbdescricaocomissao.idTipoComissao = ' . $parametroCargo . '
                     ORDER BY tbtipocomissao.simbolo, tbtipocomissao.descricao, tbdescricaocomissao.descricao';

    $descricao = $pessoal->select($select);

    array_unshift($descricao, array("Todos", "Todos"));

    $relatorio->set_formCampos([
        ['nome' => 'parametroCargo',
            'label' => 'Cargo em Comissão:',
            'tipo' => 'combo',
            'array' => $cargo,
            'size' => 30,
            'padrao' => $parametroCargo,
            'title' => 'Filtra por Cargo em Comissão',
            'linha' => 1],
        ['nome' => 'parametroDescricao',
            'label' => 'Descrição:',
            'tipo' => 'combo',
            'array' => $descricao,
            'size' => 30,
            'padrao' => $parametroDescricao,
            'title' => 'Filtra por Cargo em Comissão',
            'linha' => 1],
        ['nome' => 'parametrodtInicial',
            'label' => 'Início:',
            'tipo' => 'data',
            'size' => 4,
            'title' => 'Insira a data inicial',
            'col' => 3,
            'padrao' => $parametrodtInicial,
            'linha' => 1],
        ['nome' => 'parametrodtFinal',
            'label' => 'Término:',
            'tipo' => 'data',
            'size' => 4,
            'title' => 'Insira a data final',
            'col' => 3,
            'padrao' => $parametrodtFinal,
            'linha' => 1],
        ['nome' => 'submit',
            'valor' => 'Atualiza',
            'label' => '-',
            'size' => 4,
            'col' => 3,
            'tipo' => 'submit',
            'title' => 'Atualiza a tabela',
            'linha' => 1],
    ]);

    $relatorio->show();
    $page->terminaPagina();
}