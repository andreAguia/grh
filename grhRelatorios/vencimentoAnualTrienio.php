<?php

/**
 * Relatório Anual de Vencimento de Triênio
 * 
 * Triênio
 *   
 * By Alat
 */
# Servidor logado 
$idUsuario = NULL;

# Configuração
include ("../grhSistema/_config.php");

# Permissão de Acesso
$acesso = Verifica::acesso($idUsuario, 2);

if ($acesso) {
    # Conecta ao Banco de Dados
    $pessoal = new Pessoal();

    # Começa uma nova página
    $page = new Page();
    $page->iniciaPagina();

    # Pega os parâmetros dos relatórios
    #$relatorioMes = post('mes',date('m'));
    $relatorioAno = post('ano', date('Y'));

    ######

    $select = '(SELECT tbservidor.idFuncional,  
                      tbpessoa.nome,
                      tbservidor.dtadmissao,
                      CONCAT(MAX(tbtrienio.percentual),"%"),
                      MAX(tbtrienio.dtInicial),
                      DATE_ADD(MAX(tbtrienio.dtInicial), INTERVAL 3 YEAR),
                      month(DATE_ADD(MAX(tbtrienio.dtInicial), INTERVAL 3 YEAR))
                 FROM tbservidor LEFT JOIN tbpessoa ON (tbservidor.idPessoa = tbpessoa.idPessoa)
                                    LEFT JOIN tbtrienio ON (tbtrienio.idServidor = tbservidor.idServidor)
                WHERE tbservidor.situacao = 1
                  AND idPerfil = 1              
                GROUP BY tbservidor.idServidor
               HAVING YEAR (DATE_ADD(MAX(tbtrienio.dtInicial), INTERVAL 3 YEAR)) = "' . $relatorioAno . '"
             ORDER BY tbpessoa.nome)
             UNION
             (SELECT  tbservidor.idFuncional,  
                      tbpessoa.nome,
                      tbservidor.dtadmissao,
                      "",
                      "",
                      DATE_ADD(tbservidor.dtadmissao, INTERVAL 3 YEAR),
                      month(DATE_ADD(tbservidor.dtadmissao, INTERVAL 3 YEAR))
                 FROM tbservidor LEFT JOIN tbpessoa ON (tbservidor.idPessoa = tbpessoa.idPessoa)
                                    LEFT JOIN tbtrienio ON (tbtrienio.idServidor = tbservidor.idServidor)
                WHERE tbservidor.situacao = 1
                  AND idPerfil = 1              
             GROUP BY tbservidor.idServidor
             HAVING YEAR (DATE_ADD(tbservidor.dtadmissao, INTERVAL 3 YEAR)) = "' . $relatorioAno . '"
                 AND MAX(tbtrienio.dtInicial) IS NULL
             ORDER BY tbpessoa.nome)
             ORDER BY 6,2';


    $result = $pessoal->select($select);

    $relatorio = new Relatorio();
    $relatorio->set_titulo('Relatório Anual de Vencimento de Triênios - ' . $relatorioAno);
    $relatorio->set_subtitulo('Ordenado por Nome do Servidor');

    $relatorio->set_label(array('IdFuncional', 'Nome', 'Admissão', 'Último Percentual', 'Último Triênio', 'Próximo Triênio', 'Mês'));
    $relatorio->set_width(array(10, 50, 10, 10, 10, 10));
    $relatorio->set_align(array('center', 'left'));
    $relatorio->set_funcao(array(NULL, NULL, "date_to_php", NULL, "date_to_php", "date_to_php", "get_nomeMes"));

    $relatorio->set_conteudo($result);
    $relatorio->set_numGrupo(6);
    $relatorio->set_botaoVoltar(FALSE);
    $relatorio->set_formCampos(array(
        array('nome' => 'ano',
            'label' => 'Ano:',
            'tipo' => 'texto',
            'size' => 4,
            'onChange' => 'formPadrao.submit();',
            'title' => 'Ano',
            'padrao' => $relatorioAno,
            'col' => 3,
            'linha' => 1)));

    $relatorio->set_formFocus('ano');
    $relatorio->set_formLink('?');
    $relatorio->show();

    $page->terminaPagina();
}