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

    # Pega os parâmetros dos relatórios
    $lotacao = get('lotacao', post('lotacao'));
    if ($lotacao == "*") {
        $lotacao = null;
    }
    $subTitulo = null;

    ######

    $relatorio = new Relatorio();

    $select = 'SELECT tbservidor.idServidor,
                      concat(IFnull(tblotacao.UADM,"")," - ",IFnull(tblotacao.DIR,"")," - ",IFnull(tblotacao.GER,"")) lotacao, 
                      tbservidor.idServidor
                 FROM tbservidor JOIN tbpessoa USING (idpessoa)
                 JOIN tbhistlot USING (idServidor)
                 JOIN tblotacao ON (tbhistlot.lotacao=tblotacao.idLotacao)
                WHERE tbservidor.situacao = 1
                  AND (telCelular IS NULL OR telCelular = "") 
                  AND tbhistlot.data = (select max(data) from tbhistlot where tbhistlot.idServidor = tbservidor.idServidor)';

    if (!is_null($lotacao)) {
        # Verifica se o que veio é numérico
        if (is_numeric($lotacao)) {
            $select .= ' AND (tblotacao.idlotacao = "' . $lotacao . '")';
            $relatorio->set_numGrupo(1);
        } else { # senão é uma diretoria genérica
            $select .= ' AND (tblotacao.DIR = "' . $lotacao . '")';
            $subTitulo .= "Lotação: " . $lotacao . "<br/>";
            $relatorio->set_numGrupo(1);
        }
    }

    if (is_null($lotacao)) {
        $select .= ' ORDER BY tbpessoa.nome';
    } else {
        $select .= ' ORDER BY DIR, GER, tbpessoa.nome';
    }

    $result = $servidor->select($select);

    $relatorio->set_titulo('Relatório de Servidores Ativos Sem Celular Cadastrado');
    $relatorio->set_subtitulo($subTitulo . 'Ordenados pelo Nome');
   $relatorio->set_label(array('Servidor', 'Lotação', 'Celular'));
    $relatorio->set_classe(array("pessoal", null, "pessoal"));
    $relatorio->set_metodo(array("get_nomeECargo", null, "get_telefoneCelular"));
    $relatorio->set_align(array("left","left","left"));
    $relatorio->set_bordaInterna(true);
    $relatorio->set_conteudo($result);

    $listaLotacao = $servidor->select('(SELECT idlotacao, concat(IFnull(tblotacao.DIR,"")," - ",IFnull(tblotacao.GER,"")," - ",IFnull(tblotacao.nome,"")) lotacao
                                              FROM tblotacao
                                             WHERE ativo) UNION (SELECT distinct DIR, DIR
                                              FROM tblotacao
                                             WHERE ativo)
                                          ORDER BY 2');

    array_unshift($listaLotacao, array('*', '-- Todos --'));

    $relatorio->set_formCampos(array(
        array('nome' => 'lotacao',
            'label' => 'Lotação:',
            'tipo' => 'combo',
            'array' => $listaLotacao,
            'size' => 30,
            'padrao' => $lotacao,
            'title' => 'Mês',
            'onChange' => 'formPadrao.submit();',
            'linha' => 1)));

    $relatorio->set_formFocus('lotacao');
    $relatorio->set_formLink('?');
    $relatorio->show();

    $page->terminaPagina();
}