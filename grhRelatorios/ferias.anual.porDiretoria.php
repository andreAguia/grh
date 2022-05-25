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

    # Pega o valor arquivado
    $intra = new Intra();
    $dataDev = $intra->get_variavel("dataDevolucaoGrh");

    # Pega o ano exercicio
    $parametroAno = post("parametroAno", date('Y') + 1);
    $parametroLotacao = post("parametroLotacao", "*");

    # Monta o select
    $select = "SELECT distinct tbservidor.idfuncional,
                      tbpessoa.nome,
                      concat(IFnull(tblotacao.GER,''),' - ',IFnull(tblotacao.nome,'')) lotacao,
                      tbservidor.idServidor,
                      tbservidor.dtAdmissao,
                      concat(tbservidor.idServidor,'&',{$parametroAno})
                 FROM tbservidor LEFT JOIN tbpessoa USING (idPessoa)
                                      JOIN tbhistlot USING (idServidor)
                                      JOIN tblotacao ON (tbhistlot.lotacao=tblotacao.idLotacao)
                WHERE tbservidor.situacao = 1
                  AND (idPerfil = 1 OR idPerfil = 2 OR idPerfil = 4 OR idPerfil = 3)
                  AND tbhistlot.data = (select max(data) from tbhistlot where tbhistlot.idServidor = tbservidor.idServidor)
                  AND tblotacao.DIR = '{$parametroLotacao}'
                  AND idLotacao <> 113    
             ORDER BY tblotacao.DIR,tblotacao.GER,tbpessoa.nome";

    $result = $servidor->select($select);

    $relatorio = new Relatorio();
    $relatorio->set_titulo('Relatório Anual de Férias');
    $relatorio->set_tituloLinha2("Exercício: {$parametroAno}");
    $relatorio->set_tituloLinha3($parametroLotacao);

    $relatorio->set_label(['Id', 'Nome', 'Lotação', 'Cargo', 'Admissão', 'Observação']);
    #$relatorio->set_width([6, 25, 0, 10, 20, 25, 25]);
    $relatorio->set_align(["center", "left", "center", "left", "center", "left"]);
    $relatorio->set_funcao([null, null, null, null, "date_to_php", "exibeFeriasPendentes"]);
    $relatorio->set_classe(array(null, null, null, "pessoal"));
    $relatorio->set_metodo(array(null, null, null, "get_cargo"));
    $relatorio->set_conteudo($result);
    $relatorio->set_numGrupo(2);
    $relatorio->set_bordaInterna(true);

    $listaLotacao = $servidor->select('SELECT distinct DIR, DIR
                                              FROM tblotacao
                                             WHERE ativo');
    array_unshift($listaLotacao, array('*', 'Escolha a Lotação'));

    # Cria um array com os anos possíveis
    $anoInicial = 1999;
    $anoAtual = date('Y');
    $anoExercicio = arrayPreenche($anoInicial, $anoAtual + 2, "d");

    $relatorio->set_formCampos(array(
        array('nome' => 'parametroAno',
            'label' => 'Ano:',
            'tipo' => 'combo',
            'array' => $anoExercicio,
            'size' => 10,
            'padrao' => $parametroAno,
            'title' => 'Ano',
            'onChange' => 'formPadrao.submit();',
            'col' => 2,
            'linha' => 1),
        array('nome' => 'parametroLotacao',
            'label' => 'Lotação:',
            'tipo' => 'combo',
            'array' => $listaLotacao,
            'size' => 20,
            'padrao' => $parametroLotacao,
            'onChange' => 'formPadrao.submit();',
            'title' => 'Mês',
            'col' => 4,
            'linha' => 1),
    ));

    $relatorio->set_formFocus('mesBase');
    $relatorio->set_formLink('?');
    $relatorio->show();

    $page->terminaPagina();
}
