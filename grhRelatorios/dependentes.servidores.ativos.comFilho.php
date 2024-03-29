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

    #####
    # Corpo do relatorio
    $select = 'SELECT tbservidor.idFuncional,
                      tbpessoa.nome,
                      tbdependente.nome,
                      YEAR(CURDATE( )) - YEAR(tbdependente.dtNasc) - IF(RIGHT(CURDATE( ),5) < RIGHT(tbdependente.dtNasc,5),1,0)                 
                 FROM tbservidor LEFT JOIN tbpessoa ON (tbservidor.idPessoa = tbpessoa.idPessoa)
                                      JOIN tbdependente ON (tbdependente.idPessoa = tbpessoa.idPessoa)
                                      JOIN tbparentesco ON (tbparentesco.idParentesco = tbdependente.idParentesco)
                                      JOIN tbperfil USING (idPerfil)
                WHERE tbservidor.situacao = 1
                  AND tbperfil.tipo <> "Outros"
                  AND tbdependente.idParentesco = 2
             ORDER BY tbpessoa.nome';

    $result = $servidor->select($select);

    $relatorio = new Relatorio();
    $relatorio->set_titulo('Relatório Servidores Ativos com Dependentes (Filhos)');
    $relatorio->set_subtitulo('Ordenado pelo Nome do Servidor');
    $relatorio->set_label(['IdFuncional', 'Nome', 'Nome do Filho(a)', 'Idade']);
    $relatorio->set_width([10, 40, 40, 10]);
    $relatorio->set_align(["center", "left", "left"]);
    $relatorio->set_conteudo($result);
    $relatorio->show();

    $page->terminaPagina();
}
