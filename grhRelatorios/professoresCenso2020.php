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
    $relatorio->set_label(['Nome', 'CPF', 'Lotação', 'Email', 'Nome da Mãe', 'Admissão', 'Saída']);
    $relatorio->set_width([20, 10, 20, 10, 20, 10, 10]);
    $relatorio->set_align(["left", "left", "left", "left", "left"]);
    $relatorio->set_funcao([null, null, null, null, null, "date_to_php", "date_to_php"]);

    $relatorio->set_classe([null, null, "pessoal", "pessoal"]);
    $relatorio->set_metodo([null, null, "get_lotacao", "get_emails"]);

    $relatorio->set_conteudo($result);

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