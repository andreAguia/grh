<?php

/**
 * Relatório Mensal de Vencimento de Triênio
 * 
 * Triênio
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
    $pessoal = new Pessoal();

    # Começa uma nova página
    $page = new Page();
    $page->set_title("Servidores com Auxílio Creche");
    $page->iniciaPagina();

    # Pega os parâmetros dos relatórios
    $relatorioMes = post('mes', date('m'));
    $relatorioAno = post('ano', date('Y'));

    ######

    $select = "SELECT tbservidor.idfuncional,
                      tbpessoa.nome,
                      tbdependente.nome,
                      tbdependente.dtNasc,
                      dtTermino,
                      ciExclusao,
                      processo
                 FROM tbdependente JOIN tbpessoa ON(tbpessoa.idpessoa = tbdependente.idpessoa)
                                   JOIN tbservidor ON (tbservidor.idPessoa = tbpessoa.idPessoa)
                                   JOIN tbperfil USING (idPerfil)
                WHERE tbservidor.situacao = 1
                  AND tbperfil.tipo <> 'Outros'
                  AND YEAR(dtTermino) = '{$relatorioAno}'
                  AND MONTH(dtTermino)= '{$relatorioMes}' 
             ORDER BY dtTermino";

    $result = $pessoal->select($select);

    $relatorio = new Relatorio();

    $relatorio->set_titulo('Relatório Mensal de Vencimento de Auxílio Creche');
    $relatorio->set_tituloLinha2('Servidores Ativos');
    $relatorio->set_tituloLinha3(get_nomeMes($relatorioMes) . ' / ' . $relatorioAno);
    $relatorio->set_subtitulo('Ordenado por Data de Término do Auxílio Creche');

    $relatorio->set_label(array("IdFuncional", "Servidor", "Dependente", "Nascimento", "Término do Aux.", "CI Exclusão", "Processo"));
    $relatorio->set_width(array(5, 22, 22, 10, 10, 13, 18));
    $relatorio->set_align(array("center", "left", "left"));
    $relatorio->set_funcao(array(null, null, null, "date_to_php", "date_to_php", null, null, "get_nomeMes"));

    $relatorio->set_conteudo($result);
    $relatorio->set_formCampos(array(
        array('nome' => 'ano',
            'label' => 'Ano:',
            'tipo' => 'texto',
            'size' => 4,
            'title' => 'Ano',
            'padrao' => $relatorioAno,
            'col' => 3,
            'linha' => 1),
        array('nome' => 'mes',
            'label' => 'Mês',
            'tipo' => 'combo',
            'array' => $mes,
            'size' => 10,
            'padrao' => $relatorioMes,
            'title' => 'Mês do Ano.',
            'onChange' => 'formPadrao.submit();',
            'col' => 3,
            'linha' => 1)));

    $relatorio->show();
    $page->terminaPagina();
}