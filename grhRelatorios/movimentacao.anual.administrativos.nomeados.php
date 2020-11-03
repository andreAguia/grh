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
    $relatorioAno = post('ano', date('Y'));

    ######

    $select = 'SELECT tbservidor.idfuncional,
                      tbpessoa.nome,
                      tbservidor.idServidor,
                      CONCAT(tbtipocomissao.simbolo," - ",tbtipocomissao.descricao),
                      tbperfil.nome,                  
                      tbcomissao.dtNom,
                      tbcomissao.dtPublicNom,
                      MONTH(tbcomissao.dtNom)
                 FROM tbservidor JOIN tbpessoa ON (tbservidor.idPessoa = tbpessoa.idPessoa)                                
                                    JOIN tbperfil ON(tbservidor.idPerfil = tbperfil.idPerfil)
                                    JOIN tbdocumentacao ON (tbpessoa.idPessoa = tbdocumentacao.idPessoa)
                                    LEFT JOIN tbcomissao ON (tbservidor.idServidor = tbcomissao.idServidor)
                                    JOIN tbtipocomissao ON (tbcomissao.idTipoComissao = tbtipocomissao.idTipoComissao)
                WHERE YEAR(tbcomissao.dtNom) = "' . $relatorioAno . '"
                  AND (tbservidor.idCargo <> 128 AND tbservidor.idCargo <> 129)
             ORDER BY MONTH(tbcomissao.dtNom), tbcomissao.dtNom';


    $result = $servidor->select($select);

    $relatorio = new Relatorio();
    $relatorio->set_titulo('Relatório Anual de Administrativos & Técnicos Nomeados em ' . $relatorioAno);
    $relatorio->set_tituloLinha2('Em um Cargo em Comissao');
    $relatorio->set_subtitulo('Ordenado pela Data de Nomeação');

    $relatorio->set_label(array('IdFuncional', 'Nome', 'Lotação', 'Cargo em Comissão', 'Perfil', 'Nomeação', 'Publicação', 'Mês'));
    #$relatorio->set_width(array(10, 30, 10, 10, 10, 10, 10, 10));
    $relatorio->set_align(array('center', 'left','left','left'));
    $relatorio->set_funcao(array(null, null, null, null, null, "date_to_php", "date_to_php", "get_NomeMes"));
    
    $relatorio->set_classe(array(null, null, "pessoal"));
    $relatorio->set_metodo(array(null, null, "get_lotacao"));

    $relatorio->set_conteudo($result);
    $relatorio->set_numGrupo(7);
    $relatorio->set_botaoVoltar(false);
    $relatorio->set_formCampos(array(
        array('nome' => 'ano',
            'label' => 'Ano:',
            'tipo' => 'texto',
            'size' => 4,
            'title' => 'Ano',
            'padrao' => $relatorioAno,
            'onChange' => 'formPadrao.submit();',
            'col' => 3,
            'linha' => 1)));

    $relatorio->set_formFocus('ano');
    $relatorio->set_formLink('?');
    $relatorio->show();

    $page->terminaPagina();
}