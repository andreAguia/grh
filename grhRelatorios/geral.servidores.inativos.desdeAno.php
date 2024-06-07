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
    $relatorioAno = post('ano', date('Y'));

    ######

    $select = "SELECT tbservidor.idfuncional,
                      tbpessoa.nome,
                      tbservidor.idServidor,
                      tbservidor.dtDemissao,
                      tbservidor.idServidor
                FROM tbservidor JOIN tbpessoa USING (idPessoa)
                                JOIN tbdocumentacao USING (idPessoa)
                                JOIN tbcargo USING (idCargo)
                                JOIN tbtipocargo USING (idTipoCargo)
               WHERE YEAR(tbservidor.dtDemissao) >= '{$relatorioAno}'
                 AND (tbservidor.idPerfil = 1 OR tbservidor.idPerfil = 4)
            ORDER BY tbservidor.dtDemissao";

    $result = $servidor->select($select);

    $relatorio = new Relatorio();
    $relatorio->set_titulo('Relatório de Servidores Inativos');
    $relatorio->set_tituloLinha2("Desde {$relatorioAno}");
    $relatorio->set_subtitulo('Ordenados pela Data de Saída');

    $relatorio->set_label(["IdFuncional", "Nome", "Cargo", "Saída", "Situação"]);
    $relatorio->set_align(["center", "left", "left"]);
    $relatorio->set_funcao([null, null, null, "date_to_php"]);

    $relatorio->set_classe([null, null, "pessoal", null, "pessoal"]);
    $relatorio->set_metodo([null, null, "get_cargo", null, "get_Situacao"]);

    $relatorio->set_conteudo($result);
    $relatorio->set_formCampos(array(
        array('nome' => 'ano',
            'label' => 'Desde:',
            'tipo' => 'texto',
            'size' => 4,
            'title' => 'Ano',
            'onChange' => 'formPadrao.submit();',
            'padrao' => $relatorioAno,
            'col' => 3,
            'linha' => 1)));

    $relatorio->set_formFocus('ano');
    
    $relatorio->show();

    $page->terminaPagina();
}