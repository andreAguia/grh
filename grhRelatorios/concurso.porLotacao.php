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

    # Pega os parâmetros
    $parametroLotacao = post('parametroLotacao', 66);
    $parametroConcurso = post("parametroConcurso", 96);
    $subTitulo = null;

    # Verifica se o concurso é de Adm & Tec ou se é de Professor
    $concurso = new Concurso();
    $dados = $concurso->get_dados($parametroConcurso);
    $tipo = $dados['tipo'];

    # Começa uma nova página
    $page = new Page();
    $page->iniciaPagina();

    ######

    $select = 'SELECT tbservidor.idFuncional,
                     tbpessoa.nome,
                     tbservidor.idServidor,
                     concat(IFnull(tblotacao.UADM,"")," - ",IFnull(tblotacao.DIR,"")," - ",IFnull(tblotacao.GER,"")," - ",IFnull(tblotacao.nome,"")) lotacao,
                     tbperfil.nome,
                     tbservidor.dtAdmissao,
                     tbservidor.idServidor
                FROM tbservidor LEFT JOIN tbpessoa ON (tbservidor.idPessoa = tbpessoa.idPessoa)
                                     JOIN tbhistlot ON (tbservidor.idServidor = tbhistlot.idServidor)
                                     JOIN tblotacao ON (tbhistlot.lotacao=tblotacao.idLotacao)
                                LEFT JOIN tbperfil ON (tbservidor.idPerfil = tbperfil.idPerfil)
               WHERE tbservidor.situacao = 1
                 AND tbservidor.idPerfil = 1
                 AND tbhistlot.data = (select max(data) from tbhistlot where tbhistlot.idServidor = tbservidor.idServidor)';

    # lotacao
    if (!is_null($parametroLotacao) AND $parametroLotacao <> "*") {
        # Verifica se o que veio é numérico
        if (is_numeric($parametroLotacao)) {
            $select .= ' AND (tblotacao.idlotacao = "' . $parametroLotacao . '")';
            $subTitulo .= "Lotação: " . $servidor->get_nomeLotacao($parametroLotacao) . " - " . $servidor->get_nomeCompletoLotacao($parametroLotacao) . "<br/>";
        } else { # senão é uma diretoria genérica
            $select .= ' AND (tblotacao.DIR = "' . $parametroLotacao . '")';
            $subTitulo .= "Lotação: " . $parametroLotacao . "<br/>";
        }
    }

    # concurso
    if (!is_null($parametroConcurso)) {
        if ($tipo == 1) {
            $select .= ' AND (tbservidor.idConcurso = ' . $parametroConcurso . ')';
        } else {
            $select .= ' AND (tbvagahistorico.idConcurso = ' . $parametroConcurso . ')';
        }
        $subTitulo .= "Concurso: " . $concurso->get_nomeConcurso($parametroConcurso) . "<br/>";
    }

    $select .= ' ORDER BY lotacao, tbpessoa.nome';

    $result = $servidor->select($select);

    $relatorio = new Relatorio();
    $relatorio->set_titulo('Relatório de Servidores Estatutários Ativos');
    $relatorio->set_subtitulo($subTitulo);
    $relatorio->set_label(['IdFuncional', 'Nome', 'Cargo', 'Lotação', 'Perfil', 'Admissão', 'Situação']);
    $relatorio->set_width([10, 30, 30, 0, 10, 10, 10]);
    $relatorio->set_align(["center", "left", "left"]);
    $relatorio->set_funcao([null, null, null, null, null, "date_to_php"]);

    $relatorio->set_classe([null, null, "pessoal", null, null, null, "pessoal"]);
    $relatorio->set_metodo([null, null, "get_Cargo", null, null, null, "get_Situacao"]);

    $relatorio->set_conteudo($result);
    $relatorio->set_numGrupo(3);

    $listaLotacao = $servidor->select('(SELECT idlotacao, concat(IFnull(tblotacao.DIR,"")," - ",IFnull(tblotacao.GER,"")," - ",IFnull(tblotacao.nome,"")) lotacao
                                          FROM tblotacao
                                         WHERE ativo) UNION (SELECT distinct DIR, DIR
                                          FROM tblotacao
                                         WHERE ativo)
                                      ORDER BY 2');
    array_unshift($listaLotacao, array('*', '-- Todos --'));
    
    $listaConcurso = $servidor->select('SELECT idConcurso, concat(anobase," - ",DATE_FORMAT(dtPublicacaoEdital, "%d/%m/%Y")) concurso
                                          FROM tbconcurso
                                         WHERE tipo = 1
                                      ORDER BY 2');

    $relatorio->set_formCampos(array(
        array('nome' => 'parametroLotacao',
            'label' => 'Lotação:',
            'tipo' => 'combo',
            'array' => $listaLotacao,
            'size' => 30,
            'padrao' => $parametroLotacao,
            'onChange' => 'formPadrao.submit();',
            'col' => '8',
            'linha' => 1),
        array('nome' => 'parametroConcurso',
            'label' => 'Concurso:',
            'tipo' => 'combo',
            'array' => $listaConcurso,
            'size' => 30,
            'padrao' => $parametroConcurso,
            'onChange' => 'formPadrao.submit();',
            'col' => '4',
            'linha' => 1),
        ));
    
    

    $relatorio->show();

    $page->terminaPagina();
}