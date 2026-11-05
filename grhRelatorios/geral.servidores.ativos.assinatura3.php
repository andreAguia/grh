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

    # Parâmetros
    $letraInicial = maiuscula(post("letraInicial", "A"));
    $letraFinal = maiuscula(post("letraFinal", "Z"));

    ######

    $select = "SELECT tbpessoa.nome,'',LEFT(tbpessoa.nome, 1) as pl
                 FROM tbservidor LEFT JOIN tbpessoa ON (tbservidor.idPessoa = tbpessoa.idPessoa)
                                      JOIN tbperfil USING (idPerfil)     
               WHERE tbservidor.situacao = 1
                 AND LEFT(tbpessoa.nome, 1) BETWEEN '{$letraInicial}' AND '{$letraFinal}'
                 AND tbperfil.tipo <> 'Outros'
             ORDER BY tbpessoa.nome";

    $result = $servidor->select($select);

    $relatorio = new Relatorio();
    $relatorio->set_titulo('Entrega do Kit de Credenciamento');
    #$relatorio->set_subtitulo('Ordenados pelo Nome');
    $relatorio->set_label(['Nome', 'Assinatura', "pl"]);
    $relatorio->set_width([50, 50]);
    $relatorio->set_align(["left"]);
    #$relatorio->set_funcao(["maiuscula"]);
    $relatorio->set_bordaInterna(true);
    $relatorio->set_espacamento(6);
    $relatorio->set_numeroOrdem(true);
    $relatorio->set_conteudo($result);
    $relatorio->set_numGrupo(2);

    $relatorio->set_formCampos(array(
        array('nome' => 'letraInicial',
            'label' => 'Letre Inicial:',
            'tipo' => 'texto',
            'autofocus' => true,
            'col' => 4,
            'size' => 5,
            'padrao' => $letraInicial,
            'onChange' => 'formPadrao.submit();',
            'linha' => 1),
        array('nome' => 'letraFinal',
            'label' => 'Letre Final:',
            'tipo' => 'texto',
            'col' => 4,
            'size' => 5,
            'padrao' => $letraFinal,
            'onChange' => 'formPadrao.submit();',
            'linha' => 1),
    ));

    $relatorio->show();

    $page->terminaPagina();
}