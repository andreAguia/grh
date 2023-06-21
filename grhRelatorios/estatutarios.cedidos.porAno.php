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

    $select = "SELECT tbservidor.idFuncional,
                     tbpessoa.nome,
                     tbhistcessao.orgao,
                     tbhistcessao.dtInicio,
                     tbhistcessao.dtFim,
                     year(dtInicio),
                     tbservidor.idServidor
                FROM tbservidor LEFT JOIN tbpessoa ON (tbservidor.idPessoa = tbpessoa.idPessoa)
                                RIGHT JOIN tbhistcessao ON(tbservidor.idServidor = tbhistcessao.idServidor)
               WHERE tbservidor.idPerfil = 1";
    
    $select .= " AND (((YEAR(tbhistcessao.dtInicio) = '{$relatorioAno}') OR (YEAR(tbhistcessao.dtFim) = '{$relatorioAno}'))
                  OR ((YEAR(tbhistcessao.dtInicio) < '{$relatorioAno}') AND (YEAR(tbhistcessao.dtFim) > '{$relatorioAno}'))
                  OR ((YEAR(tbhistcessao.dtInicio) <= '{$relatorioAno}') AND tbhistcessao.dtFim IS NULL))";
    
    $select .= ' ORDER BY nome';

    $result = $servidor->select($select);

    $relatorio = new Relatorio();
    $relatorio->set_titulo("Relatorio Geral de Estatutários");
    $relatorio->set_subtitulo("Cedidos em {$relatorioAno}");

    $relatorio->set_label(['IdFuncional', 'Nome', 'Órgão', 'Início', 'Término', 'Ano', 'Situação ']);
    $relatorio->set_width([10, 30, 30, 10, 10, 0, 10]);
    $relatorio->set_align(["center", "left", "left", "left"]);
    $relatorio->set_funcao([null, null, null, "date_to_php", "date_to_php"]);
    $relatorio->set_classe([null, null, null, null, null, null, "Pessoal"]);
    $relatorio->set_metodo([null, null, null, null, null, null, "get_Situacao"]);
    $relatorio->set_conteudo($result);

    # Cria um array com os anos possíveis
    $anoInicial = 1999;
    $anoAtual = date('Y');
    $anoExercicio = arrayPreenche($anoInicial, $anoAtual + 2, "d");

    $relatorio->set_formCampos(array(
        array('nome' => 'ano',
            'label' => 'Ano:',
            'tipo' => 'combo',
            'array' => $anoExercicio,
            'size' => 4,
            'title' => 'Ano',
            'col' => 3,
            'padrao' => $relatorioAno,
            'onChange' => 'formPadrao.submit();',
            'linha' => 1)));

    $relatorio->set_formFocus('ano');
    $relatorio->set_formLink('?');
    $relatorio->show();

    $page->terminaPagina();
}