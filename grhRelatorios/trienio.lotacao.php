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

    # Começa uma nova página
    $page = new Page();
    $page->iniciaPagina();
    
    # Pega os parâmetros dos relatórios
    $lotacao = get('lotacao', post('lotacao', $servidor->get_idLotacao($intra->get_idServidor($idUsuario))));

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

    # lotacao
    if (!is_null($lotacao)) {
        # Verifica se o que veio é numérico
        if (is_numeric($lotacao)) {
            $select .= ' AND (tblotacao.idlotacao =  "' . $lotacao . '")';
            $titulo = $servidor->get_nomeLotacao($lotacao);
        } else { # senão é uma diretoria genérica
            $select .= ' AND (tblotacao.DIR = "' . $lotacao . '")';
            $titulo = "Lotação: " . $lotacao;
        }
    }

    $select .= ' ORDER BY lotacao, tbpessoa.nome';


    $result = $servidor->select($select);

    $relatorio = new Relatorio();
    $relatorio->set_titulo('Relatório de Triênio');
    $relatorio->set_subtitulo('Ordenado por Nome do Servidor');
    $relatorio->set_tituloLinha2($titulo);

    $relatorio->set_label(array('Id Funcional', 'Nome', 'Salário', 'Triênio', '%', 'a Partir de', 'Período Aquisitivo', 'Próximo Triênio', 'Processo', 'Publicação'));
    #$relatorio->set_width(array(5,20,10,10,5,10,10,10,10,10));
    $relatorio->set_align(array("center", "left", "right", "right", "center", "center", "center", "center", "right"));
    $relatorio->set_funcao(array(null, null, 'formataMoeda', 'formataMoeda'));

    $relatorio->set_classe(array(null, null, "pessoal", "Trienio", "Trienio", "Trienio", "Trienio", "Trienio", "Trienio", "Trienio"));
    $relatorio->set_metodo(array(null, null, "get_salarioBase", "getValor", "exibePercentual", "getDataInicial", "getPeriodoAquisitivo", "getProximoTrienio", "getNumProcesso", "getPublicacao"));

    $relatorio->set_conteudo($result);
    #$relatorio->set_numGrupo(3);
    
    $listaLotacao = $servidor->select('(SELECT idlotacao, concat(IFnull(tblotacao.DIR,"")," - ",IFnull(tblotacao.GER,"")," - ",IFnull(tblotacao.nome,"")) lotacao
                                              FROM tblotacao
                                             WHERE ativo) UNION (SELECT distinct DIR, DIR
                                              FROM tblotacao
                                             WHERE ativo)
                                          ORDER BY 2');
    array_unshift($listaLotacao, array('*', '-- Selecione a Lotação --'));

    $relatorio->set_formFocus('lotacao');
    $relatorio->set_formLink('?');
    
    $relatorio->set_formCampos(array(
        array('nome' => 'lotacao',
            'label' => 'Lotação:',
            'tipo' => 'combo',
            'array' => $listaLotacao,
            'size' => 30,
            'padrao' => $lotacao,
            'title' => 'Lotação',
            'onChange' => 'formPadrao.submit();',
            'linha' => 1)));

    $relatorio->set_formFocus('lotacao');
    $relatorio->set_formLink('?');
    
    $relatorio->show();

    $page->terminaPagina();
}