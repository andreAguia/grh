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
                     CONCAT(endereco," - ",bairro) as resumo,
                     tbperfil.nome,
                     CONCAT(tbestado.uf," - ",tbcidade.nome)
                FROM tbservidor LEFT JOIN tbpessoa USING (idPessoa)
                                     JOIN tbcidade USING (idCidade)
                                     JOIN tbestado USING (idEstado)
                                LEFT JOIN tbperfil ON (tbservidor.idPerfil = tbperfil.idPerfil)
               WHERE tbservidor.situacao = 1 
                 AND tbservidor.idPerfil <> 10
            ORDER BY tbestado.uf,tbcidade.nome,tbpessoa.nome';

    $result = $servidor->select($select);

    $relatorio = new Relatorio();
    $relatorio->set_titulo('Relatório de Servidores Ativos Com Endereço');
    $relatorio->set_subtitulo('Agrupado por Cidade e Ordenado pelo nome');
    $relatorio->set_label(array('IdFuncional', 'Nome', 'Cargo', 'Lotação', 'Endereço', 'Perfil', 'Cidade'));
    #$relatorio->set_width(array(10,30,30,0,10,10,10));
    $relatorio->set_align(array("center", "left", "left", "left", "left"));
    #$relatorio->set_funcao(array(null,"dv"));

    $relatorio->set_classe(array(null, null, "pessoal", "pessoal"));
    $relatorio->set_metodo(array(null, null, "get_cargo", "get_lotacao"));

    $relatorio->set_conteudo($result);
    $relatorio->set_numGrupo(6);
    #$relatorio->set_botaoVoltar('../sistema/areaServidor.php');
    $relatorio->show();

    $page->terminaPagina();
}