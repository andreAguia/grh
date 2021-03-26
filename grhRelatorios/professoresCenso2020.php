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

    # Pega o parâmetro do ano
    $parametroAno = post('parametroAno', date('Y'));

    ######

    $select = 'SELECT tbpessoa.nome,
                     tbdocumentacao.CPF,
                     tbservidor.idServidor,
                     tbservidor.idServidor,
                     tbpessoa.nomeMae,
                     dtAdmissao,
                     dtDemissao
                FROM tbservidor JOIN tbpessoa USING (idPessoa)
                                JOIN tbdocumentacao USING (idPessoa)
                                JOIN tbcargo USING (idCargo)
                                JOIN tbtipocargo USING (idTipoCargo)
               WHERE year(dtAdmissao) <= "' . $parametroAno . '"
                 AND (dtDemissao IS null OR year(dtDemissao) >= "' . $parametroAno . '")
                 AND tbtipocargo.tipo = "Professor"
                 AND (tbservidor.idPerfil = 1 OR tbservidor.idPerfil = 4)
            ORDER BY tbpessoa.nome';

    $result = $servidor->select($select);

    $relatorio = new Relatorio();
    $relatorio->set_titulo('Censo de Docentes');
    $relatorio->set_tituloLinha2("Docentes Ativos em  " . $parametroAno);
    $relatorio->set_subtitulo('Ordenados pelo Nome');
    $relatorio->set_label(array('Nome', 'CPF', 'Lotação', 'Email', 'Nome da Mãe', 'Admissão', 'Saída'));
    $relatorio->set_width(array(20, 10, 20, 10, 20, 10, 10));
    $relatorio->set_align(array("left", "left", "left", "left", "left"));
    $relatorio->set_funcao(array(null, null, null, null, null, "date_to_php", "date_to_php"));

    $relatorio->set_classe(array(null, null, "pessoal", "pessoal"));
    $relatorio->set_metodo(array(null, null, "get_lotacao", "get_emails"));

    $relatorio->set_conteudo($result);

    # Seleciona o tipo de cargo
    $listaCargo = $servidor->select('SELECT distinct tipo,tipo from tbtipocargo');

    $relatorio->set_formCampos(array(
        array('nome' => 'parametroAno',
            'label' => 'Ano:',
            'tipo' => 'texto',
            'size' => 10,
            'padrao' => $parametroAno,
            'title' => 'Ano',
            'onChange' => 'formPadrao.submit();',
            'col' => 3,
            'linha' => 1)));

    $relatorio->set_formFocus('parametroAno');
    $relatorio->set_formLink('?');

    $relatorio->show();

    $page->terminaPagina();
}