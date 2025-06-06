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
    $parametroMotivo = get_session('parametroMotivo', 3);
    $nome = $servidor->get_motivoAposentadoria($parametroMotivo);

    ######
    # Monta o select
    $select = 'SELECT tbservidor.idfuncional,
                          tbpessoa.nome,
                          tbservidor.idServidor,
                          tbservidor.dtAdmissao,
                          tbservidor.dtDemissao,
                          tbservidor.idServidor
                     FROM tbservidor LEFT JOIN tbpessoa USING (idPessoa)
                                     LEFT JOIN tbmotivo USING (idMotivo)
                    WHERE tbservidor.idMotivo = ' . $parametroMotivo . '
                      AND situacao = 2
                      AND (tbservidor.idPerfil = 1 OR tbservidor.idPerfil = 4)
                 ORDER BY dtDemissao';


    $result = $servidor->select($select);

    $relatorio = new Relatorio();
    $relatorio->set_titulo('Servidores Estatutários / Celetistas Aposentados');
    $relatorio->set_tituloLinha2($nome);
    $relatorio->set_subtitulo('Ordenado pela Data de Saída');

    $relatorio->set_label(array('IdFuncional', 'Nome', 'Cargo', 'Admissão', 'Saída', 'Perfil'));
    #$relatorio->set_width(array(10,20,10,10,10,10,10,10,10));
    $relatorio->set_align(array('center', 'left', 'left'));
    $relatorio->set_funcao(array(null, null, null, "date_to_php", "date_to_php"));

    $relatorio->set_classe(array(null, null, "pessoal", null, null, "pessoal"));
    $relatorio->set_metodo(array(null, null, "get_cargo", null, null, "get_perfil"));

    $relatorio->set_conteudo($result);
    $relatorio->show();

    $page->terminaPagina();
}
?>
