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
    $sexo = post('sexo', "Feminino");

    ######

    $select = 'SELECT tbservidor.idFuncional,
                     tbpessoa.nome,
                     tbservidor.idServidor,
                     tbservidor.idServidor,
                     tbservidor.dtAdmissao,
                     tbpessoa.dtNasc,
                     tbservidor.idServidor,
                     tbservidor.idServidor,
                     tbservidor.idServidor
                FROM tbservidor LEFT JOIN tbpessoa ON (tbservidor.idPessoa = tbpessoa.idPessoa)
               WHERE tbservidor.situacao = 1
                 AND idPerfil = 1
                 AND tbpessoa.sexo = "' . $sexo . '"
            ORDER BY tbpessoa.dtNasc';

    $result = $servidor->select($select);

    $relatorio = new Relatorio();
    $relatorio->set_titulo('Relatório de Estatutários com Idade para Aposentadoria');
    $relatorio->set_subtitulo('Servidores do Sexo ' . $sexo);
    $relatorio->set_label(array('IdFuncional', 'Nome', 'Cargo', 'Lotaçao', 'Admissão', 'Nascimento', 'Idade', 'Aposentadoria', 'Compulsória'));
    #$relatorio->set_width(array(10,30,30,0,10,10,10));
    $relatorio->set_align(array("center", "left", "left", "left"));
    $relatorio->set_funcao(array(null, null, null, null, "date_to_php", "date_to_php"));

    $relatorio->set_classe(array(null, null, "pessoal", "pessoal", null, null, "pessoal", "pessoal", "pessoal"));
    $relatorio->set_metodo(array(null, null, "get_CargoRel", "get_LotacaoRel", null, null, "get_idade", "get_dataAposentadoria", "get_dataCompulsoria"));

    $relatorio->set_conteudo($result);
    #$relatorio->set_numGrupo(3);
    #$relatorio->set_botaoVoltar('../sistema/areaServidor.php');

    $relatorio->set_formCampos(array(
        array('nome' => 'sexo',
            'label' => 'Sexo:',
            'tipo' => 'combo',
            'array' => array("Masculino", "Feminino"),
            'size' => 30,
            'padrao' => $sexo,
            'onChange' => 'formPadrao.submit();',
            'col' => 4,
            'linha' => 1)));

    $relatorio->show();
    $page->terminaPagina();
}