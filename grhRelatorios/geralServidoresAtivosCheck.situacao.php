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

    $select = 'SELECT "[ "," ]",
                     tbservidor.idFuncional,
                     tbpessoa.nome,
                     tbservidor.idServidor,
                     tbservidor.idServidor,
                     tbservidor.idServidor
                FROM tbservidor LEFT JOIN tbpessoa ON (tbservidor.idPessoa = tbpessoa.idPessoa)
               WHERE tbservidor.situacao = 1
                 AND tbservidor.idPerfil <> 10
            ORDER BY tbpessoa.nome';

    $result = $servidor->select($select);

    $relatorio = new Relatorio();
    $relatorio->set_titulo('Relatório Geral de Servidores Ativos');
    $relatorio->set_subtitulo('Com Afastamento em '.date("d/m/Y"));
    $relatorio->set_label(['', '', 'IdFuncional', 'Nome', 'Lotação', 'Perfil', 'Afastamento']);
    $relatorio->set_width([3, 3, 10, 30, 20, 10, 24]);
    $relatorio->set_align(["center", "center", "center", "left", "left", "center","left"]);

    $relatorio->set_classe([null, null, null, null, "pessoal", "pessoal"]);
    $relatorio->set_metodo([null, null, null, null, "get_lotacao", "get_perfil"]);
    $relatorio->set_funcao([null, null, null, null, null, null, "get_afastamento"]);

    $relatorio->set_bordaInterna(true);

    $relatorio->set_conteudo($result);
    #$relatorio->set_numGrupo(4);
    #$relatorio->set_botaoVoltar('../sistema/areaServidor.php');
    $relatorio->show();

    $page->terminaPagina();
}
?>
