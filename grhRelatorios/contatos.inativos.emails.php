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
    $servidor = new Pessoal();

    # Começa uma nova página
    $page = new Page();
    $page->iniciaPagina();

    # Pega os parâmetros dos relatórios
    $situacao = get('situacao', post('situacao'));
    if ($situacao == "*") {
        $situacao = null;
    }

    $desde = get('desde', post('desde', 1990));
    $subTitulo = "Desde {$desde}";

    ######

    $relatorio = new Relatorio();

    $select = "SELECT tbpessoa.nome,
                      tbservidor.idServidor,
                      tbservidor.idServidor,
                      dtDemissao,
                      tbservidor.idServidor
                 FROM tbservidor JOIN tbpessoa USING(idpessoa)                
                WHERE tbservidor.situacao <> 1
                  AND idPerfil = 1
                  AND YEAR(dtDemissao) >= {$desde}";

    if (!is_null($situacao)) {
        $select .= " AND situacao = {$situacao}";
        $subTitulo .= "<br/>" . $servidor->get_nomeSituacao($situacao);
    }

    $select .= " ORDER BY dtDemissao";
    
    $result = $servidor->select($select);

    $relatorio->set_titulo('Relatório de Emails dos Servidores Estatutários Inativos');
    $relatorio->set_subtitulo('Ordenados pela data de Saída');
    $relatorio->set_subtitulo2($subTitulo);
    $relatorio->set_label(['Servidor', 'Cargo', 'Email', 'Saída', 'Situação']);
    $relatorio->set_width([30, 20, 30, 10, 10]);
    $relatorio->set_align(["left", "left"]);
    $relatorio->set_classe([null, "pessoal", "pessoal", null, "pessoal"]);
    $relatorio->set_metodo([null, "get_cargoSimples", "get_emails", null, "get_situacao"]);
    $relatorio->set_funcao([null, null, null, "date_to_php"]);
    $relatorio->set_conteudo($result);
    $relatorio->set_bordaInterna(true);

    $listaSituacao = $servidor->select('SELECT idSituacao, situacao
                                           FROM tbsituacao
                                          WHERE idSituacao <> 1 
                                          ORDER BY situacao');

    array_unshift($listaSituacao, array('*', '-- Todos --'));

    $listaDesde = arrayPreenche(1990, date('Y'), "d");

    $relatorio->set_formCampos(array(
        array(
            'nome' => 'situacao',
            'label' => 'Situacao:',
            'tipo' => 'combo',
            'array' => $listaSituacao,
            'size' => 30,
            'col' => 3,
            'padrao' => $situacao,
            'onChange' => 'formPadrao.submit();',
            'linha' => 1),
        array(
            'nome' => 'desde',
            'label' => 'Desde:',
            'tipo' => 'combo',
            'array' => $listaDesde,
            'size' => 30,
            'col' => 3,
            'padrao' => $desde,
            'onChange' => 'formPadrao.submit();',
            'linha' => 1)
    ));

    $relatorio->set_formFocus('situacao');
    $relatorio->set_formLink('?');
    $relatorio->show();

    $page->terminaPagina();
}