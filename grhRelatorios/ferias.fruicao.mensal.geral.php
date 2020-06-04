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
$acesso = Verifica::acesso($idUsuario, 2);

if ($acesso) {
    # Conecta ao Banco de Dados
    $servidor = new Pessoal();

    # Começa uma nova página
    $page = new Page();
    $page->iniciaPagina();

    # Pega os parâmetros
    $parametroAno = get_session('parametroAno', date("Y"));
    $parametroLotacao = get_session('parametroLotacao');
    $parametroStatus = get_session('parametroStatus');

    # Transforma em nulo a máscara *
    if ($parametroLotacao == "*") {
        $parametroLotacao = null;
    }

    # Transforma em nulo a máscara *
    if ($parametroStatus == "Todos") {
        $parametroStatus = null;
    }

    # Pega o mes
    $parametroMes = post('parametroMes', 1);

    ######

    $select = "SELECT tbservidor.idfuncional,        
                     tbpessoa.nome,
                     concat(IFnull(tblotacao.DIR,''),' - ',IFnull(tblotacao.GER,''),' - ',IFnull(tblotacao.nome,'')) lotacao,
                     tbferias.anoExercicio,
                     tbferias.dtInicial,
                     tbferias.numDias,
                     date_format(ADDDATE(tbferias.dtInicial,tbferias.numDias-1),'%d/%m/%Y') as dtf,
                     idFerias,
                     tbsituacao.situacao
                FROM tbservidor LEFT JOIN tbpessoa ON (tbservidor.idPessoa=tbpessoa.idPessoa)
                                     JOIN tbferias ON (tbservidor.idServidor = tbferias.idServidor)
                                     JOIN tbhistlot ON (tbservidor.idServidor = tbhistlot.idServidor)
                                     JOIN tblotacao ON (tbhistlot.lotacao=tblotacao.idLotacao)
                                     JOIN tbsituacao ON (tbservidor.situacao = tbsituacao.idSituacao) 
               WHERE YEAR(tbferias.dtInicial) = $parametroAno
                 AND MONTH(tbferias.dtInicial) = $parametroMes
                 AND tbhistlot.data = (select max(data) from tbhistlot where tbhistlot.idServidor = tbservidor.idServidor)";

    # Lotação
    if (($parametroLotacao <> "*") AND ($parametroLotacao <> "")) {

        # Verifica se o que veio é numérico
        if (is_numeric($parametroLotacao)) {
            $select .= ' AND (tblotacao.idlotacao = "' . $parametroLotacao . '")';
        } else { # senão é uma diretoria genérica
            $select .= ' AND (tblotacao.DIR = "' . $parametroLotacao . '")';
        }
    }

    # Status
    if (($parametroStatus <> "Todos") AND ($parametroStatus <> "")) {
        $select .= ' AND (tbferias.status = "' . $parametroStatus . '")';
    }

    $select .= " ORDER BY tbpessoa.nome";

    $result = $servidor->select($select);

    $relatorio = new Relatorio();

    # Status no subtítulo
    if (!is_null($parametroStatus)) {
        $relatorio->set_tituloLinha3('Ferias ' . plm($parametroStatus) . 's');
    }

    $relatorio->set_titulo('Relatório Mensal Geral de Férias');
    $relatorio->set_tituloLinha2(get_nomeMes($parametroMes) . " / " . $parametroAno);

    $relatorio->set_subtitulo('Ordenados pelo Nome do Servidor');

    $relatorio->set_label(array('IdFuncional', 'Nome', 'Lotação', 'Exercício', 'Dt Inicial', 'Dias', 'Dt Final', 'Período', 'Situação'));
    #$relatorio->set_width(array(10,30,20,5,9,8,9,10));
    $relatorio->set_align(array("center", "left", "left"));
    $relatorio->set_funcao(array(null, null, null, null, "date_to_php"));
    $relatorio->set_classe(array(null, null, null, null, null, null, null, "pessoal"));
    $relatorio->set_metodo(array(null, null, null, null, null, null, null, "get_feriasPeriodo"));

    if (($parametroLotacao <> "*") AND ($parametroLotacao <> "")) {
        $relatorio->set_numGrupo(2);
    }

    $relatorio->set_conteudo($result);

    $relatorio->set_formCampos(array(
        array('nome' => 'parametroMes',
            'label' => 'Mês:',
            'tipo' => 'combo',
            'array' => $mes,
            'size' => 10,
            'padrao' => $parametroMes,
            'title' => 'Mês',
            'onChange' => 'formPadrao.submit();',
            'col' => 3,
            'linha' => 1)));

    $relatorio->set_formFocus('mesBase');
    $relatorio->set_formLink('?parametroAno=' . $parametroAno . '&parametroLotacao=' . $parametroLotacao);

    $relatorio->show();

    $page->terminaPagina();
}
