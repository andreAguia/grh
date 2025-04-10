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
    $page->set_title("Relatório Geral de Servidores Aposentados");
    $page->iniciaPagina();

    ######

    $select = 'SELECT tbservidor.idFuncional,
                      tbservidor.idServidor,
                      tbservidor.dtAdmissao,
                      tbservidor.dtdemissao,
                     tbservidor.idServidor
                 FROM tbservidor LEFT JOIN tbpessoa USING (idPessoa)
                                 LEFT JOIN tbcargo USING (idCargo)
                                      JOIN tbtipocargo USING (idTipoCargo)
                                 LEFT JOIN tbperfil ON (tbservidor.idPerfil = tbperfil.idPerfil)
                WHERE tbservidor.situacao = 2
                  AND (tbservidor.idPerfil = 1 OR tbservidor.idPerfil = 4) 
             ORDER BY tbpessoa.nome';

    $result = $servidor->select($select);

    $relatorio = new Relatorio();
    $relatorio->set_titulo('Relatório Geral de Servidores Aposentados');
    $relatorio->set_subtitulo("Ordenados pelo Nome");
    $relatorio->set_label(['IdFuncional', 'Servidor', 'Admissão', 'Aposentadoria', 'Tipo']);
    $relatorio->set_align(["center", "left", "center", "center", "left"]);
    $relatorio->set_funcao([null, null, "date_to_php", "date_to_php"]);
    $relatorio->set_width([10, 30, 10, 10, 30]);
    $relatorio->set_bordaInterna(true);

    $relatorio->set_classe([null, "pessoal", null, null, "Aposentadoria"]);
    $relatorio->set_metodo([null, "get_nomeECargo", null, null, "get_tipoAposentadoria"]);

    $relatorio->set_conteudo($result);
    #$relatorio->set_numGrupo(3);
    #$relatorio->set_botaoVoltar('../sistema/areaServidor.php');
    $relatorio->show();

    $page->terminaPagina();
}