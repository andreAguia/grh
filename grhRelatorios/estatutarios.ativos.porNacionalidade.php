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
                      tbpais.pais,
                      tbnacionalidade.nacionalidade
                 FROM tbservidor JOIN tbpessoa USING (idPessoa)   
                                 LEFT JOIN tbnacionalidade ON (tbpessoa.nacionalidade = tbnacionalidade.idNacionalidade)
                                 LEFT JOIN tbpais ON (tbpessoa.paisOrigem = tbpais.idPais)
               WHERE situacao = 1
                 AND idPerfil = 1
            ORDER BY tbnacionalidade.nacionalidade, tbpessoa.nome';

    $result = $servidor->select($select);

    $relatorio = new Relatorio();
    $relatorio->set_titulo('Relatório de Servidores Estatutários Ativos');
    $relatorio->set_subtitulo('Agrupados por Nacionalidade - Ordenados pelo Nome');
    $relatorio->set_label(['IdFuncional', 'Nome', 'Cargo', 'País de Origem', 'Nacionalidade']);
    $relatorio->set_align(["center", "left", "left"]);

    $relatorio->set_classe([null, null, "pessoal"]);
    $relatorio->set_metodo([null, null, "get_Cargo"]);
    $relatorio->set_funcao([null, null, null, null, "trataNulo"]);

    $relatorio->set_conteudo($result);
    $relatorio->set_numGrupo(4);
    $relatorio->show();

    $page->terminaPagina();
}