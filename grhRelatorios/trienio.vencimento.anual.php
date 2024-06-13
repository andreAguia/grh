<?php

/**
 * Relatório Anual de Vencimento de Triênio
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
    $page->iniciaPagina();

    # Pega os parâmetros dos relatórios
    #$relatorioMes = post('mes',date('m'));
    $relatorioAno = post('ano', date('Y'));

    ######

    $select = '(SELECT tbservidor.idFuncional,  
                      tbpessoa.nome,
                      tbservidor.dtadmissao,
                      if(MAX(tbtrienio.percentual) < 60,CONCAT(MAX(tbtrienio.percentual),"%"),CONCAT("<b>",MAX(tbtrienio.percentual),"%","</b>")),
                      MAX(tbtrienio.dtInicial),
                      if(MAX(tbtrienio.percentual) < 60, DATE_ADD(MAX(tbtrienio.dtInicial), INTERVAL 3 YEAR),NULL),
                      month(DATE_ADD(MAX(tbtrienio.dtInicial), INTERVAL 3 YEAR))
                 FROM tbservidor LEFT JOIN tbpessoa ON (tbservidor.idPessoa = tbpessoa.idPessoa)
                                    LEFT JOIN tbtrienio ON (tbtrienio.idServidor = tbservidor.idServidor)
                WHERE tbservidor.situacao = 1
                  AND idPerfil = 1              
                GROUP BY tbservidor.idServidor
               HAVING YEAR (DATE_ADD(MAX(tbtrienio.dtInicial), INTERVAL 3 YEAR)) = "' . $relatorioAno . '")
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
                 AND MAX(tbtrienio.dtInicial) IS null)
             ORDER BY 7,2';


    $result = $pessoal->select($select);

    $relatorio = new Relatorio();
    $relatorio->set_titulo('Relatório Anual de Vencimento de Triênios - ' . $relatorioAno);
    $relatorio->set_subtitulo('Agrupado pelo Mês e Ordenado pelo Nome');

    $relatorio->set_label(['IdFuncional', 'Nome', 'Admissão', 'Último Percentual', 'Último Triênio', 'Próximo Triênio', 'Mês']);
    $relatorio->set_width([10, 50, 10, 10, 10, 10]);
    $relatorio->set_align(['center', 'left']);
    $relatorio->set_funcao([null, null, "date_to_php", null, "date_to_php", "date_to_php", "get_nomeMes"]);

    $relatorio->set_conteudo($result);
    $relatorio->set_numGrupo(6);
    $relatorio->set_botaoVoltar(false);
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

    $relatorio->show();
    $page->terminaPagina();
}