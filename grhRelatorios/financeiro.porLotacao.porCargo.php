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
    $lotacao = get('lotacao', post('lotacao', 66));
    $cargo = get('cargo', post('cargo'));

    ######

    $select = "SELECT idfuncional,
                      tbpessoa.nome,
                      tbservidor.idServidor,
                      tbservidor.idServidor,
                      concat(IFnull(tblotacao.UADM,''),' - ',IFnull(tblotacao.DIR,''),' - ',IFnull(tblotacao.GER,''),' - ',IFnull(tblotacao.nome,'')) lotacao
                 FROM tbservidor JOIN tbpessoa USING  (idPessoa)
                                 JOIN tbhistlot ON (tbservidor.idServidor = tbhistlot.idServidor)
                                 JOIN tblotacao ON (tbhistlot.lotacao=tblotacao.idLotacao)
                            LEFT JOIN tbcargo ON (tbservidor.idCargo = tbcargo.idCargo)
                            LEFT JOIN tbtipocargo ON (tbcargo.idTipoCargo = tbtipocargo.idTipoCargo)
                 WHERE tbservidor.situacao = 1
                   AND idPerfil = 1
                   AND tbhistlot.data = (select max(data) from tbhistlot where tbhistlot.idServidor = tbservidor.idServidor)";

    # lotacao
    if (!is_null($lotacao)) {
        # Verifica se o que veio é numérico
        if (is_numeric($lotacao)) {
            $select .= " AND tblotacao.idlotacao = {$lotacao}";
            $subTitulo = null;
        } else { # senão é uma diretoria genérica
            $select .= " AND tblotacao.DIR = '{$lotacao}'";
            $subTitulo = $lotacao;
        }
    }

    # cargo
    if (!empty($cargo)) {
        if (is_numeric($cargo)) {
            $select .= " AND (tbcargo.idcargo = {$cargo})";
            $nomeCargo = "Cargo: " . $servidor->get_nomeCompletoCargo($cargo) . "<br/>";
        } else { # senão é nivel do cargo
            if ($cargo == "Professor") {
                $select .= " AND (tbcargo.idcargo = 128 OR  tbcargo.idcargo = 129)";
                $nomeCargo = "Cargo: Professor<br/>";
            } else {
                $select .= " AND (tbtipocargo.cargo = '{$cargo}')";
                $nomeCargo = "Cargo: {$cargo}<br/>";
            }
        }
    }

    $select .= " ORDER BY tblotacao.GER, tbpessoa.nome";
    
    $result = $servidor->select($select);

    $relatorio = new Relatorio();
    $relatorio->set_titulo('Relatório de Servidores Estatutarios');
    
    if (!empty($nomeCargo)) {
        $relatorio->set_subtitulo("Com Faixa e Nivel do Plano de Cargos<br/>{$nomeCargo}");
    }else{
        $relatorio->set_subtitulo("Com Faixa e Nivel do Plano de Cargos");
    }
    if (!is_null($subTitulo)) {
        $lotacaoClasse = new Lotacao();
        $relatorio->set_subtitulo2($subTitulo . " - " . $lotacaoClasse->get_nomeDiretoriaSigla($subTitulo));
    }
    $relatorio->set_label(['IdFuncional', 'Nome', 'Cargo', 'Nivel Faixa Padrao', 'Lotaçao']);
    #$relatorio->set_width([10, 90]);
    $relatorio->set_align(["center", "left", "left"]);
    $relatorio->set_classe([null, null, "Pessoal", "Progressao"]);
    $relatorio->set_metodo([null, null, "get_cargoSimples", "get_FaixaAtual"]);
    #$relatorio->set_funcao([null, null, "date_to_php"]);
    $relatorio->set_conteudo($result);
    $relatorio->set_numGrupo(4);

    # Combo Lotação
    $listaLotacao = $servidor->select('(SELECT idlotacao, concat(IFnull(tblotacao.DIR,"")," - ",IFnull(tblotacao.GER,"")," - ",IFnull(tblotacao.nome,"")) lotacao
                                              FROM tblotacao
                                             WHERE ativo) UNION (SELECT distinct DIR, DIR
                                              FROM tblotacao
                                             WHERE ativo)
                                          ORDER BY 2');

    # Combo Cargo
    $result1 = $servidor->select('SELECT idcargo, CONCAT(tbtipocargo.cargo," - ",tbcargo.nome)
                                       FROM tbcargo LEFT JOIN tbtipocargo USING(idtipocargo)
                                   ORDER BY tbtipocargo.cargo,tbcargo.nome');

    # cargos por nivel
    $result2 = $servidor->select('SELECT cargo,cargo FROM tbtipocargo WHERE cargo <> "Professor Associado" AND cargo <> "Professor Titular" ORDER BY 2');

    # junta os dois
    $listaCargo = array_merge($result2, $result1);

    # acrescenta Professor
    array_unshift($listaCargo, array('Professor', 'Professores'));

    # acrescenta todos
    array_unshift($listaCargo, array(null, 'Todos'));

    $relatorio->set_formCampos(array(
        array('nome' => 'lotacao',
            'label' => 'Lotação:',
            'tipo' => 'combo',
            'array' => $listaLotacao,
            'size' => 30,
            'col' => 12,
            'padrao' => $lotacao,
            'onChange' => 'formPadrao.submit();',
            'linha' => 1),
        array('nome' => 'cargo',
            'label' => 'Cargo:',
            'tipo' => 'combo',
            'array' => $listaCargo,
            'size' => 30,
            'col' => 12,
            'padrao' => $cargo,
            'onChange' => 'formPadrao.submit();',
            'linha' => 2)
    ));
    
    $relatorio->show();
    $page->terminaPagina();
}