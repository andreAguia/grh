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
$acesso = Verifica::acesso($idUsuario, 2);

if ($acesso) {
    # Conecta ao Banco de Dados
    $servidor = new Pessoal();

    # Começa uma nova página
    $page = new Page();
    $page->iniciaPagina();

    # Pega o parâmetro do relatório
    $cargo = post('cargo', "Adm/Tec");

    # Pega o parâmetro do ano
    $parametroAno = post('parametroAno', date('Y'));

    ######

    $select = 'SELECT tbservidor.idfuncional,
                     tbpessoa.nome,
                     tbservidor.idServidor,
                     tbservidor.idServidor,
                     dtAdmissao,
                     dtDemissao
                FROM tbservidor JOIN tbpessoa USING (idPessoa)
                                JOIN tbdocumentacao USING (idPessoa)
                                JOIN tbcargo USING (idCargo)
                                JOIN tbtipocargo USING (idTipoCargo)
               WHERE year(dtAdmissao) <= "' . $parametroAno . '"
                 AND (dtDemissao IS null OR year(dtDemissao) >= "' . $parametroAno . '")
                 AND tbtipocargo.tipo = "' . $cargo . '"  
            ORDER BY tbpessoa.nome';

    $result = $servidor->select($select);

    $relatorio = new Relatorio();
    $relatorio->set_titulo('Histórico de Servidores');
    $relatorio->set_tituloLinha2($cargo . " Ativos em  " . $parametroAno);
    $relatorio->set_subtitulo('Ordenados pelo Nome');
    $relatorio->set_label(array('IdFuncional', 'Nome', 'Cargo', 'Perfil', 'Admissão', 'Saída'));
    #$relatorio->set_width(array(10,30,16,22,22));
    $relatorio->set_align(array("center", "left", "left"));
    $relatorio->set_funcao(array(null, null, null, null, "date_to_php", "date_to_php"));

    $relatorio->set_classe(array(null, null, "pessoal", "pessoal"));
    $relatorio->set_metodo(array(null, null, "get_cargoSimples", "get_perfil"));

    $relatorio->set_conteudo($result);

    # Seleciona o tipo de cargo
    $listaCargo = $servidor->select('SELECT distinct tipo,tipo from tbtipocargo');

    $relatorio->set_formCampos(array(
        array('nome' => 'cargo',
            'label' => 'Tipo de Cargo:',
            'tipo' => 'combo',
            'array' => $listaCargo,
            'size' => 20,
            'col' => 3,
            'padrao' => $cargo,
            'title' => 'Mês',
            'onChange' => 'formPadrao.submit();',
            'linha' => 1),
        array('nome' => 'parametroAno',
            'label' => 'Ano:',
            'tipo' => 'texto',
            'size' => 10,
            'padrao' => $parametroAno,
            'title' => 'Ano',
            'onChange' => 'formPadrao.submit();',
            'col' => 3,
            'linha' => 1)));

    $relatorio->set_formFocus('cargo');
    $relatorio->set_formLink('?');

    $relatorio->show();

    $page->terminaPagina();
}