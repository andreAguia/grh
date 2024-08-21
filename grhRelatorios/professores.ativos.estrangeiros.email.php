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
                      tbservidor.idServidor,
                      tbpais.pais,
                      tbservidor.idServidor,
                      tbnacionalidade.nacionalidade
                  FROM tbservidor JOIN tbpessoa USING (idPessoa)
                                  JOIN tbperfil USING (idPerfil)  
                                  LEFT JOIN tbnacionalidade ON (tbpessoa.nacionalidade = tbnacionalidade.idNacionalidade)
                                  LEFT JOIN tbpais ON (tbpessoa.paisOrigem = tbpais.idPais)
                                  LEFT JOIN tbcargo USING (idCargo)
                                 JOIN tbtipocargo USING (idTipoCargo)
               WHERE tbservidor.situacao = 1
                 AND tbperfil.tipo <> "Outros"
                 AND tbtipocargo.tipo = "Professor"
                 AND tbnacionalidade.nacionalidade <> "Brasileira"
            ORDER BY tbnacionalidade.nacionalidade, tbpessoa.nome';

    $result = $servidor->select($select);

    $relatorio = new Relatorio();
    $relatorio->set_titulo('Relatório de Professores Estrangeiros Ativos');
    // $relatorio->set_tituloLinha2('Agrupado pela Nacionalidade');
    $relatorio->set_subtitulo("Ordenados pelo Nome");
    $relatorio->set_label(['IdFuncional', 'Nome', 'Cargo', 'Email', 'País de Origem', 'Perfil', 'Nacionalidade']);
    $relatorio->set_align(["center", "left", "left"]);

    $relatorio->set_classe([null, null, "pessoal", "pessoal", null, "pessoal"]);
    $relatorio->set_metodo([null, null, "get_CargoSimples", "get_emailUenf", null, "get_perfil"]);

    $relatorio->set_conteudo($result);
    $relatorio->set_numGrupo(6);
    $relatorio->show();

    $page->terminaPagina();
}