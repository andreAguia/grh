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

    $select = 'SELECT tbpessoa.nome,
                     tbservidor.idServidor,
                     tbservidor.idServidor,
                     tbdocumentacao.cpf,
                     DATE_FORMAT(tbpessoa.dtNasc, "%d/%m/%Y")             
                FROM tbservidor JOIN tbpessoa USING (idpessoa)
                                JOIN tbdocumentacao USING (idPessoa)
                                JOIN tbhistlot ON (tbservidor.idServidor = tbhistlot.idServidor)
                                JOIN tblotacao ON (tbhistlot.lotacao=tblotacao.idLotacao)
                                JOIN tbperfil USING (idPerfil)     
               WHERE tbservidor.situacao = 1
                 AND tbperfil.tipo <> "Outros"
                 AND tbhistlot.data = (select max(data) from tbhistlot where tbhistlot.idServidor = tbservidor.idServidor)';

    if (!is_null($lotacao)) {
        # Verifica se o que veio é numérico
        if (is_numeric($lotacao)) {
            $select .= ' AND (tblotacao.idlotacao = "' . $lotacao . '")';
            $subTitulo .= "Lotação: " . $servidor->get_nomeCompletoLotacao($lotacao) . "<br/>";
        } else { # senão é uma diretoria genérica
            $select .= ' AND (tblotacao.DIR = "' . $lotacao . '")';
            $subTitulo .= "Lotação: " . $lotacao . "<br/>";
        }
    }

    $select .= ' ORDER BY tbpessoa.dtNasc desc';

    $result = $servidor->select($select);

    $relatorio = new Relatorio();
    $relatorio->set_titulo('Relatório de Servidores Ativos');
    $relatorio->set_subtitulo($subTitulo . 'Ordenados pelo Nome');
    $relatorio->set_label(['Nome', 'Lotação', 'Cargo', 'CPF', 'Idade']);
    $relatorio->set_width([30, 20, 25, 15, 10]);
    $relatorio->set_align(["left", "left", "left"]);
    $relatorio->set_funcao([null, null, null, null, "idade"]);

    $relatorio->set_classe([null, "pessoal", "pessoal"]);
    $relatorio->set_metodo([null, "get_lotacao", "get_cargoSimples"]);

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
            'title' => 'Lotação',
            'onChange' => 'formPadrao.submit();',
            'linha' => 1)
    ));
    
    $relatorio->show();
    $page->terminaPagina();
}