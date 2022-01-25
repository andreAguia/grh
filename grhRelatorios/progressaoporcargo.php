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
$acesso = Verifica::acesso($idUsuario, 2);

if ($acesso) {
    # Conecta ao Banco de Dados
    $servidor = new Pessoal();

    # Começa uma nova página
    $page = new Page();
    $page->iniciaPagina();

    # Pega os parâmetros dos relatórios
    $parametroCargo = post('parametroCargo', get_session('parametroCargo', 6));

    ######

    $select = 'SELECT tbservidor.idFuncional,
                     tbservidor.idServidor,
                     tbservidor.idServidor,
                     tbservidor.idServidor,
                     tbservidor.idServidor,
                     tbtipocargo.cargo,
                     tbservidor.idServidor
                FROM tbservidor LEFT JOIN tbpessoa USING (idPessoa)
                                LEFT JOIN tbperfil USING (idPerfil)
                                LEFT JOIN tbcargo USING (idCargo)                     
                                     JOIN tbtipocargo USING (idTipoCargo) 
               WHERE tbservidor.situacao = 1
                 AND (idPerfil = 1 OR idPerfil = 4)
                 AND tbtipocargo.idTipoCargo = "' . $parametroCargo . '"
            ORDER BY tbpessoa.nome';

    $result = $servidor->select($select);

    $relatorio = new Relatorio();
    $relatorio->set_titulo('Relatório de Administrativos e Técnicos Ativos');
    $relatorio->set_tituloLinha2('Com a Última Progressão');
    $relatorio->set_subtitulo('Agrupados por Escolaridade do Cargo - Ordenados pelo Nome');
    $relatorio->set_label(array('IdFuncional', 'Servidor', 'Salário Atual', 'Data Inicial', 'Análise', "Cargo"));
    $relatorio->set_align(array("center", "left", "left"));
    $relatorio->set_funcao(array(null, null, "exibeDadosSalarioAtual"));

    $relatorio->set_classe(array(null, "pessoal", null, "Progressao", "Progressao"));
    $relatorio->set_metodo(array(null, "get_nomeECargoELotacao", null, "get_dtInicialAtual", "analisaServidor"));

    $relatorio->set_bordaInterna(true);
    $relatorio->set_conteudo($result);
    $relatorio->set_numGrupo(5);

    # Pega os dados da combo cargo
    $result = $servidor->select('SELECT idTipoCargo, 
                                       cargo
                                  FROM tbtipocargo
                              ORDER BY idTipoCargo');

    # Formulário de Pesquisa
    $relatorio->set_formCampos(array(
        array('nome' => 'parametroCargo',
            'label' => 'Cargo:',
            'tipo' => 'combo',
            'array' => $result,
            'size' => 30,
            'col' => 6,
            'padrao' => $parametroCargo,
            'onChange' => 'formPadrao.submit();',
            'linha' => 1)));

    $relatorio->set_formFocus('parametroCargo');
    $relatorio->set_formLink('?');

    $relatorio->show();

    $page->terminaPagina();
}