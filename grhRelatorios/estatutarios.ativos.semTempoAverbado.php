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

    ######

    $select = 'SELECT tbservidor.idFuncional,
                      tbpessoa.nome,
                      tbservidor.idServidor,
                      tbservidor.idServidor
                 FROM tbservidor LEFT JOIN tbpessoa USING (idPessoa)
                WHERE idPerfil = 1
                  AND situacao = 1
                  AND tbservidor.idServidor not in (select idServidor FROM tbaverbacao)
            ORDER BY tbpessoa.nome';

    $result = $servidor->select($select);

    $relatorio = new Relatorio();
    $relatorio->set_titulo('Relatório de Servidores Estatutários Ativos');
    $relatorio->set_subtitulo('Sem Tempo Averbado - Ordenado pelo Nome');
    $relatorio->set_label(['IdFuncional', 'Servidor', 'Cargo', 'Lotação']);
    $relatorio->set_width([10,35,25,30]);

    $relatorio->set_classe([null, null, "pessoal", "pessoal"]);
    $relatorio->set_metodo([null, null, "get_cargo", "get_lotacao"]);
    $relatorio->set_align(["center", "left", "left", "left"]);

    $relatorio->set_conteudo($result);
    $relatorio->show();

    $page->terminaPagina();
}