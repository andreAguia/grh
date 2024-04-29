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

    # Pega a Situação
    $situacao = post('situacao', "*");

    ######

    $select = "SELECT '[', '', ']',
                      tbservidor.matricula,
                      tbpessoa.nome,
                      tbservidor.idServidor,
                      tbservidor.idServidor,
                      tbservidor.idServidor
                FROM tbservidor LEFT JOIN tbpessoa ON (tbservidor.idPessoa = tbpessoa.idPessoa)
                                     JOIN tbperfil USING (idPerfil)     
               WHERE tbperfil.tipo <> 'Outros'";

    if ($situacao <> "*") {
        $select .= " AND situacao = {$situacao}";
    }

    $select .= " ORDER BY matricula";

    $result = $servidor->select($select);

    $relatorio = new Relatorio();
    $relatorio->set_titulo('Relatório de Servidores');

    if ($situacao <> "*") {
        $relatorio->set_subtitulo($servidor->get_nomeSituacao($situacao));
    }

    $relatorio->set_label(["", "", "", "Matrícula", "Nome", "Lotação", "Perfil", "Sitiação"]);
    $relatorio->set_width([3, 2, 3, 10, 38, 38, 10]);
    $relatorio->set_align(["right", null, "left", "center", "left", "left"]);

    $relatorio->set_classe([null, null, null, null, null, "pessoal", "pessoal", "pessoal"]);
    $relatorio->set_metodo([null, null, null, null, null, "get_cargoSimples", "get_perfil", "get_situacao"]);

    $relatorio->set_funcao([null, null, null, "dv"]);

    $relatorio->set_bordaInterna(true);
    $relatorio->set_conteudo($result);

    # Situação
    $situacaoCombo = $servidor->select('SELECT idsituacao, situacao
                                          FROM tbsituacao                                
                                      ORDER BY 1');
    array_unshift($situacaoCombo, array('*', '-- Todos --'));

    $relatorio->set_formCampos(array(
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

    $relatorio->set_formFocus('situacao');
    $relatorio->set_formLink("?");

    $relatorio->show();

    $page->terminaPagina();
}
