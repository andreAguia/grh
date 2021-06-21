<?php

/**
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
    $de = post('de', date('Y'));
    $para = post('para', date('Y'));

    ###### Relatório 1

    $select = 'SELECT tbservidor.idfuncional,
                      tbpessoa.nome,
                      tbdocumentacao.cpf,
                      tbpessoa.dtNasc,
                      tbservidor.idServidor,
                      tbservidor.idServidor,
                      tbperfil.nome,
                      tbservidor.dtAdmissao,
                      tbservidor.dtDemissao,
                      tbservidor.dtPublicExo,
                      tbmotivo.motivo
                 FROM tbservidor LEFT JOIN tbpessoa ON (tbservidor.idPessoa = tbpessoa.idPessoa)                                
                                 LEFT JOIN tbperfil ON(tbservidor.idPerfil = tbperfil.idPerfil)
                                 LEFT JOIN tbdocumentacao ON (tbpessoa.idPessoa = tbdocumentacao.idPessoa)
                                 LEFT JOIN tbmotivo ON (tbservidor.motivo = tbmotivo.idMotivo)
                WHERE YEAR(tbservidor.dtDemissao) >= "' . $de . '"
                  AND YEAR(tbservidor.dtDemissao) <= "' . $para . '"
             ORDER BY dtDemissao';

    $result = $servidor->select($select);

    $relatorio = new Relatorio();
    if ($de == $para) {
        $relatorio->set_titulo('Relatório Anual de Servidores Exonerados, Aposentados e Demitidos em ' . $de);
    } else {
        $relatorio->set_titulo("Relatório Anual de Servidores Exonerados, Aposentados e Demitidos de {$de} a {$para}");
    }
    $relatorio->set_subtitulo('Ordenado pela Data de Demissão');

    $relatorio->set_label(array('IdFuncional', 'Nome', 'CPF', 'Nascimento', 'Cargo', 'Lotação', 'Perfil', 'Admissão', 'Saída', 'Publicação', 'Motivo'));
    $relatorio->set_align(array('center', 'left', 'center', 'center', 'left', 'left', 'center', 'center', 'center', 'center', 'left'));
    $relatorio->set_funcao(array(null, null, null, "date_to_php", null, null, null, "date_to_php", "date_to_php", "date_to_php"));

    $relatorio->set_classe(array(null, null, null, null, "pessoal", "pessoal"));
    $relatorio->set_metodo(array(null, null, null, null, "get_cargo", "get_lotacao"));

    $relatorio->set_conteudo($result);
    
    $relatorio->set_formCampos(array(
        array('nome' => 'de',
            'label' => 'De:',
            'tipo' => 'texto',
            'size' => 4,
            'title' => 'Ano',
            'onChange' => 'formPadrao.submit();',
            'padrao' => $de,
            'col' => 3,
            'linha' => 1),
        array('nome' => 'para',
            'label' => 'Para:',
            'tipo' => 'texto',
            'size' => 4,
            'title' => 'Ano',
            'onChange' => 'formPadrao.submit();',
            'padrao' => $para,
            'col' => 3,
            'linha' => 1),
    ));

    $relatorio->set_formFocus('ano');
    $relatorio->set_formLink('?');
    $relatorio->show();

    $page->terminaPagina();
}