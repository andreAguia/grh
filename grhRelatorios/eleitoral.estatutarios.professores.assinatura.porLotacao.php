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
    $lotacao = new Lotacao();

    # Começa uma nova página
    $page = new Page();
    $page->iniciaPagina();

    # Pega os parâmetros dos relatórios
    $relatorioLotacao = post('lotacao', 'CBB');

    ######

    $select = 'SELECT tbservidor.idFuncional,
                     tbpessoa.nome,
                     tbdocumentacao.cpf,                    
                     "_________________________________________"
                FROM tbservidor LEFT JOIN tbpessoa USING (idPessoa)
                                     JOIN tbhistlot USING (idServidor)
                                     JOIN tblotacao ON (tbhistlot.lotacao=tblotacao.idLotacao)
                                LEFT JOIN tbcargo USING (idCargo)
                                     JOIN tbtipocargo USING (idTipoCargo) 
                                     JOIN tbdocumentacao using (idPessoa)
               WHERE tbservidor.situacao = 1
                 AND idPerfil = 1
                 AND tbtipocargo.tipo = "Professor"
                 AND tbhistlot.data = (select max(data) from tbhistlot where tbhistlot.idServidor = tbservidor.idServidor)';
    # Lotação
    if (!is_null($relatorioLotacao)) {
        # Verifica se o que veio é numérico
        if (is_numeric($relatorioLotacao)) {
            $select .= ' AND (tblotacao.idlotacao = "' . $relatorioLotacao . '")';
            $titulo = $servidor->get_nomeLotacao2($relatorioLotacao);
        } else { # senão é uma diretoria genérica
            $select .= ' AND (tblotacao.DIR = "' . $relatorioLotacao . '")';
            $titulo = $lotacao->get_nomeDiretoriaSigla($relatorioLotacao);
        }
    }

    $select .= ' ORDER BY tbpessoa.nome';

    $result = $servidor->select($select);
    
    $relatorio = new Relatorio();
    $relatorio->set_titulo('Relatório de Servidores Estatutários Ativos');
    $relatorio->set_tituloLinha2("Professores<br/>{$titulo}");
    $relatorio->set_subtitulo('Ordenados pelo Nome');

    $relatorio->set_label(['IdFuncional', 'Nome', 'CPF', 'Assinatura']);
    $relatorio->set_align(["center", "left", "left", "left"]);
    $relatorio->set_conteudo($result);

    # Dados da combo lotacao
    $select2 = $servidor->select('(SELECT idlotacao, concat(IFnull(tblotacao.DIR,"")," - ",IFnull(tblotacao.GER,"")," - ",IFnull(tblotacao.nome,"")) lotacao
                                              FROM tblotacao
                                             WHERE ativo) UNION (SELECT distinct DIR, DIR
                                              FROM tblotacao
                                             WHERE ativo)
                                          ORDER BY 2');

    $relatorio->set_formCampos(array(
        array('nome' => 'lotacao',
            'label' => 'Lotação',
            'tipo' => 'combo',
            'array' => $select2,
            'col' => 12,
            'size' => 10,
            'padrao' => $relatorioLotacao,
            'title' => 'Filtra por Lotação.',
            'onChange' => 'formPadrao.submit();',
            'linha' => 1)));

    $relatorio->set_formFocus('ano');
    $relatorio->set_formLink('?');
    $relatorio->set_espacamento(3);
    $relatorio->show();

    $page->terminaPagina();
}