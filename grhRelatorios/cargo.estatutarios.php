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
    $cargo = get('cargo', post('cargo'));

    # Verifica, pelo get, qual rotina chamou o relatório
    if (is_null(get('cargo'))) {
        $exibeCombo = true;
    } else {
        $exibeCombo = false;
    }

    ######

    $select = 'SELECT tbservidor.idFuncional,
                     tbpessoa.nome,
                     CONCAT(tbtipocargo.cargo," - ",tbcargo.nome),
                     tbservidor.idServidor,
                     tbperfil.nome,
                     tbservidor.dtAdmissao,
                     tbservidor.idServidor
                FROM tbservidor LEFT JOIN tbpessoa ON (tbservidor.idPessoa = tbpessoa.idPessoa)
                                LEFT JOIN tbcargo ON (tbservidor.idCargo = tbcargo.idCargo)
                                LEFT JOIN tbtipocargo ON (tbcargo.idtipocargo = tbtipocargo.idtipocargo)
                                LEFT JOIN tbsituacao ON (tbservidor.situacao = tbsituacao.idSituacao)
                                LEFT JOIN tbperfil ON (tbservidor.idPerfil = tbperfil.idPerfil)
                WHERE tbservidor.situacao = 1 
                  AND tbservidor.idPerfil = 1
                  AND tbcargo.idcargo="' . $cargo . '"
             ORDER BY tbpessoa.nome';

    $result = $servidor->select($select);

    $relatorio = new Relatorio();
    $relatorio->set_titulo('Relatório de Estatutários');
    $relatorio->set_subtitulo('Ordenados pelo Nome');
    $relatorio->set_label(['IdFuncional', 'Nome', 'Cargo', 'Lotação', 'Perfil', 'Admissão', 'Situação']);
    #$relatorio->set_width([10, 30, 0, 30, 10, 10, 10]);
    $relatorio->set_align(["center", "left", "left", "left"]);
    $relatorio->set_funcao([null, null, null, null, null, "date_to_php"]);
    
    $relatorio->set_classe([null, null, null, "Pessoal", null, null, "Pessoal"]);
    $relatorio->set_metodo([null, null, null, "get_lotacao", null, null, "get_Situacao"]);
    
    $relatorio->set_conteudo($result);
    $relatorio->set_numGrupo(2);
    $relatorio->set_subTotal(false);

    if ($exibeCombo) {
        $listaCargo = $servidor->select('SELECT idcargo, CONCAT(tbtipocargo.cargo," - ",tbcargo.nome)
                                           FROM tbcargo LEFT JOIN tbtipocargo USING(idtipocargo)
                                          ORDER BY tbtipocargo.cargo,tbcargo.nome');
        array_unshift($listaCargo, array('*', '-- Selecione o Cargo --'));

        $relatorio->set_formCampos(array(
            array('nome' => 'cargo',
                'label' => 'Cargo:',
                'tipo' => 'combo',
                'array' => $listaCargo,
                'size' => 30,
                'padrao' => $cargo,
                'onChange' => 'formPadrao.submit();',
                'linha' => 1)));       
    }
    $relatorio->show();
    $page->terminaPagina();
}