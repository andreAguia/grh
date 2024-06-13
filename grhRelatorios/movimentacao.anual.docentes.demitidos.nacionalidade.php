<?php

/**
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
                      tbpessoa.naturalidade,
                      tbperfil.nome,
                      tbservidor.dtAdmissao,
                      tbservidor.dtDemissao,
                      tbmotivo.motivo,
                      MONTH(tbservidor.dtDemissao)
                FROM tbservidor JOIN tbpessoa USING (idPessoa)                                
                                JOIN tbperfil USING (idPerfil)
                                LEFT JOIN tbmotivo ON (tbservidor.motivo = tbmotivo.idMotivo)
                 WHERE YEAR(tbservidor.dtDemissao) = '{$relatorioAno}'
                  AND (tbservidor.idCargo = 128 OR tbservidor.idCargo = 129)
                  AND tbperfil.tipo <> 'Outros'  
             ORDER BY MONTH(tbservidor.dtDemissao), dtadmissao";

    $result = $servidor->select($select);

    $relatorio = new Relatorio();
    $relatorio->set_titulo("Relatório Anual de Docentes");
    $relatorio->set_tituloLinha2("Demitidos, Aposentados ou Exonerados em {$relatorioAno}");
    $relatorio->set_subtitulo('Ordenado pela Data de Saída');

    $relatorio->set_label(['IdFuncional', 'Nome', 'Nacionalidade', 'Naturalidade', 'Perfil', 'Admissão', 'Demissão', 'Motivo', 'Mês']);
    $relatorio->set_align(['center', 'left', 'center', 'center', 'center', 'center', 'center', 'left']);
    $relatorio->set_funcao([null, null, null, null, null, "date_to_php", "date_to_php", null, "get_NomeMes"]);

    $relatorio->set_classe([null, null, "pessoal"]);
    $relatorio->set_metodo([null, null, "get_nacionalidade"]);

    $relatorio->set_conteudo($result);
    $relatorio->set_numGrupo(8);
    $relatorio->set_formCampos(array(
        array('nome' => 'ano',
            'label' => 'Ano:',
            'tipo' => 'texto',
            'size' => 4,
            'title' => 'Ano',
            'onChange' => 'formPadrao.submit();',
            'padrao' => $relatorioAno,
            'col' => 3,
            'linha' => 1)));

    $relatorio->show();
    $page->terminaPagina();
}
