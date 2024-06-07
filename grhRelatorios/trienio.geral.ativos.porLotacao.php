<?php

/**
 * Sistema GRH
 * 
 * Relatório de Triênio
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
    $relatorioLotacao = get('relatorioLotacao', post('relatorioLotacao', $servidor->get_idLotacao($intra->get_idServidor($idUsuario))));

    ######

    $select = 'SELECT tbservidor.idfuncional,
                     tbpessoa.nome,
                     tbservidor.idServidor,
                     tbservidor.idServidor,
                     tbservidor.idServidor,
                     tbservidor.idServidor,
                     tbservidor.idServidor,
                     tbservidor.idServidor,
                     tbservidor.idServidor,
                     tbservidor.idServidor					
                FROM tbservidor LEFT JOIN tbpessoa ON (tbservidor.idPessoa = tbpessoa.idPessoa)
                                     JOIN tbhistlot ON (tbservidor.idServidor = tbhistlot.idServidor)
                                     JOIN tblotacao ON (tbhistlot.lotacao=tblotacao.idLotacao)						   	    
               WHERE tbhistlot.data = (select max(data) from tbhistlot where tbhistlot.idServidor = tbservidor.idServidor)
                 AND tbservidor.situacao = 1
                 AND idperfil = 1';

    if (!is_null($relatorioLotacao)) {
        # Verifica se o que veio é numérico
        if (is_numeric($relatorioLotacao)) {
            $select .= ' AND (tblotacao.idlotacao = "' . $relatorioLotacao . '")';
            $subTitulo = $servidor->get_nomeLotacao2($relatorioLotacao);
        } else { # senão é uma diretoria genérica
            $select .= ' AND (tblotacao.DIR = "' . $relatorioLotacao . '")';
            $subTitulo = $lotacao->get_nomeDiretoriaSigla($relatorioLotacao);
        }
    }

    $select .= ' ORDER BY lotacao, tbpessoa.nome';

    $result = $servidor->select($select);
    
    $relatorio = new Relatorio();
    $relatorio->set_titulo('Relatório Geral de Triênio');
    $relatorio->set_tituloLinha2("dos Servidores Estatutários Ativos<br/>$subTitulo");
    $relatorio->set_subtitulo('Ordenado por Nome');

    $relatorio->set_label(['Id Funcional', 'Nome', 'Salário', 'Triênio', '%', 'a Partir de', 'Período Aquisitivo', 'Próximo Triênio', 'Processo', 'Publicação']);
    $relatorio->set_align(["center", "left", "right", "right", "center", "center", "center", "center", "right"]);
    $relatorio->set_funcao([null, null, 'formataMoeda', 'formataMoeda']);

    $relatorio->set_classe([null, null, "pessoal", "Trienio", "Trienio", "Trienio", "Trienio", "Trienio", "Trienio", "Trienio"]);
    $relatorio->set_metodo([null, null, "get_salarioBase", "getValor", "exibePercentual", "getDataInicial", "getPeriodoAquisitivo", "getProximoTrienio", "getNumProcesso", "getPublicacao"]);

    $relatorio->set_conteudo($result);

    $listaLotacao = $servidor->select('(SELECT idlotacao, concat(IFnull(tblotacao.DIR,"")," - ",IFnull(tblotacao.GER,"")," - ",IFnull(tblotacao.nome,"")) lotacao
                                              FROM tblotacao
                                             WHERE ativo) UNION (SELECT distinct DIR, DIR
                                              FROM tblotacao
                                             WHERE ativo)
                                          ORDER BY 2');

    array_unshift($listaLotacao, array('*', '-- Todos --'));

    $relatorio->set_formCampos(array(
        array('nome' => 'relatorioLotacao',
            'label' => 'Lotação:',
            'tipo' => 'combo',
            'array' => $listaLotacao,
            'size' => 30,
            'padrao' => $relatorioLotacao,            
            'onChange' => 'formPadrao.submit();',
            'linha' => 1)));

    $relatorio->set_formFocus('lotacao');
    

    $relatorio->show();

    $page->terminaPagina();
}