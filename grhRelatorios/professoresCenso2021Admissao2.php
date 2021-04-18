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

    # Pega os parâmetros dos relatórios
    $relatorioAno = post('ano', date('Y'));

    ######

    $select = "SELECT tbservidor.matricula,
                      tbpessoa.nome,
                      tbdocumentacao.CPF,
                      tbservidor.idServidor,
                      tbservidor.dtAdmissao,                      
                      tbpessoa.nomeMae,
                      month(dtNasc)
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
    $relatorio->set_titulo("Relatório de Docentes Admitidos em {$relatorioAno}");
    $relatorio->set_subtitulo('Ordenados pelo Nome');
    $relatorio->set_label(array("Matrícula", "Nome", "CPF", "Email", "Admissão","Nome da Mãe","Mês do Nascimento"));
    #$relatorio->set_width(array(10, 25, 15, 25, 20));
    $relatorio->set_align(array("center", "left", "center", "left", "center","left"));
    $relatorio->set_funcao(array("dv", null, null, null, "date_to_php", null, "get_nomeMes"));

    $relatorio->set_classe(array(null, null, null, "pessoal"));
    $relatorio->set_metodo(array(null, null, null, "get_emails"));

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

    $relatorio->set_formFocus('ano');
    $relatorio->set_formLink('?');
    $relatorio->set_bordaInterna(true);
    $relatorio->show();

    $page->terminaPagina();
}