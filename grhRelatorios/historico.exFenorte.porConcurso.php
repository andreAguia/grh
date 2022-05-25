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
    $concurso = new Concurso();

    # Começa uma nova página
    $page = new Page();
    $page->iniciaPagina();

    # Pega os parâmetros dos relatórios
    $parametroConcurso = post('concurso', 1);

    ######

    $select = 'SELECT tbservidor.idServidor,
                     tbpessoa.nome,
                     tbservidor.idServidor,
                     idConcurso,
                     tbperfil.nome,
                     tbservidor.dtAdmissao,
                     tbservidor.dtDemissao,
                     tbsituacao.situacao
                FROM tbservidor LEFT JOIN tbpessoa ON (tbservidor.idPessoa = tbpessoa.idPessoa)                                    
                                     JOIN tbhistlot ON (tbservidor.idServidor = tbhistlot.idServidor)
                                     JOIN tblotacao ON (tbhistlot.lotacao=tblotacao.idLotacao)
                                     JOIN tbsituacao ON (tbservidor.situacao = tbsituacao.idsituacao)
                                LEFT JOIN tbperfil ON (tbservidor.idPerfil = tbperfil.idPerfil)
               WHERE (tbservidor.idPerfil = 1 OR tbservidor.idPerfil = 4)
                 AND tbhistlot.data = (select min(data) from tbhistlot where tbhistlot.idServidor = tbservidor.idServidor)
                 AND (tblotacao.UADM = "FENORTE" OR tblotacao.UADM = "TECNORTE")
                 AND idConcurso = ' . $parametroConcurso . ' 
            ORDER BY tbservidor.situacao, tbpessoa.nome';
        
    $result = $servidor->select($select);

    $relatorio = new Relatorio();
    $relatorio->set_titulo('Relatório de Servidores Ex-Fenorte');

    $relatorio->set_subtitulo('Concurso de ' . $concurso->get_nomeConcurso($parametroConcurso));
    $relatorio->set_label(array('IdFuncional', 'Nome', 'Cargo', 'Concurso', 'Perfil', 'Admissão', 'Saída', 'Situação'));
    $relatorio->set_width(array(10, 30, 20, 20, 10, 10));
    $relatorio->set_align(array("center", "left", "left"));
    $relatorio->set_funcao(array(null, null, null, null, null, "date_to_php", "date_to_php"));

    $relatorio->set_classe(array("pessoal", null, "pessoal", "Concurso"));
    $relatorio->set_metodo(array("get_idFuncionalEMatricula", null, "get_cargoSimples", "get_nomeConcurso"));

    $relatorio->set_conteudo($result);
    $relatorio->set_bordaInterna(true);

    # Combo de concurso
    $select = "SELECT idconcurso,
                      concat(anoBase,' - Edital: ',DATE_FORMAT(dtPublicacaoEdital,'%d/%m/%Y')) as concurso
                 FROM tbconcurso
                WHERE tipo = 1     
             ORDER BY dtPublicacaoEdital desc";

    # Pega os dados da combo concurso
    $concursoArray = $servidor->select($select);
    $idConcurso = null;

    array_unshift($concursoArray, array(null, null));

    $relatorio->set_formCampos(array(
        array('nome' => 'concurso',
            'label' => 'Concurso:',
            'tipo' => 'combo',
            'autofocus' => true,
            'array' => $concursoArray,
            'col' => 6,
            'size' => 10,
            'padrao' => $parametroConcurso,
            'title' => 'Filtra por Concurso.',
            'onChange' => 'formPadrao.submit();',
            'linha' => 1)));

    $relatorio->set_formFocus('ano');
    $relatorio->set_formLink('?');
    $relatorio->set_numGrupo(7);
    $relatorio->show();

    $page->terminaPagina();
}