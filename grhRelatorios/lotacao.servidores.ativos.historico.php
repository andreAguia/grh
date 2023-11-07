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
    $intra = new Intra();

    # Começa uma nova página
    $page = new Page();
    $page->iniciaPagina();

    # Pega os parâmetros dos relatórios
    $lotacao = post('lotacao',66);
    
    # Define variáveis
    $titulo = null;

    ######

    $select = 'SELECT tbservidor.idFuncional,
                     tbpessoa.nome,
                     tbservidor.idServidor,
                     concat(IFnull(tblotacao.UADM,"")," - ",IFnull(tblotacao.DIR,"")," - ",IFnull(tblotacao.GER,"")," - ",IFnull(tblotacao.nome,"")) lotacao,
                     tbperfil.nome,
                     tbhistlot.data,
                     tbservidor.idServidor
                FROM tbservidor LEFT JOIN tbpessoa USING (idPessoa)
                                     JOIN tbhistlot ON (tbservidor.idServidor = tbhistlot.idServidor)
                                     JOIN tblotacao ON (tbhistlot.lotacao=tblotacao.idLotacao)
                                     JOIN tbperfil USING (idPerfil)     
               WHERE tbservidor.situacao = 1
                 AND tbperfil.tipo <> "Outros"';

    # lotacao
    if (!is_null($lotacao)) {
        # Verifica se o que veio é numérico
        if (is_numeric($lotacao)) {
            $select .= ' AND (tblotacao.idlotacao =  "' . $lotacao . '")';
            $titulo = null;
        } else { # senão é uma diretoria genérica
            $select .= ' AND (tblotacao.DIR = "' . $lotacao . '")';
            $lotacaoClasse = new Lotacao();
            if ($lotacao <> "Reitoria" AND $lotacao <> "Prefeitura") {
                $titulo = $lotacaoClasse->get_nomeDiretoriaSigla($lotacao) . " - {$lotacao}<br/>";
            }else{
                $titulo = "{$lotacao}<br/>";
            }
        }
    }

    $select .= ' ORDER BY lotacao, tbpessoa.nome';

    $result = $servidor->select($select);

    $relatorio = new Relatorio();
    $relatorio->set_titulo('Histórico de Servidores Ativos Por Lotação');
    $relatorio->set_subtitulo('Agrupados por Lotação - Ordenados pelo Nome');
    $relatorio->set_label(['IdFuncional', 'Nome', 'Cargo', 'Lotação', 'Perfil', 'Data da Transferência','Lotação atual']);
    $relatorio->set_subtitulo2($titulo);
    $relatorio->set_align(["center", "left", "left"]);
    $relatorio->set_funcao([null, null, null, null, null, "date_to_php"]);

    $relatorio->set_classe([null, null, "pessoal",null, null,null, "pessoal"]);
    $relatorio->set_metodo([null, null, "get_Cargo",null, null,null, "get_lotacao"]);

    $relatorio->set_conteudo($result);
    $relatorio->set_numGrupo(3);

    $listaLotacao = $servidor->select('(SELECT idlotacao, concat(IFnull(tblotacao.DIR,"")," - ",IFnull(tblotacao.GER,"")," - ",IFnull(tblotacao.nome,"")) lotacao
                                              FROM tblotacao
                                             WHERE ativo) UNION (SELECT distinct DIR, DIR FROM tblotacao 
                                             WHERE ativo)
                                          ORDER BY 2');
    $relatorio->set_formCampos(array(
        array('nome' => 'lotacao',
            'label' => 'Lotação:',
            'tipo' => 'combo',
            'array' => $listaLotacao,
            'size' => 30,
            'padrao' => $lotacao,
            'onChange' => 'formPadrao.submit();',
            'linha' => 1)));

    $relatorio->set_formFocus('lotacao');
    $relatorio->set_formLink('?');
    $relatorio->show();

    $page->terminaPagina();
}