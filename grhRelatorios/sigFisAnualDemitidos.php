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
                      tbmotivo.motivo,
                      MONTH(tbservidor.dtDemissao)
                 FROM tbservidor LEFT JOIN tbpessoa ON (tbservidor.idPessoa = tbpessoa.idPessoa)                                
                                 LEFT JOIN tbperfil ON(tbservidor.idPerfil = tbperfil.idPerfil)
                                 LEFT JOIN tbdocumentacao ON (tbpessoa.idPessoa = tbdocumentacao.idPessoa)
                                 LEFT JOIN tbmotivo ON (tbservidor.motivo = tbmotivo.idMotivo)
                WHERE YEAR(tbservidor.dtDemissao) = "' . $relatorioAno . '"
                  AND tbservidor.idPerfil <> 10  
             ORDER BY MONTH(tbservidor.dtDemissao), dtDemissao';


    $result = $servidor->select($select);

    $relatorio = new Relatorio();
    $relatorio->set_titulo('Relatório Anual de Servidores Exonerados, Aposentados e Demitidos em ' . $relatorioAno);
    $relatorio->set_tituloLinha2('Demitidos da UENF');
    $relatorio->set_subtitulo('Ordenado pela Data de Demissão');

    $relatorio->set_label(array('IdFuncional', 'Nome', 'CPF', 'Nascimento', 'Cargo', 'Lotação', 'Perfil', 'Admissão', 'Saída', 'Publicação', 'Motivo', 'Mês'));
    #$relatorio->set_width(array(10,20,10,10,10,10,10,10,10,10));
    $relatorio->set_align(array('center', 'left', 'center', 'center', 'left', 'left'));
    $relatorio->set_funcao(array(null, null, null, "date_to_php", null, null, null, "date_to_php", "date_to_php", "date_to_php", null, "get_NomeMes"));

    $relatorio->set_classe(array(null, null, null, null, "pessoal", "pessoal"));
    $relatorio->set_metodo(array(null, null, null, null, "get_cargo", "get_lotacao"));

    $relatorio->set_conteudo($result);
    $relatorio->set_numGrupo(11);
    $relatorio->set_botaoVoltar(false);
    $relatorio->set_dataImpressao(false);
    #$relatorio->set_totalRegistro(false);
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

    ###### Relatório 2
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
    $relatorio->set_titulo(null);
    $relatorio->set_tituloLinha2('Exonerados em um Cargo em Comissao');
    $relatorio->set_subtitulo('Ordenado pela Data de Exoneração');

    $relatorio->set_label(array('IdFuncional', 'Nome', 'CPF', 'Nascimento', 'Cargo', 'Lotação', 'Perfil', 'Exoneração', 'Publicação', 'Mês'));
    #$relatorio->set_width(array(10,20,10,10,10,10,10,10,10));
    $relatorio->set_align(array('center', 'left', 'center', 'center', 'left', 'left'));
    $relatorio->set_funcao(array(null, null, null, "date_to_php", null, null, null, "date_to_php", "date_to_php", "get_NomeMes"));

    $relatorio->set_classe(array(null, null, null, null, null, "pessoal"));
    $relatorio->set_metodo(array(null, null, null, null, null, "get_lotacao"));

    $relatorio->set_conteudo($result);
    $relatorio->set_numGrupo(9);
    $relatorio->set_botaoVoltar(false);
    $relatorio->set_menuRelatorio(false);
    $relatorio->set_cabecalhoRelatorio(false);
    $relatorio->set_log(false);
    $relatorio->show();

    $page->terminaPagina();
}