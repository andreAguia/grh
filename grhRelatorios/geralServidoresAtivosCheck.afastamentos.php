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
    $relatorioMes = post('mes', date('m'));
    $relatorioAno = post('ano', date('Y'));

    ######

    $select = "SELECT '[ ',' ]',
                     tbservidor.idFuncional,
                     tbpessoa.nome,
                     tbservidor.idServidor,
                     tbservidor.idServidor,
                     CONCAT(tbservidor.idServidor,'-','{$relatorioMes}','-','{$relatorioAno}')
                FROM tbservidor JOIN tbpessoa USING (idPessoa)
                                JOIN tbperfil USING (idPerfil)
               WHERE tbservidor.situacao = 1
                 AND tbperfil.tipo <> 'Outros' 
            ORDER BY tbpessoa.nome";

    $result = $servidor->select($select);

    $relatorio = new Relatorio();
    $relatorio->set_titulo('Relatório de Servidores Ativos');
    $relatorio->set_subtitulo('Com Afastamento em '.get_nomeMes($relatorioMes) . ' / ' . $relatorioAno);
    $relatorio->set_label(['', '', 'IdFuncional', 'Nome', 'Lotação', 'Perfil', 'Afastamento']);
    $relatorio->set_width([3, 3, 10, 30, 20, 10, 24]);
    $relatorio->set_align(["center", "center", "center", "left", "left", "center","left"]);

    $relatorio->set_classe([null, null, null, null, "pessoal", "pessoal"]);
    $relatorio->set_metodo([null, null, null, null, "get_lotacao", "get_perfil"]);
    $relatorio->set_funcao([null, null, null, null, null, null, "get_afastamento"]);
    
    $relatorio->set_formCampos(array(
        array('nome' => 'ano',
            'label' => 'Ano:',
            'tipo' => 'texto',
            'size' => 4,
            'title' => 'Ano',
            'padrao' => $relatorioAno,
            'onChange' => 'formPadrao.submit();',
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

    $relatorio->set_bordaInterna(true);

    $relatorio->set_conteudo($result);
    #$relatorio->set_numGrupo(4);
    #$relatorio->set_botaoVoltar('../sistema/areaServidor.php');
    $relatorio->show();

    $page->terminaPagina();
}