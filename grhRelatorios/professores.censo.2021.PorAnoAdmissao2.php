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
    $relatorioAno = post('ano', date('Y'));

    ######

    $select = "SELECT tbservidor.matricula,
                      tbpessoa.nome,
                      tbdocumentacao.CPF,
                      tbservidor.idServidor,
                      tbservidor.dtAdmissao,                      
                      tbpessoa.nomeMae,
                      dtNasc,
                      tbservidor.idServidor
                FROM tbservidor JOIN tbpessoa USING (idPessoa)
                                JOIN tbdocumentacao USING (idPessoa)
                                JOIN tbcargo USING (idCargo)
                                JOIN tbtipocargo USING (idTipoCargo)
               WHERE YEAR(tbservidor.dtAdmissao) = '{$relatorioAno}'
                 AND tbtipocargo.tipo = 'Professor'
                 AND (tbservidor.idPerfil = 1 OR tbservidor.idPerfil = 4)
            ORDER BY tbpessoa.nome";

    $result = $servidor->select($select);

    $relatorio = new Relatorio();
    $relatorio->set_titulo('Relatório de Professores');
    $relatorio->set_tituloLinha2("Admitidos em {$relatorioAno}");
    $relatorio->set_subtitulo('Ordenados pelo Nome');
    $relatorio->set_label(["Matrícula", "Nome", "CPF", "Email", "Admissão", "Nome da Mãe", "Nascimento", "Situação"]);
    $relatorio->set_align(["center", "left", "center", "left", "center", "left"]);
    $relatorio->set_funcao(["dv", null, null, null, "date_to_php", null, "date_to_php"]);

    $relatorio->set_classe([null, null, null, "pessoal", null, null, null, "pessoal"]);
    $relatorio->set_metodo([null, null, null, "get_emails", null, null, null, "get_Situacao"]);

    $relatorio->set_conteudo($result);
    $relatorio->set_formCampos(array(
        array('nome' => 'ano',
            'label' => 'Ano:',
            'tipo' => 'texto',
            'size' => 4,
            'title' => 'Ano',
            'onChange' => 'formPadrao.submit();',
            'padrao' => $relatorioAno,
            'col' => 3,
            'linha' => 1)));

    $relatorio->show();
    $page->terminaPagina();
}