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
    $lotacao = get('lotacao', post('lotacao'));
    if ($lotacao == "*") {
        $lotacao = null;
    }
    $subTitulo = null;

    ######

    $relatorio = new Relatorio();
    $label = array('Servidor','Tel Residencial', 'Celular', 'Email Uenf', 'Email Pessoal');

    $select = 'SELECT tbservidor.idServidor,                      
                      tbservidor.idServidor,
                      tbservidor.idServidor,
                      tbservidor.idServidor,
                      tbservidor.idServidor,
                      tbservidor.idServidor,
                      concat(IFnull(tblotacao.DIR,"")," - ",IFnull(tblotacao.GER,"")," - ",IFnull(tblotacao.nome,"")) lotacao
                FROM tbservidor JOIN tbpessoa USING (idpessoa)
                                JOIN tbhistlot USING (idServidor)
                                JOIN tblotacao ON (tbhistlot.lotacao=tblotacao.idLotacao)
                                JOIN tbperfil USING (idPerfil)
                WHERE tbservidor.situacao = 1
                  AND tbperfil.tipo <> "Outros"
                  AND tbhistlot.data = (select max(data) from tbhistlot where tbhistlot.idServidor = tbservidor.idServidor)';

    if (!is_null($lotacao)) {
        # Verifica se o que veio é numérico
        if (is_numeric($lotacao)) {
            $select .= ' AND (tblotacao.idlotacao = "' . $lotacao . '")';
            $relatorio->set_numGrupo(6);
            $label = array('Servidor','Tel Residencial', 'Celular', 'Email Uenf', 'Email Pessoal','Lotação');
        } else { # senão é uma diretoria genérica
            $select .= ' AND (tblotacao.DIR = "' . $lotacao . '")';
            $subTitulo .= "Lotação: " . $lotacao . "<br/>";
            $relatorio->set_numGrupo(6);
            $label = array('Servidor','Tel Residencial', 'Celular', 'Email Uenf', 'Email Pessoal','Lotação');
        }
    }


    if (is_null($lotacao)) {
        $select .= ' ORDER BY tbpessoa.nome';
    } else {
        $select .= ' ORDER BY DIR, GER, tbpessoa.nome';
    }

    $result = $servidor->select($select);

    $relatorio->set_titulo('Relatório de Contatos dos Servidores Ativos');
    $relatorio->set_subtitulo($subTitulo . 'Ordenados pelo Nome');
    $relatorio->set_label($label);
    $relatorio->set_classe(["pessoal", "pessoal", "pessoal", "pessoal", "pessoal", "pessoal"]);
    $relatorio->set_metodo(["get_siglaNomeLotacao", "get_telefoneResidencial", "get_telefoneCelular", "get_emailUenf", "get_emailPessoal", "get_emailOutro"]);
    $relatorio->set_align(["left", "left", "left", "left", "left"]);
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
            'onChange' => 'formPadrao.submit();',
            'linha' => 1)));   

    $relatorio->show();
    $page->terminaPagina();
}