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
    $page->iniciaPagina();

    # Pega os parâmetros dos relatórios
    $relatorioMes = post('mes', date('m'));
    $relatorioAno = post('ano', date('Y'));

    ######

    $select = '(SELECT DISTINCT tbservidor.idFuncional,  
                      tbpessoa.nome,
                      tbservidor.dtadmissao,
                      if(MAX(tbtrienio.percentual) < 60,CONCAT(MAX(tbtrienio.percentual),"%"),CONCAT("<b>",MAX(tbtrienio.percentual),"%","</b>")),
                      MAX(tbtrienio.dtInicial),
                      if(MAX(tbtrienio.percentual) < 60, DATE_ADD(MAX(tbtrienio.dtInicial), INTERVAL 3 YEAR),NULL)
                 FROM tbservidor LEFT JOIN tbpessoa ON (tbservidor.idPessoa = tbpessoa.idPessoa)
                                    LEFT JOIN tbtrienio ON (tbtrienio.idServidor = tbservidor.idServidor)
                WHERE tbservidor.situacao = 1
                  AND idPerfil = 1              
             GROUP BY tbservidor.idServidor
               HAVING YEAR (DATE_ADD(MAX(tbtrienio.dtInicial), INTERVAL 3 YEAR)) = "' . $relatorioAno . '"
                  AND month(DATE_ADD(MAX(tbtrienio.dtInicial), INTERVAL 3 YEAR))="' . $relatorioMes . '" 
             ORDER BY tbpessoa.nome)
             UNION 
                (SELECT DISTINCT tbservidor.idFuncional,  
                      tbpessoa.nome,
                      tbservidor.dtadmissao,
                      "",
                      "",
                      DATE_ADD(tbservidor.dtadmissao, INTERVAL 3 YEAR)
                 FROM tbservidor LEFT JOIN tbpessoa ON (tbservidor.idPessoa = tbpessoa.idPessoa)
                                    LEFT JOIN tbtrienio ON (tbtrienio.idServidor = tbservidor.idServidor)
                WHERE tbservidor.situacao = 1
                  AND idPerfil = 1
                  AND tbtrienio.dtInicial is null
                  AND YEAR (DATE_ADD(tbservidor.dtadmissao, INTERVAL 3 YEAR)) = "' . $relatorioAno . '"
                  AND month(DATE_ADD(tbservidor.dtadmissao, INTERVAL 3 YEAR))= "' . $relatorioMes . '"               
             GROUP BY tbservidor.idServidor           
             ORDER BY tbpessoa.nome) 
             ORDER BY 2';


    $result = $pessoal->select($select);

    $relatorio = new Relatorio();
    $relatorio->set_titulo('Relatório Mensal de Vencimento de Triênios');
    $relatorio->set_tituloLinha2(get_nomeMes($relatorioMes) . ' / ' . $relatorioAno);
    $relatorio->set_subtitulo('Ordenado pelo Nome do Servidor');

    $relatorio->set_label(array('IdFuncional', 'Nome', 'Admissão', 'Último Percentual', 'Último Triênio', 'Próximo Triênio'));
    $relatorio->set_width(array(10, 50, 10, 10, 10, 10));
    $relatorio->set_align(array('center', 'left'));
    $relatorio->set_funcao(array(null, null, "date_to_php", null, "date_to_php", "date_to_php"));

    $relatorio->set_conteudo($result);
    #$relatorio->set_numGrupo(2);
    $relatorio->set_botaoVoltar(false);
    $relatorio->set_formCampos(array(
        array('nome' => 'ano',
            'label' => 'Ano:',
            'tipo' => 'texto',
            'size' => 4,
            'title' => 'Ano',
            'onChange' => 'formPadrao.submit();',
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

    $relatorio->set_formFocus('ano');
    $relatorio->set_formLink('?');
    $relatorio->show();

    $page->terminaPagina();
}