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
    $pessoal = new Pessoal();

    # Começa uma nova página
    $page = new Page();
    $page->set_title("Servidores com Auxílio Creche");
    $page->iniciaPagina();

    # Corpo do relatorio
    $select = 'SELECT tbservidor.idfuncional,
                      tbpessoa.nome,
                      tbdependente.nome,
                      tbdependente.dtNasc,
                      YEAR(CURDATE( )) - YEAR(tbdependente.dtNasc) - IF(RIGHT(CURDATE( ),5) < RIGHT(tbdependente.dtNasc,5),1,0),
                      tbdependente.processo,                 
                      tbdependente.dttermino,
                      tbdependente.ciExclusao                 
                 FROM tbservidor LEFT JOIN tbpessoa ON (tbservidor.idPessoa = tbpessoa.idPessoa)
                                      JOIN tbdependente ON (tbdependente.idPessoa = tbpessoa.idPessoa)
                                      JOIN tbparentesco ON (tbparentesco.idParentesco = tbdependente.idParentesco)
                                      JOIN tbperfil USING (idPerfil)
                 WHERE tbservidor.situacao = 1
                   AND tbperfil.tipo <> "Outros"
                   AND tbdependente.idParentesco = 2
                   AND auxCreche = "Sim"
                   AND dtTermino >= CURDATE()
              ORDER BY tbpessoa.nome';

    $result = $pessoal->select($select);

    $relatorio = new Relatorio();
    $relatorio->set_titulo('Relatório Geral do Auxílio Creche de Servidores Ativos');
    $relatorio->set_subtitulo('(Servidores que estão recebendo)');
    $relatorio->set_label(['IdFuncional', 'Servidor', 'Nome do Filho(a)', 'Nascimento', 'Idade', 'Processo', 'Término', 'Documento Exclusão']);
    $relatorio->set_width([5, 20, 25, 10, 5, 15, 10, 10]);
    $relatorio->set_align(["center", "left", "left"]);
    $relatorio->set_funcao([null, null, null, "date_to_php", null, null, "date_to_php"]);
    $relatorio->set_conteudo($result);
    $relatorio->set_dataImpressao(false);
    $relatorio->show();

    ######################################
    # Corpo do relatorio
    $select = 'SELECT tbservidor.idfuncional,
                     tbpessoa.nome,
                     tbdependente.nome,
                     tbdependente.dtNasc,
                     YEAR(CURDATE( )) - YEAR(tbdependente.dtNasc) - IF(RIGHT(CURDATE( ),5) < RIGHT(tbdependente.dtNasc,5),1,0),
                     tbdependente.processo,                 
                     tbdependente.dttermino,
                     tbdependente.ciExclusao                 
                FROM tbservidor LEFT JOIN tbpessoa ON (tbservidor.idPessoa = tbpessoa.idPessoa)
                                        JOIN tbdependente ON (tbdependente.idPessoa = tbpessoa.idPessoa)
                                        JOIN tbparentesco ON (tbparentesco.idParentesco = tbdependente.idParentesco)
               WHERE tbdependente.idParentesco = 2
                 AND tbservidor.situacao=1
                 AND auxCreche = "Sim"
                 AND dtTermino < CURDATE()
            ORDER BY tbpessoa.nome';

    $result = $pessoal->select($select);

    $relatorio = new Relatorio();
    $relatorio->set_titulo('Relatório Geral do Auxílio Creche de Servidores Ativos');
    $relatorio->set_subtitulo('(Servidores que já receberam mais NÂO estão mais recebendo)');
    $relatorio->set_label(['IdFuncional', 'Servidor', 'Nome do Filho(a)', 'Nascimento', 'Idade', 'Processo', 'Término', 'Documento Exclusão']);
    $relatorio->set_width([5, 20, 25, 10, 5, 15, 10, 10]);
    $relatorio->set_align(["center", "left", "left"]);
    $relatorio->set_funcao([null, null, null, "date_to_php", null, null, "date_to_php"]);
    $relatorio->set_conteudo($result);
    $relatorio->set_cabecalhoRelatorio(false);
    $relatorio->set_menuRelatorio(false);
    $relatorio->show();

    $page->terminaPagina();
}