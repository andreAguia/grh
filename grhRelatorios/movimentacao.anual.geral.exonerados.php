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
$acesso = Verifica::acesso($idUsuario, [1, 2, 12]);

if ($acesso) {
    # Conecta ao Banco de Dados
    $servidor = new Pessoal();

    # Começa uma nova página
    $page = new Page();
    $page->iniciaPagina();

    # Pega os parâmetros dos relatórios
    $relatorioAno = post('ano', date('Y'));

    # Monta o select
    $select = 'SELECT tbservidor.idfuncional,
                      tbpessoa.nome,
                      tbdocumentacao.cpf,
                      tbpessoa.dtNasc,
                      CONCAT(tbtipocomissao.simbolo," - ",tbtipocomissao.descricao),
                      tbservidor.idServidor,
                      tbperfil.nome,                  
                      tbcomissao.dtExo,
                      tbcomissao.dtPublicExo,
                      MONTH(tbcomissao.dtExo)
                 FROM tbservidor JOIN tbpessoa ON (tbservidor.idPessoa = tbpessoa.idPessoa)                                
                                    JOIN tbperfil ON(tbservidor.idPerfil = tbperfil.idPerfil)
                                    JOIN tbdocumentacao ON (tbpessoa.idPessoa = tbdocumentacao.idPessoa)
                                    LEFT JOIN tbcomissao ON (tbservidor.idServidor = tbcomissao.idServidor)
                                    JOIN tbtipocomissao ON (tbcomissao.idTipoComissao = tbtipocomissao.idTipoComissao)
                WHERE YEAR(tbcomissao.dtExo) = "' . $relatorioAno . '"
             ORDER BY MONTH(tbcomissao.dtExo), tbcomissao.dtExo';


    $result = $servidor->select($select);

    $relatorio = new Relatorio();
    $relatorio->set_titulo('Relatório Anual de Servidores Exonerados em ' . $relatorioAno);
    $relatorio->set_tituloLinha2('Em um Cargo em Comissao');
    $relatorio->set_subtitulo('Ordenado pela Data de Exoneração');

    $relatorio->set_label(array('IdFuncional', 'Nome', 'CPF', 'Nascimento', 'Cargo', 'Lotação', 'Perfil', 'Exoneração', 'Publicação', 'Mês'));
    $relatorio->set_align(array('center', 'left', 'center', 'center', 'left', 'left'));
    $relatorio->set_funcao(array(null, null, null, "date_to_php", null, null, null, "date_to_php", "date_to_php", "get_NomeMes"));

    $relatorio->set_classe(array(null, null, null, null, null, "pessoal"));
    $relatorio->set_metodo(array(null, null, null, null, null, "get_lotacao"));

    $relatorio->set_conteudo($result);
    $relatorio->set_numGrupo(9);
    
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
    $relatorio->show();

    $page->terminaPagina();
}